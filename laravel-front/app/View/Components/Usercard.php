<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ResultCard extends Component
{
    public $courseName;
    public $schedule;
    public $group;
    public $available;

    public function __construct($courseName = 'Course Name', $schedule, $group, $available = true)
    {
        $this->courseName = $courseName;
        $this->schedule = $schedule;
        $this->group = $group;
        $this->available = $available;
    }

    public function render()
    {
        return view('components.result-card');
    }
}
