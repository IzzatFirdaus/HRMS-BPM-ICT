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
    Schema::create('discounts', function (Blueprint $table) {
      $table->id();
      $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // Cascade delete if employee is removed

      // 👇 UPDATED: Removed length(3) from integer type
      $table->integer('rate'); // Changed from length(3)
      // ☝️ END UPDATED

      $table->date('date');
      $table->string('reason');
      $table->boolean('is_auto')->default(0);
      $table->boolean('is_sent')->default(0);
      $table->string('batch');

      // 👇 UPDATED: Audit columns as nullable unsignedBigInteger with foreign keys
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for audit columns
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
      // ☝️ END UPDATED

      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('discounts', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      $table->dropForeign(['employee_id']);
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('discounts');
  }
};
