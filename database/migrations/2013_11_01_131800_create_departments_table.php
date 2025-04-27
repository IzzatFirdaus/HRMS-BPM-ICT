<?php

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
    Schema::create('departments', function (Blueprint $table) {
      $table->id();
      $table->string('name');

      // 👇 ADDED: Description column for the department 👇
      // Use text for potentially longer descriptions, and make it nullable.
      $table->text('description')->nullable();
      // ☝️ END ADDED ☝️

      // Audit columns as nullable unsignedBigInteger with foreign keys
      // Assumes 'users' table exists and has standard 'id'.
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for audit columns
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

      // Standard timestamps and soft delete timestamp
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('departments', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    // Drop the table
    Schema::dropIfExists('departments');
  }
};
