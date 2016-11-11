<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Member;
use App\UserRoles;
use DB;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function get(Request $request)
    {
        $searchByName =  $request->input('searchByName');

        $sortAsc =  $request->input('sortAsc');
        $sortDesc =  $request->input('sortDesc');

        $members = Member::where('is_deleted', '<>', 1)
            ->join('user_roles', 'users.role_id', '=', 'user_roles.id')
            ->select('users.*', 'user_roles.role_label');

        if ($request->has('searchByName')) {
            $members = $members->where('name', 'LIKE', '%' . $searchByName . '%');
        } elseif ($request->has('sortAsc')) {
            $members = $members->orderBy($sortAsc, 'asc');
        } elseif ($request->has('sortDesc')) {
            $members = $members->orderBy($sortDesc, 'desc');
        }

        $members = $members->get();

        return response()->success(compact('members'));
    }

    public function getPersonal(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        return response()->success(compact('user'));
    }

    public function getRoles(Request $request)
    {
        $userRoles = UserRoles::all();
        return response()->success(compact('userRoles'));
    }

}