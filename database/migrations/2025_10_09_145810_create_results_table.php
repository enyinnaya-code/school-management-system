<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->decimal('ca', 5, 2)->default(0); // e.g., 30.00 max for Continuous Assessment
            $table->decimal('test', 5, 2)->default(0); // e.g., 10.00 max for Test
            $table->decimal('exam', 5, 2)->default(0); // e.g., 60.00 max for Exam
            $table->decimal('total', 5, 2)->default(0);
            $table->string('grade')->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Unique constraint to prevent duplicate results per student/course
            $table->unique(['student_id', 'course_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('results');
    }
};