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

    public function register(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->authenticate();

        if (!$currentUser->canSetRole(trim($request->role_id))) {
            return response()->error('Could not create user with such a role', 403);
        }

        $this->validate($request, [
            'name'       => 'required|max:20',
            'surname'       => 'max:20',
            'nick_name'       => 'max:20',
            'personal_id'=> 'required|integer|unique:users',
            'role_id'=> 'required',
            'email'      => 'required|email',
            'mobile_phone'      => 'required',
            'address'      => 'required|string|max:40',
            'password'   => 'required|string|min:5',
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

        $user->save();

        $role = Role::where('id','=',trim($request->role_id))->first();
        $user->attachRole($role);

        $token = JWTAuth::fromUser($user);

        return response()->success(compact('user', 'token'));
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
    
    public function getUser(User $user){
        $user = $user->getFullData();
        return response()->success(compact('user'));
    }
    
    public function editUser(Request $request){

        $this->validate($request, [
            'name'       => 'required|max:20',
            'surname'       => 'max:20',
            'nick_name'       => 'max:20',
            'personal_id'=> 'required|integer',
            'role_id'=> 'required',
            'email'      => 'required|email',
            'mobile_phone'      => 'required',
            'address'      => 'required|string|max:40',
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

        $user = User::find(trim($request->id));
        $currentUser = JWTAuth::parseToken()->authenticate();

        //update role
        if ($currentUser->canSetRole(trim($request->role_id))) {
            $user->roles()->sync([trim($request->role_id)]);
        } else {
            return response()->error('Could not update user with such a role', 403);
        }

        $user = $user->update($request->all());
        
        if ($user) {
            return response()->success(compact('user'));
        } else {
            return response()->error('It is impossible to save the user', 500);
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
//        $permission->name         = 'edit-user';
//        $permission->display_name = 'Edit new user'; // optional
//        $permission->description  = 'Can edit new user'; // optional
//        $permission->save();

//        attach permission to role
//        $role = Role::where('name','=','admin')->first();
//        $permission = Permission::where('id', '=', '4')->first();
//        $role->attachPermission($permission);

    }

}