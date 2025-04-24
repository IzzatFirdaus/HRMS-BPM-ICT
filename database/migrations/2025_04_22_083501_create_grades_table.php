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
      $table->string('name')->unique(); // e.g., "41", "JUSA C"
      $table->integer('level')->unique()->nullable(); // Numeric level for comparison (e.g., 41, 44, 52, 54, etc.)
      $table->foreignId('min_approval_grade_id')->nullable()->constrained('grades'); // Self-referencing, useful if a grade requires approval from a specific minimum grade
      $table->boolean('is_approver_grade')->default(false); // Flag if this grade level can be an approver
      $table->timestamps();
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
