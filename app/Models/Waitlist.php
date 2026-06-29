<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'professional_profile_id',
        'specialty_id',
        'status',
        'offered_datetime',
        'managed_by',
    ];

    /**
     * Relación: El paciente en lista de espera.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Relación: El médico solicitado (puede ser null).
     */
    public function professionalProfile(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class);
    }

    /**
     * Relación: La especialidad solicitada (puede ser null).
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    /**
     * Relación: El administrador que gestionó este registro.
     */
    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'managed_by');
    }

    /**
     * Find the first patient in the waitlist and notify them.
     */
    public static function notifyNextInWaitlist(int $specialtyId, int $professionalProfileId, string $freedDatetime): void
    {
        $nextInLine = self::with(['patient', 'specialty', 'professionalProfile.user'])
            ->where('specialty_id', $specialtyId)
            ->where('status', 'Pendiente')
            ->where(function ($query) use ($professionalProfileId) {
                $query->whereNull('professional_profile_id')
                      ->orWhere('professional_profile_id', $professionalProfileId);
            })
            ->orderBy('created_at', 'asc')
            ->first();

        if ($nextInLine) {
            $doctorName = $nextInLine->professionalProfile 
                ? $nextInLine->professionalProfile->user->name 
                : (\App\Models\ProfessionalProfile::with('user')->find($professionalProfileId)->user->name ?? 'Médico');
            
            $specialtyName = $nextInLine->specialty 
                ? $nextInLine->specialty->name 
                : (\App\Models\Specialty::find($specialtyId)->name ?? 'Especialidad');

            $nextInLine->update([
                'status' => 'Notificado',
                'offered_datetime' => $freedDatetime,
                'professional_profile_id' => $nextInLine->professional_profile_id ?? $professionalProfileId,
            ]);

            try {
                \Illuminate\Support\Facades\Mail::to($nextInLine->patient->email)->send(
                    new \App\Mail\WaitlistSlotFreed($nextInLine, $doctorName, $specialtyName, $freedDatetime)
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error sending waitlist notification email: ' . $e->getMessage());
            }
        }
    }
}
