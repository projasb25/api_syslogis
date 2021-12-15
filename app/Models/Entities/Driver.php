<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Driver extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'driver';
    protected $primaryKey = 'driverid';
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
