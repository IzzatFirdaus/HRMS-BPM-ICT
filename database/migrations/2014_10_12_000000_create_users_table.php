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
      $table->string('name');
      // This foreign key links users to employees. Ensure employee_id is nullable if not every user is an employee.
      // Based on previous errors, this is likely needed and should be nullable.
      $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null'); // Ensure onDelete('set null') is here if nullable

      $table->string('mobile')->nullable()->unique(); // Original mobile field
      $table->timestamp('mobile_verified_at')->nullable();

      $table->string('email')->unique(); // Standard unique email for login
      $table->timestamp('email_verified_at')->nullable();

      $table->string('password');
      $table->rememberToken();

      // <--- Uncomment this line to create the current_team_id column
      $table->foreignId('current_team_id')->nullable(); // This column is related to Jetstream teams

      $table->string('profile_photo_path', 2048)->nullable();

      // Audit columns (added here or by another migration - ensure consistency)
      // If you added these in add_motac_columns_to_users_table, remove them here.
      // If they are here, ensure the add_motac_columns migration doesn't duplicate them
      // or conflict (like changing string to integer FK later).
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();
      // Add foreign key constraints for audit columns if not already done in this or another migration
      // if (Schema::hasTable('users')) { // Check self-reference carefully
      //    $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      //    $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      //    $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
      // }


      $table->timestamps(); // created_at, updated_at
      $table->softDeletes(); // deleted_at
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
