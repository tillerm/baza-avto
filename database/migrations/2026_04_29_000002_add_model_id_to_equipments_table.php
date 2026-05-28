<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('equipments', 'model_id')) {
            return;
        }

        Schema::table('equipments', function (Blueprint $table) {
            $table->foreignId('model_id')
                ->nullable()
                ->after('body')
                ->constrained('models')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('equipments', 'model_id')) {
            return;
        }

        Schema::table('equipments', function (Blueprint $table) {
            $table->dropForeign(['model_id']);
            $table->dropColumn('model_id');
        });
    }
};
