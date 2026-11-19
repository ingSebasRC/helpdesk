<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('tickets', 'notes')) {
            return;
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->json('notes')->nullable();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('tickets', 'notes')) {
            return;
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
