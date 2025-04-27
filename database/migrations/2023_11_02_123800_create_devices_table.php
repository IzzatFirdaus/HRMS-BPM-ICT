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
    Schema::create('devices', function (Blueprint $table) {
      // Primary key (unsignedBigInteger)
      $table->id();

      // Device identification and details
      $table->string('name'); // e.g., "Fingerprint Scanner Entrance"
      $table->string('serial_number')->unique()->nullable(); // Unique serial number, can be null if not applicable
      $table->string('model')->nullable(); // e.g., "ZKTECO F18"
      $table->string('manufacturer')->nullable(); // e.g., "ZKTECO"
      $table->string('location')->nullable(); // Physical location description

      // Status flag
      $table->boolean('is_active')->default(true); // Whether the device is currently in use

      // Standard audit columns (nullable foreign keys to users table)
      // Assumes the 'users' table exists and has standard 'id' (unsignedBigInteger).
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
    Schema::table('devices', function (Blueprint $table) {
      // Drop foreign key constraints before dropping the table
      // Constraint names follow the convention: table_name_column_name_foreign
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    // Drop the table
    Schema::dropIfExists('devices');
  }
};
