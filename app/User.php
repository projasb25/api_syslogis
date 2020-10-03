<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'user';
    
    /**
    * The primary key associated with the table.
    *
    * @var string
    */
    protected $primaryKey = 'id_user';
    
    /**
    * Indicates if the IDs are auto-incrementing.
    *
    * @var bool
    */
    public $incrementing = false;
    
    /**
    * Indicates if the model should be timestamped.
    *
    * @var bool
    */
    public $timestamps = false;
    
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $fillable = [];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getIdentifierData()
    {
        $payload = JWTAuth::parseToken()->getPayload();
        return [
            'username' => $this->username,
            'current_org' => $payload->get('current_org'),
            'current_corp' => $payload->get('current_corp')
        ];
    }
}