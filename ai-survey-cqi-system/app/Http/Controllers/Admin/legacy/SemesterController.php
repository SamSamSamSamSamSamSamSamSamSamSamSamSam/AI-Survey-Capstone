<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    public function index()
    {
        $semesters = Semester::orderByDesc('academic_start_year')
            ->orderByDesc('semester_number')
            ->get();

        return view('admin.semesters.index', compact('semesters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_start_year' => ['required', 'integer', 'digits:4', 'min:2000'],
            'semester_number'     => ['required', 'in:1,2,3'],
        ]);

        $semesterNumber = (int) $request->semester_number;
        $suffixes       = [1 => '1st', 2 => '2nd', 3 => '3rd'];
        $suffix         = $suffixes[$semesterNumber];
        $endYear        = $request->academic_start_year + 1;
        $name           = "{$suffix} Semester {$request->academic_start_year}-{$endYear}";

        $exists = Semester::where('academic_start_year', $request->academic_start_year)
            ->where('semester_number', $semesterNumber)
            ->exists();

        if ($exists) {
            return back()->with('error', "{$name} already exists.");
        }

        Semester::create([
            'name'                => $name,
            'academic_start_year' => $request->academic_start_year,
            'semester_number'     => $semesterNumber,
            'is_active'           => false,
        ]);

        return back()->with('success', "{$name} created successfully.");
    }

    public function activate(Semester $semester)
    {
        Semester::where('id', '!=', $semester->id)->update(['is_active' => false]);
        $semester->update(['is_active' => true]);

        return back()->with('success', "{$semester->name} is now the active semester.");
    }

    public function destroy(Semester $semester)
    {
        if ($semester->is_active) {
            return back()->with('error', 'Cannot delete the active semester. Activate another one first.');
        }

        $semester->delete();

        return back()->with('success', 'Semester deleted successfully.');
    }
}