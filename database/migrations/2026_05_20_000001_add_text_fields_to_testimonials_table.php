<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('type', 16)->default('text')->after('id');
            $table->text('text')->nullable()->after('title');
            $table->string('author_name')->nullable()->after('text');
            $table->string('car_model')->nullable()->after('author_name');
            $table->string('city')->nullable()->after('car_model');
            $table->unsignedTinyInteger('rating')->default(5)->after('city');
        });

        DB::statement('ALTER TABLE testimonials MODIFY title VARCHAR(255) NULL');
        DB::statement('ALTER TABLE testimonials MODIFY video_url VARCHAR(255) NULL');

        DB::table('testimonials')->whereNotNull('video_url')->update(['type' => 'video']);
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['type', 'text', 'author_name', 'car_model', 'city', 'rating']);
        });
    }
};
