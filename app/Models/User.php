<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getPreferencesAttribute( $value ) {
        $value = str_replace('[',"",$value);
        $value = str_replace(']',"",$value);
        $value = str_replace('"', "", $value);
        return $value? explode(',',$value) : null;
    }

    public function getUpdatedAtAttribute( $value ) {
        return $value? (new Carbon($value))->format('Y-m-d H:i:s') : null;
    }

    public function getCreatedAtAttribute( $value ) {
        return $value? (new Carbon($value))->format('Y-m-d H:i:s') : null;
    }
}
