<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Importante para la relación

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Relación Muchos a Muchos: Una especialidad pertenece a muchos Perfiles Profesionales.
     */
    public function professionalProfiles(): BelongsToMany
    {
        return $this->belongsToMany(ProfessionalProfile::class);
    }
}