<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Illuminate\Http\Request;
use App\QueryBuilderByParamsTrait;
use App\Document;


class User extends Authenticatable
{
    use EntrustUserTrait;
    use QueryBuilderByParamsTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 
        'email', 
        'surname', 
        'nick_name', 
        'mobile_phone', 
        'address', 
        'working_status', 
        'cdl_experience', 
        'doubles_experience',
        'term_reason',
        'veteran',
        'dl_exp_date',
        'mc_exp_date',
        'hire_date',
        'term_date'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at'
    ];

    /**
     * Get all of the role for the user.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }

    public function documents()
    {
        return $this->hasOne('App\Document');
    }

    public function getDlExpDateAttribute($value)
    {
        return $this->formatDateToGet($value);
    }

    public function getMcExpDateAttribute($value)
    {
        return $this->formatDateToGet($value);
    }

    public function getHireDateAttribute($value)
    {
        return $this->formatDateToGet($value);
    }

    public function getTermDateAttribute($value)
    {
        return $this->formatDateToGet($value);
    }
    
    public function formatDateToGet($value)
    {
        return ($value == '0000-00-00 00:00:00')? 0: strtotime($value);
    } 
    

    public function setDlExpDateAttribute($value)
    {
        $this->attributes['dl_exp_date'] = $this->formatDateToSet($value);
    }

    public function setMcExpDateAttribute($value)
    {
        $this->attributes['mc_exp_date'] = $this->formatDateToSet($value);
    }

    public function setHireDateAttribute($value)
    {
        $this->attributes['hire_date'] = $this->formatDateToSet($value);
    }

    public function setTermDateAttribute($value)
    {
        $this->attributes['term_date'] = $this->formatDateToSet($value);
    }
    
    public function formatDateToSet($value)
    {
        return ($value)? date("Y-m-d H:i:s", $value): null;
    }

    public function scopeJoinRole($query)
    {
        return $query->join('role_user', 'role_user.user_id', '=', 'users.id')
        ->join('roles', 'roles.id', '=', 'role_user.role_id');
    }

    public function scopeGetAllWhere($query, $request)
    {
        $query->joinRole()
            ->where('is_deleted', '<>', 1)
            ->select('users.*', 'roles.display_name as role_label', 'roles.id as role_id');
        $query = $this->useParams($query, $request, ['users', 'roles']);

        return $query->get();
    }
    
    public function getRoleId()
    {
        $role = $this->roles()->get();
        return $role[0]->id;
    }

    public function setRoles()
    {
        $role = $this->roles()->get();
        $this->role_label = $role[0]->display_name;
        $this->role_id = $role[0]->id;
    }
    
    public function setPermissions()
    {
        $this->permissions = Role::find($this->getRoleId())
            ->permissions()
            ->get();
    }

    public function getFullData()
    {
        $this->setRoles();
        $this->setPermissions();
        return $this;
    }

    public function canSetRole($roleId)
    {
        //only admin can create admin(1) and manager(2) roles
        return !(!$this->hasRole('admin') && ($roleId == 1 || $roleId == 2));
    }

}
