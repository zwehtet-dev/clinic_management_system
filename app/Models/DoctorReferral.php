<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorReferral extends Model
{
    protected $fillable = [
        'doctor_id',
        'visit_id',
        'referral_fee',
        'status'
    ];

    protected $casts = [
        'referral_fee' => 'decimal:2',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }
}
