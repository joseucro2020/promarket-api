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
        Schema::table('addresses', function (Blueprint $table) {
            // Solo agregamos las columnas que realmente faltan en producción
            if (!Schema::hasColumn('addresses', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('addresses', 'state')) {
                $table->string('state')->nullable();
            }
            if (!Schema::hasColumn('addresses', 'zip')) {
                $table->string('zip')->nullable();
            }
            if (!Schema::hasColumn('addresses', 'country_code')) {
                $table->string('country_code')->nullable();
            }
            if (!Schema::hasColumn('addresses', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('addresses', 'type')) {
                $table->string('type')->default('home');
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'zip', 'country_code', 'email', 'type']);
        });
    }
};
