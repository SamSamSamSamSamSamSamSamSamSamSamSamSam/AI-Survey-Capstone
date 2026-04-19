{{-- ============================================================
     resources/views/partials/_analytics_panels.blade.php
     Shared panel HTML for both admin/analytics/charts.blade.php
     and faculty/analytics/charts.blade.php
     All interactions wired in analytics-charts.js
     ============================================================ --}}

{{-- ══════════ OVERVIEW ══════════ --}}
<div id="tab-overview" class="an-panel an-panel--active">

    <div class="an-metrics" id="ov-metrics">
        <div class="an-metric">
            <div class="an-metric-label">Overall avg rating</div>
            <div class="an-metric-val" id="ov-avg">—</div>
            <div class="an-metric-sub">across all surveys</div>
        </div>
        <div class="an-metric">
            <div class="an-metric-label">Total responses</div>
            <div class="an-metric-val" id="ov-resp">—</div>
            <div class="an-metric-sub">submitted</div>
        </div>
        <div class="an-metric">
            <div class="an-metric-label">Positive sentiment</div>
            <div class="an-metric-val" id="ov-pos">—</div>
            <div class="an-metric-sub">open-ended</div>
        </div>
        <div class="an-metric">
            <div class="an-metric-label">Surveys analysed</div>
            <div class="an-metric-val" id="ov-count">—</div>
            <div class="an-metric-sub">completed</div>
        </div>
    </div>

    <div class="an-two-col">
        <div class="an-card">
            <div class="an-card-title">Rating distribution</div>
            <div class="an-card-sub">Count of surveys by rounded avg rating</div>
            <div style="position:relative;height:220px">
                <canvas id="c-dist" role="img" aria-label="Rating distribution bar chart"></canvas>
            </div>
        </div>
        <div class="an-card">
            <div class="an-card-title">Sentiment breakdown</div>
            <div class="an-card-sub">Latest period</div>
            <div id="leg-sent" class="an-legend"></div>
            <div style="position:relative;height:200px">
                <canvas id="c-sent" role="img" aria-label="Sentiment doughnut chart"></canvas>
            </div>
        </div>
    </div>

    <div class="an-card">
        <div class="an-card-title">Category scores</div>
        <div class="an-card-sub">Average per category — threshold shown</div>
        <div style="position:relative;height:280px">
            <canvas id="c-cats-ov" role="img" aria-label="Category scores horizontal bar chart"></canvas>
        </div>
    </div>

</div>

{{-- ══════════ TRENDS ══════════ --}}
<div id="tab-trends" class="an-panel">

    <div class="an-controls">
        <label class="an-controls-label">Metric</label>
        <select id="trend-metric" class="an-select">
            <option value="avg_rating">Avg rating</option>
            <option value="positive_sentiment_percent">Positive sentiment %</option>
            <option value="negative_sentiment_percent">Negative sentiment %</option>
            <option value="response_count">Response count</option>
        </select>
        <label class="an-controls-label">Chart</label>
        <select id="trend-type" class="an-select">
            <option value="line">Line</option>
            <option value="bar">Bar</option>
        </select>
    </div>

    <div class="an-card">
        <div class="an-card-title" id="trend-title">Avg rating over time</div>
        <div class="an-card-sub">All courses combined — per semester</div>
        <div style="position:relative;height:280px">
            <canvas id="c-trend" role="img" aria-label="Trend chart"></canvas>
        </div>
    </div>

    <div class="an-two-col">
        <div class="an-card">
            <div class="an-card-title">Semester-over-semester change</div>
            <div id="sem-changes"></div>
        </div>
        <div class="an-card">
            <div class="an-card-title">Per-course comparison</div>
            <div class="an-card-sub">Last 4 semesters</div>
            <div id="leg-course" class="an-legend"></div>
            <div style="position:relative;height:200px">
                <canvas id="c-course-trend" role="img" aria-label="Per-course trend comparison"></canvas>
            </div>
        </div>
    </div>

</div>

{{-- ══════════ CATEGORIES ══════════ --}}
<div id="tab-categories" class="an-panel">

    <div class="an-controls">
        <label class="an-controls-label">Sort</label>
        <select id="cat-sort" class="an-select">
            <option value="score">By score</option>
            <option value="name">Alphabetically</option>
        </select>
    </div>

    <div class="an-card">
        <div class="an-card-title">Category scores with benchmarks</div>
        <div class="an-card-sub">Your avg vs department avg — threshold line shown</div>
        <div style="position:relative;height:300px">
            <canvas id="c-cats" role="img" aria-label="Category scores bar chart"></canvas>
        </div>
    </div>

    <div class="an-two-col">
        <div class="an-card">
            <div class="an-card-title">Top performing</div>
            <div id="cat-top"></div>
        </div>
        <div class="an-card">
            <div class="an-card-title">Needs improvement</div>
            <div id="cat-low"></div>
        </div>
    </div>

    <div class="an-card">
        <div class="an-card-title">Category scores over time</div>
        <div id="leg-cat-time" class="an-legend"></div>
        <div style="position:relative;height:260px">
            <canvas id="c-cat-time" role="img" aria-label="Category scores over time"></canvas>
        </div>
    </div>

</div>

{{-- ══════════ SENTIMENT ══════════ --}}
<div id="tab-sentiment" class="an-panel">

    <div class="an-three-col" id="sent-metrics"></div>

    <div class="an-two-col">
        <div class="an-card">
            <div class="an-card-title">Sentiment trend — stacked</div>
            <div style="position:relative;height:240px">
                <canvas id="c-sent-trend" role="img" aria-label="Stacked sentiment trend"></canvas>
            </div>
        </div>
        <div class="an-card">
            <div class="an-card-title">Sentiment by course (latest semester)</div>
            <div style="position:relative;height:240px">
                <canvas id="c-sent-course" role="img" aria-label="Sentiment by course"></canvas>
            </div>
        </div>
    </div>

    <div class="an-card">
        <div class="an-card-title">Top keywords from open-ended responses</div>
        <div class="an-kw-cloud" id="kw-cloud"></div>
    </div>

</div>

{{-- ══════════ BENCHMARKING ══════════ --}}
<div id="tab-benchmark" class="an-panel">

    <div class="an-controls">
        <label class="an-controls-label">Compare against</label>
        <select id="bench-against" class="an-select">
            <option value="dept">Department average</option>
            <option value="history">Own historical average</option>
        </select>
    </div>

    <div class="an-card">
        <div class="an-card-title">Your rating vs benchmark</div>
        <div id="leg-bench" class="an-legend"></div>
        <div style="position:relative;height:260px">
            <canvas id="c-bench" role="img" aria-label="Benchmark comparison"></canvas>
        </div>
    </div>

    <div class="an-two-col">
        <div class="an-card">
            <div class="an-card-title">Category-level gap analysis</div>
            <div class="an-card-sub">Your avg vs department avg</div>
            <div id="bench-detail"></div>
        </div>
        <div class="an-card">
            <div class="an-card-title">Department ranking</div>
            <div class="an-card-sub">Standing among all faculty</div>
            <div id="rank-list"></div>
        </div>
    </div>

</div>

{{-- ══════════ PIVOT EXPLORER ══════════ --}}
<div id="tab-pivot" class="an-panel">

    <div class="an-pivot-bar">
        <div>
            <label class="an-pivot-label">X axis</label>
            <select id="piv-x" class="an-select">
                <option value="semester">Semester</option>
                <option value="course">Course</option>
                <option value="category">Category</option>
            </select>
        </div>
        <div>
            <label class="an-pivot-label">Metric (Y)</label>
            <select id="piv-y" class="an-select">
                <option value="avg_rating">Avg rating</option>
                <option value="response_count">Response count</option>
                <option value="positive_sentiment_percent">Positive %</option>
                <option value="negative_sentiment_percent">Negative %</option>
            </select>
        </div>
        <div>
            <label class="an-pivot-label">Group by</label>
            <select id="piv-group" class="an-select">
                <option value="none">None</option>
                <option value="course">Course</option>
                <option value="semester">Semester</option>
            </select>
        </div>
        <div>
            <label class="an-pivot-label">Chart type</label>
            <select id="piv-chart" class="an-select">
                <option value="bar">Bar</option>
                <option value="line">Line</option>
                <option value="radar">Radar</option>
            </select>
        </div>
    </div>

    <div class="an-card">
        <div class="an-card-title" id="piv-title">Pivot chart</div>
        <div id="leg-pivot" class="an-legend"></div>
        <div style="position:relative;height:320px">
            <canvas id="c-pivot" role="img" aria-label="Pivot explorer chart"></canvas>
        </div>
    </div>

    <div class="an-card">
        <div class="an-card-title">Data table</div>
        <div id="piv-table" style="overflow-x:auto;font-size:12px"></div>
    </div>

</div>