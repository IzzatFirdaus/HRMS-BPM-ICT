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

        // Add columns identified as missing from seeder/error (from previous fixes)
        // Ensure these columns are added only if they don't exist
        if (!Schema::hasColumn('users', 'full_name')) {
          $table->string('full_name')->nullable()->after('name'); // Added full_name after 'name'
        }
        if (!Schema::hasColumn('users', 'personal_email')) {
          $table->string('personal_email')->nullable()->unique()->after('email'); // Added personal_email after 'email'
        }

        // Existing columns from the original migration, ensuring they are added if missing

        // Add foreign key to grades table
        if (!Schema::hasColumn('users', 'grade_id')) {
          // Check if 'grades' table exists before adding FK
          if (Schema::hasTable('grades')) {
            $table->foreignId('grade_id')->nullable()->constrained('grades')->onDelete('set null')->after('profile_photo_path'); // Adjust placement
          } else {
            // Add the column without the FK if grades table isn't ready yet
            $table->unsignedBigInteger('grade_id')->nullable()->after('profile_photo_path');
          }
        }

        // Add service_status column if it doesn't exist
        if (!Schema::hasColumn('users', 'service_status')) {
          $table->enum('service_status', ['permanent', 'contract', 'mystep', 'intern', 'other_agency'])->nullable()->default('permanent')->after('grade_id'); // Adjust placement
        }

        // Add appointment_type column if it doesn't exist
        if (!Schema::hasColumn('users', 'appointment_type')) {
          $table->string('appointment_type')->nullable()->after('service_status'); // Adjust placement
        }

        // Add motac_email column if it doesn't exist
        if (!Schema::hasColumn('users', 'motac_email')) {
          $table->string('motac_email')->nullable()->unique()->after('personal_email'); // Adjust placement, ensure unique
        }

        // Add nric column if it doesn't exist
        if (!Schema::hasColumn('users', 'nric')) {
          $table->string('nric')->unique()->nullable()->after('motac_email'); // Assuming nric is unique per user, adjust placement
        }

        // Add mobile_number column if it doesn't exist
        if (!Schema::hasColumn('users', 'mobile_number')) {
          $table->string('mobile_number')->nullable()->after('nric'); // Adjust placement
        }

        // Add foreign key to departments table
        if (!Schema::hasColumn('users', 'department_id')) {
          // Check if 'departments' table exists before adding FK
          if (Schema::hasTable('departments')) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null')->after('mobile_number'); // Adjust placement
          } else {
            // Add the column without the FK if departments table isn't ready yet
            $table->unsignedBigInteger('department_id')->nullable()->after('mobile_number');
          }
        }

        // Add foreign key to designations table
        if (!Schema::hasColumn('users', 'position_id')) {
          // Check if 'designations' table exists before adding FK
          if (Schema::hasTable('designations')) {
            $table->foreignId('position_id')->nullable()->constrained('designations')->onDelete('set null')->after('department_id'); // Adjust placement
          } else {
            // Add the column without the FK if designations table isn't ready yet
            $table->unsignedBigInteger('position_id')->nullable()->after('department_id');
          }
        }

        // --- Corrected chaining for user status and roles ---
        // Add user_id_assigned column if it doesn't exist
        // Make sure its placement is relative to a known column from the original table or an earlier addition
        if (!Schema::hasColumn('users', 'user_id_assigned')) {
          // Let's chain from position_id, which was added earlier
          $table->string('user_id_assigned')->nullable()->unique()->after('position_id');
        }

        // Add status column if it doesn't exist
        // Chain this AFTER user_id_assigned
        if (!Schema::hasColumn('users', 'status')) {
          $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('user_id_assigned');
        }

        // Add is_admin column if it doesn't exist
        // Chain this AFTER status
        if (!Schema::hasColumn('users', 'is_admin')) {
          $table->boolean('is_admin')->default(false)->after('status');
        }

        // Add is_bpm_staff column if it doesn't exist
        // Chain this AFTER is_admin
        if (!Schema::hasColumn('users', 'is_bpm_staff')) {
          $table->boolean('is_bpm_staff')->default(false)->after('is_admin');
        }
        // --- End corrected chaining ---


        // You might also want to add an index to frequently queried columns
        // $table->index(['department_id']);
        // $table->index(['position_id']);
        // $table->index(['grade_id']);

        // Add FKs if the tables weren't ready when the columns were added initially
        // Check if the FK constraint already exists before adding it again
        if (Schema::hasColumn('users', 'grade_id') && !Schema::hasForeignKey('users', 'users_grade_id_foreign') && Schema::hasTable('grades')) {
          $table->foreign('grade_id')->references('id')->on('grades')->onDelete('set null');
        }
        if (Schema::hasColumn('users', 'department_id') && !Schema::hasForeignKey('users', 'users_department_id_foreign') && Schema::hasTable('departments')) {
          $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        }
        if (Schema::hasColumn('users', 'position_id') && !Schema::hasForeignKey('users', 'users_position_id_foreign') && Schema::hasTable('designations')) {
          $table->foreign('position_id')->references('id')->on('designations')->onDelete('set null');
        }
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
        // Drop foreign key constraints first (if they were added in this migration)
        // Use dropConstrainedForeignId or dropForeign with the conventional name

        if (Schema::hasColumn('users', 'grade_id')) {
          // Check if the constraint exists before dropping
          if (Schema::hasForeignKey('users', 'users_grade_id_foreign')) {
            $table->dropConstrainedForeignId('grade_id');
          }
        }
        if (Schema::hasColumn('users', 'department_id')) {
          if (Schema::hasForeignKey('users', 'users_department_id_foreign')) {
            $table->dropConstrainedForeignId('department_id');
          }
        }
        if (Schema::hasColumn('users', 'position_id')) {
          if (Schema::hasForeignKey('users', 'users_position_id_foreign')) {
            $table->dropConstrainedForeignId('position_id');
          }
        }


        // Drop the columns, checking if they exist before dropping
        $columnsToDrop = [
          'full_name',        // Added this migration
          'personal_email',   // Added this migration
          'is_admin',         // Added this migration
          'is_bpm_staff',     // Added this migration
          'service_status',
          'appointment_type',
          'motac_email',
          'nric', // Column name from this migration
          'mobile_number', // Column name from this migration
          'user_id_assigned',
          'status', // Column name from this migration
          // Do NOT include grade_id, department_id, position_id here if dropped via dropConstrainedForeignId above
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
