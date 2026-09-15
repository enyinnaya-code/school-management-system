<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE promotions MODIFY promotion_type ENUM('bulk','performance','direct') NOT NULL");
        DB::statement("ALTER TABLE promotion_records MODIFY promotion_status ENUM('promoted','repeating','graduated') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE promotions MODIFY promotion_type ENUM('bulk','performance') NOT NULL");
        DB::statement("ALTER TABLE promotion_records MODIFY promotion_status ENUM('promoted','repeating') NOT NULL");
    }
};
