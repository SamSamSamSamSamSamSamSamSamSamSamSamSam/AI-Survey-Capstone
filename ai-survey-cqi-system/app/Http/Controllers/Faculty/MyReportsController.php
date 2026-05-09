<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\CqiReport;
use App\Models\CqiReportLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyReportsController extends Controller
{
    public function index(): View
    {
        $reports = CqiReport::with([
            'survey.offering.subject',
            'survey.offering.semester',
        ])
        ->where('faculty_id', Auth::id())
        ->whereNull('deleted_at')
        ->whereHas('logs', function ($query) { $query->where('action', 'sent_to_faculty'); })
        ->latest()
        ->paginate(15);

        return view('faculty.reports.index', compact('reports'));
    }

    public function show(CqiReport $cqiReport): View
    {
        $wasSent = $cqiReport->logs()->where('action', 'sent_to_faculty')->exists();
        // Faculty can only view their own reports
        if ($cqiReport->faculty_id !== Auth::id() || !$wasSent) {
            abort(403);
        }

        $cqiReport->load(['survey.offering.subject', 'survey.offering.semester']);

        return view('faculty.reports.show', compact('cqiReport'));
    }

    public function download(CqiReport $cqiReport): StreamedResponse|RedirectResponse
    {
        if ($cqiReport->faculty_id !== Auth::id()) {
            abort(403);
        }

        $wasSent = $cqiReport->logs()->where('action', 'sent_to_faculty')->exists();
        if (!$wasSent) {
            abort(403, 'This report has not been released by the administrator yet.');
        }

        if (! Storage::disk('public')->exists($cqiReport->pdf_path)) {
            return back()->with('error', 'PDF file not found. Please contact the administrator.');
        }

        CqiReportLog::create([
            'report_id'    => $cqiReport->id,
            'performed_by' => Auth::id(),
            'action'       => 'downloaded',
            'notes'        => 'Downloaded by faculty.',
        ]);

        return Storage::disk('public')->download($cqiReport->pdf_path, basename($cqiReport->pdf_path));
    }
}
