<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('favicon_path')->nullable();
            $table->string('body_font', 40)->nullable();
            $table->string('heading_font', 40)->nullable();
            $table->string('base_font_size', 10)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['favicon_path', 'body_font', 'heading_font', 'base_font_size']);
        });
    }
};
