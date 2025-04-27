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
    Schema::create('grades', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique(); // e.g., "41", "JUSA C" - The display name of the grade
      // 👇 UPDATED: level is now integer (removed unique constraint as discussed)
      $table->integer('level')->nullable(); // Numeric level for comparison (e.g., 41, 44, 52, 54, etc.) - Used for logic like minimum approver grade
      // ☝️ END UPDATED
      // $table->foreignId('min_approval_grade_id')->nullable()->constrained('grades'); // Removed: Not used in current workflow logic relying on min_approver_grade_level config

      $table->boolean('is_approver_grade')->default(false); // Flag if this grade level can be an approver (optional metadata)
      // Add other relevant grade information columns if needed

      $table->timestamps(); // created_at and updated_at

      // 👇 ADDED: Audit columns as nullable unsignedBigInteger with foreign keys
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for audit columns
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
      // ☝️ END ADDED

      // Optional: Add indexes for frequently queried columns
      // $table->index(['level']);
      // $table->index(['is_approver_grade']);

      // 👇 ADDED: Soft deletes for consistency
      $table->softDeletes();
      // ☝️ END ADDED
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('grades', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('grades');
  }
};
