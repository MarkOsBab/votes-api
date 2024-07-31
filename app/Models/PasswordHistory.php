<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $fillable = ['password', 'admin_id'];

    public function user()
    {
        return $this->belongsTo(Admin::class);
    }
}
