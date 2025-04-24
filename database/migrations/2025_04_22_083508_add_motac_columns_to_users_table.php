<?php

// database/migrations/[timestamp]_add_motac_columns_to_users_table.php

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
    // Check if the 'users' table exists before attempting to modify it
    if (Schema::hasTable('users')) {
      Schema::table('users', function (Blueprint $table) {
        // Add columns based on the MOTAC user data requirements from the system design

        // Add foreign key to grades table (assuming a 'grades' table is created or exists)
        // Ensure the 'grades' table migration runs BEFORE this migration.
        if (!Schema::hasColumn('users', 'grade_id')) {
          $table->foreignId('grade_id')->nullable()->constrained('grades')->onDelete('set null');
        }

        // Add service_status column if it doesn't exist
        if (!Schema::hasColumn('users', 'service_status')) {
          // Use enum as specified in the system design
          $table->enum('service_status', ['permanent', 'contract', 'mystep', 'intern', 'other_agency'])->nullable()->default('permanent');
        }

        // Add appointment_type column if it doesn't exist
        if (!Schema::hasColumn('users', 'appointment_type')) {
          $table->string('appointment_type')->nullable();
        }

        // Add motac_email column if it doesn't exist
        if (!Schema::hasColumn('users', 'motac_email')) {
          $table->string('motac_email')->nullable()->unique();
        }

        // Add nric column if it doesn't exist
        if (!Schema::hasColumn('users', 'nric')) {
          $table->string('nric')->unique()->nullable(); // Assuming nric is unique per user
        }

        // Add mobile_number column if it doesn't exist
        if (!Schema::hasColumn('users', 'mobile_number')) {
          $table->string('mobile_number')->nullable();
        }

        // Add foreign key to departments table (assuming 'departments' table exists)
        // Ensure the 'departments' table migration runs BEFORE this migration.
        if (!Schema::hasColumn('users', 'department_id')) {
          $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
        }

        // Add foreign key to designations table (corresponds to positions in HRMS repo)
        // Ensure the 'designations' table migration runs BEFORE this migration.
        if (!Schema::hasColumn('users', 'position_id')) {
          // Corrected table name to 'designations' based on HRMS repo structure
          $table->foreignId('position_id')->nullable()->constrained('designations')->onDelete('set null');
        }

        // Add user_id_assigned column if it doesn't exist
        if (!Schema::hasColumn('users', 'user_id_assigned')) {
          $table->string('user_id_assigned')->nullable()->unique(); // Based on system design
        }

        // Add status column if it doesn't exist
        if (!Schema::hasColumn('users', 'status')) {
          // Use enum as specified in the system design
          $table->enum('status', ['active', 'inactive', 'suspended'])->default('active'); // User status based on system design
        }

        // You might also want to add an index to frequently queried columns like department_id, position_id, grade_id
        // $table->index(['department_id']);
        // $table->index(['position_id']);
        // $table->index(['grade_id']);
      });
    }
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    // Check if the 'users' table exists before attempting to modify it
    if (Schema::hasTable('users')) {
      Schema::table('users', function (Blueprint $table) {
        // Drop columns added in the up method

        // Drop foreign key constraints first
        // Check if the foreign key exists before dropping
        if (Schema::hasColumn('users', 'grade_id')) {
          $table->dropConstrainedForeignId('grade_id'); // Laravel 11+ helper for dropping foreign keys
        }

        // Drop foreign key for department_id if it was added in this migration
        if (Schema::hasColumn('users', 'department_id')) {
          $table->dropConstrainedForeignId('department_id');
        }

        // Drop foreign key for position_id (designations) if it was added in this migration
        if (Schema::hasColumn('users', 'position_id')) {
          // Use dropConstrainedForeignId or dropForeign for 'position_id' referring to 'designations'
          // dropConstrainedForeignId might work if Laravel named the constraint conventionally
          // Otherwise, use dropForeign with the conventional name like users_position_id_foreign
          $table->dropConstrainedForeignId('position_id');
        }


        // Drop the columns, checking if they exist before dropping
        $columnsToDrop = [
          'service_status',
          'appointment_type',
          'motac_email',
          'nric',
          'mobile_number',
          'user_id_assigned',
          'status',
          // Do NOT drop department_id or position_id here if they were dropped via dropConstrainedForeignId above
        ];

        foreach ($columnsToDrop as $column) {
          if (Schema::hasColumn('users', $column)) {
            $table->dropColumn($column);
          }
        }
      });
    }
  }
};
