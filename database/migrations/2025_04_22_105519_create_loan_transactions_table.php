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
      $table->foreignId('loan_application_id')->constrained()->cascadeOnDelete();
      $table->foreignId('equipment_id')->nullable()->constrained('equipment')->cascadeOnDelete(); // Specific equipment asset issued
      $table->foreignId('issuing_officer_id')->nullable()->constrained('users'); // BPM staff who issued
      $table->foreignId('receiving_officer_id')->nullable()->constrained('users'); // Officer receiving on behalf of applicant (if applicable)
      $table->json('accessories_checklist_on_issue')->nullable(); // Checklist of accessories issued
      $table->timestamp('issue_timestamp')->nullable();

      $table->foreignId('returning_officer_id')->nullable()->constrained('users'); // Officer returning on behalf of applicant (if applicable)
      $table->foreignId('return_accepting_officer_id')->nullable()->constrained('users'); // BPM staff who accepted return
      $table->json('accessories_checklist_on_return')->nullable(); // Checklist of accessories returned
      $table->timestamp('return_timestamp')->nullable();
      $table->text('return_notes')->nullable();

      $table->enum('status', ['issued', 'returned', 'overdue', 'lost', 'damaged'])->default('issued');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('loan_transactions');
  }
};
