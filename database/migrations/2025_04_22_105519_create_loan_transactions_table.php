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
    Schema::create('loan_transactions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('loan_application_id')->constrained('loan_applications')->cascadeOnDelete();
      // 👇 UPDATED: Column name back to equipment_id, references 'equipment' table
      // Note: This FK references 'equipment' table consistently with the chosen standard.
      $table->foreignId('equipment_id')->nullable()->constrained('equipment')->onDelete('set null'); // Column name back to equipment_id
      // ☝️ END UPDATED

      $table->foreignId('issuing_officer_id')->nullable()->constrained('users')->onDelete('set null'); // BPM staff who issued
      $table->foreignId('receiving_officer_id')->nullable()->constrained('users')->onDelete('set null'); // Officer receiving on behalf of applicant (if applicable)
      $table->json('accessories_checklist_on_issue')->nullable(); // Checklist of accessories issued
      $table->timestamp('issue_timestamp')->nullable();

      $table->foreignId('returning_officer_id')->nullable()->constrained('users')->onDelete('set null'); // Officer returning on behalf of applicant (if applicable)
      $table->foreignId('return_accepting_officer_id')->nullable()->constrained('users')->onDelete('set null'); // BPM staff who accepted return
      $table->json('accessories_checklist_on_return')->nullable(); // Checklist of accessories returned
      $table->timestamp('return_timestamp')->nullable();
      $table->text('return_notes')->nullable();

      $table->enum('status', ['issued', 'returned', 'overdue', 'lost', 'damaged'])->default('issued'); // Status of the transaction (not the asset itself)
      $table->timestamps();

      // Audit columns as nullable unsignedBigInteger with foreign keys (Already added in previous step)
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for audit columns (Already added in previous step)
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

      // Soft deletes for consistency (Already added in previous step)
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('loan_transactions', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      $table->dropForeign(['loan_application_id']);
      // 👇 UPDATED: This will drop the FK to 'equipment' by the correct column name
      if (Schema::hasColumn('loan_transactions', 'equipment_id')) { // Check if column exists
        $table->dropForeign(['equipment_id']);
      }
      // ☝️ END UPDATED
      if (Schema::hasColumn('loan_transactions', 'issuing_officer_id')) { // Check if column exists
        $table->dropForeign(['issuing_officer_id']);
      }
      if (Schema::hasColumn('loan_transactions', 'receiving_officer_id')) { // Check if column exists
        $table->dropForeign(['receiving_officer_id']);
      }
      if (Schema::hasColumn('loan_transactions', 'returning_officer_id')) { // Check if column exists
        $table->dropForeign(['returning_officer_id']);
      }
      if (Schema::hasColumn('loan_transactions', 'return_accepting_officer_id')) { // Check if column exists
        $table->dropForeign(['return_accepting_officer_id']);
      }
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('loan_transactions');
  }
};
