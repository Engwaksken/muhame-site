<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('title');
            $table->string('seo_description', 160)->nullable()->after('seo_title');
            $table->boolean('noindex')->default(false)->after('seo_description');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'noindex']);
        });
    }
};
