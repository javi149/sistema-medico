<?php

namespace App\Http\Controllers;

use App\Models\Waitlist;
use App\Models\ProfessionalProfile;
use App\Mail\WaitlistSlotFreed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AdminWaitlistController extends Controller
{
    /**
     * Display a listing of the pending waitlist entries.
     */
    public function index()
    {
        $waitlists = Waitlist::where('status', 'Pendiente')
            ->with(['patient', 'specialty', 'professionalProfile.user'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Get all unique specialty IDs from the waitlist
        $specialtyIds = $waitlists->pluck('specialty_id')->filter()->unique();

        // Map specialty IDs to doctors having that specialty
        $doctorsBySpecialty = [];
        foreach ($specialtyIds as $specialtyId) {
            $doctorsBySpecialty[$specialtyId] = ProfessionalProfile::whereHas('specialties', function($q) use ($specialtyId) {
                $q->where('specialties.id', $specialtyId);
            })->with('user')->get();
        }

        return view('admin.waitlist.index', compact('waitlists', 'doctorsBySpecialty'));
    }

    /**
     * Approve slot availability for a waitlisted patient and notify them.
     */
    public function approve(Request $request, Waitlist $waitlist)
    {
        $request->validate([
            'start_datetime' => 'required|date|after:now',
            'professional_profile_id' => 'required|exists:professional_profiles,id',
        ]);

        $doctor = ProfessionalProfile::with('user')->findOrFail($request->professional_profile_id);
        $specialtyName = $waitlist->specialty->name ?? 'Especialidad';

        // Update the waitlist status, offered datetime, and professional profile
        $waitlist->update([
            'status' => 'Notificado',
            'offered_datetime' => $request->start_datetime,
            'professional_profile_id' => $request->professional_profile_id,
            'managed_by' => Auth::id(),
        ]);

        // Send the notification email
        try {
            Mail::to($waitlist->patient->email)->send(
                new WaitlistSlotFreed($waitlist, $doctor->user->name, $specialtyName, $request->start_datetime)
            );
        } catch (\Exception $e) {
            // Log or handle mail sending failure in local development if needed
        }

        return redirect()->route('admin.waitlist.index')->with('success', 'Disponibilidad aprobada y paciente notificado con éxito.');
    }
}
