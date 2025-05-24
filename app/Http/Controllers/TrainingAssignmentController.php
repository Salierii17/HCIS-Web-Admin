<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Package;
use App\Notifications\TrainingAssignedNotification;

class TrainingAssignmentController extends Controller
{
    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $package = Package::findOrFail($request->package_id);

        // Kirim notifikasi ke email user
        $user->notify(new TrainingAssignedNotification($package));

        return back()->with('success', 'Training package has been assigned and notification sent.');
    }

    public function form()
    {
        $users = User::all();
        $packages = Package::all();

        return view('assign-training', compact('users', 'packages'));
    }
}
