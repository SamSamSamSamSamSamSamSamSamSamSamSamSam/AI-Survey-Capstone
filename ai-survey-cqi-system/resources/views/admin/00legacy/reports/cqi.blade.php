<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CQI Summary Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; color: #222; line-height: 1.4; }
        h1, h2, h3 { text-align: center; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; page-break-inside: avoid; }
        th, td { border: 1px solid #444; padding: 5px 7px; text-align: center; font-size: 9pt; }
        th { background-color: #f0f0f0; font-weight: bold; }

        .section-title { 
            background-color: #d9d9d9; 
            padding: 6px;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 20px;
            text-align: left;
        }

        .note { font-size: 9pt; color: #555; }
        .left { text-align: left; }
        .small { font-size: 9pt; }
    </style>
</head>
<body>

    <h1>CQI Summary Report</h1>
    <p style="text-align:center;">
        Reporting Period: <strong>{{ $startDate }}</strong> – <strong>{{ $endDate }}</strong>
    </p>

    <!-- SECTION 1: RAW DATA MATRIX -->
    <div class="section-title">1. Respondent Rating Matrix</div>
    <table>
        <thead>
            <tr>
                <th>Respondent #</th>
                @foreach($questionsOrdered as $code)
                    <th>{{ strtoupper($code) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    @foreach($questionsOrdered as $code)
                        <td>{{ $row[$code] ?? '' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>


    <!-- SECTION 2: PER-QUESTION STATS -->
    <div class="section-title">2. Descriptive Statistics per Question</div>
    <table>
        <thead>
            <tr>
                <th>Variable</th>
                <th>Mean</th>
                <th>Median</th>
                <th>Mode</th>
                <th>Std Dev</th>
            </tr>
        </thead>
        <tbody>
            @foreach($questionStats as $code => $stats)
                <tr>
                    <td>{{ strtoupper($code) }}</td>
                    <td>{{ $stats['mean'] }}</td>
                    <td>{{ $stats['median'] }}</td>
                    <td>{{ $stats['mode'] }}</td>
                    <td>{{ $stats['std_dev'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <!-- SECTION 3: CATEGORY SUMMARY -->
    <div class="section-title">3. Summary of Descriptive Statistics by Category</div>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Avg Mean</th>
                <th>Avg Median</th>
                <th>Most Common Mode</th>
                <th>Avg Std Dev</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaryData as $category => $data)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $data['avg_mean'] }}</td>
                    <td>{{ $data['avg_median'] }}</td>
                    <td>{{ $data['most_common_mode'] }}</td>
                    <td>{{ $data['avg_std_dev'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @foreach($summaryData as $category => $data)
        <p><strong>{{ $category }} Interpretation:</strong> {{ $data['interpretation'] }}</p>
    @endforeach


    <!-- SECTION 4: GAP ANALYSIS -->
    <div class="section-title">4. GAP Analysis (Target = 4.5)</div>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Mean</th>
                <th>Standard (4.5)</th>
                <th>Gap</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaryData as $category => $data)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $data['avg_mean'] }}</td>
                    <td>4.5</td>
                    <td>{{ $data['gap'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <!-- SECTION 5: CQI PRIORITY LEVEL -->
    <div class="section-title">5. Priority Level Based on Gaps</div>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Gap</th>
                <th>Priority Level</th>
                <th>Interpretation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaryData as $category => $data)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $data['gap'] }}</td>
                    <td>{{ $data['priority'] }} 
                        @if($data['priority']==3) (Highest)
                        @elseif($data['priority']==2) (Medium)
                        @else (Low)
                        @endif
                    </td>
                    <td>{{ $data['interpretation'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <!-- SECTION 6: CQI ACTION PLAN (Gemini AI inserts actions here) -->
    <div class="section-title">6. CQI Action Plan (AI-Assisted)</div>

    <p class="small">* This section can be automatically filled by Gemini AI based on the gaps and priorities. *</p>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Gap</th>
                <th>Priority</th>
                <th>Action Plan (AI)</th>
                <th>Responsible Unit</th>
                <th>Time Frame</th>
                <th>Success Indicator</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summaryData as $category => $data)
                <tr>
                    <td>{{ $category }}</td>
                    <td>{{ $data['gap'] }}</td>
                    <td>{{ $data['priority'] }}</td>

                    <!-- EMPTY CELLS: Gemini AI will input text -->
                    <td class="left">[AI-generated Action Plan]</td>
                    <td class="left">[AI Unit Assignment]</td>
                    <td class="left">[AI Time Frame]</td>
                    <td class="left">[AI Success Indicators]</td>
                </tr>
            @endforeach
        </tbody>
    </table>


    <!-- FOOTER NOTE -->
    <p class="note">
        Standard deviation (SD) indicates variability in responses. 
        A higher SD reflects mixed or inconsistent perceptions across respondents.
        GAP values indicate how far actual performance is from the target rating (4.5).
    </p>

</body>
</html>
