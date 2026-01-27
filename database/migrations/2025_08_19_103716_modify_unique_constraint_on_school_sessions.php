<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyUniqueConstraintOnSchoolSessions extends Migration
{
    public function up()
    {
        Schema::table('school_sessions', function (Blueprint $table) {
            // Drop the existing unique index on 'name'
            $table->dropUnique(['name']);
            // Add a composite unique index on 'name' and 'section_id'
            $table->unique(['name', 'section_id'], 'school_sessions_name_section_id_unique');
        });
    }

    public function down()
    {
        Schema::table('school_sessions', function (Blueprint $table) {
            // Reverse the changes: drop the composite unique index and restore the unique index on 'name'
            $table->dropUnique('school_sessions_name_section_id_unique');
            $table->unique('name');
        });
    }
}