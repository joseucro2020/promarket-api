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
        // El foreign key ya fue eliminado en el intento anterior exitosamente, 
        // por lo que si intentamos borrarlo de nuevo dará error de que no existe.
        // Avanzamos directamente a modificar el campo y crear la llave de nuevo.


        Schema::table('addresses', function (Blueprint $table) {
            // Hacemos que los campos que ahora pueden fallar sean opcionales (nullable)
            $table->unsignedInteger('user_id')->nullable()->change();
            
            // Re-agregar la llave foránea ahora que permite nulos
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('zip')->nullable()->change();

            // Agregamos las nuevas columnas para el mapa y contacto
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('name')->nullable();
            $table->string('country_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['lat', 'lng', 'name', 'country_code', 'phone', 'email']);
            // Nota: revertir nullable()->change() no siempre es seguro si hay datos nulos, por lo que se omite.
        });
    }
};
