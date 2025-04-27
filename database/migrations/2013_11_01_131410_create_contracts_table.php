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
    Schema::create('contracts', function (Blueprint $table) {
      $table->id(); // Standard unsignedBigInteger primary key
      $table->string('name')->unique(); // ADDED: Assuming contract names should be unique
      // Integer type for work_rate
      $table->integer('work_rate')->default(100); // ADDED: Default value
      $table->longText('notes')->nullable();

      // 👇 UPDATED: Audit columns using foreignId shorthand 👇
      // These should be nullable foreign keys referencing the 'users' table.
      // Assumes 'users' table exists and has standard 'id' (unsignedBigInteger).
      $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
      $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
      // ☝️ END UPDATED ☝️

      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('contracts', function (Blueprint $table) {
      // Drop foreign key constraints before dropping the table
      // Constraint names follow the convention: table_name_column_name_foreign
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    // Drop the table
    Schema::dropIfExists('contracts');
  }
};
