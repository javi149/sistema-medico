<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\Waitlist;
use App\Mail\WaitlistSlotFreed;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        // 1. Check if status changed to cancelled (case-insensitive)
        if ($appointment->wasChanged('status') && in_array(strtolower($appointment->status), ['cancelada'])) {
            $this->notifyNextInWaitlist(
                $appointment->specialty_id,
                $appointment->professional_profile_id,
                $appointment->start_datetime
            );
        }
        // 2. Check if start_datetime changed (rescheduled), which frees the old slot
        elseif ($appointment->wasChanged('start_datetime')) {
            $oldDatetime = $appointment->getOriginal('start_datetime');
            // Only notify if the old datetime is in the future
            if ($oldDatetime && \Carbon\Carbon::parse($oldDatetime)->isFuture()) {
                $this->notifyNextInWaitlist(
                    $appointment->specialty_id,
                    $appointment->professional_profile_id,
                    $oldDatetime
                );
            }
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        // Only notify if the deleted appointment was in the future and was active (not already cancelled)
        if (\Carbon\Carbon::parse($appointment->start_datetime)->isFuture() && !in_array(strtolower($appointment->status), ['cancelada'])) {
            $this->notifyNextInWaitlist(
                $appointment->specialty_id,
                $appointment->professional_profile_id,
                $appointment->start_datetime
            );
        }
    }

    /**
     * Find the first patient in the waitlist and notify them.
     */
    protected function notifyNextInWaitlist(int $specialtyId, int $professionalProfileId, string $freedDatetime): void
    {
        // Find the first pending waitlist entry matching the specialty and (optionally) the doctor
        $nextInLine = Waitlist::with(['patient', 'specialty', 'professionalProfile.user'])
            ->where('specialty_id', $specialtyId)
            ->where('status', 'Pendiente')
            ->where(function ($query) use ($professionalProfileId) {
                $query->whereNull('professional_profile_id')
                      ->orWhere('professional_profile_id', $professionalProfileId);
            })
            ->orderBy('created_at', 'asc')
            ->first();

        if ($nextInLine) {
            // Get doctor and specialty names
            $doctorName = $nextInLine->professionalProfile 
                ? $nextInLine->professionalProfile->user->name 
                : (\App\Models\ProfessionalProfile::with('user')->find($professionalProfileId)->user->name ?? 'Médico');
            
            $specialtyName = $nextInLine->specialty 
                ? $nextInLine->specialty->name 
                : (\App\Models\Specialty::find($specialtyId)->name ?? 'Especialidad');

            // Update status to Notificado
            $nextInLine->update([
                'status' => 'Notificado',
            ]);

            // Send Email
            try {
                Mail::to($nextInLine->patient->email)->send(
                    new WaitlistSlotFreed($nextInLine, $doctorName, $specialtyName, $freedDatetime)
                );
            } catch (\Exception $e) {
                Log::error('Error sending waitlist notification email: ' . $e->getMessage());
            }
        }
    }
}
