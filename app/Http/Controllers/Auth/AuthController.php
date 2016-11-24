<?php

namespace App\Http\Controllers\Auth;

use Auth;
use JWTAuth;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->validate($request, [
            'personal_id'    => 'required',
            'password' => 'required|min:5',
        ]);

        $credentials = $request->only('personal_id', 'password');

        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->error('Invalid credentials', 401);
            }
        } catch (\JWTException $e) {
            return response()->error('Could not create token', 500);
        }

        $user = Auth::user();

        return response()->success(compact('user', 'token'));
    }
}
