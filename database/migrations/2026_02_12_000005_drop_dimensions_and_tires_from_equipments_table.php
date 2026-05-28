<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter([
            'length',
            'width',
            'height',
            'tires_name',
        ], fn (string $column) => Schema::hasColumn('equipments', $column)));

        if ($columns !== []) {
            Schema::table('equipments', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->integer('length')->unsigned();
            $table->integer('width')->unsigned();
            $table->integer('height')->unsigned();
            $table->string('tires_name', 20);
        });
    }
};
