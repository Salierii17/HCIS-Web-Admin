<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Return the authenticated user's details.
     */
    public function show(Request $request)
    {
        // The auth()->user() helper returns the full user model
        // for the token that was sent with the request.
        // We wrap it in a 'user' key to match your API documentation.
        return response()->json([
            'status' => 'success',
            'code' => 200,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'user' => auth()->user()
            ]
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        // Add your update logic here
        // e.g., $request->user()->update($request->validated());
        return response()->json(['message' => 'Profile update endpoint hit.']);
    }

    /**
     * Upload a new profile photo.
     */
    public function uploadPhoto(Request $request)
    {
        // Add your photo upload logic here
        return response()->json(['message' => 'Photo upload endpoint hit.']);
    }

    /**
     * Delete the user's profile photo.
     */
    public function deletePhoto(Request $request)
    {
        // Add your photo deletion logic here
        return response()->json(['message' => 'Photo delete endpoint hit.']);
    }

    /**
     * Change the user's password.
     */
    public function changePassword(Request $request)
    {
        // Add your password change logic here
        return response()->json(['message' => 'Password change endpoint hit.']);
    }

    /**
     * Delete the user's account.
     */
    public function deleteAccount(Request $request)
    {
        // Add your account deletion logic here
        return response()->json(['message' => 'Account delete endpoint hit.']);
    }
}
