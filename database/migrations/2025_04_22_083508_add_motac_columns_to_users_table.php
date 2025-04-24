<?php

// database/migrations/[timestamp]_add_motac_columns_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::table('users', function (Blueprint $table) {
      // Add columns based on the MOTAC user data requirements
      $table->foreignId('grade_id')->nullable()->constrained(); // Ensure a 'grades' table exists or is created
      $table->string('service_status')->default('permanent');
      $table->string('appointment_type')->nullable();
      $table->string('motac_email')->nullable()->unique();
      $table->string('nric')->unique()->nullable(); // Assuming nric is unique per user
      $table->string('mobile_number')->nullable();
      // Add department_id and position_id if they don't exist
      $table->foreignId('department_id')->nullable()->constrained(); // Ensure 'departments' table exists or is created
      $table->foreignId('position_id')->nullable()->constrained(); // Ensure 'positions' table exists or is created
      $table->string('user_id_assigned')->nullable()->unique(); // Based on system design
      $table->string('status')->default('active'); // User status based on system design
    });
  }

  public function down()
  {
    Schema::table('users', function (Blueprint $table) {
      // Drop the columns added in the up method
      // Ensure foreign key constraints are dropped first
      $table->dropForeign(['grade_id']);
      // If you added department_id or position_id here, drop their foreign keys too
      // $table->dropForeign(['department_id']);
      // $table->dropForeign(['position_id']);
      $table->dropColumn([
        'grade_id',
        'service_status',
        'appointment_type',
        'motac_email',
        'nric',
        'mobile_number',
        'user_id_assigned',
        'status',
        // Drop department_id and position_id here if you added them in up()
        // 'department_id',
        // 'position_id',
      ]);
    });
  }
};
