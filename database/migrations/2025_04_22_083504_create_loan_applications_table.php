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
      $table->foreignId('responsible_officer_id')->nullable()->constrained('users')->cascadeOnDelete(); // Optional
      $table->text('purpose');
      $table->string('location'); // Location where equipment will be used
      $table->date('loan_start_date');
      $table->date('loan_end_date');
      $table->enum('status', ['draft', 'pending_support', 'approved', 'rejected', 'partially_issued', 'issued', 'returned', 'overdue', 'cancelled'])->default('draft');
      $table->text('rejection_reason')->nullable();
      $table->timestamp('applicant_confirmation_timestamp')->nullable(); // Timestamp for applicant's Part 4 confirmation
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('loan_applications');
  }
};
