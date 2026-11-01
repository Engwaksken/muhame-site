<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 100);
            $table->string('tagline', 180)->nullable();
            $table->string('homepage_title', 160);
            $table->string('homepage_description', 300)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('logo_path')->nullable();
            $table->json('navigation_links')->nullable();
            $table->json('social_links')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
