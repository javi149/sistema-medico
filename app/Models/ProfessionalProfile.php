<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessionalProfile extends Model
{
    use HasFactory;

    // Le damos luz verde a Laravel para insertar masivamente en estas columnas
    protected $fillable = [
        'user_id',
        'bio',
        'consultation_duration_minutes',
    ];

    /**
     * Relación Inversa (Uno a Uno): Este perfil pertenece a un Usuario (Médico).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación Muchos a Muchos: Un perfil profesional tiene muchas especialidades.
     */
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class);
    }

    /**
     * Relación Uno a Muchos: Un perfil tiene muchas disponibilidades horarias.
     */
    public function availabilities(): HasMany
    {
        return $this->hasMany(Availability::class);
    }

    /**
     * Relación Uno a Muchos: Un perfil tiene muchas citas médicas.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}