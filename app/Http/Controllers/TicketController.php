<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Form;
use App\Models\Ticket;
use App\Models\FormField;
use App\Models\UserTicket;
use App\Models\TicketReply;
use Illuminate\Support\Str;
use App\Models\TicketDetail;
use Illuminate\Http\Request;
use App\Models\UserAllocation;
use App\Models\FormFieldOption;
use App\Models\FormFieldDependency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
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
    // List tickets
    public function index()
    {
        if (is_null($this->user) || !$this->user->can('ticket.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any Ticket !');
        }
        $roles = Auth::user()->roles->pluck('id')->toArray();

        $form_ids = Form::where(function ($query) use ($roles) {
            foreach ($roles as $roleId) {
                $query->orWhereJsonContains('roles', (string)$roleId);
            }
        })->pluck('id')->toArray();

        $role_names = Auth::user()->roles->pluck('name')->toArray();
        if (in_array('Manager', $role_names)) {
            $tickets = Ticket::with('user')->latest();
        } else {
            $tickets = Ticket::query();
            if (!in_array('1', $roles)) {
                $tickets = $tickets->whereIn('form_id', $form_ids);
            }
        }
        $tickets = $tickets->get();
        $forms = Form::where('status','1')->get();

        return view('tickets.index', compact('tickets', 'forms'));
    }

    public function getTickets(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('ticket.view')) {
            return response()->json([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
        // Input parameters for pagination and filtering
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');
        $statusFilter = $request->input('status_filter', 'Show All');
        $sortBy = $request->input('sort_by', 'Date Added');
        $draw = $request->input('draw', 1);

        $roles = Auth::user()->roles->pluck('id')->toArray();

        $form_ids = Form::where(function ($query) use ($roles) {
            foreach ($roles as $roleId) {
                $query->orWhereJsonContains('roles', (string)$roleId);
            }
        })->pluck('id')->toArray();

        $query = UserTicket::with(['details.field', 'user']);

        $query = $query->where(function ($q) use ($roles, $form_ids) {
            if (!in_array('1', $roles)) {
                $q->whereIn('form_id', $form_ids);
                $q->orWhere('user_id', auth()->id());
            }
        });

        // Search filter
        if (!empty($search)) {
            $query->whereHas('details', function ($q) {
                $q->where('value', 'like', '%' . $search . '%');
            });
        }

        // Status filter
        if ($statusFilter !== 'Show All') {
            // $query->where('tickets.status', $statusFilter);
        }

        // Sorting logic
        switch ($sortBy) {
            case 'Date Added':
                $query->orderBy('user_tickets.created_at', 'asc');
                break;
            default:
                $query->orderBy('user_tickets.created_at', 'desc'); // Default sorting
                break;
        }

        // Total records count before filtering
        $totalRecords = Ticket::count();

        // Filtered records count
        $totalFilteredRecords = $query->count();

        // Paginate the results
        $tickets = $query->skip($start)->take($length)->get();
        $response = [];
        $FieldOptionIds = [];
        foreach ($tickets as $ticket) {
            foreach ($ticket->details as $detail) {
                $field = $detail->field;
                $valueType = $detail->value_type;
                if (in_array($valueType, ['select', 'checkbox', 'radio'])) {
                    $optionIds = $this->isJsonArray($detail->value) ? json_decode($detail->value, true) : [$detail->value];
                    $FieldOptionIds = array_merge($FieldOptionIds, $optionIds);
                }
            } 
        }
        
        $optionMap = FormFieldOption::whereIn('id', $FieldOptionIds)
            ->pluck('option', 'id');
        $formattedTickets = [];
        
        foreach ($tickets as $ticket) {
            $temp_arr = [];
            foreach ($ticket->details as $detail) {
                $field = $detail->field;
                $valueType = $detail->value_type;

                if (in_array($valueType, ['select', 'checkbox', 'radio'])) {
                    $optionIds = $valueType === 'checkbox'
                        ? json_decode($detail->value, true)
                        : [$detail->value];

                    $optionTexts = collect($optionIds)->map(function ($id) use ($optionMap) {
                        return $optionMap[$id] ?? null;
                    })->filter()->toArray();

                    $displayValue = implode(', ', $optionTexts);
                } else {
                    $displayValue = $detail->value;
                }

                $temp_arr[] = [
                    'field' => $field->name,
                    'value' => $displayValue,
                ];
            }

            $ticket->field_values = $temp_arr;
            $formattedTickets[] = array(
                'formatted_id' => sprintf("%05d", $ticket->id),
                'user_id' => $ticket->user_id,
                'id' => $ticket->id,
                'title' => isset($temp_arr[1]) ? $temp_arr[1]['value'] : $temp_arr[0]['value'],
                'status' => ucfirst($ticket->status),
                'created_at' => \Carbon\Carbon::parse($ticket->created_at)->format('d-M-Y, h:i:s A'),
                'user' => optional($ticket->user)->name
            );
        }

        // Return response as JSON
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $formattedTickets
        ]);
    }

    // Show create form
    public function create($slug)
    {
        if (is_null($this->user) || !$this->user->can('ticket.generate')) {
            abort(403, 'Sorry !! You are Unauthorized to Generate any Ticket !');
        }

        $form = Form::with([
            'fields' => function ($query) {
                $query->orderBy('order', 'asc'); // replace `order_column` with your actual column name for ordering
            },
            'fields.dependencies.dependsOn',
            'fields.selectOptions'
        ])->where('slug', $slug)->first();

        return view('tickets.create', compact('form'));
    }

    // Store a new ticket
    public function store(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('ticket.generate')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }


        try {
            $formData = $request->input('form_data');
            $formId = $request->input('form_id');

            // Get roles from form
            $formRoles = Form::where('id', $formId)->value('roles');

            // Decode and ensure it's an array
            $form_roles = [1]; // Always include role ID 1 ( superadmin ).
            if (!empty($formRoles)) {
                $decodedRoles = json_decode($formRoles, true);
                if (is_array($decodedRoles)) {
                    $form_roles = array_merge($form_roles, $decodedRoles);
                }
            }

            // Fetch users having any of the form roles
            $userIds = User::whereHas('roles', function ($query) use ($form_roles) {
                $query->whereIn('id', $form_roles);
            })->pluck('id');


            $userId = auth()->id();

            $ticket = UserTicket::create([
                'user_id' => $userId,
                'form_id' => $formId,
                'status'  => 'open',
            ]);

            foreach ($formData as $fieldData) {
                $fieldId = $fieldData['name'] ?? null;
                $rawValue = $fieldData['value'] ?? null;
                $tagType = $fieldData['tagType'] ?? 'input';
                $elementType = $fieldData['elementType'] ?? 'text';

                if (!$fieldId || is_null($rawValue)) {
                    continue;
                }

                $isArray = is_array($rawValue);
                $value = $isArray ? json_encode($rawValue) : (string) $rawValue;
                $insertData = array(
                    'ticket_id'  => $ticket->id,
                    'field_id'   => $fieldId,
                    'value_type' => $tagType === 'input' ? $elementType : 'select',
                    'value'      => $value,
                );
                TicketDetail::create($insertData);
            }
            // Send Notification to Form Roles
            $title = '';
            // Check and set title from second form field
            if (!empty($formData[1]['value']) && !empty($formData[1]['name'])) {
                $field = FormField::with('selectOptions')->find($formData[1]['name']);
                $title = optional($field->selectOptions->firstWhere('id', $formData[1]['value']))->option ?? '';
            }

            // Append location if present in the first form field
            if (!empty($formData[0]['name']) && !empty($formData[0]['value'])) {
                $field = FormField::with('selectOptions')->find($formData[0]['name']);
                $fieldNameLower = strtolower($field->name);
                $location = optional($field->selectOptions->firstWhere('id', $formData[0]['value']))->option ?? '';

                if (stripos($fieldNameLower, 'location') !== false) {
                    $title .= ' from ' . $location.' location.';
                } else {
                    $title = $location;
                }
            }

            $insert_data = [];
            foreach ($userIds as $key => $value) {
                $insert_data[] = array(    
                    "type"    => "ticket",
                    "user_id" => $value,
                    "message" => Auth::user()->name . " has submitted a new ticket: " . $title ,
                    "url"     => url('/tickets'),
                    "task_id" => $ticket->id
                );
            }
            sendNotification($insert_data);

            // Add Chat

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
                'message'   => $title,
                'status'    => 'open'
            ]);

            return response()->json([
                'status'    => true,
                'message'   => 'Ticket created successfully.',
                'ticket_id' => $ticket->id
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Show ticket details
    public function chat($ticket)
    {
        if (is_null($this->user) || !$this->user->can('ticket.view')) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Ticket not found.',
                'html' => ''
            ], 404);
        }

        $ticket = UserTicket::with(['replies' => function ($query) {
                $query->orderBy('created_at');
            }, 'user'])
            ->where('id', $ticket)
            ->first();

        if ($ticket) {
            $ticket->grouped_replies = $ticket->replies->groupBy(function ($reply) {
                $date = Carbon::parse($reply->created_at)->startOfDay();
                $now = Carbon::now()->startOfDay();

                if ($date->equalTo($now)) {
                    return 'Today';
                } elseif ($date->equalTo($now->copy()->subDay())) {
                    return 'Yesterday';
                } elseif ($date->greaterThanOrEqualTo($now->copy()->subDays(6))) {
                    return $date->translatedFormat('l');
                } else {
                    return $date->translatedFormat('d F Y');
                }
            });
        }

        $html = view('tickets.chat', compact('ticket'))->render();

        return response()->json([
            'status' => true,
            'html' => $html
        ]);
    }

    public function show($ticket)
    {
        if (is_null($this->user) || !$this->user->can('ticket.view')) {
            return response()->json([
                'success' => false,
                'status' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => 'Ticket not found.',
                'html' => ''
            ], 404);
        }

        $ticket = UserTicket::with(['details.field'])->findOrFail($ticket);

        $response = [];

        foreach ($ticket->details as $detail) {
            $field = $detail->field;
            $valueType = $detail->value_type;

            if (in_array($valueType, ['select', 'checkbox', 'radio'])) {
                $optionIds = $this->isJsonArray($detail->value) ? json_decode($detail->value, true) : [$detail->value];
                $options = FormFieldOption::whereIn('id', $optionIds)->pluck('option')->toArray();
                $displayValue = implode(', ', $options);
            } else {
                $displayValue = $detail->value;
            }

            $response[] = [
                'field' => $field->name,
                'value' => $displayValue,
            ];
        }

        $html = view('tickets.show', compact('response'))->render();

        return response()->json([
            'status' => true,
            'html' => $html
        ]);
    }

    // Mark as completed
    public function complete(UserTicket $ticket)
    {
        if (is_null($this->user) || !$this->user->can('ticket.status')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        if (Auth::user()->id == $ticket->user_id) {
            $ticket->update(['status' => 'closed']);

            return response()->json([
                'success' => true,
                'message' => 'Ticket marked as closed.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized action.'
        ], 403);
    }


    public function storeReply(Request $request, UserTicket $ticket)
    {
        if (is_null($this->user) || !$this->user->can('ticket.reply')) {
            abort(403, 'Sorry !! Unauthorized action.');
        }

        $request->validate([
            'message' => 'nullable|string',
            'status' => 'required|string',
        ]);
        $userIds = [];
        $message = 'Replied for Ticket '.$ticket->title;
        if ($ticket->user_id == Auth::id()) {
            $userIds = UserAllocation::where('user_id', Auth::id())->pluck('parent_id')->toArray();
            $userIds[] = 1;
            $message = Auth::user()->name.' '.$message;
        } else {
            $userIds[] = $ticket->user_id;
        }

        if (!empty($request->message)) {
            $insert_data = [];
            foreach ($userIds as $key => $value) {
                $insert_data[] = array(    
                    "type" => "ticket_reply",
                    "user_id" => $value,
                    "message" => $message,
                    "url" => url('/tickets'),
                    "task_id" => $ticket->id
                );
            }

            sendNotification($insert_data);

            if ($ticket->status == 'open') {
                $ticket->update(['status' => 'Submitted']);
            }

            TicketReply::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'message' => $request->message,
            ]);
        }
        $ticket->update(['status' => $request->status]);

        return redirect()->route('tickets.show', $ticket->id)->with('success', 'Reply added successfully.');
    }

    // Create Ticket form
    public function showTicketForm($slug)
    {
        $form = Form::with([
            'fields' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'fields.dependencies.dependsOn'
        ])->where('status','1')->where('slug', $slug)->first();

        return view('tickets.show_ticket_form', compact('form'));
    }

    public function submitTicketForm(Request $request, $slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();

        // Validation rules based on form fields
        $rules = [];
        foreach ($form->fields as $field) {
            if ($field->is_required) {
                $rules[$field->name] = 'required';
            }
        }

        $validated = $request->validate($rules);

        // Save to database or process logic
        // You can create a FormSubmission model/table if needed

        return back()->with('success', 'Form submitted successfully!');
    }

    public function createTicketFormField($slug)
    {
        if (is_null($this->user) || !$this->user->can('ticket.manage_ticket_form')) {
            abort(403, 'Sorry !! Unauthorized action.');
        }
        $form = Form::with([
            'fields' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'fields.dependencies.dependsOn',
            'fields.selectOptions'
        ])->where('slug', $slug)->first();
        return view('tickets.create_ticket_form', compact('form'));
    }

    public function submitTicketFormFields(Request $request, $slug)
    {
        if (is_null($this->user) || !$this->user->can('ticket.manage_ticket_form')) {
            abort(403, 'Sorry !! Unauthorized action.');
        }
        try {
            // pred($request->all());
            $form = Form::where('slug', $slug)->firstOrFail();

          $request->validate([
            'field_id'   => 'nullable|array',
            'field_id.*' => 'nullable|integer',

            'name'   => 'required|array',
            'name.*' => 'required|string|max:255',

            'type'   => 'required|array',
            'type.*' => 'required|in:dropdown,checklist,text,date,radio', // <- radio added

            'is_required'   => 'nullable|array',
            'is_required.*' => 'nullable|in:on',

            'dropdown'     => 'nullable|array',
            'dropdown.*'   => 'nullable|array',
            'dropdown.*.*' => 'nullable|string|max:255',

            'radio'     => 'nullable|array',
            'radio.*'   => 'nullable|array',
            'radio.*.*' => 'nullable|string|max:255',

            'checklist'     => 'nullable|array',
            'checklist.*'   => 'nullable|array',
            'checklist.*.*' => 'nullable|string|max:255',

            'dependency'     => 'nullable|array',
            'dependency.*'   => 'nullable|array',
            'dependency.*.*' => 'nullable|integer',
        ]);

            $field_ids          = $request->input('field_id', []);
            $field_names        = $request->input('name', []);
            $field_types        = $request->input('type', []);
            $field_requireds    = $request->input('is_required', []);
            $field_checklists   = $request->input('checklist', []);
            $field_radios       = $request->input('radio', []);
            $field_dropdowns    = $request->input('dropdown', []);
            $field_dependency   = $request->input('dependency', []);
            $prepopulate_by     = $request->input('prepopulate', []);
            $inserted_field_ids = [];
            $field_order = 1;
            foreach ($field_names as $key => $field_name) {
                $field = FormField::updateOrCreate(
                    [
                        'id' => $field_ids[$key] ?? null,
                        'form_id'     => $form->id
                    ],
                    [
                        'name'  => $field_name,
                        'type'  => $field_types[$key],
                        'order' => $field_order,
                        'is_required'    => isset($field_requireds[$key]),
                        'prepopulate_by' => isset($prepopulate_by[$key]) ? $prepopulate_by[$key] : null
                    ]
                );
                $field_order++;
                $inserted_field_ids[] = $field->id;

               // Manage Dependency
                $dependency_ids = [];

                if (isset($field_dependency[$key]) && is_array($field_dependency[$key])) {

                    foreach ($field_dependency[$key] as $dependency) {
                        if (!empty($dependency)) {
                            $formFieldDependency = FormFieldDependency::updateOrCreate(
                                [
                                    'field_id' => $field->id,
                                    'depends_on_option_id' => $dependency,
                                ],
                                []
                            );
                            $dependency_ids[] = $formFieldDependency->id;
                        }
                    }
                }
                // Delete removed dependencies for this field
                FormFieldDependency::where('field_id', $field->id)
                    ->whereNotIn('id', $dependency_ids)
                    ->delete();



                // Prepare options if field type is dropdown or checklist
                $rawOptions = match ($field_types[$key]) {
                    'dropdown'  => $field_dropdowns[$key] ?? [],
                    'radio'     => $field_radios[$key] ?? [],
                    'checklist' => $field_checklists[$key] ?? [],
                    default     => [],
                };

                $optionIds = [];
                $index = 1;
                if (!empty($rawOptions)) {
                    foreach ($rawOptions as $id => $option) {
                        if (!empty($option)) {
                            $formFieldOption = FormFieldOption::updateOrCreate(
                                [
                                    'id'             => $id,
                                    'form_field_id'  => $field->id,
                                ],
                                [
                                    'option'         => $option,
                                    'order'          => $index + 1,
                                ]
                            );
                            $optionIds[] = $formFieldOption->id;
                            $index++;
                        }
                    }
                }
                FormFieldOption::where('form_field_id', $field->id)->whereNotIn('id', $optionIds)->delete();

            }
           FormField::where('form_id', $form->id)->whereNotIn('id', $inserted_field_ids)->delete();
            // die;

            return back()->with('success', 'Form submitted successfully!');
        } catch (ModelNotFoundException $e) {
            return back()->with('warning', 'The form you are looking for does not exist.');
        }
    }

    function isJsonArray($value): bool
    {
        if (!is_string($value)) return false;

        $decoded = json_decode($value, true);
        
        return json_last_error() === JSON_ERROR_NONE && is_array($decoded);
    }

    public function ticketForms()
    {
        if (is_null($this->user) || !$this->user->can('ticket.manage_ticket_form')) {
            abort(403, 'Sorry !! You are Unauthorized to view any Ticket !');
        }
        return view('tickets.ticket_forms');
    }

    public function getForms(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('ticket.manage_ticket_form')) {
            return response()->json([
                'draw'            => 0,
                'recordsTotal'    => 0,
                'recordsFiltered' => 0,
                'data'            => []
            ]);
        }
        // Input parameters for pagination and filtering
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');
        $statusFilter = $request->input('status_filter', 'Show All');
        $sortBy = $request->input('sort_by', 'Date Added');
        $draw = $request->input('draw', 1);

        $query = Form::select();
        $totalRecords = $query->count();
        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                // Match on the 'name' field in the 'forms' table
                $q->where('name', 'like', '%' . $search . '%')
                  // Or match on the 'value' field in the related 'details' (form_details) relationship
                  ->orWhereHas('details', function ($subQuery) use ($search) {
                      $subQuery->where('value', 'like', '%' . $search . '%');
                  });
            });
        }

        // Sorting logic
        switch ($sortBy) {
            case 'Date Added':
                $query->orderBy('forms.created_at', 'asc');
                break;
            default:
                $query->orderBy('forms.created_at', 'desc');
                break;
        }

        // Filtered records count
        $totalFilteredRecords = $query->count();

        // Paginate the results
        $forms = $query->skip($start)->take($length)->get();

        // Return response as JSON
        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $forms
        ]);
    }

    public function addTicketForms($id = null)
    {
        if (is_null($this->user) || !$this->user->can('ticket.manage_ticket_form')) {
            abort(403, 'Sorry !! You are Unauthorized to view any Ticket !');
        }

        $form = Form::where('id',$id)->first();
        $roles = Role::where('id', '<>', '1')->get();
        return view('tickets.add_form', compact('id','form', 'roles'));
    }

    public function formStore(Request $request, $id = null)
    {
        // Validate the input
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        // Generate base slug
        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $suffix = 'a';

        // Ensure slug is unique using alphabetic suffix
        while (
            Form::where('slug', $slug)
                ->when($id, function ($query) use ($id) {
                    return $query->where('id', '!=', $id);
                })
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        // Prepare data
        $data = [
            'name' => $request->name,
            'slug' => $slug,
            'roles' => json_encode($request->roles),
            'form_details' => $request->title,
            'status' => $request->status === 'active' ? 1 : 0,
        ];

        $message = 'Form created successfully.';
        if ($id) {
            // Update existing form
            $form = Form::findOrFail($id);
            $form->update($data);
            $message = 'Form updated successfully.';
        } else {
            // Create new form
            Form::create($data);
        }
        return redirect()->route('tickets.ticketForms')->with('success', 'Form updated successfully.');
    }

    public function destroy($id)
    {
        try {
            $form = Form::findOrFail($id);
            $form->delete();

            return response()->json([
                'success' => true,
                'message' => 'Form deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete the form.',
            ], 500);
        }
    }

    // public function getValue($valueType, ) {
    //     if (in_array($valueType, ['select', 'checkbox', 'radio'])) {
    //         $optionIds = $this->isJsonArray($detail->value) ? json_decode($detail->value, true) : [$detail->value];
    //         $options = FormFieldOption::whereIn('id', $optionIds)->pluck('option')->toArray();
    //         $displayValue = implode(', ', $options);
    //     } else {
    //         $displayValue = $detail->value;
    //     }
    // }
 
}