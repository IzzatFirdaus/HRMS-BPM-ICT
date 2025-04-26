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
    // Create the 'designations' table as specified by the Position model
    Schema::create('designations', function (Blueprint $table) {
      $table->id(); // Primary key ID

      // Position details
      $table->string('name')->unique(); // Position name (should be unique)
      $table->integer('vacancies_count')->nullable()->default(0); // Vacancies count, nullable with default
      $table->text('description')->nullable(); // Description field, nullable

      // Foreign key to the grades table
      // Assuming grade_id is nullable based on your Position model's fillable
      $table->foreignId('grade_id')->nullable()->constrained('grades')->onDelete('set null');

      $table->timestamps(); // Adds created_at and updated_at columns
      $table->softDeletes(); // Adds deleted_at column for soft deletes

      // Audit columns for CreatedUpdatedDeletedBy trait
      // These are foreign keys to the users table
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for the audit columns
      // Assuming your users table is named 'users'
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
    // Drop the 'designations' table if it exists
    Schema::dropIfExists('designations');
  }
};
