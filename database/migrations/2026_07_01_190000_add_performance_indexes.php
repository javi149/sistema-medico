<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migración de rendimiento: Agrega índices faltantes para acelerar consultas frecuentes.
     */
    public function up(): void
    {
        // Índices en tabla appointments
        Schema::table('appointments', function (Blueprint $table) {
            $table->index('start_datetime');
            $table->index('status');
            $table->index(['professional_profile_id', 'start_datetime'], 'idx_appt_prof_datetime');
        });

        // Índices en tabla availabilities
        Schema::table('availabilities', function (Blueprint $table) {
            $table->index(['professional_profile_id', 'day_of_week'], 'idx_avail_prof_day');
        });

        // Índices en tabla waitlists
        Schema::table('waitlists', function (Blueprint $table) {
            $table->index('status');
            $table->index(['specialty_id', 'status'], 'idx_waitlist_spec_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['start_datetime']);
            $table->dropIndex(['status']);
            $table->dropIndex('idx_appt_prof_datetime');
        });

        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropIndex('idx_avail_prof_day');
        });

        Schema::table('waitlists', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex('idx_waitlist_spec_status');
        });
    }
};
