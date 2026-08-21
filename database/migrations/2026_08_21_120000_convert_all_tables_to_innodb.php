<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        $database = DB::connection()->getDatabaseName();

        $tables = DB::select(
            'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND ENGINE = ?',
            [$database, 'MyISAM']
        );

        foreach ($tables as $table) {
            $name = $table->TABLE_NAME;
            DB::statement("ALTER TABLE `{$name}` ENGINE = InnoDB");
        }
    }

    public function down(): void
    {
    }
};
