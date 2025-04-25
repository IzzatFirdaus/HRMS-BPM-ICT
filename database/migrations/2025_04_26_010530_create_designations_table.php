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
    // The table name should be 'designations' to match the $table property in the Position model
    Schema::create('designations', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique(); // Position name
      $table->integer('vacancies_count')->nullable()->default(0); // Existing HRMS field
      $table->text('description')->nullable(); // Added description field
      $table->foreignId('grade_id')->nullable()->constrained('grades')->onDelete('set null'); // Link to grades table
      $table->timestamps(); // Adds created_at and updated_at
      $table->softDeletes(); // Adds deleted_at for soft deletes

      // Columns for CreatedUpdatedDeletedBy trait
      // Assuming 'users' table exists and user IDs are integers
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Foreign key constraints for the audit columns
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('designations');
  }
};
