<?php

namespace App\Http\Controllers;

use App\Imports\ClientsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use App\Models\AssignActivity;
use App\Models\WorkflowProcessName;
use App\Models\Client;
use App\Models\AssignClient;
use Carbon\Carbon;

class ClientsController extends Controller
{

    public $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->guard('web')->user();
            return $next($request);
        });
    }

    public function index(){
        if (is_null($this->user) || !$this->user->can('client.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any client !');
        }
        return view('Clients.index');
    }

    public function getAllClients(): JsonResponse
    {
        if (is_null($this->user) || !$this->user->can('time_entry.view_all_clients') && !$this->user->can('time_entry.view_assigned_clients')) {
            return response()->json([
                'status' => 'success',
                'data' => [],
            ], 200);
        }
        try {
            $clients = Client::select('id', 'client_name', 'assigned_to');
            if (!is_null($this->user) && !$this->user->can('time_entry.view_all_clients') && $this->user->can('time_entry.view_assigned_clients') || !$this->user->can('time_entry.view_all_clients') && !$this->user->can('time_entry.view_assigned_clients')) {
                $clients = $clients->where(function ($query) {
                    $query->where('assigned_to', $this->user->id)
                          ->orWhereNull('assigned_to');
                });
            }
            $clients = $clients->where('status', '1')->get();
            return response()->json([
                'status' => 'success',
                'data' => $clients,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getclients(Request $request) {
        if (is_null($this->user) || !$this->user->can('client.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any client !');
        }
        // Input parameters for pagination and filtering
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');
        $dateFilter = $request->input('date_filter', 'Show All');
        $sortBy = $request->input('sort_by', 'Date Added');
        $draw = $request->input('draw', 1);

        // Base query for workflows with relationships
        $query = Client::select('id', 'client_name', 'status');

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', '%' . $search . '%');
            });
        }

        // Date filter
        // if ($dateFilter !== 'Show All') {
        //     $query->whereBetween('workflows.created_at', $this->getDateRange($dateFilter));
        // }

        // Sorting logic
        switch ($sortBy) {
            case 'Z to A':
                $query->orderBy('client_name', 'desc');
                break;
            case 'Date Added':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc'); // Default sorting
                break;
        }

        // Total records count before filtering
        $totalRecords = Client::count();

        // Filtered records count
        $totalFilteredRecords = $query->count();

        // Paginate the results
        $workflows = $query->skip($start)->take($length)->get();
        // echo '<pre>'; print_r($workflows); die;
        // Format the results
        $formattedWorkflows = $workflows->map(function ($workflow) {
            return [
                'formatted_id' => sprintf("%05d", $workflow->id),
                'id' => $workflow->id,
                'client_name' => $workflow->client_name,
                'created_at' => Carbon::parse($workflow->created_at)->format('g:i A, d M Y'),
                'status' => $workflow->status ? 'Enabled' : 'Disabled',
                'created_by' => ''
            ];
        });

        // Return response as JSON
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $formattedWorkflows
        ]);
    }

    public function getClientByUser($id) {
        try {
            $data = Client::where(['assigned_to' => $id, 'status' => 1])->select('id','client_name')->get();
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
                'data' => ''
            ], 500);
        }
    }


    /**
     * Show the form to upload the Excel file.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (is_null($this->user) || !$this->user->can('client.create')) {
            abort(403, 'Sorry !! You are Unauthorized to create any client !');
        }
        return view('Clients.edit');
    }

    public function importForm()
    {
        if (is_null($this->user) || !$this->user->can('client.create')) {
            abort(403, 'Sorry !! You are Unauthorized to create any client !');
        }
        return view('Clients.create');
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'client_name' => 'required|string|max:255',
            'status' => 'required|boolean',
        ]);

        // Create a new client record
        $client = Client::create([
            'client_name' => $validatedData['client_name'],
            'status' => $validatedData['status'],
        ]);

        // Redirect or return a response
        return redirect()->route('clients.index')->with('success', 'Client created successfully!');
    }

    /**
     * Handle the import of the Excel file.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('client.create')) {
            abort(403, 'Sorry !! You are Unauthorized to create any client !');
        }
        $request->validate([
            'file' => 'required|mimes:xlsx,csv', // Ensure it's an Excel or CSV file
        ]);

        Excel::import(new ClientsImport, $request->file('file'));
        return back()->with('success', 'Clients imported successfully.');
    }

    public function edit($id)
    {
        if (is_null($this->user) || !$this->user->can('client.edit')) {
            abort(403, 'Sorry !! You are Unauthorized to edit any client !');
        }
        // Fetch the workflow with its related fields, options, and process names
        $client = Client::findOrFail($id);
        return view('Clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        if (is_null($this->user) || !$this->user->can('workflow.edit')) {
            abort(403, 'Sorry !! You are Unauthorized to update the Client !');
        }

        $client = Client::findOrFail($id);
        $client->update([
            'client_name' => $request->input('client_name'),
            'status' => $request->input('status'),
        ]);
        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy($id)
    {
        if (is_null($this->user) || !$this->user->can('client.delete')) {
            abort(403, 'Sorry !! You are Unauthorized to delete any client !');
        }
        $client = Client::findOrFail($id);
        $client->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Client deleted successfully!'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('client.delete')) {
            abort(403, 'Sorry !! You are Unauthorized to delete any client !');
        }
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:clients,id',
        ]);
        Client::destroy($validated['ids']);

        return response()->json([
            'status' => 'success',
            'message' => 'Selected clients deleted successfully!'
        ]);
    }

    public function clientAssign(Request $request) {
        if (is_null($this->user) || !$this->user->can('client.assign')) {
            abort(403, 'Sorry !! You are Unauthorized to assign Clients!');
        }

        // Validate the request data
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'clients' => 'required|array',
            'clients.*' => 'exists:clients,id',
        ]);

        $user_id = $request->get('user_id');
        $clients = $request->input('clients');
        if (!empty($clients)) {
            $Activities = WorkflowProcessName::with('workflow.client')
                ->whereHas('workflow.client', function ($query) use ($clients) {
                    $query->whereIn('id', $clients);
                })->pluck('id');

            $AssignedActivities = AssignActivity::where('user_id',$user_id)->pluck('activity_id')->toArray();
            $insertData = [];
            foreach ($Activities as $activity) {
                if (!in_array($activity, $AssignedActivities)) {
                    $insertData[] = [
                        'user_id'     => $user_id,
                        'activity_id' => $activity,
                        'assigned_by' => auth()->id(),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
            }

            // Insert only if there is data to insert
            if (!empty($insertData)) {
                AssignActivity::insert($insertData);
                $insert_data = array(
                    "type" => "activity",
                    "user_id" => $user_id,
                    "message" => count($clients) == 1 ? "Assigned the client and all associated activities to the user." : ' Assigned the clients and all associated activities to the user.',
                    "url" => url('/time-entry'),
                    "task_id" => ''
                );
                sendNotification($insert_data);
            }
        }

        $assignedClients = AssignClient::where([
                                ['status', '=', null],
                                ['user_id', '=', $user_id],
                                ['assigned_by', '=', $this->user->id]
                            ])
                            // ->whereIn('client_id', $clients)
                            ->pluck('client_id')
                            ->toArray();

        // remove not assigned clients Activities
        $currentAssignedClients = AssignClient::where([
                                ['status', '=', null],
                                ['user_id', '=', $user_id],
                                ['assigned_by', '=', $this->user->id]
                            ])
                            ->pluck('client_id')
                            ->toArray();
        $removedClients = array_diff($currentAssignedClients, $clients);
        $removedActivities = WorkflowProcessName::with('workflow.client')
                ->whereHas('workflow.client', function ($query) use ($removedClients) {
                    $query->whereIn('id', $removedClients);
                })->pluck('id');
        if (!empty($removedActivities)) {
            AssignActivity::where('user_id', $user_id)->whereIn('activity_id', $removedActivities)->delete();
        }

        // 

        $unassignedClients = array_diff($assignedClients, $clients);
        if (!empty($clients)) {
            $clientInsertData = [];
            foreach ($clients as $key => $client) {
                $clientInsertData[] = [
                    'user_id' => $user_id,
                    'client_id' => $client,
                    'assigned_by' => $this->user->id,
                    'status' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($clientInsertData)) {
                AssignClient::insert($clientInsertData);
            }
        }

        if (!empty($unassignedClients)) {
            AssignClient::whereIn('client_id',$unassignedClients)->delete();
        }

        return redirect()->route('users.index')->with('success', 'Clients successfully assigned with all associated activities.');
    }

}
