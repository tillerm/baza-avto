<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use EloquentFilter\Filterable;

class Supply extends Model
{
    use HasFactory, Filterable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'price',
        'equipment_id',
        'supplier_id',
        'created_at',
        'supplied_at',
    ];

    protected $dates = [
        'created_at',
        'supplied_at'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->hasOne(Car::class);
    }
}
