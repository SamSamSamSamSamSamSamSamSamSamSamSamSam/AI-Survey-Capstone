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
        ->latest()
        ->paginate(15);

        return view('faculty.reports.index', compact('reports'));
    }

    public function show(CqiReport $cqiReport): View
    {
        // Faculty can only view their own reports
        if ($cqiReport->faculty_id !== Auth::id()) {
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
