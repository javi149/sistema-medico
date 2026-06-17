<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
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
