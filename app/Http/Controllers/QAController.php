<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\QcScore;
use App\Models\TaskCallLog;
use App\Models\QcParameter;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\CallAllocation;
use App\Models\UserAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QAController extends Controller
{
    public $user; 
    public $parentIds = [];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->guard('web')->user();
            return $next($request);
        });
    }

    public function index(Request $request, $id = '')
    {   
        if (is_null($this->user) || !$this->user->can('call_allocation.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any allocations !');
        }
        $data['id'] = $id;
        return view('quality_assurance.index', $data);
    }

    public function CallAllocations(Request $request, $id = '')
    {   
        if (is_null($this->user) || !$this->user->can('call_allocation.assign_calls')) {
            abort(403, 'Sorry !! You are Unauthorized to assign any call !');
        }
        $data['id'] = $id;
        return view('quality_assurance.assign_calls', $data);
    }


    public function getCalls(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('call_allocation.view')) {
            abort(403, 'Sorry !! You are Unauthorized to this functionality !');
        }
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $user = $request->input('user');
        $is_assigned = $request->input('is_assigned');
        $sortBy = $request->input('sort_by', 'Date Added');
        $draw = $request->input('draw', 1);
        $user_id = $this->user->id;

        $query = TaskCallLog::with(['user', 'activity.activity.workflow.client', 'assignedCalls']);
        if (!$this->user->can('call_allocation.view_all_allocations')) {
            if ($this->user->can('call_allocation.view_assigned_calls')) {
                $childs = UserAllocation::where('parent_id', $user_id)->pluck('user_id')->toArray();
                $query = $query->whereIn('user_id',$childs);
            } else {
                $query = $query->where('user_id',$user_id);
            }
        }
        $totalRecords = $query->count(); // Total without filters
        // Apply filters
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay()
            ]);
        }

        if (!empty($user)) {
            $query->where('user_id', $user);
        }

        if ($is_assigned !== null && $is_assigned !== '') {
            if ($is_assigned == 'assigned') {
                $query->has('assignedCalls');
            } else {
                $query->doesntHave('assignedCalls');
            }
        }

        // Sorting
        switch ($sortBy) {
            case 'Z to A':
                $query->orderBy('id', 'desc');
                break;
            case 'Date Added':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Clone query for filtered records count
        $filteredQuery = clone $query;

        $totalFilteredRecords = $filteredQuery->count(); // Total after filters

        $calls = $query->skip($start)->take($length)->get();

        $formattedCalls = $calls->map(function ($call) {
            // pre($call->assignedCalls);
        // dd($call);
            return [
                'formatted_id' => sprintf("%05d", $call->id),
                'id' => $call->id,
                'client_name' => optional($call->activity->activity->workflow->client)->client_name,
                'activity_name' => optional($call->activity->activity)->process_name,
                'created_at' => Carbon::parse($call->created_at)->format('d M Y'),
                'user_name' => optional($call->user)->name,
                'assigned_agent_id' => !is_null($call->assignedCalls) ? $call->assignedCalls->qc_agent_id : ''
            ];
        });
        // die;
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $formattedCalls
        ]);
    }

    public function getAssignedCalls(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $user = $request->input('user');
        $is_assigned = $request->input('is_assigned');
        $sortBy = $request->input('sort_by', 'Date Added');
        $draw = $request->input('draw', 1);
        $calls_ids = CallAllocation::where('status','<>','completed');
        $currentUserRoles = $this->user->roles->pluck('id')->toArray();
        if (!in_array(1, $currentUserRoles)) {
            $calls_ids = $calls_ids->where('qc_agent_id', $this->user->id);
        }
        $calls_ids = $calls_ids->pluck('call_id')->toArray();

        $query = TaskCallLog::with(['user', 'activity.activity.workflow.client', 'assignedCalls.score'])->whereIn('id', $calls_ids);

        $totalRecords = $query->count(); // Total without filters
        // Apply filters
        if (!empty($start_date) && !empty($end_date)) {
            $query->whereBetween('created_at', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay()
            ]);
        }

        if (!empty($user)) {
            $query->where('user_id', $user);
        }

        if ($is_assigned !== null && $is_assigned !== '') {
            if ($is_assigned == 'assigned') {
                $query->has('assignedCalls');
            } else {
                $query->doesntHave('assignedCalls');
            }
        }

        // Sorting
        switch ($sortBy) {
            case 'Z to A':
                $query->orderBy('id', 'desc');
                break;
            case 'Date Added':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Clone query for filtered records count
        $filteredQuery = clone $query;

        $totalFilteredRecords = $filteredQuery->count(); // Total after filters

        $calls = $query->skip($start)->take($length)->get();
        // dd($calls->assignedCalls->score);

        $formattedCalls = $calls->map(function ($call) {
            // pre($call->assignedCalls);
            return [
                'formatted_id' => sprintf("%05d", $call->id),
                'id' => $call->id,
                'recording_id' => $this->getRecordingId($call),
                'client_name' => optional($call->activity->activity->workflow->client)->client_name,
                'activity_name' => optional($call->activity->activity)->process_name,
                'created_at' => Carbon::parse($call->created_at)->format('d M Y'),
                'user_name' => optional($call->user)->name,
                'assigned_agent_id' => !is_null($call->assignedCalls) ? $call->assignedCalls->qc_agent_id : '',
                'status' => $call->assignedCalls->status
            ];
        });
        // die;
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $formattedCalls
        ]);
    }

    public function getRecordingId($call) {
        $recording_id = null;

        try {
            if (!empty($call->call_details)) {
                $decoded = json_decode($call->call_details, true);
                
                // Check if it's a JSON string inside a JSON string
                if (is_string($decoded)) {
                    $call_details = json_decode($decoded);
                } else {
                    $call_details = (object)$decoded;
                }

                if (
                    isset($call_details->participants[0]->recordings[0]->id)
                ) {
                    $recording_id = $call_details->participants[0]->recordings[0]->id;
                }
            }
        } catch (Exception $e) {
            // Log the error or handle it accordingly
        }
        return $recording_id;

    }


    public function AssignCalls(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('call_allocation.assign_calls')) {
            abort(403, 'Sorry !! You are Unauthorized to assign any call !');
        }
        // Validate inputs
        $validator = Validator::make($request->all(), [
            'user'             => 'required|exists:users,id',
            'selectedValues'   => 'required|array',
            'unselectedValues' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user_id = $request->user;
        $selected = $request->selectedValues;
        $unselected = $request->unselectedValues ?? [];

        $finalIds = [];

        if (!in_array('checkAll', $selected)) {
            $finalIds = $selected;
        } else {
            $allIds = TaskCallLog::pluck('id')->toArray();
            $finalIds = !empty($unselected)
                ? array_diff($allIds, $unselected)
                : $allIds;
        }

        if (empty($finalIds)) {
            return response()->json([
                'status' => false,
                'message' => 'No calls to assign.'
            ], 400);
        }

        $now = now();
        $insertData = [];

        foreach ($finalIds as $callId) {
            $insertData[] = [
                'call_id'     => $callId,
                'qc_agent_id' => $user_id,
                'assigned_by' => $this->user->id,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        // Use upsert to insert or update existing records
        CallAllocation::upsert(
            $insertData,
            ['call_id'],                // Unique key
            ['qc_agent_id', 'updated_at'] // Fields to update
        );

        return response()->json([
            'status'  => true,
            'message' => 'Calls successfully assigned.',
            'data'    => $insertData
        ]);
    }

    public function scoringForm(Request $request, $id = '')
    {
        // Authorization check (uncomment if needed)
        if (is_null($this->user) || !$this->user->can('call_allocation.score_call')) {
            abort(403, 'Sorry !! You are Unauthorized to score any call !');
        }

        // Eager load required relationships
        $allocation = CallAllocation::with([
            'call.activity.activity.workflow.client',
            'qcAgent',
            'score'
        ])->where('call_id', $id)->first();

        if (!$allocation) {
            abort(404, 'Call Allocation not found.');
        }

        $callActivity = $allocation->call->activity->activity ?? null;
        $workflow = $callActivity?->workflow;

        return view('quality_assurance.scoring', [
            'id' => $id,
            'recording_id' =>$this->getRecordingId($allocation->call),
            'allocation_id' => $allocation->id,
            'agent_name' => $allocation->qcAgent->name ?? 'N/A',
            'client_name' => $workflow->client->client_name ?? 'N/A',
            'workflow_name' => $workflow->workflow_name ?? 'N/A',
            'process_name' => $callActivity->process_name ?? 'N/A',
            'score' => $allocation->score,
            'status' => $allocation->status,
            'QcParameter' => QcParameter::where('status', true)->orderBy('order', 'asc')->get(),
        ]);
    }


    public function submitScore(Request $request, $id = '')
    {   
        if (is_null($this->user) || !$this->user->can('call_allocation.score_call')) {
            abort(403, 'Sorry !! You are Unauthorized to this functionality !');
        }
        $request->validate([
            'score' => 'required|numeric',
            'allocation_id' => 'required|numeric',
            'q' => 'required|array',
        ]);
        if ($request->has('status')) {
            CallAllocation::where('id', $request->allocation_id)
                ->update(['status' => $request->status]);
        }
        QcScore::updateOrCreate(
            ['call_allocation_id' => $request->allocation_id], // search condition
            [
                'score' => $request->score,
                'details' => $request->details,
                'parameter_scores' => $request->q,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()->back()->with('success', 'QC Score saved successfully.');
    }

    public function release(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('call_allocation.score_call')) {
            abort(403, 'Sorry !! You are Unauthorized to this functionality !');
        }
        // Validate input
        $request->validate([
            'id' => 'required|numeric',
        ]);

        // Update the `released` field (not `release`) – fix key name if needed
        $updated = CallAllocation::where([
            'call_id' => $request->id,
            'qc_agent_id' => auth()->id(), // or $this->user->id if using injected user
        ])->update(['released' => true]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Call released successfully.']);
        } else {
            return response()->json(['success' => false, 'message' => 'Release failed or already released.'], 422);
        }
    }



}