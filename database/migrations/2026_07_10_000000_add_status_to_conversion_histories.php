<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversion_histories', function (Blueprint $table) {
            $table->string('status')->default('pending')->index()->after('visitor_id');
            $table->text('error_message')->nullable()->after('file_count');
        });
    }

    public function down(): void
    {
        Schema::table('conversion_histories', function (Blueprint $table) {
            $table->dropColumn(['status', 'error_message']);
        });
    }
};
