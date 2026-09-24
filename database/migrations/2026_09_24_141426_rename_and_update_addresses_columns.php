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
            if (Schema::hasColumn('addresses', 'address_line_1')) {
                $table->renameColumn('address_line_1', 'address');
            }
            if (Schema::hasColumn('addresses', 'address_line_2')) {
                $table->renameColumn('address_line_2', 'address_line2');
            }
            if (Schema::hasColumn('addresses', 'lng')) {
                $table->renameColumn('lng', 'long');
            }
            if (!Schema::hasColumn('addresses', 'tag')) {
                $table->string('tag')->nullable();
            }
            if (!Schema::hasColumn('addresses', 'reference')) {
                $table->string('reference')->nullable();
            }
            // Make fields nullable in case they aren't
            if (Schema::hasColumn('addresses', 'city')) {
                $table->string('city')->nullable()->change();
            }
            if (Schema::hasColumn('addresses', 'state')) {
                $table->string('state')->nullable()->change();
            }
            if (Schema::hasColumn('addresses', 'zip')) {
                $table->string('zip')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'address')) {
                $table->renameColumn('address', 'address_line_1');
            }
            if (Schema::hasColumn('addresses', 'address_line2')) {
                $table->renameColumn('address_line2', 'address_line_2');
            }
            if (Schema::hasColumn('addresses', 'long')) {
                $table->renameColumn('long', 'lng');
            }
        });
    }
};
