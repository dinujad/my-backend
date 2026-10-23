<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_popups', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(true);
            $table->string('image_path')->nullable();
            $table->unsignedSmallInteger('close_seconds')->default(3);
            $table->timestamps();
        });

        DB::table('home_popups')->insert([
            'id' => 1,
            'enabled' => true,
            'image_path' => null,
            'close_seconds' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_popups');
    }
};
