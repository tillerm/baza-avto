<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'link',
        'content',
        'published_at',
        'approved',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'published_at' => 'datetime',
    ];
}
