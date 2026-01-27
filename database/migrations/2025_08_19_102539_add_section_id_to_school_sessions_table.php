<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSectionIdToSchoolSessionsTable extends Migration
{
    public function up()
    {
        Schema::table('school_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id')->nullable()->after('name');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('school_sessions', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }
}