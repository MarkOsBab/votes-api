<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Vote",
 *     type="object",
 *     description="Modelo de Voto",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID del voto"
 *     ),
 *     @OA\Property(
 *         property="voter",
 *         ref="#/components/schemas/Voter"
 *     ),
 *     @OA\Property(
 *         property="candidate",
 *         ref="#/components/schemas/Voter"
 *     ),
 *     @OA\Property(
 *         property="date",
 *         type="string",
 *         format="date",
 *         description="Fecha del voto"
 *     )
 * )
*/
class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'candidate_voted_id',
        'date',
    ];

    public $timestamps = false;

    public function voter()
    {
        return $this->belongsTo(Voter::class, 'candidate_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Voter::class, 'candidate_voted_id');
    }
    

    public function toArray()
    {
        return [
            'id' => $this->id,
            'voter' => $this->voter,
            'candidate' => $this->candidate,
            'date' => $this->date
        ];
    }
}
