<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\TaskLog;
use App\Models\Workflow;
use App\Models\TimeEntry;
use App\Models\AssignTask;
use App\Events\MessageSent;
use App\Imports\TaskImport;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\ImportedTask;
use App\Models\AssignActivity;
use App\Models\UserAllocation;
use App\Models\WorkflowFieldValue;
use App\Models\WorkflowProcessName;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Pagination\LengthAwarePaginator;

class UserAllocationController extends Controller
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

    public function index()
    {   
        if (is_null($this->user) || (!$this->user->can('case_allocation.view') && !$this->user->can('case_allocation.view_all_allocations'))) {
            abort(403, 'Sorry !! You are Unauthorized to view any Allocations !');

        }
        return view('user_allocation.index');
    }

    public function assignParent(Request $request)
    {
        if (is_null($this->user) || (!$this->user->can('case_allocation.add'))) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry !! You are Unauthorized to Add any Allocations !'
            ], 403);
        }
        $this->parentIds = [];
        try {
            $request->validate([
                'parent' => 'nullable|exists:users,id',
                'users' => 'required|array',
                'users.*' => 'exists:users,id',
            ]);
            $clientUsers = $request->users;
            $this->getParentIds($clientUsers);
            $existingAllocations = UserAllocation::where('parent_id', $request->parent)->pluck('user_id')->toArray();

            $toDelete = array_diff($existingAllocations, $clientUsers);
            if ($toDelete) {
                UserAllocation::where('parent_id', $request->parent)
                    ->whereIn('user_id', $toDelete)
                    ->delete();
            }

            foreach ($clientUsers as $userId) {
                if (!in_array($userId, $this->parentIds)) {
                    UserAllocation::updateOrCreate(
                        ['user_id' => $userId, 'parent_id' => $request->parent]
                    );
                }
            }

             return response()->json([
                'status' => true,
                'message' => 'success',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
            ], 500);
        }

        // return redirect()->back()->with('success', 'Parent assigned successfully.');
    }

    public function getAllocations(Request $request)
    {
        if (is_null($this->user) || (!$this->user->can('case_allocation.view') && !$this->user->can('case_allocation.view_all_allocations'))) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry !! You are Unauthorized to view any Allocations !',
                'draw' => $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => 0
            ], 200);

        }
        
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search.value');
        $dateFilter = $request->input('date_filter');
        $sortBy = $request->input('sort_by');
        $user_id = $this->user->id;

        $query = User::query()->with(['role','allocatedUsers'])
            ->where('users.id', '<>', auth()->id())
            ->where('users.id', '<>', 1)
            ->select('users.id', 'users.name', 'users.email', 'users.last_login', 'users.status');

        if (!$this->user->can('case_allocation.view_all_allocations')) {
            $userIds = UserAllocation::where('parent_id', $this->user->id)->pluck('user_id')->toArray();
            if (!$this->user->can('case_allocation.manage_team_members')) {
                $Ids = UserAllocation::where('parent_id', $user_id)->pluck('user_id')->toArray();
                $query->whereIn('id', $Ids);
            } elseif (!empty($userIds)) {
                $query->whereIn('id', $userIds);
            }
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', '%' . $search . '%')
                    ->orWhere('users.email', 'like', '%' . $search . '%');
            });
        }

        $totalRecords = User::count();
        $totalFilteredRecords = $query->count(); 
        $users = $query->skip($start)->take($length)->get()->map(function($user){
            $user->roles_list = $user->roles->pluck('name')->join(', ');
            $user->allocated_users_count = count($user->allocatedUsers);

            return $user;
        });


        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $users
        ]);
    }

    public function getAllocatedUsers(Request $request)
    {
        if (is_null($this->user) || (!$this->user->can('case_allocation.view') && !$this->user->can('case_allocation.view_all_allocations'))) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry !! You are Unauthorized to view any Allocations !',
            ], 403);
        }
        try {
            $parentId = $request->id;
            $users = UserAllocation::with('children','parent')->where('parent_id', $parentId)->get();
            if ($users->count()) {
                 foreach ($users as $user) {
                    if ($user->children) {
                        $user->children->children_arr = $this->buildHierarchy($user->children);
                    }
                }
            } else {
                $users = User::find($parentId);
            }
            $html = view('user_allocation.partials.alloted-users', ['users' => $users])->render();

             return response()->json([
                'status' => true,
                'message' => 'success',
                'html' => $html,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
                'html' => '',
            ], 500);
        }
    }

    private function buildHierarchy($children)
    {
        $users = UserAllocation::with('children','parent')->where('parent_id', $children->id)->get();
         foreach ($users as $user) {
            if ($user->children) {
                $user->children->children_arr = $this->buildHierarchy($user->children);
            }
        }
        return $users;
    }

    public function getAllocatedUserlist(Request $request) {
        try {
            $this->parentIds = [];
            $userId = $request->id;

            $selected = UserAllocation::where('parent_id', $userId)->pluck('user_id')->toArray();
            $this->getParentIds([$userId]);

            $excludedIds = [$userId, 1];
            $users = User::usersWithLowerRole(auth()->user())->whereNotIn('id', $excludedIds)->get();

            if (!empty($this->parentIds)) {
                $users = $users->whereNotIn('id', $this->parentIds);
            }
            $html = view('user_allocation.partials.alloted-user-select', ['users' => $users, 'selected' => $selected, 'parent' => $userId])->render();

             return response()->json([
                'status' => true,
                'message' => 'success',
                'html' => $html,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
                'html' => '',
            ], 500);
        }
    }

    public function getParentIds($userIds) {
        $Ids = UserAllocation::whereIn('user_id', $userIds)->pluck('parent_id')->toArray();
        if (!empty($Ids)) {
            $this->parentIds = array_merge($this->parentIds, $Ids);
            $this->getParentIds($Ids);
        }
    }

    public function removeUser(Request $request)
    {
        if (is_null($this->user) || (!$this->user->can('case_allocation.view') && !$this->user->can('case_allocation.view_all_allocations'))) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry !! You are Unauthorized to view any Allocations !',
            ], 403);
        }

        try {
            $userId = $request->id;
            $parent_id = $request->parent;
            if (!$userId || !$parent_id) {
                return response()->json([
                    'status' => false,
                    'message' => 'User ID and Parent ID is required.'
                ], 400);
            }
            $deleted = UserAllocation::where(['user_id'=> $userId, 'parent_id' => $parent_id])->delete();

            if ($deleted) {
                return response()->json([
                    'status' => true,
                    'message' => 'User allocation removed successfully.',
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No allocation found for the given user ID.',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => 'An error occurred while fetching allocated users',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function assignTask() {
        $users = User::where('users.id', '<>', auth()->id())->where('users.id', '<>', 1)->get();
        return view('user_allocation.task.create', compact('users'));
    }

    public function importAssignTask() {
        if (is_null($this->user) || !$this->user->can('case_allocation.view') || !$this->user->can('case_allocation.import_task')) {
            abort(403, 'Sorry !! You are Unauthorized to Import Tasks !');
        }
        return view('user_allocation.task.import');
    }

    public function importTaskList(Request $request) {
        if (is_null($this->user) || !$this->user->can('case_allocation.view') || !$this->user->can('case_allocation.import_task')) {
            abort(403, 'Sorry !! You are Unauthorized to Import Tasks !');
        }
        $file = $request->file('file');
        $userId = auth()->id();
        Excel::import(new TaskImport($userId), $file);
        return redirect()->route('userAllocation.assignTaskss')->with('success', 'Task imported successfully.');
    }

    public function taskStore(Request $request) {
        $processId = $request->process;
        // Fetch the process along with related workflow, fields, and field values
        $process = WorkflowProcessName::with(['workflow.fields', 'workflow.fields.fieldValues'])->find($processId);

        if (!$process) {
            return back()->with('error', 'Process not found.');
        }

        $fields = $process->workflow->fields;

        // Filter fields where fieldValues are empty, then prepare the data for insertion
        $fieldsArray = $fields
            ->filter(fn($field) => $field->fieldValues->isEmpty())
            ->map(fn($field) => [
                'field_id' => $field->id,
                'process_id' => $processId,
                'created_at' => now(),
                'updated_at' => now()
            ])
            ->toArray();

        // Insert data into `WorkflowFieldValue` if the array is not empty
        if (!empty($fieldsArray)) {
            WorkflowFieldValue::insert($fieldsArray);
        }

        // Prepare data to update the process
        $updateData = [
            'priority' => $request->priority,
            'assigned_by' => auth()->id()
        ];

        // Update status if the current status is 'completed' or null
        if (in_array($process->status, ['completed', null])) {
            $updateData['status'] = 'pending';
        }
        WorkflowProcessName::where('id', $process->id)->update($updateData);
        $taskData = [
            'process_id' => $processId,
            'user_id' => $request->user,
            'client_id' => $request->client,
            'activity' => $process->process_name,
            'category' => 'Production Activities',
            'sub_category' => 'Direct Production',
            'type' => 'task',
            'comments' => 'Assign Task',
            'start_time' => now(),
            'end_time' =>now()
        ];
        $existingTask = TimeEntry::create($taskData);
        TaskLog::create([
            'user_id' => $request->user,
            'time_entry_id' => $existingTask->id,
            'action' => 'start_task',
            'status' => 'active',
        ]);

        return back()->with('success', 'Task assigned successfully.');
    }

    public function taskUpdate() {
        echo 'Update Assigned Task';
    }

    public function getUserActivityList($id) {
        // pred($id);
        try {
            $html = '';
            $taskDetailsWithUser = [];
            // $clientIds = Client::where('assigned_to', $id)->pluck('id')->toArray();
            // $clientIds = AssignActivity::with(['activity.workflow.client'])->where('user_id', $id)->get();
            $assignActivities = AssignActivity::with(['activity.workflow.client'])->where('user_id', $id)->get();
            // dd($clientIds);
            // $clientIds = AssignActivity::with(['activity.workflow.client'])
            //     ->where('user_id', $id)
            //     ->get()
            //     ->pluck('activity.workflow.client.id')
            //     ->filter() // Removes nulls if any client is missing
            //     ->unique()
            //     ->values()
            //     ->toArray();
            // // dd($clientIds);
            $user = User::findOrFail($id);
            // if (!empty($clientIds)) {

            //     $workflowIds = Workflow::whereIn('client_id', $clientIds)->pluck('id')->toArray();
            //     $processIds = WorkflowProcessName::whereIn('workflow_id', $workflowIds)->pluck('id')->toArray();
            //     $query = TimeEntry::where('user_id',$id)->whereIn('process_id', $processIds)->with(['user', 'process', 'client']);

            //     $reports = $query->get();
            //     $breakEntries = $reports->where('type', 'break');
            //     $totalTaskTimeSeconds = 0;
            //     foreach ($reports as $task) {

            //         if (in_array($task->type, ['task', 'idle']) && $task->process_id != null) {
            //             $index = $task->user->id.'-'.$task->process_id.'-'.$task->type;
            //             $timeSheet = $this->getEntryTimeSheet($task, $breakEntries);
            //             if (!isset($indexGroup[$index])) {
            //                 $indexGroup[$index] = $task->process_id;
            //                 $taskDetail = [
            //                     'task_id'          => $task->id,
            //                     'process_id'       => $task->process_id,
            //                     'activity'         => $task->activity,
            //                     'status'           => $task->status,
            //                     'priority'         => $task->process->priority,
            //                     'client_name'      => $task->client->client_name ?? '-',
            //                     'category'         => $task->category,
            //                     'sub_category'     => $task->sub_category,
            //                     'start_time'       => date('h:i:s A', strtotime($task->start_time)),
            //                     'end_time'         => date('h:i:s A', strtotime($task->end_time)),
            //                     'date'             => date('d-M-Y', strtotime($task->created_at)),
            //                     'total_task_time'  => '00:00:00',
            //                     'total_break_time' => '00:00:00',
            //                     'active_work_time' => '00:00:00',
            //                 ];
            //                 $taskDetailsWithUser[] = array_merge($taskDetail, $timeSheet);
            //             } else {
            //                 $index = array_search($indexGroup[$index], array_column($taskDetailsWithUser, 'process_id'));
            //                 $old_active_work_time     = timeToSeconds($taskDetailsWithUser[$index]['active_work_time']);
            //                 $current_total_break_time = timeToSeconds($timeSheet['total_break_time']);
            //                 $current_active_work_time = timeToSeconds($timeSheet['active_work_time']);

            //                 $taskDetailsWithUser[$index]['active_work_time'] = secondsToTime($old_active_work_time + $current_active_work_time);
            //                 $taskDetailsWithUser[$index]['total_task_time']  = secondsToTime($old_active_work_time + $current_active_work_time + $current_total_break_time);
            //                 $taskDetailsWithUser[$index]['status']           = $task->status;
            //             }
            //         }
            //     }
            // }
            $html = view('user_allocation.task.activity-list', ['assignActivities' => $assignActivities])->render();

            return response()->json([
                'status' => 'success',
                'html' => $html,
                'user_name' => $user->name
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'user_name' => $user->name
            ], 500);
        }
    }

    public function getUserTaskList($id) {
        try {
            $html = '';
            $user = User::findOrFail($id);
            $assignedTasks = AssignTask::with('task')->where(['assigned_to' => $id, 'archive' => '0'])->get();
            $data = ['records' => []];
            foreach ($assignedTasks as $task) {
                $data['records'][$task->id] = json_decode($task->task->data, true);
                $data['taskIds'][$task->id] = $task->task_id;
            }
            $data['columns'] = array_unique(array_merge(...array_map('array_keys', $data['records'] ?? [])));

            $html = view('user_allocation.task.task-list', ['data' => $data])->render();

            return response()->json([
                'status' => 'success',
                'html' => $html,
                'user_name' => $user->name
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'user_name' => $user->name
            ], 500);
        }
    }

    public function getEntryTimeSheet($task, $breakEntries, $single = false) {
        $taskDetail = [];
        $totalTaskTimeSeconds = 0;
        $taskDetail['total_task_time'] = '00:00:00';
        
            if ($task->end_time && $task->start_time) {
                $start = Carbon::parse($task->start_time);
                $end = Carbon::parse($task->end_time);
                $totalTaskTimeSeconds = $start->diffInSeconds($end);
            }

            $breaks = $breakEntries->where('user_id',$task->user->id)->where('type', 'break')->where('process_id', $task->process_id); // Get all breaks related to this task
            $totalBreakTime = 0;
            foreach ($breaks as $break) {
                if ($break->end_time && $break->start_time) {
                    $start = Carbon::parse($break->start_time);
                    $end = Carbon::parse($break->end_time);
                    $totalBreakTime += $start->diffInSeconds($end);
                }
            }
            
            $taskDetail['total_break_time'] = gmdate("H:i:s", $totalBreakTime);
            $taskDetail['active_work_time'] = gmdate("H:i:s", $totalTaskTimeSeconds);
            $taskDetail['total_task_time'] = gmdate("H:i:s", $single ? $totalTaskTimeSeconds : ($totalTaskTimeSeconds + $totalBreakTime));
            return $taskDetail;
    }

    public function getUserTaskDetails($id) {
        try {

            $taskValues = WorkflowFieldValue::with('workflowField')->where('process_id', $id)->get();
            $html = view('user_allocation.task.task-details', ['taskValues' => $taskValues])->render();

            return response()->json([
                'status' => 'success',
                'html' => $html,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function assignActivities(Request $request, $id) {
        if (is_null($this->user) || !$this->user->can('case_allocation.assign_activities')) {
            abort(403, 'Sorry !! You are Unauthorized to assign any Activity !');
        }

        $currentUserRoles = $this->user->roles->pluck('id')->toArray();
        // pred($currentUserRoles);


        if ($request->isMethod('get')) {
            $user = User::findOrFail($id);
            $Data = Client::with([
                'workflows.processNames',
                'workflows.fields.fieldValues',
                'workflows.processNames.assignActivities'
            ]);
            if (!in_array(1, $currentUserRoles)) {
                $Data->whereHas('assignedTo', function ($query) use ($id ,$currentUserRoles) {
                        $query->where('user_id', auth()->id());
                });
            }
            $Data = $Data->get();
            $workflows = [];
            $data = [];
            foreach ($Data as $client) {
                foreach ($client->workflows ?? [] as $workflow) {
                    // Collect working task IDs
                    $working_tasks = [];
                    foreach ($workflow->fields ?? [] as $field) {
                        foreach ($field->fieldValues ?? [] as $fieldValue) {
                            $working_tasks[] = $fieldValue->process_id;
                        }
                    }

                    // Collect process names
                    foreach ($workflow->processNames ?? [] as $process_name) {
                        $todayActivities = $process_name->assignActivities->where('created_at', '>=', now()->startOfDay())->where('user_id', $id)->where('activity_id',$process_name->id)->count();
                        $data['clients'][$client->id] = $client->client_name;
                        $data['workflows'][$workflow->id] = (object) array(
                            'client_id' => $client->id,
                            'name' => $workflow->workflow_name,
                        );
                        $data['activities'][$process_name->id] = (object) array(
                            'name' => $process_name->process_name,
                            'workflow_id' => $workflow->id
                        );
                        $workflows[$workflow->workflow_name][] = (object) array(
                            'activity_id'   => $process_name->id,
                            'activity_name' => $process_name->process_name,
                            'workflow_id'   => $workflow->id,
                            'workflow_name' => $workflow->workflow_name,
                            'client_id'     => $client->id,
                            'client_name'   => $client->client_name,
                            'existing_task' => $todayActivities
                        );
                        
                    }

                }
            }
            return view('user_allocation.partials.assign_activity', compact('workflows', 'id', 'user', 'data'));
        }
        if ($request->ajax()) {
            $request->validate([
                'id' => 'required'
            ]);
            // pred($request->all());

            $this->assignActivity([$request->id], $id);
            return response()->json([
                'status' => 'success',
                'message' => 'Activity Assigned successfully.'
            ], 200);
        }

        // Assign task to agents
        $request->validate([
            'selected_activities' => 'required|array',
            'selected_activities.*' => 'integer',
        ]);

        $this->assignActivity($request->selected_activities, $id);
        return redirect()->back()->with('success', 'Task assigned successfully.');
    }

    public function assignTasks(Request $request, $id = '') {
        if ($request->ajax()) {
            // Permission check
            if (is_null($this->user) || !$this->user->can('case_allocation.assign_task')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Sorry! You are unauthorized to assign any task.'
                ], 403);
            }

            // Validation
            $validated = $request->validate([
                'id'       => 'required|integer|exists:imported_tasks,id',
                'user'     => 'required|integer|exists:users,id',
                'priority' => 'nullable|in:1,2,3,4',
            ]);

            // Task assignment
            $message = $this->assignTaskToUser([$validated['id']], $validated['user'], $validated['priority']);

            return response()->json([
                'status'  => 'success',
                'message' => $message
            ], 200);
        }

        if (is_null($this->user) || !$this->user->can('case_allocation.assign_task')) {
            abort(403, 'Sorry !! You are Unauthorized to assign any task !');
        }
        if ($request->isMethod('get')) {
            $data['id'] = $id;
            return view('user_allocation.partials.assign_task', $data);
        }
        $ids = json_decode($request->id);
        if ($request->assign_all) {
            $assignedTaskIds = AssignTask::where(['assigned_to'=> $id, 'archive' => 0])
            ->pluck('task_id')
            ->toArray();

            $ids = ImportedTask::where('imported_by', $this->user->id);
            if (!empty($assignedTaskIds)) {
                $ids->whereNotIn('id', $assignedTaskIds);
            }
            $ids = $ids->pluck('id')->toArray();
        }
        if (!empty($ids)) {
            $this->assignTaskToUser($ids, $id);
        }
        return redirect()->back()->with('success', 'Task assigned successfully.');
    }

    public function BulkAssignTask(Request $request) {
        // Validate user selection
        $user_id = $request->input('user');
        if (!$user_id) {
            return response()->json([
                'status' => false,
                'message' => 'Please select a user first.'
            ], 200);
        }

        // Get selection range and task range
        $selected_tasks = [];
        if ($request->selected_tasks) {
            $selected_tasks = json_decode($request->selected_tasks);
        }

        if (empty($selected_tasks)) {
            return response()->json([
                'status' => false,
                'message' => 'Please select at least one task.'
            ], 200);
        }

        // Assign tasks if any found
        $message = $this->assignTaskToUser($selected_tasks, $user_id, $request->priority);

        return response()->json([
            'status' => true,
            'message' => $message
        ], 200);
    }


    public function getTaskList(Request $request) {
        // Get Route Code
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');

        // Fetch only needed columns and data for optimization
        $query = ImportedTask::with('assignedTask')
        ->where(function ($q) {
            $q->where('imported_by', $this->user->id)
              ->orWhereHas('assignedTask', function ($subQ) {
                  $subQ->where('assigned_to', $this->user->id);
              });
        })->orderBy('id', 'desc');
        if ($request->is_assigned != '') {
            if ($request->is_assigned == 'assigned') {
                $query->whereHas('assignedTask', function ($q) use ($request) {
                    if (!empty($request->user)) {
                        $q->where('assigned_to', $request->user);
                    }
                    if ($request->priority != '') {
                        $q->where('priority', $request->priority);
                    }
                });
            } else {
                $query->whereDoesntHave('assignedTask');
            }
        } elseif (!empty($request->user) || $request->priority != '') {
            $query->whereHas('assignedTask', function ($q) use ($request) {
                if (!empty($request->user)) {
                    $q->where('assigned_to', $request->user);
                }
                if ($request->priority != '') {
                    $q->where('priority', $request->priority);
                }
            });
        }

        if (!empty($startDate) && !empty($endDate)) {
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
            if ( $startDate != $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            } else {
                 $query->whereDate('created_at', $startDate);
            }
        }

        $totalCount = $query->count();
        $allTasks = $query->get();
        $tasks = $query->offset($offset)
            ->limit($perPage)
            ->get();
        $totalPages = ceil($totalCount / $perPage);
        $data['total'] = $totalCount;
        $data['records'] = $tasks->mapWithKeys(fn($task) => [$task->id => json_decode($task->data, true)])->all();
       
        $data['assigned_tasks'] = $allTasks->mapWithKeys(fn($task) => [$task->id => $task->assignedTask->count()])->all();
        $data['filter_records'] = [];
        foreach ($tasks as $key => $task) {
            $data['filter_records'][] = $task->id;
        }
        $data['ids'] = [];
        foreach ($allTasks as $key => $task) {
            $data['ids'][] = $task->id;
        }
        // $data['ids'] = $allTasks->mapWithKeys(fn($task) => [$task->id => $task->assignedTask->count()])->all();
        // pred($data['ids']);
        $data['tasks_user'] = $tasks->mapWithKeys(fn($task) => [$task->id => $task->assignedTask->count() ? $task->assignedTask[0]->assigned_to : null])->all();
        $data['columns'] = array_unique(array_merge(...array_map('array_keys', $data['records'])));
        $data['pagination'] = $this->generatePagination($page, $totalPages);
        $data['pagination_info'] = $this->generatePaginationInfo($page, $perPage, $totalCount);
        return response()->json([
            'draw' => $request->input('draw'),
            'status' => true,
            'data' => $data
        ]);
    }

    public function assignTaskToUser($taskIds, $user_id, $priority = 1) {
        $existingAssignments = AssignTask::whereIn('task_id', $taskIds)
            ->whereDate('created_at', now()->toDateString())
            ->where('archive', 0)
            ->get()
            ->keyBy('task_id');

        $insertData = [];
        $updateData = [];

        $insert_data = array(
            "type" => "task",
            "user_id" => $user_id,
            "message" => count($taskIds) == 1 ? " New task assigned by ".Auth()->user()->name."." : count($taskIds).' New tasks Assigned by '.Auth()->user()->name.".",
            "url" => url('/time-entry'),
            "task_id" => $taskIds[0],
            "created_at" => now(),
            "updated_at" => now(),
        );
        sendNotification($insert_data);
        foreach ($taskIds as $task) {
            if (isset($existingAssignments[$task])) {
                $updateData[] = [
                    'id' => $existingAssignments[$task]->id,
                    'assigned_to' => $user_id,
                    'assigned_by' => auth()->id(),
                    'updated_at' => now(),
                ];
            } else {
                $insertData[] = [
                    'assigned_to' => $user_id,
                    'task_id' => $task,
                    'assigned_by' => auth()->id(),
                    'priority' => $priority,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        $message = '';
        $isSingle = count($taskIds) < 2;
        $updatedCount = count($updateData);
        $insertedCount = count($insertData);

        if ($isSingle) {
            $message = $updatedCount
                ? 'Task Reassigned successfully.'
                : 'Task Assigned successfully.';
        } else {
            if ($updatedCount && $insertedCount) {
                $message = "$updatedCount Task(s) Reassigned and $insertedCount Task(s) Assigned successfully.";
            } elseif ($updatedCount) {
                $message = "$updatedCount Task(s) Reassigned successfully.";
            } elseif ($insertedCount) {
                $message = "$insertedCount Task(s) Assigned successfully.";
            }
        }


        // Bulk update existing assignments
        if (!empty($updateData)) {
            foreach ($updateData as $update) {
                AssignTask::where('id', $update['id'])->update([
                    'assigned_to' => $update['assigned_to'],
                    'assigned_by' => $update['assigned_by'],
                    'priority' => $priority,
                    'updated_at' => $update['updated_at'],
                ]);
            }
        }

        // Bulk insert new assignments
        if (!empty($insertData)) {
            AssignTask::insert($insertData);
        }
        return $message;
    }



    function generatePagination($currentPage, $totalPages, $limit = 5) {
        if ($totalPages <= 1) return ''; // No pagination needed for single-page results

        $pagination = '<ul class="pagination">';

        // Previous button
        if ($currentPage > 1) {
            $pagination .= '<li class="paginate_button page-item previous"><a href="?page=' . ($currentPage - 1) . '" data-page="' . ($currentPage - 1) . '" class="page-link"><i class="fa-solid fa-arrow-left"></i> Previous</a></li>';
        }

        // Calculate start and end pages
        $start = max(1, $currentPage - floor($limit / 2));
        $end = min($totalPages, $start + $limit - 1);

        // Adjust start if at the end
        if ($end - $start < $limit - 1) {
            $start = max(1, $end - $limit + 1);
        }
        // pred($end);
        if ($end >= $limit+2) {
            $pagination .= '<li class="paginate_button page-item">
                            <a href="?page=1" data-page="1" class="page-link">1</a>
                        </li>
                        <li class="paginate_button page-item">
                            <a href="#" class="page-link">...</a>
                        </li>';
        }

        // Page number buttons
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $currentPage) ? 'active' : '';
            $pagination .= '<li class="paginate_button page-item ' . $active . '">
                                <a href="?page=' . $i . '" data-page="' . $i . '" class="page-link">' . $i . '</a>
                            </li>';
        }

        // pred($end);
        if ($currentPage+$limit < $totalPages) {
            $pagination .= '<li class="paginate_button page-item">
                                <a href="#" class="page-link">...</a>
                            </li>
                            <li class="paginate_button page-item">
                                <a href="?page=' . $totalPages . '" data-page="' . $totalPages . '" class="page-link">' . $totalPages . '</a>
                            </li>';
        }

        // Next button
        if ($currentPage < $totalPages) {
            $pagination .= '<li class="paginate_button page-item next"><a href="?page=' . ($currentPage + 1) . '" data-page="' . ($currentPage + 1) . '" class="page-link">Next <i class="fa-solid fa-arrow-right"></i></a></li>';
        }

        $pagination .= '</ul>';

        return $pagination;
    }

    function generatePaginationInfo($currentPage, $perPage, $totalRecords) {
        $start = ($currentPage - 1) * $perPage + 1;
        $end = min($currentPage * $perPage, $totalRecords);

        return "Showing $start to $end of $totalRecords entries";
    }

    public function assignActivity($processIds, $user_id) {
        $AssignActivities = AssignActivity::where('created_at', '>=', now()->startOfDay())
            ->where('user_id', $user_id)
            ->pluck('activity_id')
            ->toArray();

        $insertData = [];

        foreach ($processIds as $activity) {
            if (!in_array($activity, $AssignActivities)) {
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
                "message" => count($insertData) == 1 ? "1 Activity Assigned." : count($insertData).' Activities Assigned.',
                "url" => url('/time-entry'),
                "task_id" => ''
            );
            sendNotification($insert_data);
        }
    }

    // public function createReportEntry($processIds, $user_id) {
    //     $processes = WorkflowProcessName::with(['workflow.client', 'workflow.fields'])->whereIn('id', $processIds)->get();

    //     if (!$processes) {
    //         return;
    //     }
    //     $fieldsArray = [];
    //         $taskData = [];
    //     foreach ($processes as $key => $process) {
    //         $client = $process->workflow->client ?? null;
    //         Client::where('id', $client->id)->update(['assigned_to' => $user_id]);
    //         $fieldsArray = array_merge($fieldsArray, $process->workflow->fields->map(fn($field) => [
    //             'field_id' => $field->id,
    //             'process_id' => $process->id,
    //         ])->all());

    //         $taskData[] = [
    //             'process_id' => $process->id,
    //             'user_id' => $user_id,
    //             'client_id' => $client?->id,
    //             'activity' => $process->process_name,
    //             'category' => 'Production Activities',
    //             'sub_category' => 'Direct Production',
    //             'type' => 'task',
    //             'comments' => 'Task',
    //             'start_time' => now(),
    //             'end_time' => now()
    //         ];
    //     }

    //     if (!empty($fieldsArray)) {
    //         WorkflowFieldValue::insert($fieldsArray);
    //         WorkflowProcessName::whereIn('id', $processIds)->update(['status' => 'pending', 'assigned_by' => $this->user->id]);
    //         TimeEntry::insert($taskData);
    //         $timeEntries = TimeEntry::whereIn('process_id', $processIds)->orderBy('id', 'desc')->get()->groupBy('process_id');
    //         if (!empty($timeEntries)) {
    //             $TaskLog = [];
    //             foreach ($timeEntries as $key => $entries) {
    //                 foreach ($entries as $key => $entrie) {
    //                     $TaskLog[] = [
    //                         'user_id' => $user_id,
    //                         'time_entry_id' => $entrie->id,
    //                         'action' => 'start_task',
    //                         'status' => 'active',
    //                     ];
    //                 }
    //             }
    //             TaskLog::insert($TaskLog);
    //         }
    //     }


    // }

}