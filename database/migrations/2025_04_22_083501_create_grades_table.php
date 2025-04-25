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
      $table->integer('level')->unique()->nullable(); // Numeric level for comparison (e.g., 41, 44, 52, 54, etc.) - Used for logic like minimum approver grade
      // $table->foreignId('min_approval_grade_id')->nullable()->constrained('grades'); // Removed: Not used in current workflow logic relying on min_approver_grade_level config

      $table->boolean('is_approver_grade')->default(false); // Flag if this grade level can be an approver (optional metadata)
      // Add other relevant grade information columns if needed

      $table->timestamps(); // created_at and updated_at

      // Optional: Add indexes for frequently queried columns
      // $table->index(['level']);
      // $table->index(['is_approver_grade']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('grades');
  }
};
