<?php

namespace App\Http\Controllers;

use App\Models\UserAllocation;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;
use App\Models\Role;
use Carbon\Carbon;

class UsersController extends Controller
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
        if (is_null($this->user) || !$this->user->can('user.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any user !');
        }
        return view('Users.index');
    }

    public function getUsers(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('user.view')) {
            abort(403, 'Sorry !! You are Unauthorized to view any user !');
        }
        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search.value');
        $dateFilter = $request->input('date_filter');
        $sortBy = $request->input('sort_by');

        $query = User::query()->usersWithLowerRole(auth()->user())
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.id', '<>', auth()->id())
            ->where('users.id', '<>', 1)
            ->select('users.id', 'users.name', 'users.email', 'users.last_login', 'roles.name as role', 'users.status');

        if ($this->user->can('user.view_assigned_users')) {
            $assignedUsers = UserAllocation::where('parent_id', auth()->user()->id)->pluck('user_id')->toArray();
            $query = $query->whereIn('users.id', $assignedUsers);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', '%' . $search . '%')
                    ->orWhere('users.email', 'like', '%' . $search . '%')
                    ->orWhere('roles.name', 'like', '%' . $search . '%');
            });
        }

        if (!empty($dateFilter)) {
            switch ($dateFilter) {
                case 'Show All':
                    break;
                case 'Last Week':
                    $query->whereBetween('users.last_login', [now()->subWeek(), now()]);
                    break;
                case 'Last Month':
                    $query->whereBetween('users.last_login', [now()->subMonth(), now()]);
                    break;
                case 'Last Year':
                    $query->whereBetween('users.last_login', [now()->subYear(), now()]);
                    break;
            }
        }

        if (!empty($sortBy)) {
            if ($sortBy == 'Z to A') {
                $query->orderBy('users.name', 'desc');
            } elseif ($sortBy == 'Date Added') {
                $query->orderBy('users.created_at', 'asc');
            }
        }

        $totalRecords = User::count();
        $totalFilteredRecords = $query->count(); 
        $currentUserRoles = $this->user->roles->pluck('id')->toArray();
        $clients = Client::with('assignedTo');
        if (!in_array(1, $currentUserRoles)) {
            $clients->whereHas('assignedTo', function ($query) {
                $query->where('user_id', auth()->id());
            });
        }
        $clients = $clients->get();

        $users = $query->skip($start)->take($length)->get()->map(function($user) use ($clients){
            $user->formatted_id = sprintf("%05d", $user->id);
            $user->avatar_url = $user->avatar ? asset($user->avatar) : asset('images/dummy_user_avatar.png');
            $user->last_login = $user->last_login == null ? '-' : Carbon::parse($user->last_login)->format('d-M-Y, h:i A');
            $user->roles_list = $user->roles->pluck('name')->join(', ');
            $user->clients_html = base64_encode(view('Users.partials.clients', ['clients' => $clients, 'user_id' => $user->id])->render());
            return $user;
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFilteredRecords,
            'data' => $users
        ]);
    }

    public function getUserFilterList() {
        $query = User::query()->usersWithLowerRole(auth()->user())
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.id', '<>', auth()->id())
            ->where('users.id', '<>', 1)
            ->select('users.id', 'users.name', 'users.email','roles.name as role');

        if ($this->user->can('user.view_assigned_users')) {
            $assignedUsers = UserAllocation::where('parent_id', auth()->user()->id)->pluck('user_id')->toArray();
            $query = $query->whereIn('users.id', $assignedUsers);
        }

        $users = $query->get()->map(function($user){
            $user->roles_list = $user->roles->pluck('name')->join(', ');
            unset($user->roles);
            return $user;
        });

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }
    public function create()
    {
        if (is_null($this->user) || !$this->user->can('user.create')) {
            abort(403, 'Sorry !! You are Unauthorized to create any user !');
        }
        $roles = Role::all();
        return view('Users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('user.create')) {
            abort(403, 'Sorry !! You are Unauthorized to create any user !');
        }
        // Validate the incoming request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',             // Minimum 8 characters
                'regex:/[a-z]/',     // At least one lowercase letter
                'regex:/[A-Z]/',     // At least one uppercase letter
                'regex:/[0-9]/',     // At least one number
                'regex:/[@$!%*?&#]/' // At least one special character
            ],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name', // Ensure roles exist in the database
            'status' => 'required|in:0,1',
        ]);

        // Create the user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'status' => $validatedData['status'],
        ]);

        // Assign roles to the user
        $user->syncRoles($validatedData['roles']);

        // Redirect with success message
        return redirect()->route('users.index')->with('success', 'User created successfully!');
    }


    public function edit($id)
    {
        if (is_null($this->user) || !$this->user->can('user.edit')) {
            abort(403, 'Sorry !! You are Unauthorized to edit any user !');
        }
        $user = User::findOrFail($id);
        $roles = Role::all();
        return view('Users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        if (is_null($this->user) || !$this->user->can('user.edit')) {
            abort(403, 'Sorry !! You are Unauthorized to edit any user !');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'status' => 'required|in:1,0',
        ]);

        $user = User::findOrFail($id); // Fetch the user
        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'status' => $request->input('status'),
        ]);
        $user->syncRoles($request->roles);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        try {
            // Find the user by ID
            $user = User::findOrFail($id);

            // Delete the user
            $user->delete();

            // Return a JSON response
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the user.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        if (is_null($this->user) || !$this->user->can('user.delete')) {
            abort(403, 'Sorry !! You are Unauthorized to delete any user !');
        }
        $ids = $request->input('ids');
        $ids = array_diff($ids, [1]);
        $deletedCount = User::whereIn('id', $ids)->delete();

        // Check if any users were deleted
        if ($deletedCount > 0) {
            return response()->json(['message' => 'Users deleted successfully.']);
        } else {
            return response()->json(['message' => 'No users found to delete.'], 404);
        }
    }
}
