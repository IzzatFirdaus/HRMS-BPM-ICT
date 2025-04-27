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
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name'); // Standard Laravel name field
      $table->string('email')->unique(); // Standard Laravel email field
      $table->timestamp('email_verified_at')->nullable(); // Standard Laravel email verification timestamp
      $table->string('password'); // Standard Laravel password field
      $table->rememberToken(); // Standard Laravel remember token
      $table->timestamps(); // Standard Laravel created_at and updated_at timestamps

      // 👇 REMOVED: employee_id column and its foreign key constraint
      // These are added in the 2025_04_22_083508_add_motac_columns_to_users_table migration
      // $table->unsignedBigInteger('employee_id')->nullable();
      // $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
      // ☝️ END REMOVED

      // Standard Laravel table does NOT include these initially.
      // They are added in the 2025_04_22_083508 migration.
      // $table->string('profile_photo_path', 2048)->nullable();
      // $table->softDeletes(); // Soft deletes are also added later
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
