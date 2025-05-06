<?php

// database/migrations/..._create_equipment_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('equipment_categories', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique(); // Category name, assuming unique
      $table->text('description')->nullable(); // Optional description

      // Add audit columns (created_by, updated_by, deleted_by) - Ensure these match your trait's expectations
      // You might need foreign keys to the 'users' table if created_by/updated_by/deleted_by are user IDs
      // $table->unsignedBigInteger('created_by')->nullable();
      // $table->unsignedBigInteger('updated_by')->nullable();
      // $table->unsignedBigInteger('deleted_by')->nullable();
      // If adding FKs for audit columns:
      // $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      // $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      // $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

      $table->timestamps(); // Adds created_at and updated_at
      $table->softDeletes(); // Adds deleted_at column
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('equipment_categories');
  }
};
