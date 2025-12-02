<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileEditController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users,phone,' . $user->id,
        ]);

        $user->update([
            'name'  => $request->name,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Профиль обновлён!');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|confirmed|min:6',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Текущий пароль неверный.');
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Пароль изменён!');
    }
}
