<?php

namespace Database\Seeders;

use App\Models\User; // Import the User model
// Remove these imports as seed order is controlled by DatabaseSeeder
// use App\Models\Department;
// use App\Models\Position;
// use App\Models\Grade;

use Illuminate\Database\Seeder;
// Remove these imports if only using factories
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run(): void
  {
    // Remove truncate() - migrate:fresh handles clearing tables.
    // User::truncate();

    // Remove duplicate admin user creation logic - AdminUserSeeder handles this.
    /*
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'nric' => '000000000000',
            'phone_number' => '0123456789',
            'personal_email' => 'admin.personal@example.com',
            'is_admin' => true,
            'is_bpm_staff' => true,
            'department_id' => Department::first()?->id,
            'position_id' => Position::first()?->id,
            'grade_id' => Grade::first()?->id,
        ]);
        */

    // Remove nested $this->call() checks - seeding order is controlled by DatabaseSeeder.
    /*
        if (Department::count() === 0) {
            $this->call(DepartmentSeeder::class);
        }
        if (Position::count() === 0) {
            $this->call(PositionSeeder::class);
        }
        if (Grade::count() === 0) {
            $this->call(GradeSeeder::class);
        }
        */

    // --- Create some regular users using the User Factory ---
    // This is the primary purpose of UserSeeder when AdminUserSeeder exists.
    // The factory should handle assigning random departments, positions, grades etc.
    // based on existing data, relying on DatabaseSeeder order.
    User::factory()->count(50)->create();

    // Log a message (optional)
    // \Log::info('Regular users seeded successfully.');
  }
}
