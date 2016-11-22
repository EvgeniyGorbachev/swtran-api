<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Permission;
use Storage;
use File;
use App\User;
use App\Document;
use App\Role;
use DB;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function get(Request $request)
    {
        $users = User::getAllWhere($request);
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

    public function currentUser()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    public function checkLogin(Request $request)
    {
        $users = User::where('personal_id', '=', trim($request->login))->first();
        $isUnique = !boolval($users);

        return response()->success(compact('isUnique'));

    }

    public function upload(Request $request)
    {
        $user_id = trim($request->user_id);
        $file_type = trim($request->file_type);

        $pathDocuments =  public_path('img/documents');
        $pathUser =  public_path('img/documents/user');
        $pathUserPersonal =  public_path('img/documents/user/' . $user_id);

        $salt = 'asduf8uqhfinDFWuw45356$%^@!%#adshfi___udsiofj qejfkjnLOP:OLIKTRE~~~dsfmndsafqiu';

        if ($request->file('file')->isValid()) {

            if(!File::exists($pathDocuments)) {
                File::makeDirectory($pathDocuments, $mode = 0775, true, true);
            }

            if(!File::exists($pathUser)) {
                File::makeDirectory($pathUser, $mode = 0775, true, true);
            }

            if(!File::exists($pathUserPersonal)) {
                File::makeDirectory($pathUserPersonal, $mode = 0775, true, true);
            }

            //no more than 1 Mb
            if ($request->file('file')->getClientSize() <= 1000000) {

                $file_ext =  $request->file('file')->getClientOriginalExtension();
                $file_name = md5($user_id . $salt . $file_type . time());
                $full_file_name = $file_name . '.' .  $file_ext;

                //save file on disk
                if ($request->file('file')->move($pathUserPersonal, $full_file_name)){
                    //save file to database
                    Document::saveDocument($full_file_name, $file_type, $user_id);
                } else {
                    return response()->error('Could not save file', 403);
                }

                return response()->success(compact('full_file_name'));
            } else {
                return response()->error('Could not save file more than 1 Mb', 403);
            }
        } else {
            return response()->error('Its not valid file', 403);
        }
    }

    public function deleteUser(User $user){
        $user->is_deleted = true;
        if ($user->save()) {
            return response()->success(compact('user'));
        }
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