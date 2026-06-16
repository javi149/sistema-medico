<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Availability extends Model
{
    use HasFactory;

    protected $fillable = [
        'professional_profile_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    /**
     * Días de la semana en español para mostrar en vistas.
     */
    public const DAYS = [
        0 => 'Domingo',
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
    ];

    /**
     * Relación: Esta disponibilidad pertenece a un Perfil Profesional.
     */
    public function professionalProfile(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class);
    }

    /**
     * Accessor: Nombre del día en español.
     */
    public function getDayNameAttribute(): string
    {
        return self::DAYS[$this->day_of_week] ?? 'Desconocido';
    }
}
