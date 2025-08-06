<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ActivityLog;
use App\Models\User;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $oldData = $user->only(['name', 'email', 'profile_image']);
        $newData = $request->only(['name', 'email']);

        $changes = [];
        foreach ($newData as $key => $value) {
            if ($oldData[$key] != $value) {
                $changes[$key] = [
                    'old' => $oldData[$key],
                    'new' => $value
                ];
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update($request->only(['name']));

        if (!empty($changes)) {
            ActivityLog::create([
                'user_id' => $user->id,
                'activity' => 'Updated profile information',
                'model' => 'User',
                'model_id' => $user->id,
                'changes' => $changes,
            ]);
        }

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $oldImage = $user->profile_image;

        // Delete old image if exists
        if ($oldImage && Storage::exists('public/profile_images/' . basename($oldImage))) {
            Storage::delete('public/profile_images/' . basename($oldImage));
        }

        // Store new image
        $imageName = time() . '.' . $request->profile_image->extension();
        $request->profile_image->storeAs('public/profile_images', $imageName, 'public');
        $newImage = 'storage/public/profile_images/' . $imageName;

        // Update user profile image
        $user->update(['profile_image' => $newImage]);

        // Log the image update
        ActivityLog::create([
            'user_id' => $user->id,
            'activity' => 'Updated profile image',
            'model' => 'User',
            'model_id' => $user->id,
            'changes' => [
                'profile_image' => [
                    'old' => $oldImage,
                    'new' => $newImage
                ]
            ],
        ]);

        return response()->json(['success' => true, 'image' => $newImage]);
    }
}
