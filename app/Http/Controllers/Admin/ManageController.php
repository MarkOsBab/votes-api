<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangePassword;
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
}