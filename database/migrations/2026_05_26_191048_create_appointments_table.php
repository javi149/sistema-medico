<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            
            // 1. Llave foránea del paciente (Apunta a la tabla users)
            $table->foreignId('patient_id')->constrained('users')->onDelete('cascade');
            
            // 2. Llave foránea del perfil del médico
            $table->foreignId('professional_profile_id')->constrained()->onDelete('cascade');
            
            // 3. Llave foránea de la especialidad a atender
            $table->foreignId('specialty_id')->constrained()->onDelete('cascade');
            
            // Fecha y hora exacta de la cita
            $table->timestamp('start_datetime');
            
            // Estado de la cita (por defecto siempre nacerá como 'reservada')
            $table->string('status', 20)->default('reservada');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
