<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Permission;
use App\User;
use App\Role;
use DB;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function get(Request $request)
    {
        $searchByName =  $request->input('searchByName');

        $sortAsc =  $request->input('sortAsc');
        $sortDesc =  $request->input('sortDesc');

        $users = User::where('is_deleted', '<>', 1)
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.*', 'roles.display_name as role_label', 'roles.id as role_id');

        if ($request->has('searchByName')) {
            $users = $users->where('name', 'LIKE', '%' . $searchByName . '%');
        } elseif ($request->has('sortAsc')) {
            $users = $users->orderBy($sortAsc, 'asc');
        } elseif ($request->has('sortDesc')) {
            $users = $users->orderBy($sortDesc, 'desc');
        }

        $users = $users->get();

        return response()->success(compact('users'));
    }

    public function getPersonal(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user = $user->getFullData();

        return response()->success(compact('user'));
    }

    public function getRoles(Request $request)
    {
        $userRoles = Role::all();
        return response()->success(compact('userRoles'));
    }

    public function checkLogin(Request $request)
    {
//        return trim($request->login);
        $users = User::where('personal_id', '=', trim($request->login))->first();
        $isUnique = !boolval($users);

        return response()->success(compact('isUnique'));
        
    }

    public function createEntrustEntity(Request $request)
    {
//        attach role to user
//        $admin = Role::where('name','=','driver')->first();
//        $user = User::where('id', '=', '11')->first();
//        $user->attachRole($admin);
        
//        add new role
//        $owner = new Role();
//        $owner->name         = 'driver';
//        $owner->display_name = 'Driver'; // optional
//        $owner->description  = 'User is the driver of a given project'; // optional
//        $owner->save();

//        create permission
//        $permission = new Permission();
//        $permission->name         = 'create-user';
//        $permission->display_name = 'Create new user'; // optional
//        $permission->description  = 'Can create new user'; // optional
//        $permission->save();

//        attach permission to role
//        $role = Role::where('name','=','support')->first();
//        $permission = Permission::where('id', '=', '1')->first();
//        $role->attachPermission($permission);

    }

}