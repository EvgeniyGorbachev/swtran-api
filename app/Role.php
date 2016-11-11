<?php namespace App;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at',
    ];

    /**
     * The roles that belong to the permissions.
     */
    public function permissions()
    {
        return $this->belongsToMany('App\Permission');
    }
}