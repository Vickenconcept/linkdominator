<?php

namespace App\Http\Controllers;

use App\Models\Integration;
use Illuminate\Http\Request;

class SocialAccountController extends Controller
{
    public function index()
    {
        $user = auth()->user()->id;

        $accounts = Integration::where('user_id', $user)->paginate(10);

        return view('auth.social-account', compact('accounts'));
    }

    public function disconnect(string $id)
    {
        $account = Integration::findOrFail($id);

        $account->delete();

        notify()->success('Account disconnected');
        return redirect()->route('social-account.index');
    }
}
