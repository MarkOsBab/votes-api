<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_name' => $this->lastName,
            'birth_day' => $this->dob,
            'is_candidate' => $this->is_candidate
        ];
    }
}
