<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->unsignedInteger('total_rows')->default(0)->after('status');
            $table->unsignedInteger('processed_rows')->default(0)->after('total_rows');
            $table->unsignedInteger('successful_rows')->default(0)->after('processed_rows');
            $table->unsignedInteger('skipped_rows')->default(0)->after('successful_rows');
            $table->unsignedInteger('failed_rows')->default(0)->after('skipped_rows');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('source_handle')->nullable()->after('price');
            $table->string('source_sku')->nullable()->after('source_handle');
            $table->string('source_key')->nullable()->after('source_sku');
            $table->index('source_key');
        });
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropColumn([
                'total_rows',
                'processed_rows',
                'successful_rows',
                'skipped_rows',
                'failed_rows',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['source_key']);
            $table->dropColumn([
                'source_handle',
                'source_sku',
                'source_key',
            ]);
        });
    }
};
