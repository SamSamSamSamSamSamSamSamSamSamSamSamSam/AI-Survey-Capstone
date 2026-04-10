<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Enrollment;
use App\Models\CourseOffering;
use App\Models\User;
use App\Models\StudentStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        $students = User::whereHas('roles', function($query) {
            $query->where('name', 'student');})->get();
        $offerings = CourseOffering::all();
        $statuses = StudentStatus::all();

        $student = $this->faker->randomElement($students);
        $offering = $this->faker->randomElement($offerings);

        return [
            'student_id' => $student->id,
            'offering_id' => $offering->id,
            'student_status_id' => $this->faker->randomElement($statuses)->id,
        ];
    }
}