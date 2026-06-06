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
    Schema::create('availabilities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('professional_profile_id')->constrained()->cascadeOnDelete();
        
        // Usamos un número del 0 al 6 para los días de la semana (0 = Domingo, 1 = Lunes...)
        $table->tinyInteger('day_of_week'); 
        
        // Tipo 'time' de PostgreSQL para guardar solo la hora (ej. '08:00:00')
        $table->time('start_time'); 
        $table->time('end_time');   
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
