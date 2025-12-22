<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('access_type')->default('user')->after('email');
            $table->boolean('is_active')->default(true)->after('password');
            $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            $table->softDeletes(); // adds deleted_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['access_type', 'is_active', 'created_by', 'updated_by', 'deleted_by']);
            $table->dropSoftDeletes();
        });
    }
};
