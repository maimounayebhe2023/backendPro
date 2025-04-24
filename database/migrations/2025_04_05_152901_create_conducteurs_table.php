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
        Schema::create('conducteurs', function (Blueprint $table) {
           
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('tel',18)->unique();
        });
    
        Schema::table('enregistrements', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Conducteur::class)->after('id')->nullable()->constrained()->nullOnDelete(); 
        });
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conducteurs');
        Schema::table('enregistrements', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Conducteur::class);
        });
    }
};
