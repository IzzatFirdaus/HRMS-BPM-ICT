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
    Schema::create('email_applications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The applicant who submitted the application

      // Add Service Status column (Taraf Perkhidmatan)
      // Use enum matching the options from the form request/PDF. Set default to 'draft' or nullable.
      // 👇 Note: This enum's values differ from the users table's service_status. Ensure this is intentional
      $table->enum('service_status', ['Kakitangan Tetap', 'Lantikan Kontrak', 'Personel MySTEP', 'Pelajar Latihan Industri', 'E-mel Sandaran MOTAC'])->nullable();
      // ☝️ END Note

      $table->string('purpose')->nullable(); // Tujuan Permohonan (nullable based on form/logic)
      $table->string('proposed_email')->nullable(); // Cadangan E-mel/ID (nullable)
      $table->string('group_email')->nullable(); // Nama Group Email (nullable)
      $table->string('group_admin_name')->nullable(); // Nama Admin/EO/CC (nullable)
      $table->string('group_admin_email')->nullable(); // E-mel Admin/EO/CC (nullable)

      // Add foreign key for the Supporting Officer (the first approver assigned)
      $table->foreignId('supporting_officer_id')->nullable()->constrained('users')->onDelete('set null');

      // 👇 ADDED: Status enum needs default value
      $table->enum('status', ['draft', 'pending_support', 'pending_admin', 'approved', 'rejected', 'processing', 'completed', 'provision_failed'])->default('draft');
      // ☝️ END ADDED

      $table->boolean('certification_accepted')->default(false); // Pengesahan Pemohon checkbox state
      $table->timestamp('certification_timestamp')->nullable(); // Timestamp when applicant confirmed
      $table->string('rejection_reason')->nullable(); // Reason for rejection

      $table->string('final_assigned_email')->nullable()->unique(); // The actual email assigned after provisioning
      $table->string('final_assigned_user_id')->nullable()->unique(); // The actual user ID assigned by external system after provisioning
      $table->timestamp('provisioned_at')->nullable(); // Timestamp when provisioning was completed

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
      // $table->index(['status']);
      // $table->index(['user_id']); // Already indexed by foreignId
      // $table->index(['supporting_officer_id']); // Already indexed by foreignId

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
    Schema::table('email_applications', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      $table->dropForeign(['user_id']);
      if (Schema::hasColumn('email_applications', 'supporting_officer_id')) { // Check if column exists
        $table->dropForeign(['supporting_officer_id']);
      }
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('email_applications');
  }
};
