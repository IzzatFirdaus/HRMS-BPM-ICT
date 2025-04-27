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
    Schema::create('transitions', function (Blueprint $table) {
      $table->id();
      // Link to Equipment and Employee
      $table->foreignId('equipment_id')->constrained('equipment')->onDelete('restrict'); // Constrain to 'equipment' table
      $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Cascade delete if employee is removed

      $table->date('handed_date')->nullable();
      $table->date('return_date')->nullable();
      $table->string('center_document_number')->unique()->nullable();
      $table->string('reason')->nullable();
      $table->longText('note')->nullable();

      // 👇 ADDED: Standard timestamps (created_at and updated_at) 👇
      $table->timestamps(); // <-- ADD THIS LINE

      // Audit columns (correctly defined)
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for audit columns
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

      // Soft deletes (correctly defined as deleted_at)
      $table->softDeletes(); // Adds deleted_at

      // Optional: Add indexes for frequently queried columns
      // $table->index(['equipment_id']); // Already indexed by foreignId
      // $table->index(['employee_id']); // Already indexed by foreignId
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // When doing migrate:fresh, dropping the table is sufficient.
    // The explicit foreign key drops within Schema::table before dropIfExists
    // are technically redundant here but harmless.
    Schema::table('transitions', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      if (Schema::hasColumn('transitions', 'equipment_id')) {
        $table->dropForeign(['equipment_id']);
      }
      if (Schema::hasColumn('transitions', 'employee_id')) {
        $table->dropForeign(['employee_id']);
      }
      // Drop audit FKs
      if (Schema::hasColumn('transitions', 'created_by')) {
        $table->dropForeign(['created_by']);
      }
      if (Schema::hasColumn('transitions', 'updated_by')) {
        $table->dropForeign(['updated_by']);
      }
      if (Schema::hasColumn('transitions', 'deleted_by')) {
        $table->dropForeign(['deleted_by']);
      }
      // Soft deletes column is dropped by dropSoftDeletes or implicitly by dropIfExists
      // $table->dropSoftDeletes(); // <-- This is the method to drop deleted_at explicitly if needed

    });
    Schema::dropIfExists('transitions'); // <-- This drops the table and all its columns
  }
};
