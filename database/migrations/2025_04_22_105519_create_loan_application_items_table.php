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
    Schema::create('loan_application_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('loan_application_id')->constrained()->cascadeOnDelete(); // Links to the parent LoanApplication
      $table->string('equipment_type'); // e.g., "Laptop", "Projector" - refers to desired type, not specific asset
      $table->integer('quantity_requested'); // Quantity requested by the applicant
      $table->text('notes')->nullable(); // Any specific requirements for this item
      $table->integer('quantity_approved')->nullable(); // Quantity approved by the approver(s)
      // $table->integer('quantity_issued')->default(0); // Removed: Issued quantity is tracked via LoanTransaction models

      $table->timestamps(); // created_at and updated_at

      // Optional: Add indexes for frequently queried columns
      // $table->index(['loan_application_id']); // Already indexed by foreignId
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('loan_application_items');
  }
};
