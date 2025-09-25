<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'name',
        'age',
        'gender',
        'phone',
        'address',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function generatePublicId(): string
    {
        $year = now()->format('Y');
        $prefix = "PAT-{$year}-";

        // Get the latest patient ID
        $lastPatient = self::latest('id')->first();

        $nextNumber = $lastPatient ? $lastPatient->id + 1 : 1;

        return $prefix . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function drugSales(): HasMany
    {
        return $this->hasMany(DrugSale::class);
    }

}
