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
    Schema::create('fingerprints', function (Blueprint $table) {
      $table->id();

      // Foreign key to employees table - Assumes employees table exists and has standard 'id' (unsignedBigInteger)
      // Your current migration order ensures employees is created before fingerprints.
      $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // Cascade delete if employee is removed

      // 👇 Foreign key to devices table 👇
      // THIS MIGRATION WILL FAIL IF THE 'devices' TABLE DOES NOT EXIST YET.
      // Ensure the 'create_devices_table.php' migration has an earlier timestamp than this file.
      $table->foreignId('device_id')->nullable()->constrained('devices')->onDelete('set null');
      // ☝️ End device_id FK ☝️

      $table->date('date');
      $table->string('log')->nullable();
      $table->time('check_in')->nullable(); // Time format
      $table->time('check_out')->nullable(); // Time format
      $table->boolean('is_checked')->default(false); // Default to false
      $table->string('excuse')->nullable();

      // 👇 UPDATED: Audit columns using foreignId shorthand 👇
      // These should be nullable foreign keys referencing the 'users' table.
      // Assumes 'users' table exists and has standard 'id' (unsignedBigInteger), which it does by this point.
      $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
      // ☝️ END UPDATED ☝️

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
    Schema::table('fingerprints', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      // Constraint names follow the convention: table_name_column_name_foreign
      $table->dropForeign(['employee_id']);
      // Check if column exists before attempting to drop the foreign key
      if (Schema::hasColumn('fingerprints', 'device_id')) {
        $table->dropForeign(['device_id']);
      }
      // Drop audit foreign keys
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    // Drop the table
    Schema::dropIfExists('fingerprints');
  }
};
