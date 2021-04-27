<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class IntegrationUser extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'integration_user';
    protected $primaryKey = 'id_integration_user';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
