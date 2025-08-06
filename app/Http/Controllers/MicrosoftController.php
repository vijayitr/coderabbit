<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MicrosoftController extends Controller
{
    public function redirectToMicrosoft()
    {
        $user = Auth::user();

        // If the user is already authenticated and has a valid Microsoft token
        if ($user && $user->microsoft_token) {
            try {
                // Use the stored token to authenticate
                $microsoftUser = Socialite::driver('microsoft')->userFromToken($user->microsoft_token);

                // If token is valid, log the user in and redirect
                Auth::login($user, true);
                return redirect('/dashboard');
            } catch (\Exception $e) {
                // If the token is invalid, proceed with OAuth flow
                return Socialite::driver('microsoft')->redirect();
            }
        }

        // If not authenticated, proceed with OAuth flow
        return Socialite::driver('microsoft')->redirect();
    }

    // Handle Microsoft callback
    public function handleMicrosoftCallback()
    {
        try {
            // Retrieve the user from Microsoft OAuth
            $microsoftUser = Socialite::driver('microsoft')->user();
            
            // Check if the user already exists
            $user = User::where('email', $microsoftUser->email)->first();

            if (!$user) {
                // First-time login: create a new user
                $user = User::create([
                    'name' => $microsoftUser->name ?? 'No Name',
                    'email' => $microsoftUser->email,
                    'microsoft_token' => $microsoftUser->token,
                    'password' => bcrypt('Itr1233#'), // Generate random password
                ]);

                $allowedEmails = ['arvind@turnaroundin.com', 'vijay@itradicals.com'];

                if (in_array($microsoftUser->email, $allowedEmails)) {
                    $user->syncRoles('Manager');
                }

            }else {
                // If the user exists, you can update the token (if necessary)
                echo 'User already exists';
                $user->update([
                    'microsoft_token' => $microsoftUser->token,  // Save or update the token
                ]);
            }
            // Log the user in
             Auth::login($user);

            // Redirect to home/dashboard
            return redirect('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Something went wrong. Please try again.');
        }
    }
    public function dashboard()
    {
        $user = Auth::user(); // Retrieve the currently authenticated user
        return view('dashboard', ['user' => $user]);
    }
}
