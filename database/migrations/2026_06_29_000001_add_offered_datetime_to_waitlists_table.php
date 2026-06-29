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
        Schema::table('waitlists', function (Blueprint $table) {
            $table->dateTime('offered_datetime')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waitlists', function (Blueprint $table) {
            $table->dropColumn('offered_datetime');
        });
    }
};
