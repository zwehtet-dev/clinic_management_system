<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'patient_id',
        'doctor_id',
        'visit_type',
        'consultation_fee',
        'visit_date',
        'diagnosis',
        'notes',
        'status'
    ];

    protected $casts = [
        'consultation_fee' => 'decimal:2',
        'visit_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function invoice()
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }


    public function doctorReferral()
    {
        return $this->hasOne(DoctorReferral::class);
    }

    public static function generatePublicId(): string
    {
        $year = now()->format('Y');
        $prefix = "VIS-{$year}-";

        // Get the latest visit ID
        $lastPatient = self::latest('id')->first();

        $nextNumber = $lastPatient ? $lastPatient->id + 1 : 1;

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
