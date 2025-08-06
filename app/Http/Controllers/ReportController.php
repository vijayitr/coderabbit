<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Client;
use App\Models\Setting;
use App\Models\TimeEntry;
use App\Helpers\TimeHelper;
use App\Models\QcParameter;
use Illuminate\Http\Request;
use App\Models\CallAllocation;
use App\Models\UserAllocation;
use App\Models\ManagerEntryTime;
use App\Exports\ChartSheetExport;
use App\Exports\MultiSheetExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->guard('web')->user();
            return $next($request);
        });
    }

    public function index()
    {
        if (is_null($this->user) || !$this->user->can('report.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any reports !');
        }
        $filterData = [];
        if ($this->user->can('report.agent_reports')) {
            // Agent Report page
            $userIds = [];
            if ($this->user->can('report.access_team_report')) {
                $userIds = UserAllocation::where('parent_id', $this->user->id)->pluck('user_id')->toArray();
            }
            $query = TimeEntry::with(['user', 'client', 'process', 'process.workflow'])
                ->when(!empty($userIds), function ($query) use ($userIds) {
                    $query->whereHas('user', function ($q) use ($userIds) {
                        $q->whereIn('id', $userIds);
                    })
                    ->whereHas('client', function ($q) use ($userIds) {
                        $q->whereIn('assigned_to', $userIds);
                    })
                    ->whereHas('process.workflow', function ($q) {
                        $q->whereColumn('workflows.client_id', 'time_entries.client_id');
                    });
                });

            if (!$this->user->can('report.view')) {
                $query->where('user_id', $this->user->id);
            }

            $entries = $query->select('id', 'user_id', 'client_id', 'process_id', 'status', 'category', 'activity')->get();

            $filterData = [
                'status' => $entries->pluck('status')->unique()->values(),
                'process' => $entries->pluck('activity')->unique()->values(),
                'categorys' => $entries->pluck('category')->unique()->values(),
                'users' => $entries->pluck('user')
                    ->filter(fn($user) => !empty($user) && isset($user->id))
                    ->whenEmpty(fn() => collect()) // return empty collection if no valid users
                    ->unique('id')
                    ->map(fn($user) => ['id' => $user->id, 'name' => $user->name])
                    ->values(),

               'clients' => $entries->pluck('client')
                ->filter(fn($client) => !empty($client) && isset($client->id))
                ->unique('id')
                ->map(fn($client) => ['id' => $client->id, 'client_name' => $client->client_name])
                ->values()
                ->toArray(),


            ];
        }
        $reports = [];
        $filterManagerData = [];
        if ($this->user->can('report.manager_reports')) {
            $reports = ManagerEntryTime::with(['user','department','task']);
            $userRoles = auth()->user()->roles->pluck('id')->toArray();
            if (!$this->user->can('report.view_all_reports')  && !in_array(1, $userRoles)) {
                $reports = $reports->where('user_id', $this->user->id);
            }
            $reports = $reports->get();
            $filterManagerData = [
                'users' => $reports->whereNotNull('user')->pluck('user.name', 'user.id')->toArray(),
                'departments' => $reports->whereNotNull('department')->pluck('department.field_name', 'department.id')->toArray(),
                'tasks' => $reports->whereNotNull('task')->pluck('task.option_value', 'task.id')->toArray(),
            ];
        }

        return view('Reports.index', compact('filterData', 'filterManagerData', 'reports'));
    }

    public function getReports(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('report.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any reports !');
        }

        if ($request->report_type == 'manager') {
            return $this->getManagerReports($request);
        }

        if ($request->report_type == 'qc_report') {
            return $this->getQCReports($request);
        }

        if (!$this->user->can('report.agent_reports')) {
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');
        $sortBy = $request->input('sort_by', '');
        $endDate = $request->input('end_date', '');
        $startDate = $request->input('start_date', '');
        $dateFilter = $request->input('date_filter', '');
        $userFilter = $request->input('user_filter', '');
        $statusFilter = $request->input('status_filter', '');
        $clientFilter = $request->input('client_filter', '');
        $processFilter = $request->input('activity_filter', '');
        $categoryFilter = $request->input('category_filter', '');

        // Build the query
        $userIds = [];
        if ($this->user->can('report.access_team_report')) {
            $userIds = UserAllocation::where('parent_id', $this->user->id)->pluck('user_id')->toArray();
        }
        $query = TimeEntry::with(['user', 'client', 'process', 'process.workflow', 'assignActivity'])
            ->when(!empty($userIds), function ($query) use ($userIds) {
                $query->whereHas('user', function ($q) use ($userIds) {
                    $q->whereIn('id', $userIds);
                })
                ->whereHas('client', function ($q) use ($userIds) {
                    // $q->whereIn('assigned_to', $userIds);
                })
                ->whereHas('process.workflow', function ($q) {
                    $q->whereColumn('workflows.client_id', 'time_entries.client_id');
                });
            });
        if (!$this->user->can('report.view_all_reports') && !$this->user->can('report.access_team_report')) {
            $query->where('user_id',$this->user->id);
        }

        if (!empty($startDate) && !empty($endDate)) {
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
            if ( $startDate != $endDate) {
                $query->whereBetween('time_entries.created_at', [$startDate, $endDate]);
            } else {
                 $query->whereDate('time_entries.created_at', $startDate);
            }
        }

        if (!empty($userFilter)) {
            $query->where('user_id', $userFilter);
        }

        if (!empty($processFilter)) {
            $query->where('activity', $processFilter);
        }

        if (!empty($categoryFilter)) {
            $query->where('category', $categoryFilter);
        }

        if (!empty($clientFilter)) {
            $query->whereHas('client', function ($clientQuery) use ($clientFilter) {
                $clientQuery->where('id', 'like', '%' . $clientFilter . '%');
            });
        }

        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('time_entries.activity', 'like', '%' . $search . '%')
                  ->orWhere('time_entries.status', 'like', '%' . $search . '%')
                  ->orWhere('time_entries.category', 'like', '%' . $search . '%')
                  ->orWhere('time_entries.sub_category', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('process', function ($processQuery) use ($search) {
                      $processQuery->where('process_name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('client_name', 'like', '%' . $search . '%');
                  });
            });
        }

        if (!empty($dateFilter)) {
            switch ($dateFilter) {
                case 'Show All':
                    break;
                case 'Last Week':
                    $query->whereBetween('time_entries.created_at', [now()->subWeek(), now()]);
                    break;
                case 'Last Month':
                    $query->whereBetween('time_entries.created_at', [now()->subMonth(), now()]);
                    break;
                case 'Last Year':
                    $query->whereBetween('time_entries.created_at', [now()->subYear(), now()]);
                    break;
                default:
                    break;
            }
        }

        // Apply sorting
        if (!empty($sortBy)) {
            switch ($sortBy) {
                case 'A to Z':
                    $query->orderBy('time_entries.activity', 'asc');
                    break;
                case 'Z to A':
                    $query->orderBy('time_entries.activity', 'desc');
                    break;
                case 'Date Added':
                    $query->orderBy('time_entries.created_at', 'asc');
                    break;
                case 'Date Updated':
                    $query->orderBy('time_entries.updated_at', 'desc');
                    break;
                default:
                    $query->orderBy('time_entries.created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('time_entries.created_at', 'desc');
        }

        $totalRecords = TimeEntry::count();
        $totalFilteredRecords = $query->count();
        $reports = $query->get();
        $breakEntries = $reports->where('type', 'break');

        $indexGroup = [];
        $report_data = [];
        $taskDetailsWithUser = [];
        $totalTaskTimeSeconds = 0;
        foreach ($reports as $task) {
            if (in_array($task->type, ['task', 'idle'])) {
                $created_at = date('d-M-Y', strtotime($task->created_at));
                $index = $created_at . '-' . ($task->user?->id ?? 'null') . '-' . $task->process_id . '-' . $task->assignment_id . '-' . $task->type;

                $timeSheet = $this->getEntryTimeSheet($task, $breakEntries);
                if (!isset($indexGroup[$index])) {
                    $indexGroup[$index] = $task->id;
                    $taskDetail = [
                        'task_id'          => $task->id,
                        'user_id'          => $task->user?->id,
                        'user_name'        => $task->user?->name,
                        'user_email'       => $task->user?->email,
                        'process_id'       => $task->process_id,
                        'activity'         => $task->activity,
                        'assignment_id'    => $task->assignment_id,
                        'comments'         => 'N/A',
                        'status'           => $task->assignActivity->status ?? $task->status,
                        'type'             => $task->type,
                        'client_name'      => $task->client->client_name ?? '-',
                        'category'         => $task->category,
                        'sub_category'     => $task->sub_category,
                        'start_time'       => date('h:i:s A', strtotime($task->start_time)),
                        'end_time'         => date('h:i:s A', strtotime($task->end_time)),
                        'date'             => date('d-M-Y', strtotime($task->created_at)),
                        'total_task_time'  => '00:00:00',
                        'total_break_time' => '00:00:00',
                        'active_work_time' => '00:00:00',
                    ];
                    $taskDetailsWithUser[] = array_merge($taskDetail, $timeSheet);
                } else {
                    $index = array_search($indexGroup[$index], array_column($taskDetailsWithUser, 'task_id'));
                    $old_active_work_time = $this->timeToSeconds($taskDetailsWithUser[$index]['active_work_time']);
                    $current_total_break_time = $this->timeToSeconds($timeSheet['total_break_time']);
                    $current_active_work_time = $this->timeToSeconds($timeSheet['active_work_time']);

                    $taskDetailsWithUser[$index]['active_work_time'] = $this->secondsToTime($old_active_work_time + $current_active_work_time);
                    $taskDetailsWithUser[$index]['total_task_time']  = $this->secondsToTime($old_active_work_time + $current_active_work_time + $current_total_break_time);
                    $taskDetailsWithUser[$index]['status'] = $task->assignActivity->status ?? $task->status;
                }
            }
        }

        if($request->export) {
            $start = 0;
            $length = count($taskDetailsWithUser);
        }
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => count($taskDetailsWithUser),
            'recordsFiltered' => count($taskDetailsWithUser),
            'data' => array_slice($taskDetailsWithUser, $start, $length),
        ]);
    }

    public function getQCReports(Request $request) {
        if (is_null($this->user) || !$this->user->can('report.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any reports !');
        }
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');
        $sortBy = $request->input('sort_by', '');
        $endDate = $request->input('end_date', '');
        $startDate = $request->input('start_date', '');
        $agent_filter = $request->input('agent_filter', '');
        $userFilter = $request->input('user_filter', '');
        $statusFilter = $request->input('status_filter', '');
        $clientFilter = $request->input('client_filter', '');
        $processFilter = $request->input('activity_filter', '');
        $categoryFilter = $request->input('category_filter', '');

        $Calls = CallAllocation::with(['qcAgent', 'call.activity.activity.workflow.client', 'call.user'])
            ->where('status','completed')
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($agent_filter, fn($q) => $q->where('qc_agent_id', $agent_filter))
            ->when($userFilter, fn($q) => $q->whereHas('call', fn($q) => $q->where('user_id', $userFilter)))
            ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
            ->when($clientFilter, fn($q) =>
                $q->whereHas('call.activity.activity.workflow.client', fn($q) => $q->where('id', $clientFilter))
            )
            ->when($processFilter, fn($q) =>
                $q->whereHas('call.activity.activity', fn($q) => $q->where('id', $processFilter))
            )
            ->when($categoryFilter, fn($q) =>
                $q->where('category_id', $categoryFilter) // adjust this if `category_id` is in a relationship
            )
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->whereHas('qcAgent', fn($q) => $q->where('name', 'like', "%$search%"))
                      ->orWhereHas('call.user', fn($q) => $q->where('name', 'like', "%$search%"))
                      ->orWhereHas('call.activity.activity', fn($q) => $q->where('process_name', 'like', "%$search%"))
                      ->orWhereHas('call.activity.activity.workflow.client', fn($q) => $q->where('client_name', 'like', "%$search%"));
                });
            });

        $totalRecords = $Calls->count();
        if (!$request->export) {
            $Calls = $Calls->skip($start)->take($length);
        }
        $Calls = $Calls->get();

        $return_data = [];
        foreach ($Calls as $key => $value) {
            $call_duration = '00:00:00';
            if (!empty($value->call->call_details)) {
                try {
                    $callData = json_decode(json_decode($value->call->call_details), true);

                    $startTimestamp = $callData['participants'][0]['recordings'][0]['startTimestamp'] ?? null;
                    $endTimestamp = $callData['callEnded'] ?? null;

                    if ($startTimestamp && $endTimestamp) {
                        $start = Carbon::parse($startTimestamp);
                        $end = Carbon::parse($endTimestamp);

                        if ($start && $end && $start->lte($end)) {
                            $durationInSeconds = $start->diffInSeconds($end);
                            $call_duration = gmdate('H:i:s', $durationInSeconds);
                        }
                    }

                } catch (\Throwable $e) {
                    // Optional: Log error for debugging
                    // Log::error('Error parsing call duration: ' . $e->getMessage());
                    $call_duration = '00:00:00';
                }
            }

            $call = $value->call;
            $qcAgent = $value->qcAgent;
            $user = $call?->user;
            $activity = $call?->activity?->activity;
            $workflow = $activity?->workflow;
            $client = $workflow?->client;

            $return_data[] = [
                'id' => $value->id,
                'date' => $value->created_at ? $value->created_at->format('d-M-Y') : null,
                'qc_agent' => $qcAgent?->name,
                'qc_agent_id' => $qcAgent?->id,
                'user_name' => $user?->name,
                'user_id' => $user?->id,
                'client' => $client?->client_name,
                'client_id' => $client?->id,
                'activity' => $activity?->process_name,
                'activity_id' => $activity?->id,
                'status' => $value->status,
                'call_id' => '#' . str_pad($call->id, 5, '0', STR_PAD_LEFT),
                'call_duration' => $call_duration,
            ];

        }

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => count($Calls),
            'data' => $return_data,
        ]);
    }

    public function getManagerReports(Request $request) {
        // Check permissions
        if (!$this->user->can('report.manager_reports')) {
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        // Get the parameters from the request
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');
        $userFilter = $request->input('user_filter', '');
        $departmentFilter = $request->input('department_filter', '');
        $taskFilter = $request->input('task_filter', '');
        $statusFilter = $request->input('status_filter', '');
        $startDate = $request->input('start_date', '');
        $endDate = $request->input('end_date', '');

        // Query builder with necessary filters
        $query = ManagerEntryTime::with(['user', 'department', 'task']);

        $userRoles = auth()->user()->roles->pluck('id')->toArray();
        if (!$this->user->can('report.view_all_reports') && !in_array(1, $userRoles)) {
            $query->where('user_id', $this->user->id);
        }

        // Apply user filter
        if (!empty($userFilter)) {
            $query->where('user_id', $userFilter);
        }

        // Apply department filter
        if (!empty($departmentFilter)) {
            $query->where('manage_field_id', $departmentFilter);
        }

        // Apply task filter
        if (!empty($taskFilter)) {
            $query->where('manage_field_option_id', $taskFilter);
        }

        // Apply status filter
        if (!empty($statusFilter)) {
            // Assuming that status is determined by the `end_time`
            if ($statusFilter === 'completed') {
                $query->whereNotNull('end_time');
            } elseif ($statusFilter === 'pending') {
                $query->whereNull('end_time');
            }
        }

        // Apply date range filter
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('created_at', [
                date('Y-m-d 00:00:00', strtotime($startDate)),
                date('Y-m-d 23:59:59', strtotime($endDate))
            ]);
        }

        // Handle search (if any)
        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->where('user.name', 'LIKE', "%{$search}%")
                      ->orWhere('department.field_name', 'LIKE', "%{$search}%")
                      ->orWhere('task.option_value', 'LIKE', "%{$search}%");
            });
        }

        // Get total filtered records for pagination
        $totalFilteredRecords = $query->count();

        // Paginate results
        if (!$request->export) {
            $query->skip($start)->take($length);
        }
        $reports = $query->orderBy('created_at', 'desc')->get();

        // Process data for response
        $formattedData = [];
        foreach ($reports as $key => $value) {
            $startTime = $value->start_time ? date('h:i:s A', strtotime($value->start_time)) : '-';
            $endTime = $value->end_time ? date('h:i:s A', strtotime($value->end_time)) : '-';
            $duration = $value->end_time 
                ? \Carbon\Carbon::parse($value->start_time)->diff(\Carbon\Carbon::parse($value->end_time))->format('%H:%I:%S') 
                : '-';

            $formattedData[] = [
                'index' => $key + 1 + $start,
                'date' => date('d-M-Y', strtotime($value->created_at)),
                'user_name' => $value->user->name ?? 'N/A',
                'department' => $value->department->field_name ?? 'N/A',
                'task' => $value->task->option_value ?? 'N/A',
                'status' => $value->end_time ? 'Completed' : 'Pending',
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration' => $duration,
                'id' => $value->id,
            ];
        }

        // Return the response in the expected format
        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalFilteredRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $formattedData
        ]);
    }



    function timeToSeconds($time) { // Convert HH:MM:SS to seconds
        sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    function secondsToTime($seconds) { // Convert seconds to HH:MM:SS
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
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

            $userId = $task->user?->id;

            $breaks = $userId
                ? $breakEntries
                    ->where('user_id', $userId)
                    ->where('type', 'break')
                    ->where('process_id', $task->process_id)
                : collect(); // Get all breaks related to this task
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
            $taskDetail['total_task_time']  = gmdate("H:i:s", $single ? $totalTaskTimeSeconds : ($totalTaskTimeSeconds + $totalBreakTime));
            return $taskDetail;
    }

    public function getReportsDetails(Request $request)
    {
        if (is_null($this->user) || (!$this->user->can('report.view')) ) {
            abort(403, 'Sorry !! You are Unauthorized to view any reports !');
        }

        $userRoles = auth()->user()->roles->pluck('id')->toArray();
        if ($this->user->can('report.manager_reports') && $request->type && $request->type == 'manager') {
            $report = ManagerEntryTime::with(['user', 'department', 'task'])->find($request->id);
            if (!$report) {
                return response()->json(['success' => false, 'message' => 'Report not found.']);
            }
            $extraData = [];
            if (!empty($report->extra_data)) {
                $extra_jsondata = json_decode($report->extra_data);
                if (is_object($extra_jsondata)) {
                    $tempExtra_jsondata = (array) $extra_jsondata;
                    $extraData = array_merge($extraData, $tempExtra_jsondata);
                }
                if (isset($extra_jsondata->clients) && !empty($extra_jsondata->clients)) {
                    $clients = Client::select('id', 'client_name')->whereIn('id', $extra_jsondata->clients)->get();
                    $extraData['clients'] = $clients;
                }

                if (isset($extra_jsondata->user) && !empty($extra_jsondata->user)) {
                    $user = User::select('email', 'name')->where('id', $extra_jsondata->user)->first();
                    $extraData['user'] = $user;
                }
            }
            $html = view('Reports.partial.manager-report-details-modal', compact('report','extraData'))->render();

            return response()->json(['success' => true, 'html' => $html]);
        }

        $request->validate([
            'id' => 'required|integer|exists:time_entries,id',
        ]);

        $timeEntry = TimeEntry::with('taskLogs')->find($request->id);
        if ($timeEntry) {
            $query = TimeEntry::with('taskLogs')->where('assignment_id',$timeEntry->assignment_id);
            if (!empty($timeEntry->process_id)) {
                $query->where('process_id', $timeEntry->process_id);
            } else {
                $query->where(['type' => $timeEntry->type, 'user_id' => $timeEntry->user_id, 'category' => $timeEntry->category]);
            }

            if ($timeEntry->created_at) {
                $query->whereDate('created_at', $timeEntry->created_at->toDateString());
            }

            $timeEntries = $query->get();
            $breakEntries = $timeEntries->where('type', 'break');

            foreach ($timeEntries as $key => $timeEntrie) {
                $timeEntries[$key]->timesheet = $this->getEntryTimeSheet($timeEntrie, $breakEntries, true);
            }
            $html = view('Reports.partial.report-details-table', ['timeEntries' => $timeEntries])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        // If no entry is found
        return response()->json([
            'success' => false,
            'message' => 'No matching records found.',
        ]);

    }

    public function getQCCallReportsDetails(Request $request)
    {
        if (is_null($this->user) || (!$this->user->can('report.view')) ) {
            abort(403, 'Sorry !! You are Unauthorized to view any reports !');
        
        }

        $allocation = CallAllocation::with([
            'call.activity.activity.workflow.client',
            'qcAgent',
            'score'
        ])->where('id', $request->id)->first();

        if (!$allocation) {
            // If no entry is found
            return response()->json([
                'success' => false,
                'message' => 'No matching records found.',
            ]);
        }

        $callActivity = $allocation->call->activity->activity ?? null;
        $workflow = $callActivity?->workflow;
        $compact_data = [
            'id' => $request->id,
            'recording_id' => $this->getRecordingId($allocation->call),
            'allocation_id' => $allocation->id,
            'agent_name' => $allocation->qcAgent->name ?? 'N/A',
            'user_name' => $allocation->call->user->name ?? 'N/A',
            'client_name' => $workflow->client->client_name ?? 'N/A',
            'workflow_name' => $workflow->workflow_name ?? 'N/A',
            'process_name' => $callActivity->process_name ?? 'N/A',
            'score' => $allocation->score,
            'status' => $allocation->status,
            'QcParameter' => QcParameter::where('status', true)->orderBy('order', 'asc')->get(),
        ];

        // $html = view('Reports.partial.qc-report-details', $compact_data);

        $html = view('Reports.partial.qc-report-details', $compact_data)->render();

        return response()->json([
            'success' => true,
            'html' => $html,
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

    public function getUsersProductivity($userIds = [], $startDate = '', $endDate = '', $client_filter, $activity_filter, $manager_roles) {    

        // Get filter parameters from request
        $userFilter = '';
        $processFilter = '';
        $categoryFilter = '';
        $clientFilter = '';
        $statusFilter = '';
        $search = '';
        $dateFilter = '';
        $sortBy = '';

        // Build the query
        $query = TimeEntry::with(['user', 'process', 'client'])
            ->whereHas('user', function ($query) use ($manager_roles) {
                $query->whereHas('roles', function ($roleQuery) use ($manager_roles) {
                    $roleQuery->whereNotIn('id', $manager_roles);
                });
            });

        if ($client_filter) {
            $query->where('client_id', $client_filter);
        }

        if ($activity_filter) {
            $query->where('process_id', $activity_filter);
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
        // if (!$this->user->can('report.view')) {
        //     $query->where('user_id', $this->user->id);
        // }

        // Apply date range filter
        if (!empty($startDate) && !empty($endDate)) {
            $endDate = date('Y-m-d 23:59:59', strtotime($endDate));
            $startDate = date('Y-m-d 00:00:00', strtotime($startDate));
            if ($startDate != $endDate) {
                $query->whereBetween('time_entries.created_at', [$startDate, $endDate]);
            } else {
                $query->whereDate('time_entries.created_at', $startDate);
            }
        }

        // Apply other filters
        if (!empty($userFilter)) {
            $query->where('user_id', $userFilter);
        }
        if (!empty($processFilter)) {
            $query->where('activity', $processFilter);
        }
        if (!empty($categoryFilter)) {
            $query->where('category', $categoryFilter);
        }
        if (!empty($clientFilter)) {
            $query->whereHas('client', function ($clientQuery) use ($clientFilter) {
                $clientQuery->where('id', 'like', '%' . $clientFilter . '%');
            });
        }
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        // Apply search functionality
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('time_entries.activity', 'like', '%' . $search . '%')
                  ->orWhere('time_entries.status', 'like', '%' . $search . '%')
                  ->orWhere('time_entries.category', 'like', '%' . $search . '%')
                  ->orWhere('time_entries.sub_category', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('process', function ($processQuery) use ($search) {
                      $processQuery->where('process_name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('client_name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Apply date filter (e.g., Last Week, Last Month)
        if (!empty($dateFilter)) {
            switch ($dateFilter) {
                case 'Show All':
                    break;
                case 'Last Week':
                    $query->whereBetween('time_entries.created_at', [now()->subWeek(), now()]);
                    break;
                case 'Last Month':
                    $query->whereBetween('time_entries.created_at', [now()->subMonth(), now()]);
                    break;
                case 'Last Year':
                    $query->whereBetween('time_entries.created_at', [now()->subYear(), now()]);
                    break;
                default:
                    break;
            }
        }

        // Apply sorting
        if (!empty($sortBy)) {
            switch ($sortBy) {
                case 'A to Z':
                    $query->orderBy('time_entries.activity', 'asc');
                    break;
                case 'Z to A':
                    $query->orderBy('time_entries.activity', 'desc');
                    break;
                case 'Date Added':
                    $query->orderBy('time_entries.created_at', 'asc');
                    break;
                case 'Date Updated':
                    $query->orderBy('time_entries.updated_at', 'desc');
                    break;
                default:
                    break;
            }
        } else {
            $query->orderBy('time_entries.created_at', 'desc');
        }

        // Get the reports data
        $reports = $query->get();
        $breakEntries = $reports->where('type', 'break');
        
        // Process the report data
        $indexGroup = [];
        $taskDetailsWithUser = [];
        $usersProductivityData = [];
        foreach ($reports as $task) {
            $user_filter = empty($userIds) || in_array($task->user->id, $userIds);
            if (in_array($task->type, ['task', 'idle']) && $user_filter) {
                $created_at = date('d-M-Y', strtotime($task->created_at));
                $index = $created_at . '-' . $task->user->id . '-' . $task->process_id . '-' . $task->type;
                $timeSheet = $this->getEntryTimeSheet($task, $breakEntries);
                $total_task_time  = $this->timeToMinutes($timeSheet['total_task_time']);
                $total_break_time = $this->timeToMinutes($timeSheet['total_break_time']);
                $active_work_time = $this->timeToMinutes($timeSheet['active_work_time']);
                $user_id = $task->user->id;
                if (isset($usersProductivityData[$user_id])) {
                    $usersProductivityData[$user_id]['total_task_time'] += $total_task_time;
                    $usersProductivityData[$user_id]['total_break_time'] += $total_break_time;
                    $usersProductivityData[$user_id]['active_work_time'] += $active_work_time;
                    $usersProductivityData[$user_id]['time'] = $this->secondsToTime($usersProductivityData[$user_id]['total_task_time']);

                    $usersProductivityData[$user_id]['active_time_percentage'] = $this->getPercentage($usersProductivityData[$user_id]['active_work_time'], $usersProductivityData[$user_id]['total_task_time']);
                } else {
                    $usersProductivityData[$user_id] = array(
                        'user_id'   => $user_id,
                        'user_name' => $task->user->name,
                        'user_name' => $task->user->name,
                        'total_task_time' => $total_task_time,
                        'total_break_time' => $total_break_time,
                        'active_work_time' => $active_work_time,
                        'time' => $this->secondsToTime($total_task_time),
                        'active_time_percentage' => $this->getPercentage($active_work_time, $total_task_time)
                    );
                }

            }
        }

        return $usersProductivityData;
    }

    public function timeToMinutes($timeString = '') {
        $totalSeconds = 0;
        if ($timeString != '') {
            list($hours, $minutes, $seconds) = explode(":", $timeString);
            $totalSeconds =  ($hours * 3600) + ($minutes * 60) + $seconds;
        }
        return $totalSeconds;
    }

    public function getPercentage($activeWorkTime, $totalTaskTime) {
        if ($activeWorkTime == 0 || $totalTaskTime == 0) {
            return 0;
        }
        $percentage = ($activeWorkTime / $totalTaskTime) * 100;
        return round($percentage, 2);
    }

    public function ExportMultiSheet() {
        $Dashboard_chart = [
            ['', 'Jan', 'Feb', 'Mar'],
            ['Product A', 10, 30, 20],
            ['Product B', 20, 10, 40],
        ];

        $attendanceData = TimeHelper::attendanceData([], '', '');
        $multi_chart = array(
            ['data' => $attendanceData, 'type' => 'pie'],
            ['data' => $attendanceData, 'type' => 'pie']
        );

        $dynamicData = array(
            'Dashboard' => ['data' => $Dashboard_chart, 'multi_chart' =>$multi_chart , 'is_chart' => true],
            'Data' => ['data' => $this->ExcelDataSheet()],
            'Attn Data' => ['data' => $this->ExcelAttnSheet()]
        );
        $exporter = new ChartSheetExport();
        $filePath = $exporter->generate($dynamicData);
        return response()->download($filePath)->deleteFileAfterSend(true);
        // $export = new \App\Exports\ChartExport();
        // return $export->export();
        // return Excel::download(new MultiSheetExport($dynamicData), 'multi-sheet-report.xlsx');
    }

    public function ExcelDataSheet() {
        $sheet_data = [[
            'Emp Id', 'Date', 'Employee Name', 'Activity', 'Idle time',
            'Start Time', 'End Time', 'Total Time', 'Patient name/id',
            'STATUS', 'Comment', 'Client', 'production%', 'Task target',
            'count', 'Category', 'Sub Cat', '*', 'define AHT', 'Agent Idle'
        ]];
        // Fetch data with necessary relationships
        $data = TimeEntry::with([
            'user:id,name',
            'client:id,client_name',
            'process:id,process_name',
            'process.workflow:id,workflow_name',
            'assignActivity.workflowFieldValues.workflowField'
        ])->select([
            'id', 'user_id', 'client_id', 'process_id', 'activity', 'category', 'sub_category',
            'status', 'comments', 'start_time', 'end_time', 'created_at', 'assignment_id'
        ])->get();

        // Format and map data
        foreach ($data as $entry) {
            $patientName = '-';
            foreach ($entry->assignActivity?->workflowFieldValues ?? [] as $fieldValue) {
                $fieldName = $fieldValue->workflowField?->field_name;
                $fieldValueText = $fieldValue->value;

                if ($fieldName && str_contains(strtolower($fieldName), 'patient')) {
                    $lowerField = strtolower($fieldName);

                    if ((str_contains($lowerField, 'name') || str_contains($lowerField, 'id')) && $patientName === null) {
                        $field_id = $fieldValue->id;
                        $patientName = $fieldValueText;
                    }
                }
            }

            $start = Carbon::parse($entry->start_time);
            $end = Carbon::parse($entry->end_time);
            $diffInSeconds = $start->diffInSeconds($end); // total seconds
            $formattedDuration = gmdate('H:i:s', $diffInSeconds); // e.g., 00:04:38
            $sheet_data[] = [
                $entry->user_id,
                Carbon::parse($entry->created_at)->format('d-M-Y'),
                $entry->user->name ?? '-',
                $entry->activity ?? '-',
                '-', // Idle time (not available in current data)
                $entry->start_time ? Carbon::parse($entry->start_time)->format('h:i:s A') : '-',
                $entry->end_time ? Carbon::parse($entry->end_time)->format('h:i:s A') : '-',
                $formattedDuration,
                $patientName, // Patient name/id (not available)
                $entry->status ?? '-',
                $entry->comments ?? '-',
                $entry->client->client_name ?? '-',
                '%', // Placeholder for production%
                '-', // Task target
                '1', // Count
                $entry->category ?? '-',
                $entry->sub_category ?? '-',
                '-', '-', '-' // Attendance, define AHT, Agent Idle
            ];
        }
        // die;
        // dd($sheet_data);
        return $sheet_data;

    }

    public function ExcelAttnSheet() {
        $result = [[
            'Emp Id', 'Employee Name', 'In Time', 'Out Time',
            'Total', 'Status', 'Date', 'EOD File Status'
        ]];
        $rawEntries = TimeEntry::with(['user:id,name'])
            ->where('category', 'Attendance Activities')
            ->whereIn('activity', ['Timesheet Login', 'Timesheet Logout'])
            ->orderBy('user_id')
            ->orderBy('created_at') // Ensure chronological order
            ->get();

        // Group by user and date
        $grouped = $rawEntries->groupBy(function ($entry) {
            return $entry->user_id . '-' . Carbon::parse($entry->created_at)->format('Y-m-d');
        });

        $office_time = Setting::where('setting_name', 'Office Timing')->value('setting_value');
        list($start, $end) = explode(' - ', $office_time);

        $officeStart = Carbon::parse($start); // 9:00 AM
        $officeEnd = Carbon::parse($end);  

        foreach ($grouped as $groupKey => $entries) {
            $userId = $entries->first()->user_id;
            $userName = $entries->first()->user->name ?? '-';
            $date = Carbon::parse($entries->first()->created_at)->format('n/j/Y');

            $loginTime = null;

            foreach ($entries as $entry) {
                if ($entry->activity === 'Timesheet Login') {
                    $loginTime = Carbon::parse($entry->start_time)->format('d M Y H:i');
                }

                if ($entry->activity === 'Timesheet Logout' && $loginTime !== null) {
                    $logoutTime = Carbon::parse($entry->end_time)->format('d M Y H:i');
                    $duration = Carbon::parse($loginTime)->diff(Carbon::parse($logoutTime))->format('%H:%I:%S');

                    $login = isset($entry->start_time) ? Carbon::parse($entry->start_time) : null;
                    $logout = isset($entry->end_time) ? Carbon::parse($entry->end_time) : null;

                    if (empty($login)) {
                        $entry['status'] = 'No Record';
                    } elseif ($login->format('H:i:s') > $officeStart->format('H:i:s')) {
                        $entry['status'] = 'Late';
                    } else {
                        $entry['status'] = 'On Time';
                    }

                    $result[] = [
                        $userId,
                        $userName,
                        $loginTime,
                        $logoutTime,
                        $duration,
                        $entry['status'],
                        $date,
                        ''
                    ];

                    // Reset for next pair
                    $loginTime = null;
                }
            }
        }

        return $result;

    }
}
