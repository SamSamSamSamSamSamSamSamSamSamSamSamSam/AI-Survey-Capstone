<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateCqiReportJob;
use App\Models\CqiReport;
use App\Models\CqiReportLog;
use App\Models\FacultyAnalytics;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

use Symfony\Component\HttpFoundation\StreamedResponse;

    class CqiReportController extends Controller
    {
    public function index(Request $request): \Illuminate\Contracts\View\View|string
    {
        $semesters          = Semester::orderByDesc('academic_start_year')->get();
        $activeSemester     = Semester::current();
        $selectedSemesterId = $request->input('semester_id', $activeSemester?->id);

        $query = CqiReport::with([
            'survey.offering.subject',
            'survey.offering.teacher',
            'survey.offering.semester',
            'generatedBy',
        ])->withTrashed();

        if ($selectedSemesterId) {
            $query->whereHas('survey.offering', fn ($q) =>
                $q->where('semester_id', $selectedSemesterId)
            );
        }

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->input('status') === 'deleted') {
            $query->onlyTrashed();
        } elseif ($request->input('status') !== 'all') {
            $query->whereNull('cqi_reports.deleted_at');
        }

        $reports = $query->latest()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            // This returns a string, which caused the TypeError
            return view('admin.cqi-reports._table', compact('reports'))->render();
        }

        return view('admin.cqi-reports.index', compact('reports', 'semesters', 'activeSemester', 'selectedSemesterId'));
    }

    public function show(CqiReport $cqiReport): View
    {
        $cqiReport->load(['survey.offering.subject', 'survey.offering.teacher', 'survey.offering.semester', 'generatedBy', 'logs.performedBy']);

        return view('admin.cqi-reports.show', compact('cqiReport'));
    }

    /**
     * Dispatch the GenerateCqiReportJob for a given survey.
     */
    public function generate(Request $request): RedirectResponse
    {
        $request->validate([
            'survey_id'  => ['required', 'exists:surveys,id'],
            'scope_type' => ['required', 'in:survey,offering,faculty'],
        ]);

        // Check analytics exist
        $analytics = FacultyAnalytics::where('survey_id', $request->survey_id)->first();

        if (! $analytics) {
            return back()->with('error', 'Analytics have not been computed for this survey yet. Please wait for the analytics job to complete or recompute manually.');
        }

        if ($analytics->response_count === 0) {
            return back()->with('error', 'No responses found for this survey. Cannot generate a CQI report.');
        }

        $isRegenerated = CqiReport::where('survey_id', $request->survey_id)->whereNull('deleted_at')->exists();

        GenerateCqiReportJob::dispatch(
            $request->survey_id,
            Auth::id(),
            $request->scope_type,
            $isRegenerated,
        );

        $action = $isRegenerated ? 'regeneration' : 'generation';

        return redirect()->route('admin.cqi-reports.index')
                         ->with('success', "CQI report {$action} has been queued. It will be available shortly.");
    }

    public function download(CqiReport $cqiReport): StreamedResponse|RedirectResponse
    {
        if (! Storage::disk('public')->exists($cqiReport->pdf_path)) {
            return back()->with('error', 'PDF file not found.');
        }

        // Log download
        CqiReportLog::create([
            'report_id'    => $cqiReport->id,
            'performed_by' => Auth::id(),
            'action'       => 'downloaded',
            'notes'        => 'Downloaded by admin.',
        ]);

        $filename = basename($cqiReport->pdf_path);

        return Storage::disk('public')->download($cqiReport->pdf_path, $filename);
    }

    /**
     * Mark report as sent to faculty and log it.
     */
    public function sendToFaculty(CqiReport $cqiReport): RedirectResponse
    {
        CqiReportLog::create([
            'report_id'    => $cqiReport->id,
            'performed_by' => Auth::id(),
            'action'       => 'sent_to_faculty',
            'notes'        => "Sent to faculty: {$cqiReport->faculty->name}.",
        ]);

        return back()->with('success', 'Report marked as sent to faculty.');
    }

    public function destroy(CqiReport $cqiReport): RedirectResponse
    {
        $cqiReport->delete();

        return redirect()->route('admin.cqi-reports.index')
                         ->with('success', 'CQI report archived.');
    }
}
