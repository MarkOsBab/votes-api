<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject; 

/**
 * @OA\Schema(
 *     schema="Admin",
 *     type="object",
 *     description="Modelo de Administrador",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID del administrador"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         description="Clave del administrador"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre del administrador"
 *     ),
 *     @OA\Property(
 *         property="lastname",
 *         type="string",
 *         description="Apellido del administrador"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         description="Email del administrador"
 *     )
 * )
*/
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
        $this->passwordHistories()->create(['password' => $this->password, 'admin_id' => $this->id]);
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
