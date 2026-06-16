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
        Schema::table('professional_profiles', function (Blueprint $table) {
            // Agregamos la columna bio como texto. 
            // Le ponemos nullable() para que no choque con los registros que ya existen.
            $table->text('bio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('professional_profiles', function (Blueprint $table) {
            // Si nos arrepentimos, este comando borra la columna
            $table->dropColumn('bio');
        });
    }
};
