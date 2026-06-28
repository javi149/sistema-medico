<?php

namespace App\Http\Controllers;

use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaitlistController extends Controller
{
    /**
     * Store a new waitlist entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'specialty_id' => 'required|exists:specialties,id',
            'professional_profile_id' => 'nullable|exists:professional_profiles,id',
        ]);

        // Check if the patient is already in the waitlist for this specialty with a 'Pendiente' status
        $exists = Waitlist::where('patient_id', $request->patient_id)
            ->where('specialty_id', $request->specialty_id)
            ->where('status', 'Pendiente')
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors([
                'error' => 'Ya te encuentras registrado en la lista de espera para esta especialidad.'
            ]);
        }

        Waitlist::create([
            'patient_id' => $request->patient_id,
            'specialty_id' => $request->specialty_id,
            'professional_profile_id' => $request->professional_profile_id,
            'status' => 'Pendiente',
        ]);

        return redirect()->route('dashboard')->with('success', 'Te has inscrito exitosamente en la lista de espera. Te enviaremos un correo apenas se libere un cupo.');
    }
}
