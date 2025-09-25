<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'name',
        'specialization',
        'license_number',
        'phone',
        'address',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function visits()
    {
        return $this->hasMany(Visit::class);
    }

    public function doctorReferrals()
    {
        return $this->hasMany(DoctorReferral::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
