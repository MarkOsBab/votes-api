<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="PasswordHistory",
 *     type="object",
 *     description="Historial de contraseñas",
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         description="Contraseña del historial"
 *     ),
 *     @OA\Property(
 *         property="admin_id",
 *         type="integer",
 *         description="ID del administrador"
 *     )
 * )
*/
class PasswordHistory extends Model
{
    protected $fillable = ['password', 'admin_id'];

    public function user()
    {
        return $this->belongsTo(Admin::class);
    }
}
