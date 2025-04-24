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
      $table->morphs('approvable'); // Creates approvable_id (INT) and approvable_type (VARCHAR) columns

      $table->foreignId('officer_id')->constrained('users')->onDelete('cascade'); // The user (officer) who made the decision
      $table->string('status')->default('pending')->index(); // Status of this specific approval step: pending, approved, rejected
      $table->text('comments')->nullable();
      $table->timestamp('approval_timestamp')->nullable(); // When the decision was made
      $table->timestamps();
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
