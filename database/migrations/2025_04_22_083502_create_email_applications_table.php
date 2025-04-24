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
    Schema::create('email_applications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->string('purpose');
      $table->string('proposed_email')->nullable(); // Suggested email based on naming convention
      $table->string('group_email')->nullable(); // For group email requests
      $table->string('group_admin_name')->nullable(); // Admin for group email
      $table->string('group_admin_email')->nullable(); // Admin email for group email
      $table->enum('status', ['draft', 'pending_support', 'pending_admin', 'approved', 'rejected', 'processing', 'completed'])->default('draft');
      $table->boolean('certification_accepted')->default(false);
      $table->timestamp('certification_timestamp')->nullable();
      $table->string('rejection_reason')->nullable();
      $table->string('final_assigned_email')->nullable()->unique(); // The actual email assigned
      $table->string('final_assigned_user_id')->nullable()->unique(); // The actual user ID assigned
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('email_applications');
  }
};
