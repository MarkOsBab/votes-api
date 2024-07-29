<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 

class Admin extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lastName',
        'email',
    ];

    protected $hidden = [
        'password'
    ];

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function storePasswordInHistory()
    {
        $this->passwordHistories()->create(['password' => $this->password]);
    }

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

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'email' => $this->email,
        ];
    }
}
