<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('pages')->nullOnDelete();
            $table->boolean('is_homepage')->default(false)->after('status');
            $table->string('excerpt', 300)->nullable()->after('slug');
            $table->string('hero_image')->nullable()->after('excerpt');
            $table->json('blocks')->nullable()->after('body');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->longText('body')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['is_homepage', 'excerpt', 'hero_image', 'blocks']);
        });
    }
};
