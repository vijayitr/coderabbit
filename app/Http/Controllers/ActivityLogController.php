<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view('activity_logs.index'); // Just return the view
    }

    public function getData(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');
        $sortBy = $request->input('sort_by', 'Date Added');
        $draw = $request->input('draw', 1);

        // Base query for activity logs
        $query = ActivityLog::with('user');

        // Total records count before filtering
        $totalRecords = $query->count();

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('activity', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }
        // Sorting logic
        $sortColumns = [
            'Date Added' => ['created_at', 'desc'],
            'Oldest First' => ['created_at', 'asc'],
            'Activity Name' => ['activity', 'asc']
        ];
        $sort = $sortColumns[$sortBy] ?? $sortColumns['Date Added'];
        $query->orderBy($sort[0], $sort[1]);

        // Get filtered records count
        $totalFilteredRecords = $query->count();

        // Paginate results
        $logs = $query->skip($start)->take($length)->get();

        // Format data
        $logs = $logs->map(fn($log) => [
            'id' => $log->id,
            'user' => optional($log->user)->name ?? 'System',
            'date' => $log->created_at->format('d-M-Y'),
            'activity' => $log->activity,
            'model' => $log->model,
            'changes' => $log->changes
        ]);

        // Return JSON response
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $logs
        ]);
    }

}
