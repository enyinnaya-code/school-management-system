<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeachersAttendanceTable extends Migration
{
    public function up()
{
    if (!Schema::hasTable('teachers_attendance')) {
        Schema::create('teachers_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->enum('attendance', ['Present', 'Absent', 'Late', 'On Leave']);
            $table->date('date');
            $table->time('time');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('session_term');
            $table->timestamps();
        });
    }
}


    public function down()
    {
        Schema::dropIfExists('teachers_attendance');
    }
}
