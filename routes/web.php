<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QAController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\TeamLeadController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MicrosoftController;
use App\Http\Controllers\QcParameterController;
use App\Http\Controllers\GoToConnectController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\UserAllocationController;
use App\Http\Controllers\WorkflowFieldValueController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/impersonate/{id}', function ($id) {
    if (Auth::check()) {
        Auth::loginUsingId($id);
        return redirect('/dashboard');
    }

    abort(403);
});

Auth::routes();
Route::get('auth/microsoft', [MicrosoftController::class, 'redirectToMicrosoft'])->name('microsoft.login');
Route::get('auth/microsoft/callback', [MicrosoftController::class, 'handleMicrosoftCallback']);

Route::post('goto/webrtc/callback', [GoToConnectController::class, 'handleWebRTCCallback']);


Route::get('/logout', function (Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('login'); // Redirect after logout
})->name('logout');

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/home', [DashboardController::class, 'index']);
// Route::middleware(['role:1,2,3'])->group(function () {
Route::middleware(['auth'])->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity.logs');
    Route::get('/activity-logs/data', [ActivityLogController::class, 'getData'])->name('activity.logs.data');

    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::post('/', 'update')->name('update');
        Route::post('/upload-image', 'uploadImage')->name('upload.image');
    });

    Route::controller(RolePermissionController::class)->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{role}/permissions', 'assignPermissions')->name('assignPermissions');
        Route::post('/{role}/permissions', 'updatePermissions')->name('updatePermissions');
        Route::delete('/{id}', 'deleteRole')->name('delete');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('/dashboard', 'index');
        Route::get('/import-data', 'importTestingDataForm');
        Route::post('/import-data', 'importTestingData')->name('importTestingData');
        Route::get('/test-workflow-data/{client}', 'GetClientWorkflowDetails');
        Route::get('/dashboard/get-all', 'getChartData')->name('dashboard.getAllData');
        Route::get('/dashboard/getTasks', 'getTasks')->name('dashboard.getTasks');
        Route::get('/notifications', 'notifications')->name('notifications');
        Route::get('/mark-as-read', 'markAsRead')->name('markAsRead');
    });

    Route::controller(TimeEntryController::class)->group(function () {
        Route::get('/time-entry', 'index')->name('time-entry');
        Route::get('/archive-task', 'archiveTask')->name('timeEntry.archiveTask');
        Route::get('/get-assigned-tasks', 'getAssignedTasks')->name('timeEntry.getTasks');
        Route::get('/get-clients-users', 'getClientsAndUsers')->name('timeEntry.getClientsAndUsers');
        Route::post('/time-entry', 'store')->name('timeEntry.store');
        Route::post('/time-entry/complete-task', 'completeTask')->name('timeEntry.completeTask');
        Route::post('/time-entry/start-task', 'startTask')->name('timeEntry.startTask');
        Route::get('/api/time-entry', 'getTimeEntryTasks')->name('timeEntry.getTimeEntryTasks');
        Route::get('/api/call-list', 'getCallList')->name('timeEntry.getCallList');
        Route::get('/api/check-list', 'getCheckList')->name('timeEntry.getCheckList');
        Route::get('/api/script', 'getScript')->name('timeEntry.getScript');
        Route::get('/api/get-notes', 'getNotes')->name('timeEntry.getNotes');
        Route::get('/api/resume-task', 'resumeTasks')->name('timeEntry.resumeTasks');
        Route::post('/time-entry/start-break', 'startBreak')->name('timeEntry.startBreak');
        Route::post('/time-entry/end-break', 'endBreak')->name('timeEntry.endBreak');
        Route::post('/time-entry/user-idle', 'userIdle')->name('timeEntry.userIdle');
        Route::post('/user-checklist', 'updateChecklist')->name('timeEntry.checklist.update');
        Route::post('/save-assignment-note', 'saveOrUpdateNote')->name('timeEntry.updateNotes');

    });

    //  make call by gotoconnect
    //Route::get('/time-entry/make-call', [GoToConnectController::class, 'call'])->name('makeCall');

    Route::get('goto/callback', [GoToConnectController::class, 'handleCallback']);
    Route::post('goto/request', [GoToConnectController::class, 'callGoToApi']);
    Route::post('goto/save-call-log', [GoToConnectController::class, 'saveCallLog']); 
    Route::post('goto/update-call-log', [GoToConnectController::class, 'updateCallLog']); 
    Route::resource('qc-parameters', QcParameterController::class);


    Route::controller(UsersController::class)->group(function () {
        Route::get('/users', 'index')->name('users.index');
        Route::get('/getUsers', 'getUsers')->name('getUsers');
        Route::get('/getUserFilterList', 'getUserFilterList')->name('getUserFilterList');
        Route::get('/users/create', 'create')->name('users.create');
        Route::post('/users', 'store')->name('users.store');
        Route::get('/users/{id}/edit', 'edit')->name('users.edit');
        Route::post('/users/{id}/update', 'update')->name('users.update');
        Route::delete('/users/bulk-delete', 'bulkDelete')->name('users.bulkDelete');
        Route::delete('/users/{id}', 'destroy')->name('users.delete');
    });

    Route::controller(ClientsController::class)->group(function () {
        // Client Management Routes
        Route::get('/clients', 'index')->name('clients.index');
        Route::get('/clients/by-user/{id}', 'getClientByUser')->name('clients.getClientByUser');
        Route::get('/clients/create', 'create')->name('clients.create');
        Route::post('/clients', 'store')->name('clients.store');
        Route::get('/clients/{id}/edit', 'edit')->name('clients.edit');
        Route::put('/clients/{id}', 'update')->name('clients.update');
        Route::delete('/clients/{id}', 'destroy')->name('clients.delete');
        Route::delete('/clients/bulk-delete', 'bulkDelete')->name('clients.bulkDelete');

        // Import and Assignment Routes
        Route::post('clients/import', 'import')->name('clients.import');
        Route::get('/clients/import', 'importForm')->name('clients.importForm');
        Route::post('clients/assign', 'clientAssign')->name('clients.clientAssign');

        // API Routes
        Route::get('/api/clients', 'getAllClients')->name('workflows.getAllClients');
        Route::get('/get-clients', 'getClients')->name('getClients');
    });

    //Workflow
    Route::controller(WorkflowController::class)->group(function () {
        Route::post('import-workflow', 'import')->name('import.workflow');
        Route::get('/workflows/task-sample-export/{id}', 'taskSampleExport')->name('workflows.taskSampleExport');
        // API Routes
        Route::get('/api/workflows/fields', 'getWorkflowsFields')->name('workflows.getWorkflowsFields');
        Route::get('/api/workflows/globle-qc-fields', 'getGlobleQCFields')->name('workflows.getGlobleQCFields');
        Route::get('/api/workflows/process-qc-fields/{id}', 'getProcessQCFields')->name('workflows.getProcessQCFields');
        Route::get('/api/workflows/{id}', 'getAllWorkflows')->name('workflows.getAllWorkflows');

        // Workflow Management Routes
        Route::get('/workflows', 'index')->name('workflows.index');
        Route::get('/get-workflows', 'getWorkflows')->name('getWorkflows');
        Route::get('/workflows/create', 'create')->name('workflows.create');
        Route::get('/workflows/import', 'importForm')->name('workflows.import');
        Route::post('/workflows', 'store')->name('workflows.store');
        Route::get('/workflows/{id}/edit', 'edit')->name('workflows.edit');
        Route::post('/workflows/{id}/global-qc-status', 'globalQcStatus')->name('workflows.globalQcStatus');
        Route::post('/workflows/{id}/process-qc-status', 'processQcStatus')->name('workflows.processQcStatus');
        Route::post('/workflows/{workflowId}/duplicate', 'duplicateWorkflow')->name('workflows.duplicate');
        Route::put('/workflows/globle-qc-fields', 'globleQCstore')->name('workflows.globleQCstore');
        Route::put('/workflows/workflow-qc-fields/{id}', 'workflowQCstore')->name('workflows.workflowQCstore');
        Route::put('/workflows/{id}', 'update')->name('workflows.update');
        Route::delete('/workflows/bulk-delete', 'bulkDelete')->name('workflows.bulkDelete');
        Route::delete('/workflows/{id}', 'destroy')->name('workflows.delete');

        Route::get('/activity-checklist', 'getChecklistItems')->name('workflows.getChecklistItems');
        Route::get('/activity-script', 'getScript')->name('workflows.getScript');
        Route::post('/activity-checklist/{id}', 'storeChecklistItems')->name('workflows.storeChecklistItems');
        Route::post('/activity-script/{id}', 'storeScript')->name('workflows.storeScript');

    });

    //Entry time Reporting
    Route::controller(ReportController::class)->group(function () {
        Route::get('/reports', 'index')->name('reports.index');
        Route::post('/reports/idle-activity', 'index')->name('reports.idleActivity');
        Route::get('/getReports', 'getReports')->name('reports.getReports');
        Route::get('/getReportsDetails', 'getReportsDetails')->name('reports.getReportsDetails');
        Route::get('/get-qc-report-details', 'getQCCallReportsDetails')->name('reports.getQCCallReportsDetails');
        Route::get('/export-multi-sheet', 'ExportMultiSheet')->name('reports.ExportMultiSheet');
    });

    // User Allocation
     Route::controller(UserAllocationController::class)->prefix('case-allocations')->name('userAllocation.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/all', 'getAllocations')->name('getAllocations');
        Route::get('/allocated-users', 'getAllocatedUsers')->name('getAllocatedUsers');
        Route::get('/allocated-user-list', 'getAllocatedUserlist')->name('getAllocatedUserlist');
        Route::get('/assign-task', 'assignTask')->name('assignTask');
        Route::get('/task-list', 'getTaskList')->name('getTaskList');
        Route::get('/import-task', 'importAssignTask')->name('importAssignTask');
        Route::get('/activity-list/{id}', 'getUserActivityList')->name('getUserActivityList');
        Route::get('/task-list/{id}', 'getUserTaskList')->name('getUserTaskList');
        Route::match(['get', 'post'], '/assign-task/{id}', 'assignTasks')->name('assignTasks');
        Route::match(['get', 'post'], '/assign-tasks', 'assignTasks')->name('assignTaskss');
        Route::post('/bulk-assign-tasks', 'BulkAssignTask')->name('BulkAssignTask');
        Route::match(['get', 'post'], '/assign-activity/{id}', 'assignActivities')->name('assignActivities');
        Route::get('/task-details/{id}', 'getUserTaskDetails')->name('getUserTaskDetails');
    });

    Route::controller(UserAllocationController::class)->prefix('case-allocation')->name('userAllocation.')->group(function () {
        Route::post('/assign', 'assignParent')->name('assignParent');
        Route::post('/remove-user', 'removeUser')->name('removeUser');
        Route::post('/import-task', 'importTaskList')->name('importTaskList');
        Route::post('/store', 'taskStore')->name('taskStore');
        Route::post('/update', 'taskUpdate')->name('taskUpdate');
    });

    Route::controller(TicketController::class)->prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/ticket-forms', 'ticketForms')->name('ticketForms');
        Route::get('/ticket-form', 'addTicketForms')->name('addTicketForms');
        Route::get('/ticket-form/{id}', 'addTicketForms')->name('editTicketForms');
        Route::get('/create/{slug}', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/form', 'formStore')->name('formStore');
        Route::post('/ticket-form/{id}', 'formStore')->name('editForm');
        Route::get('/get-tickits', 'getTickets')->name('getTickets');
        Route::get('/get-forms', 'getForms')->name('getForms');
        Route::post('/ticket-form/{id}/edit', 'editticketForm')->name('editticketForm');
        Route::get('/{ticket}/chat', 'chat')->name('chat');
        Route::get('/{ticket}', 'show')->name('show');
        Route::patch('/{ticket}/complete', 'complete')->name('complete');
        Route::post('/{ticket}/reply', 'storeReply')->name('storeReply');

        Route::get('/form/{slug}', 'showTicketForm')->name('showTicketForm');
        Route::get('/create-form-fields/{slug}', 'createTicketFormField')->name('createTicketFormField');
        Route::post('/form/{slug}', 'submitTicketForm')->name('submitTicketForm');
        Route::post('/form-fields/{slug}', 'submitTicketFormFields')->name('form-fields.submit');
        Route::delete('/forms/{id}','destroy')->name('forms.destroy');

    });

    // Quality Assurance (QA) Allocation
     Route::controller(QAController::class)->prefix('quality-assurance')->name('qualityAssurance.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/get-allocations', 'CallAllocations')->name('CallAllocations');
        Route::get('/scoring/{id}', 'scoringForm')->name('scoringForm');
        Route::get('/get-calls', 'getCalls')->name('getCalls');
        Route::get('/get-assigned-calls', 'getAssignedCalls')->name('getAssignedCalls');
        Route::post('/submit-score', 'submitScore')->name('submitScore');
        Route::get('/release', 'release')->name('release');
        Route::post('/assign-calls', 'AssignCalls')->name('AssignCalls');
    });



});

Route::get('/team-lead-dashboard', [TeamLeadController::class, 'index'])->middleware('role:Team Lead');
