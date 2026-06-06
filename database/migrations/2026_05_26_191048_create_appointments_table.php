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
        
        // El paciente es simplemente un 'user' en nuestro sistema
        $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete(); 
        
        $table->foreignId('professional_profile_id')->constrained()->cascadeOnDelete();
        
        $table->date('date'); // El día específico (ej. 2026-06-01)
        $table->time('start_time'); // A qué hora inicia (ej. '08:30:00')
        $table->time('end_time'); // A qué hora termina (ej. '09:00:00')
        
        // Estado de la cita (Agendada, Confirmada, Ausente, Atendida)
        $table->string('status')->default('Agendada'); 
        
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
