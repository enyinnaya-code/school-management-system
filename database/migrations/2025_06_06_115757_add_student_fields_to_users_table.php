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
        Schema::table('users', function (Blueprint $table) {
            $table->string('admission_no')->nullable()->unique()->after('email');
            $table->date('dob')->nullable()->after('admission_no');
            $table->string('phone')->nullable()->after('dob');
            $table->string('guardian_name')->nullable()->after('phone');
            $table->text('address')->nullable()->after('guardian_name');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['admission_no', 'dob', 'phone', 'guardian_name', 'address']);
        });
    }
};
