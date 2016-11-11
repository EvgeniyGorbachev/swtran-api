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

    public function register(Request $request)
    {
        $this->validate($request, [
            'name'       => 'required|max:20',
            'surname'       => 'max:20',
            'nick_name'       => 'max:20',
            'personal_id'=> 'required|integer|unique:users',
            'role_id'=> 'required',
            'email'      => 'required|email',
            'mobile_phone'      => 'required',
            'address'      => 'required|string|max:40',
            'password'   => 'required|min:5',
            'working_status'   => 'integer',
            'cdl_experience'   => 'numeric',
            'doubles_experience'   => 'string|max:50',
            'term_reason'   => 'string|max:50',
            'veteran'   => 'string|max:50',
            'dl_exp_date'   => 'digits:10',
            'mc_exp_date'   => 'digits:10',
            'hire_date'   => 'digits:10',
            'term_date'   => 'digits:10',
        ]);

        $user = new User;
        $user->name = trim($request->name);
        $user->surname = trim($request->surname);
        $user->nick_name = trim($request->nick_name);
        $user->personal_id = trim($request->personal_id);
        $user->email = trim(strtolower($request->email));
        $user->password = bcrypt($request->password);
        $user->mobile_phone = trim($request->mobile_phone);
        $user->address = trim($request->address);
        $user->working_status = trim($request->working_status);
        $user->cdl_experience = trim($request->cdl_experience);
        $user->doubles_experience = trim($request->doubles_experience);
        $user->term_reason = trim($request->term_reason);
        $user->veteran = trim($request->veteran);
        
        $user->dl_exp_date = date("Y-m-d H:i:s", $request->dl_exp_date);
        $user->mc_exp_date = date("Y-m-d H:i:s", $request->mc_exp_date);
        $user->hire_date = date("Y-m-d H:i:s", $request->hire_date);
        $user->term_date = date("Y-m-d H:i:s", $request->term_date);
        $user->save();

        $role = Role::where('id','=',trim($request->role_id))->first();
        $user->attachRole($role);

        $token = JWTAuth::fromUser($user);

        return response()->success(compact('user', 'token'));
    }
}
