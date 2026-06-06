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
    Schema::create('waitlists', function (Blueprint $table) {
        $table->id();
        $table->foreignId('patient_id')->constrained('users')->cascadeOnDelete();
        
        // Nullable, porque puede buscar cualquier médico de una especialidad
        $table->foreignId('professional_profile_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('specialty_id')->nullable()->constrained()->nullOnDelete();
        
        // ¡NUEVO! Control de estado para que el administrador la gestione usando ENUM
        $table->enum('status', ['Pendiente', 'Notificado', 'Reasignado', 'Cancelado'])->default('Pendiente');
        
        // Opcional pero recomendado: Saber qué administrador gestionó este registro
        $table->foreignId('managed_by')->nullable()->constrained('users')->nullOnDelete();

        $table->timestamps(); // created_at nos dirá quién llegó primero (FIFO)
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waitlists');
    }
};
