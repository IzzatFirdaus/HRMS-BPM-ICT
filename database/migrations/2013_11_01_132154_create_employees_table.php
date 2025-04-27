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
    Schema::create('employees', function (Blueprint $table) {
      $table->id(); // Standard unsignedBigInteger primary key

      // Foreign key to contracts table - Assumes contracts table exists and has standard 'id'
      // The error suggests a potential incompatibility here with the contracts table definition.
      $table->foreignId('contract_id')->constrained()->onDelete('restrict'); // Added onDelete restrict as deleting a contract with employees might be undesirable

      $table->string('first_name');
      $table->string('father_name')->nullable(); // Made nullable based on common data structure
      $table->string('last_name');
      $table->string('mother_name')->nullable(); // Made nullable
      $table->string('birth_and_place')->nullable(); // Made nullable
      $table->string('national_number')->unique();
      $table->string('mobile_number')->unique();
      $table->string('degree')->nullable(); // Made nullable
      // Enum for gender - or use a lookup table
      $table->enum('gender', ['Male', 'Female', 'Other'])->nullable(); // Added nullable
      $table->string('address')->nullable(); // Made nullable
      $table->longText('notes')->nullable();
      // Integer types for counters - standard sizes
      $table->integer('balance_leave_allowed')->default(0);
      $table->integer('max_leave_allowed')->default(0);
      // Time format for counters
      $table->time('delay_counter')->default('00:00:00'); // Default without milliseconds unless needed
      $table->time('hourly_counter')->default('00:00:00'); // Default without milliseconds
      $table->boolean('is_active')->default(true); // Use boolean default true
      // profile_photo_path can be nullable if not immediately set
      $table->string('profile_photo_path')->nullable();

      // 👇 UPDATED: Audit columns using foreignId shorthand 👇
      // These should be nullable foreign keys referencing the 'users' table.
      // Assumes 'users' table exists and has standard 'id' (unsignedBigInteger).
      $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
      // ☝️ END UPDATED ☝️

      // Standard timestamps and soft delete timestamp
      $table->timestamps();
      $table->softDeletes();

      // 👇 ADDED: Foreign keys to Department, Position, Grade if they are added in this migration
      // Based on our previous plan, these were added in the add_motac_columns_to_employees migration.
      // If you moved them here, uncomment and ensure their referenced tables exist.
      // $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null'); // Assumes departments exists
      // $table->foreignId('position_id')->nullable()->constrained()->onDelete('set null'); // Assumes positions exists
      // $table->foreignId('grade_id')->nullable()->constrained()->onDelete('set null'); // Assumes grades exists
      // ☝️ END ADDED ☝️
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('employees', function (Blueprint $table) {
      // Drop foreign key constraints before dropping the table
      // Constraint names follow the convention: table_name_column_name_foreign
      $table->dropForeign(['contract_id']);
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
      // Drop added foreign keys if you uncommented them above:
      // if (Schema::hasColumn('employees', 'department_id')) $table->dropForeign(['department_id']);
      // if (Schema::hasColumn('employees', 'position_id')) $table->dropForeign(['position_id']);
      // if (Schema::hasColumn('employees', 'grade_id')) $table->dropForeign(['grade_id']);
    });
    Schema::dropIfExists('employees');
  }
};
