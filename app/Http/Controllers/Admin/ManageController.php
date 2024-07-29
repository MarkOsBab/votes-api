<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePassword;
use App\Http\Requests\Admin\CreateVoterRequest;
use App\Models\Voter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManageController extends Controller
{
    
    public function changePassword(ChangePassword $request)
    {
        $admin = Auth::user();

        if (!Hash::check($request->currentPassword, $admin->password)) {
            return response()->json(['message' => 'Clave actual incorrecta'], 400);
        }

        $lastThreePasswords = $admin->passwordHistories()
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->pluck('password');

        foreach ($lastThreePasswords as $oldPassword) {
            if (Hash::check($request->newPassword, $oldPassword)) {
                return response()->json(['message' => 'Ya has utilizado esta clave'], 400);
            }
        }

        $admin->storePasswordInHistory();

        $admin->password = Hash::make($request->newPassword);
        $admin->save();

        return response()->json(['message' => 'Clave actualizada exitosamente'], 200);
    }

    public function crateVoter(CreateVoterRequest $request)
    {
        $voter = Voter::create([
            'document' => $request->validated('document'),
            'name' => $request->validated('name'),
            'lastName' => $request->validated('lastName'),
            'dob' => $request->validated('dob'),
            'is_candidate' => $request->validated('is_candidate'),
        ]);

        return response()->json($voter);
    }
}