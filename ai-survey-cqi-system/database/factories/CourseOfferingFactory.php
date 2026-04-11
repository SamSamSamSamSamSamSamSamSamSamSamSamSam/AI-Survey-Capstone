<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\CourseOffering;
use App\Models\Subject;
use App\Models\Semester;
use App\Models\User;
use App\Models\OfferingType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourseOffering>
 */
class CourseOfferingFactory extends Factory
{
    protected $model = CourseOffering::class;

    public function definition(): array
    {
        $subjects = Subject::all();
        $semesters = Semester::all();
        $teachers = User::whereHas('roles', function($query) {
            $query->where('name', 'faculty');})->get(); // assign roles properly to avoid errors
        $offeringTypes = OfferingType::all();

        return [
            'subject_id' => $this->faker->randomElement($subjects)->id,
            'semester_id' => $this->faker->randomElement($semesters)->id,
            'teacher_id' => $this->faker->randomElement($teachers)->id,
            'block_id' => null, // Can be assigned later if needed
            'group_number' => $this->faker->numberBetween(1, 10),
            'offering_type_id' => $this->faker->randomElement($offeringTypes)->id,
        ];
    }
}