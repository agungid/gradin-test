<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    public const MIN_LEVEL = 1;
    public const MAX_LEVEL = 5;

    public const VEHICLE_TYPES = [
        'motorcycle',
        'car',
        'van',
        'bicycle',
        'pickup',
    ];

    public const STATUSES = [
        'active',
        'inactive',
        'suspended',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'level',
        'vehicle_type',
        'vehicle_plate',
        'service_area',
        'status',
        'registered_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'registered_at' => 'datetime',
        ];
    }
}
