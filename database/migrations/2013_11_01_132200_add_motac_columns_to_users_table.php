<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('users', function (Blueprint $table) {
      // Add MOTAC specific columns and foreign keys.
      // Adding employee_id back as it's needed by the factory and not added elsewhere.

      // Add Foreign Key columns first (order doesn't strictly matter without after())
      $table->unsignedBigInteger('employee_id')->nullable(); // Added back
      $table->unsignedBigInteger('department_id')->nullable();
      $table->unsignedBigInteger('position_id')->nullable();
      $table->unsignedBigInteger('grade_id')->nullable();

      // Add other MOTAC fields
      // Removed ->after() calls to simplify and avoid column order issues
      $table->string('full_name')->nullable();
      $table->string('personal_email')->nullable()->unique();
      $table->string('motac_email')->nullable()->unique();
      $table->string('nric')->unique()->nullable();
      $table->string('mobile_number')->nullable();

      $table->string('user_id_assigned')->nullable()->unique();
      $table->enum('service_status', ['permanent', 'contract', 'mystep', 'intern', 'other_agency'])->nullable()->default('permanent');
      $table->string('appointment_type')->nullable();
      $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
      $table->boolean('is_admin')->default(false);
      $table->boolean('is_bpm_staff')->default(false);

      // Add Jetstream-related profile photo path if it wasn't in create_users_table
      // Assuming this was not in the initial users table.
      $table->string('profile_photo_path', 2048)->nullable();


      // ADDED previously: Add audit columns (created_by, updated_by, deleted_by)
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // ADDED previously: Add soft delete column (deleted_at)
      // This adds the 'deleted_at' timestamp column.
      $table->softDeletes(); // This adds the 'deleted_at' timestamp column

    }); // End of first Schema::table closure for adding columns

    // Add Foreign Key CONSTRAINTS in a separate closure, for FKs added in *this* migration.
    Schema::table('users', function (Blueprint $table) {
      // Add Foreign Key constraints referencing other tables (employee, department, position, grade)
      // Assuming these tables exist from earlier migrations based on timestamps.
      // We are no longer checking with hasForeignKey

      // Added back employee_id foreign key
      $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
      $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
      $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
      $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');

      // Add Foreign Key constraints referencing the users table itself (for audit columns)
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('users', function (Blueprint $table) {
      // Drop foreign key constraints added in the up() method of *this* migration.
      // Added back employee_id foreign key drop
      $table->dropForeign(['employee_id']);
      $table->dropForeign(['department_id']);
      $table->dropForeign(['position_id']);
      $table->dropForeign(['grade_id']);

      // Drop audit column FKs.
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);

      // Drop the columns added in the up() method of *this* migration.
      $columnsToDrop = [
        'employee_id', // Added back
        'department_id',
        'position_id',
        'grade_id',
        'full_name',
        'personal_email',
        'motac_email',
        'nric',
        'mobile_number',
        'user_id_assigned',
        'service_status',
        'appointment_type',
        'status',
        'is_admin',
        'is_bpm_staff',
        'profile_photo_path',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at', // Timestamp for soft deletes
      ];

      $table->dropColumn($columnsToDrop);
    });
  }
};
