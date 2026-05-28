<?php

namespace App\Models;

use App\Traits\SearchTrait;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EModel;

class Car extends EModel
{
    use Filterable, HasFactory, SearchTrait;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'price',
        'car_price',
        'customs',
        'status',
        'vin',
        'chassis_number',
        'body_number',
        'mileage',
        'color',
        'state_number',
        'pts_series',
        'pts_number',
        'pts_issued_by',
        'pts_issued_at',
        'sts_series',
        'sts_number',
        'sts_issued_by',
        'sts_issued_at',
        'supply_id',
        'manager_id',
        'release_date',
        'pinned',
    ];

    protected $dates = [
        'release_date',
    ];

    protected $casts = [
        'pinned' => 'boolean',
    ];

    public function supply()
    {
        return $this->belongsTo('App\Models\Supply');
    }

    public function photos()
    {
        return $this->hasMany('App\Models\CarPhoto')
            ->orderByDesc('is_primary')
            ->orderBy('id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }
}
