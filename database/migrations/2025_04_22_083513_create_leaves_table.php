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
    Schema::create('leaves', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->boolean('is_instantly');
      $table->boolean('is_accumulative');
      // 👇 UPDATED: Removed length(X) from integer types
      $table->integer('discount_rate'); // Changed from length(3)
      $table->integer('days_limit'); // Changed from length(3)
      $table->integer('minutes_limit'); // Changed from length(3)
      // ☝️ END UPDATED
      $table->longText('notes')->nullable();

      // 👇 UPDATED: Audit columns as nullable unsignedBigInteger with foreign keys
      $table->unsignedBigInteger('created_by')->nullable();
      $table->unsignedBigInteger('updated_by')->nullable();
      $table->unsignedBigInteger('deleted_by')->nullable();

      // Define foreign key constraints for audit columns
      $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
      $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
      // ☝️ END UPDATED

      $table->timestamps();
      // 👇 UPDATED: Added soft deletes for consistency
      $table->softDeletes();
      // ☝️ END UPDATED
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('leaves');
  }
};
