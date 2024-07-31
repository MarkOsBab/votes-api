<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Voter",
 *     type="object",
 *     description="Modelo de Votante",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID del votante"
 *     ),
 *     @OA\Property(
 *         property="document",
 *         type="string",
 *         description="Documento del votante"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Nombre del votante"
 *     ),
 *     @OA\Property(
 *         property="lastname",
 *         type="string",
 *         description="Apellido del votante"
 *     ),
 *     @OA\Property(
 *         property="birth_day",
 *         type="string",
 *         format="date",
 *         description="Fecha de nacimiento del votante"
 *     ),
 *     @OA\Property(
 *         property="is_candidate",
 *         type="integer",
 *         description="Indica si el votante es candidato"
 *     )
 * )
*/
class Voter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lastName',
        'document',
        'dob',
        'is_candidate',
    ];

    public function votes()
    {
        return $this->hasOne(Vote::class, 'candidate_id');
    }

    public function voted()
    {
        return $this->hasMany(Vote::class, 'candidate_voted_id', 'id');
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'document' => $this->document,
            'name' => $this->name,
            'lastname' => $this->lastName,
            'birth_day' => $this->dob,
            'is_candidate' => $this->is_candidate
        ];
    }
}
