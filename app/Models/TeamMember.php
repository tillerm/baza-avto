<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'role',
        'city',
        'phone',
        'telegram_username',
        'description',
        'photo',
        'photo_focus_x',
        'photo_focus_y',
        'position',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
