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
            \App\Models\Waitlist::notifyNextInWaitlist(
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
                \App\Models\Waitlist::notifyNextInWaitlist(
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
            \App\Models\Waitlist::notifyNextInWaitlist(
                $appointment->specialty_id,
                $appointment->professional_profile_id,
                $appointment->start_datetime
            );
        }
    }
}
