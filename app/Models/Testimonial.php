<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    public const TYPE_TEXT = 'text';
    public const TYPE_VIDEO = 'video';

    protected $fillable = [
        'type',
        'title',
        'text',
        'author_name',
        'car_model',
        'city',
        'rating',
        'video_url',
        'photo',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
        'position' => 'integer',
    ];
}
