<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoToConnectService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use App\Models\TaskCallLog;



class GoToConnectController extends Controller
{
    protected $gotoService;

    public function __construct(GoToConnectService $gotoService)
    {
        $this->gotoService = $gotoService;
    }

    /**
     * Handle all GoTo API calls (Auto Auth + Refresh Token)
     */
    public function callGoToApi(Request $request)
    {
       // Session::forget(['goto_access_token', 'goto_refresh_token', 'goto_token_expires']);
        // ✅ Check if access token exists
        if (!session()->has('goto_access_token')) {
            return response()->json([
                'auth_url' => $this->gotoService->getAuthorizationUrl()
            ]);
        }
    
        // ✅ Refresh token if expired
        if (now()->greaterThan(session('goto_token_expires'))) {
            $refreshResponse = $this->gotoService->refreshAccessToken();

            // ❌ If refresh token is also expired, force login
            if (isset($refreshResponse['error'])) {
                session()->forget(['goto_access_token', 'goto_refresh_token', 'goto_token_expires']);
                return response()->json([
                    'auth_url' => $this->gotoService->getAuthorizationUrl()
                ]);
            }
        }
    
        // ✅ Make API request
        $endpoint = $request->input('endpoint'); // Example: "/calls"
        $method = $request->input('method', 'GET');
        $data = $request->input('data', []);

        

        $response = $this->gotoService->makeApiRequest($endpoint, $method, $data);
        $body = $response->getContents();

        // ✅ Check if Response is a WAV File (Contains "RIFFdo")
        if (str_starts_with($body, "RIFF")) {
            return response($body, 200)
                ->header('Content-Type', 'audio/wav')
                ->header('Content-Disposition', 'inline; filename="recording.wav"');
        }
        $responseData=json_decode($response, true);
    
        return response()->json($responseData);
    }

    /**
     * Handle GoTo Connect OAuth Callback
     */
    public function handleCallback(Request $request)
    {
        if ($request->has('error')) {
            return response()->json(['error' => $request->error]);
        }
    
        if ($request->has('code')) {
            $response=$this->gotoService->getAccessToken($request->code);
            return "<script>window.close();</script>"; // Close the OAuth tab after success

        }
    
        return "<script>window.close();</script>"; // Close the tab if something goes wrong

    }

    /**
     * Test API Call (Example: Get User Profile)
     */
    public function getUserProfile()
    {
       // Session::forget(['goto_access_token', 'goto_refresh_token', 'goto_token_expires']);

        $response = $this->gotoService->makeApiRequest('/admin/rest/v1/me');
        return response()->json($response);
    }

    public function handleWebRTCCallback(Request $request)
    {
        // Log the incoming WebRTC events
        Log::info('WebRTC Callback Received:', $request->all());

        return response()->json(['message' => 'Callback received']);
    }

    public function saveCallLog(Request $request)
    {

        try {
            /* $request->validate([
                'assignment_id' => 'nullable|integer',
                'call_details' => 'required|json'
            ]); */

            $callLog = TaskCallLog::create([
                'assignment_id' => $request->input('assignment_id'),
                'call_details' => json_encode($request->input('call_details')),
                'notes' => $request->input('notes'),
                'checklist' => json_encode($request->input('checklist')),
                'user_id' => auth()->id(),
            ]);
            
        } catch (\Exception $e) {
            log::error('Failed to save call log', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to save call log', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Call log saved successfully', 'data' => $callLog]);
    }

    public function updateCallLog(Request $request){
        try {
            $callLog = TaskCallLog::find($request->input('id'));
            if($request->has('notes')){
                $callLog->notes = $request->input('notes');
            }
            if($request->has('checklist')){
                $callLog->checklist = json_encode($request->input('checklist'));
            }
            if($request->has('call_details')){
                $callLog->call_details = json_encode($request->input('call_details'));
            }
            $callLog->save();
        } catch (\Exception $e) {
            log::error('Failed to update call log', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to update call log', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Call log updated successfully', 'data' => $callLog]);
    }
}
