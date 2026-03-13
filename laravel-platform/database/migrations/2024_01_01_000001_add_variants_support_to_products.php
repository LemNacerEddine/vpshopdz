<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add has_variants to products table
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('has_variants')->default(false)->after('status');
        });

        // Create product_options table
        Schema::create('product_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->string('name'); // e.g. "اللون", "المقاس"
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        // Create product_option_values table
        Schema::create('product_option_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('option_id');
            $table->string('value'); // e.g. "أحمر", "XL"
            $table->integer('position')->default(0);
            $table->timestamps();

            $table->foreign('option_id')->references('id')->on('product_options')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('has_variants');
        });
    }
};
