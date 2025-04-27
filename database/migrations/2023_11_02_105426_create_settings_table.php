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
    Schema::create('settings', function (Blueprint $table) {
      $table->id();
      $table->string('sms_api_sender')->nullable(); // Made nullable as per common practice for configuration
      $table->string('sms_api_username')->nullable(); // Made nullable
      $table->string('sms_api_password')->nullable(); // Made nullable

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
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('settings', function (Blueprint $table) {
      // Drop foreign keys before dropping the table
      $table->dropForeign(['created_by']);
      $table->dropForeign(['updated_by']);
      $table->dropForeign(['deleted_by']);
    });
    Schema::dropIfExists('settings');
  }
};
