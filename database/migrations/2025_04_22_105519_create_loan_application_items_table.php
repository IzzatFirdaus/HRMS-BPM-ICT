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
      // Link to the parent LoanApplication
      $table->foreignId('loan_application_id')->constrained()->cascadeOnDelete();

      // Equipment details based on the migration schema
      $table->string('equipment_type'); // e.g., "Laptop", "Projector" - refers to desired type
      $table->integer('quantity_requested'); // Quantity requested by the applicant
      $table->integer('quantity_approved')->nullable(); // Quantity approved by the approver(s)
      $table->text('notes')->nullable(); // Any specific requirements for this item

      // Standard timestamps
      $table->timestamps(); // Adds created_at and updated_at

      // 👇 ADDED: Audit columns (as per your correct analysis) 👇
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // 👇 ADDED: Soft deletes timestamp (as per your correct analysis) 👇
      $table->softDeletes(); // Adds deleted_at

      // 👇 ADDED: Define foreign key constraints for audit columns referencing the users table 👇
      // These assume the 'users' table exists when this migration runs (which it does based on your migration output)
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
      // ☝️ END ADDED LINES ☝️


      // Optional: Add indexes for frequently queried columns
      // $table->index(['loan_application_id']); // Already indexed by foreignId
      // $table->index(['equipment_type']); // Maybe useful for queries filtering by type
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // When doing migrate:fresh, dropping the table is sufficient.
    // Explicitly dropping foreign keys first is good practice if you were
    // only rolling back this specific migration, but not strictly necessary with dropIfExists.
    Schema::dropIfExists('loan_application_items');
  }
};
