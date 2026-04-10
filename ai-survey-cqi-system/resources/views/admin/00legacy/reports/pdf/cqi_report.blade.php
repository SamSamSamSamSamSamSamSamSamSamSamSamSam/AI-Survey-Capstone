<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>CQI Report - {{ $meta['teacher_name'] }}</title>
    <style>
        /* ── Reset & Base ───────────────────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10pt;
            color: #222;
            background: #fff;
            line-height: 1.5;
        }

        /* ── Page Header (repeats on each page) ─────────────────────────── */
        .page-header {
            text-align: center;
            margin-bottom: 18pt;
            padding-bottom: 8pt;
        }
        .page-header h1 {
            font-size: 20pt;
            font-weight: bold;
            letter-spacing: 0.02em;
        }
        .page-header h2 {
            font-size: 11pt;
            font-weight: normal;
            color: #444;
            margin: 2pt 0;
        }
        .page-header h3 {
            font-size: 15pt;
            font-weight: bold;
        }

        /* ── Info Table (cover page meta) ────────────────────────────────── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20pt;
        }
        .info-table td {
            border: 1px solid #aaa;
            padding: 6pt 10pt;
            font-size: 10pt;
        }
        .info-table td.label {
            font-weight: bold;
            width: 18%;
            background: #f5f5f5;
        }
        .info-table td.value {
            width: 30%;
        }

        /* ── Section Card ────────────────────────────────────────────────── */
        .section-card {
            border: 1px solid #bbb;
            border-radius: 4pt;
            margin-bottom: 18pt;
            overflow: hidden;
        }
        .section-instruction {
            background: #f0f0e8;
            padding: 8pt 10pt;
            font-size: 8.5pt;
            color: #333;
            border-bottom: 1px solid #bbb;
            line-height: 1.4;
        }
        .section-title-row {
            background: #d6d9b8;
            padding: 7pt 10pt;
            font-weight: bold;
            font-size: 10pt;
            display: flex;
            align-items: center;
            gap: 6pt;
        }
        .section-dot {
            width: 8pt;
            height: 8pt;
            background: #888;
            border-radius: 50%;
            display: inline-block;
        }

        /* ── Rating Table ────────────────────────────────────────────────── */
        .rating-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        .rating-table th {
            background: #f5f5f5;
            border-bottom: 1px solid #bbb;
            padding: 5pt 8pt;
            font-size: 9pt;
            text-align: center;
            font-weight: bold;
        }
        .rating-table th.q-header {
            text-align: left;
            width: 55%;
        }
        .rating-table td {
            padding: 5pt 8pt;
            font-size: 9pt;
            border-bottom: 1px solid #e0e0d8;
            vertical-align: top;
        }
        .rating-table td.q-text {
            text-align: left;
        }
        .rating-table td.num {
            text-align: center;
        }
        .rating-table tr:nth-child(even) td {
            background: #fafaf5;
        }
        .rating-total-row td {
            font-weight: bold;
            text-align: right;
            padding: 6pt 10pt;
            background: #f0f0e8;
            border-top: 1px solid #bbb;
            font-size: 10pt;
        }

        /* ── Satisfaction Table ──────────────────────────────────────────── */
        .sat-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        .sat-table th, .sat-table td {
            border: 1px solid #bbb;
            padding: 5pt 7pt;
            text-align: center;
        }
        .sat-table th {
            background: #d6d9b8;
            font-weight: bold;
        }
        .sat-result {
            font-size: 11pt;
            font-weight: bold;
            color: #2a4a2a;
            text-align: center;
            padding: 6pt;
        }

        /* ── Open Text Section ───────────────────────────────────────────── */
        .opentext-card {
            border: 1px solid #bbb;
            border-radius: 4pt;
            margin-bottom: 18pt;
            background: #fafaf5;
            padding: 10pt 12pt;
        }
        .opentext-question {
            font-weight: bold;
            font-size: 9.5pt;
            margin-bottom: 5pt;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4pt;
        }
        .opentext-response {
            font-size: 9pt;
            color: #444;
            padding: 2pt 0 2pt 10pt;
        }
        .opentext-response::before {
            content: "* ";
            color: #888;
        }

        /* ── Summary Table ───────────────────────────────────────────────── */
        .summary-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 12pt;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }
        .summary-table th {
            background: #d6d9b8;
            padding: 7pt 10pt;
            text-align: center;
            font-weight: bold;
            border: 1px solid #aaa;
        }
        .summary-table td {
            padding: 7pt 10pt;
            border: 1px solid #ccc;
        }
        .summary-table tr:nth-child(even) td {
            background: #f5f5ee;
        }
        .summary-table td.mean {
            text-align: center;
            font-weight: bold;
        }
        .summary-table td.interp {
            text-align: center;
        }
        .summary-overall td {
            background: #d6d9b8 !important;
            font-weight: bold;
            font-size: 11pt;
        }

        /* ── AI Narrative Sections ───────────────────────────────────────── */
        .narrative-section {
            margin-bottom: 16pt;
        }
        .narrative-section h4 {
            font-size: 11pt;
            font-weight: bold;
            color: #1a3a1a;
            margin-bottom: 6pt;
            padding-bottom: 3pt;
            border-bottom: 1.5pt solid #d6d9b8;
        }
        .narrative-section p {
            font-size: 9.5pt;
            color: #333;
            line-height: 1.6;
            text-align: justify;
        }
        .narrative-list {
            margin: 0;
            padding-left: 14pt;
        }
        .narrative-list li {
            font-size: 9.5pt;
            color: #333;
            margin-bottom: 4pt;
            line-height: 1.5;
        }

        /* ── Gap / Action Plan Tables ────────────────────────────────────── */
        .analysis-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-top: 6pt;
        }
        .analysis-table th {
            background: #d6d9b8;
            padding: 6pt 8pt;
            text-align: left;
            font-weight: bold;
            border: 1px solid #aaa;
        }
        .analysis-table td {
            padding: 6pt 8pt;
            border: 1px solid #ccc;
            vertical-align: top;
            line-height: 1.4;
        }
        .analysis-table tr:nth-child(even) td {
            background: #f8f8f0;
        }

        /* ── Page Break ──────────────────────────────────────────────────── */
        .page-break {
            page-break-after: always;
        }

        /* ── Footer ──────────────────────────────────────────────────────── */
        .pdf-footer {
            text-align: center;
            font-size: 8pt;
            color: #888;
            margin-top: 20pt;
            border-top: 1px solid #ddd;
            padding-top: 6pt;
        }
    </style>
</head>
<body>

{{-- ============================================================
     PAGE 1 — COVER + COURSE SYLLABUS
     ============================================================ --}}
<div class="page-header">
    <h1>University of San Carlos</h1>
    <h2>School of Arts and Sciences</h2>
    <h3>Teachers' Evaluation and CQI Report</h3>
</div>

<table class="info-table">
    <tr>
        <td class="label">Teacher's Name</td>
        <td class="value">{{ $meta['teacher_name'] }}</td>
        <td class="label">Name of Program</td>
        <td class="value">{{ $meta['program'] }}</td>
    </tr>
    <tr>
        <td class="label">Academic Term</td>
        <td class="value">{{ $meta['academic_term'] }}</td>
        <td class="label">Course Handled</td>
        <td class="value">{{ $meta['course_handled'] }}</td>
    </tr>
    <tr>
        <td class="label">Academic Year</td>
        <td class="value">{{ $meta['academic_year'] }}</td>
        <td class="label">Group Number</td>
        <td class="value">{{ $meta['group_number'] }}</td>
    </tr>
</table>

{{-- ── Render each rated category ──────────────────────────────────────── --}}
@foreach($categories as $key => $category)

    @if(!$loop->first)
        <div class="page-break"></div>
        <div class="page-header">
            <h1>University of San Carlos</h1>
            <h2>School of Arts and Sciences</h2>
            <h3>Teachers' Evaluation and CQI Report</h3>
        </div>
    @endif

    <div class="section-card">
        <div class="section-instruction">
            The following statements describe the extent to which {{ $category['label'] }} criteria
            are met in this course. Based on the scale below, rate the extent to which you agree
            with the following statements. Please tick the column corresponding to your level of agreement.
        </div>

        <div class="section-title-row">
            <span class="section-dot"></span>
            {{ $category['label'] }}
        </div>

        {{-- Scale legend --}}
        <div style="padding: 6pt 10pt; font-size: 8pt; background: #f8f8f0; border-bottom: 1px solid #ddd;">
            <strong>1</strong> - Strongly Disagree &nbsp;|&nbsp;
            <strong>2</strong> - Disagree &nbsp;|&nbsp;
            <strong>3</strong> - Agree &nbsp;|&nbsp;
            <strong>4</strong> - Strongly Agree
        </div>

        <table class="rating-table">
            <thead>
                <tr>
                    <th class="q-header">Item</th>
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>Total</th>
                    <th>WM</th>
                </tr>
            </thead>
            <tbody>
                @foreach($category['questions'] as $i => $q)
                    <tr>
                        <td class="q-text">{{ ($i + 1) }}. {{ $q['text'] }}</td>
                        <td class="num">{{ $q['counts'][1] }}</td>
                        <td class="num">{{ $q['counts'][2] }}</td>
                        <td class="num">{{ $q['counts'][3] }}</td>
                        <td class="num">{{ $q['counts'][4] }}</td>
                        <td class="num">{{ $q['total'] }}</td>
                        <td class="num">{{ $q['wm'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="rating-total-row">
                    <td colspan="7">
                        Total: &nbsp;
                        {{ round($category['total_wm'], 1) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

@endforeach

{{-- ============================================================
     SATISFACTION + OPEN TEXT PAGE
     ============================================================ --}}
<div class="page-break"></div>
<div class="page-header">
    <h1>University of San Carlos</h1>
    <h2>School of Arts and Sciences</h2>
    <h3>Teachers' Evaluation and CQI Report</h3>
</div>

{{-- Satisfaction question --}}
@if($satisfaction)
<div class="section-card">
    <div class="section-instruction">
        {{ $satisfaction['question'] }}
    </div>
    <div class="section-title-row">
        <span class="section-dot"></span>
        Learning Experience (Satisfaction)
    </div>

    <div style="padding: 10pt;">
        <table class="sat-table">
            <thead>
                <tr>
                    @for($i = 1; $i <= 10; $i++)
                        <th>{{ $i }}</th>
                    @endfor
                    <th>Total</th>
                    <th>WM</th>
                    <th>Label</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    @for($i = 1; $i <= 10; $i++)
                        <td>{{ $satisfaction['counts'][$i] ?? 0 }}</td>
                    @endfor
                    <td>{{ $satisfaction['total'] }}</td>
                    <td><strong>{{ $satisfaction['wm'] }}</strong></td>
                    <td><strong>{{ $satisfaction['label'] }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Open-ended responses --}}
@foreach($open_text as $item)
<div class="opentext-card">
    <div class="opentext-question">{{ $item['question'] }}</div>
    @foreach($item['responses'] as $response)
        <div class="opentext-response">{{ $response }}</div>
    @endforeach
</div>
@endforeach

{{-- ============================================================
     SUMMARY OF FINDINGS PAGE
     ============================================================ --}}
<div class="page-break"></div>
<div class="page-header">
    <h1>University of San Carlos</h1>
    <h2>School of Arts and Sciences</h2>
    <h3>Teachers' Evaluation and CQI Report</h3>
</div>

<div class="summary-title">Summary of Findings</div>

<table class="summary-table">
    <thead>
        <tr>
            <th style="text-align:left;">Criteria</th>
            <th>Mean Score</th>
            <th>Interpretation</th>
        </tr>
    </thead>
    <tbody>
        @foreach($summary as $item)
            <tr>
                <td>{{ $item['label'] }}</td>
                <td class="mean">{{ $item['mean_score'] }}</td>
                <td class="interp">{{ $item['interpretation'] }}</td>
            </tr>
        @endforeach
        @if($satisfaction)
            <tr>
                <td>Learning Experience</td>
                <td class="mean">{{ $satisfaction['wm'] }}</td>
                <td class="interp">{{ $satisfaction['label'] }}</td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr class="summary-overall">
            <td><strong>Overall Mean Score:</strong></td>
            <td class="mean">{{ $overall_mean }}</td>
            <td class="interp">{{ $overall_interpretation }}</td>
        </tr>
    </tfoot>
</table>

{{-- ============================================================
     AI NARRATIVE — ANALYSIS, GAPS, STRENGTHS, IMPROVEMENTS
     ============================================================ --}}
<div class="page-break"></div>
<div class="page-header">
    <h1>University of San Carlos</h1>
    <h2>School of Arts and Sciences</h2>
    <h3>Teachers' Evaluation and CQI Report</h3>
</div>

<div class="narrative-section">
    <h4>Analysis of Results (OBE Perspective)</h4>
    <p>{{ $narrative['analysis'] }}</p>
</div>

@if(!empty($narrative['identified_gaps']))
<div class="narrative-section">
    <h4>Identified Gaps</h4>
    <table class="analysis-table">
        <thead>
            <tr>
                <th style="width:25%">Area</th>
                <th style="width:40%">Gap</th>
                <th style="width:35%">Impact</th>
            </tr>
        </thead>
        <tbody>
            @foreach($narrative['identified_gaps'] as $gap)
            <tr>
                <td>{{ $gap['area'] ?? '' }}</td>
                <td>{{ $gap['gap'] ?? '' }}</td>
                <td>{{ $gap['impact'] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($narrative['strengths']))
<div class="narrative-section">
    <h4>Strengths Identified</h4>
    <ul class="narrative-list">
        @foreach($narrative['strengths'] as $strength)
            <li>{{ $strength }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(!empty($narrative['areas_for_improvement']))
<div class="narrative-section">
    <h4>Areas for Improvement</h4>
    <ul class="narrative-list">
        @foreach($narrative['areas_for_improvement'] as $area)
            <li>{{ $area }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- ============================================================
     ROOT CAUSE + ACTION PLAN + MONITORING + CONCLUSION
     ============================================================ --}}
<div class="page-break"></div>
<div class="page-header">
    <h1>University of San Carlos</h1>
    <h2>School of Arts and Sciences</h2>
    <h3>Teachers' Evaluation and CQI Report</h3>
</div>

@if(!empty($narrative['root_cause_analysis']))
<div class="narrative-section">
    <h4>Root Cause Analysis</h4>
    <table class="analysis-table">
        <thead>
            <tr>
                <th style="width:35%">Issue</th>
                <th>Possible Cause</th>
            </tr>
        </thead>
        <tbody>
            @foreach($narrative['root_cause_analysis'] as $rca)
            <tr>
                <td>{{ $rca['issue'] ?? '' }}</td>
                <td>{{ $rca['possible_cause'] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($narrative['action_plan']))
<div class="narrative-section">
    <h4>Action Plan</h4>
    <table class="analysis-table">
        <thead>
            <tr>
                <th>Area</th>
                <th>Action</th>
                <th>Responsible</th>
                <th>Timeline</th>
                <th>Expected Outcome</th>
            </tr>
        </thead>
        <tbody>
            @foreach($narrative['action_plan'] as $plan)
            <tr>
                <td>{{ $plan['area'] ?? '' }}</td>
                <td>{{ $plan['action'] ?? '' }}</td>
                <td>{{ $plan['responsible'] ?? '' }}</td>
                <td>{{ $plan['timeline'] ?? '' }}</td>
                <td>{{ $plan['expected_outcome'] ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if(!empty($narrative['monitoring']))
<div class="narrative-section">
    <h4>Monitoring and Evaluation</h4>
    <ul class="narrative-list">
        @foreach($narrative['monitoring'] as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(!empty($narrative['conclusion']))
<div class="narrative-section">
    <h4>Conclusion</h4>
    <p>{{ $narrative['conclusion'] }}</p>
</div>
@endif

<div class="pdf-footer">
    Generated by DCISM Admin Portal &mdash; {{ now()->format('F d, Y') }}
    &nbsp;|&nbsp; University of San Carlos, School of Arts and Sciences
</div>

</body>
</html>