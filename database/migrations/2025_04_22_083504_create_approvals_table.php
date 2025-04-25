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
    Schema::create('approvals', function (Blueprint $table) {
      $table->id();
      // Polymorphic relationship to link to different approvable models (EmailApplication, LoanApplication)
      // Creates approvable_id (unsignedBigInteger) and approvable_type (string) columns
      $table->morphs('approvable');

      $table->foreignId('officer_id')->constrained('users')->onDelete('cascade'); // The user (officer) assigned to make the decision

      // Add a column to identify the approval stage (e.g., 'support_review', 'admin_review')
      $table->string('stage')->nullable(); // FIX: Added stage column

      $table->string('status')->default('pending')->index(); // Status of this specific approval step: pending, approved, rejected
      $table->text('comments')->nullable(); // Comments provided by the officer
      $table->timestamp('approval_timestamp')->nullable(); // When the decision was made

      $table->timestamps(); // created_at and updated_at

      // Optional: Add indexes for frequently queried columns
      // $table->index(['officer_id']); // Already indexed by foreignId
      // $table->index(['approvable_type', 'approvable_id']); // Created by morphs
      // $table->index(['stage']); // Consider indexing if querying by stage frequently
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('approvals');
  }
};
