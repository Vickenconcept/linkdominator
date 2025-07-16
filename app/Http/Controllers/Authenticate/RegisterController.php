<?php

namespace App\Http\Controllers\Authenticate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function bundleSignup()
    {
        return view('auth.bundle-access');
    }

    public function bundleSignupAuth(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'created_by' => 1,
            'user_type' => 'owner'
        ])->assignRole('User');

        // Assign permission FE
        $user->givePermissionTo(['FE','OTO1','OTO2','OTO3','OTO4','OTO5','OTO6','OTO7','OTO8','Bundle']);

        notify()->success('You have successfully signed up.');
        return redirect()->route('auth.login');
    }

    public function resellerSignup()
    {
        return view('auth.reseller-access');
    }

    public function resellerSignupAuth(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'email' => ['required','email'],
            'password' => ['required']
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'created_by' => 1,
            'user_type' => 'owner'
        ])->assignRole('User');

        // Assign permission FE
        $user->givePermissionTo(['FE','OTO5']);

        notify()->success('You have successfully signed up.');
        return redirect()->route('auth.login');
    }
}
