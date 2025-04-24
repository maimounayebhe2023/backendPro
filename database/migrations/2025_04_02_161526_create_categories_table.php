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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 30)->unique();
        });

        Schema::table('conducteurs', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Categorie::class)->after('id')->nullable()->constrained()->nullOnDelete(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
        Schema::table('conducteurs', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Categorie::class);
        });
    }
};
