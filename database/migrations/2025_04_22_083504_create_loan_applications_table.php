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
    Schema::create('loan_applications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Applicant
      $table->foreignId('responsible_officer_id')->nullable()->constrained('users')->onDelete('set null'); // Optional, Set null if user deleted

      $table->text('purpose');
      $table->string('location'); // Location where equipment will be used
      $table->date('loan_start_date');
      $table->date('loan_end_date');
      $table->enum('status', ['draft', 'pending_support', 'approved', 'rejected', 'partially_issued', 'issued', 'returned', 'overdue', 'cancelled'])->default('draft');
      $table->text('rejection_reason')->nullable();
      $table->timestamp('applicant_confirmation_timestamp')->nullable(); // Timestamp for applicant's Part 4 confirmation
      $table->timestamps();

      // 👇 ADDED: Audit columns as nullable unsignedBigInteger with foreign keys
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for audit columns
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
      // ☝️ END ADDED

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
    Schema::table('loan_applications', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      $table->dropForeign(['user_id']);
      if (Schema::hasColumn('loan_applications', 'responsible_officer_id')) { // Check if column exists
        $table->dropForeign(['responsible_officer_id']);
      }
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('loan_applications');
  }
};
