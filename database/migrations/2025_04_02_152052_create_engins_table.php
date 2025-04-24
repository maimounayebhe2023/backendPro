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
        Schema::create('engins', function (Blueprint $table) {
            $table->id();
            $table->string('plaque_immatricu', 101)->unique();
            $table->string('type_engin');
        });

        Schema::table('enregistrements', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Engin::class)->after('conducteur_id')->nullable()->constrained()->nullonDelete(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('engins');
        Schema::table('enregistrements', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Engin::class);
        });
    }
};
