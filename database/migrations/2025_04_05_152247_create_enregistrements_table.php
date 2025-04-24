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
        Schema::create('enregistrements', function (Blueprint $table) {
            $table->id();
            $table->string('code_pin');
            $table->string('statut');
            $table->timestamps();
            $table->datetime('date_sortie')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enregistrements');
    }
};
