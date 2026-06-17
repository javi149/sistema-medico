<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'professional_profile_id',
        'specialty_id',
        'start_datetime',
        'status',
    ];

    // 1. Relación con el Paciente (Una cita le pertenece a un usuario/paciente)
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // 2. Relación con el Médico (Una cita le pertenece a un perfil profesional)
    public function professionalProfile()
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_profile_id');
    }

    // 3. Relación con la Especialidad (Una cita es para una especialidad específica)
    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
}
    protected $fillable = [
        'patient_id',
        'professional_profile_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    /**
     * Obtener el paciente asociado a la cita.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}
