<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;


class User extends Authenticatable
{
    use EntrustUserTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'personal_id'
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
     * Get all of the posts for the country.
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role');
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

}
