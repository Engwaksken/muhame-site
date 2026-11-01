<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('contact_phone', 40)->nullable();
            $table->string('contact_address', 500)->nullable();
            $table->string('office_hours', 300)->nullable();
            $table->string('map_location', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['contact_phone', 'contact_address', 'office_hours', 'map_location']);
        });
    }
};
