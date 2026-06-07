<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProfessionalProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'biography',
        'consultation_duration_minutes', // Tu regla de negocio para los bloques de tiempo
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
}