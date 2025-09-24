<?php

namespace App\Filament\Resources\Visits\Pages;

use App\Models\Drug;
use App\Models\Visit;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\InvoiceItem;
use Illuminate\Support\Str;
use App\Models\DoctorReferral;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Visits\VisitResource;

class CreateVisit extends CreateRecord
{
    protected static string $resource = VisitResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pre-fill patient_id if passed from URL (with validation)
        if (request()->has('patient_id')) {
            $patientId = request()->integer('patient_id');
            if ($patientId > 0 && \App\Models\Patient::where('id', $patientId)->exists()) {
                $data['patient_id'] = $patientId;
            }
        }

        // Pre-fill doctor_id if passed from URL (with validation)
        if (request()->has('doctor_id')) {
            $doctorId = request()->integer('doctor_id');
            if ($doctorId > 0 && \App\Models\Doctor::where('id', $doctorId)->exists()) {
                $data['doctor_id'] = $doctorId;
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Visit $visit */
        $visit = $this->record;

        if ($visit->doctor_id && $visit->consultation_fee > 0) {
            DoctorReferral::create([
                'doctor_id'       => $visit->doctor_id,
                'visit_id'        => $visit->id,
                'referral_fee' => $visit->consultation_fee, // You can adjust if percentage applies
                'status'          => 'unpaid',
            ]);
        }
    }

}
