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
    Schema::create('timelines', function (Blueprint $table) {
      $table->id();
      $table->foreignId('center_id')->constrained()->onDelete('restrict'); // Restrict deleting center if timelines exist
      $table->foreignId('department_id')->constrained()->onDelete('restrict'); // Restrict deleting department if timelines exist
      // Note: position_id here references 'positions' table, users.position_id references 'designations'.
      // This will be addressed in a separate step to standardize table names.
      $table->foreignId('position_id')->constrained()->onDelete('restrict'); // Restrict deleting position if timelines exist
      $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // Cascade delete if employee is removed

      $table->date('start_date');
      $table->date('end_date')->nullable();
      $table->longText('notes')->nullable();

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
    Schema::table('timelines', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      $table->dropForeign(['center_id']);
      $table->dropForeign(['department_id']);
      $table->dropForeign(['position_id']);
      $table->dropForeign(['employee_id']);
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('timelines');
  }
};
