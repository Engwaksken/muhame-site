<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('primary_color', 7)->nullable();
            $table->string('accent_color', 7)->nullable();
            $table->text('footer_text')->nullable();
            $table->json('footer_links')->nullable();
            $table->string('newsletter_heading')->nullable();
            $table->string('newsletter_text', 300)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'accent_color', 'footer_text', 'footer_links', 'newsletter_heading', 'newsletter_text']);
        });
    }
};
