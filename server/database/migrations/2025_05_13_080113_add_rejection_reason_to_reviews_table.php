<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('reviews', function (Blueprint $table) {
        $table->string('rejection_reason')->nullable();
    });
}

public function down()
{
    Schema::table('reviews', function (Blueprint $table) {
        $table->dropColumn('rejection_reason');
    });
}
};
