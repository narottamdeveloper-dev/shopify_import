<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->foreignId('shopify_store_id')->nullable()->after('id')->constrained('shopify_stores')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('shopify_store_id')->nullable()->after('upload_id')->constrained('shopify_stores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shopify_store_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shopify_store_id');
        });
    }
};
