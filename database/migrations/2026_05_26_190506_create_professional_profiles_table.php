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
    Schema::create('professional_profiles', function (Blueprint $table) {
        $table->id();
        // Relación 1 a 1: Un usuario tiene un solo perfil médico. 
        // Usamos unique() para garantizar esta regla en la base de datos.
        $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); 
        
        $table->string('box_number')->nullable(); // Box físico asignado
        $table->integer('consultation_duration_minutes')->default(30); // RF-08: Duración de consulta
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professional_profiles');
    }
};
