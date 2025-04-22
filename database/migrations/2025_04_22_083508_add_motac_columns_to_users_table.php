<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up()
  {
    Schema::table('users', function (Blueprint $table) {
      $table->foreignId('grade_id')->nullable()->constrained();
      $table->string('service_status')->default('permanent');
      $table->string('appointment_type')->nullable();
      $table->string('motac_email')->nullable()->unique();
      $table->string('nric')->unique()->nullable();
      $table->string('mobile_number')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('users', function (Blueprint $table) {
      //
    });
  }
};
