<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversion_histories', function (Blueprint $table) {
            $table->string('visitor_id')->default('')->index()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('conversion_histories', function (Blueprint $table) {
            $table->dropColumn('visitor_id');
        });
    }
};
