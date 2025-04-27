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
    Schema::create('positions', function (Blueprint $table) {
      $table->id(); // Standard unsignedBigInteger primary key
      $table->string('name')->unique(); // Assuming position name should be unique

      // Vacancies count
      $table->integer('vacancies_count')->nullable()->default(0);

      // 👇 ADDED: Foreign key to the grades table 👇
      // This column links a position to a specific grade.
      // Assumes the 'grades' table exists and has a standard 'id' (unsignedBigInteger).
      // Your current migration order ensures grades is created before positions.
      $table->foreignId('grade_id')->nullable()->constrained()->onDelete('set null');
      // ☝️ END ADDED ☝️

      // Optional: Add other position-specific fields here, like description, etc.
      $table->text('description')->nullable(); // Added description field based on seeder usage

      // Standard audit columns (nullable foreign keys to users table)
      // Assumes the 'users' table exists and has standard 'id' (unsignedBigInteger).
      // Your current migration order ensures users is created before positions.
      $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

      // Standard timestamps and soft delete timestamp
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Drop foreign key constraints before dropping the table
    Schema::table('positions', function (Blueprint $table) {
      // 👇 ADDED: Drop the grade_id foreign key 👇
      $table->dropForeign(['grade_id']);
      // ☝️ END ADDED ☝️
      // Drop audit foreign keys
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    // Drop the table
    Schema::dropIfExists('positions');
  }
};
