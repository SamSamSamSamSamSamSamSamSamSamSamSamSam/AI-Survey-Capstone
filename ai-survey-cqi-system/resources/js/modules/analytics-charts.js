/**
 * analytics-charts.js
 * Shared Chart.js analytics engine for both admin and faculty views.
 * Config injected by Blade via window.ANALYTICS_CONFIG.
 *
 * Expected config shape:
 * {
 *   baseUrl:           '/api/analytics',
 *   passingThreshold:  3.0,
 *   hasFacultyFilter:  true|false,          // admin=true, faculty=false
 *   activeSemesterId:  '5'|null,
 *   coursesBySemester: { semId: [{id, code, name}], ... } | null,
 *   palette:           ['#...'],            // optional override
 * }
 */

(function () {
    'use strict';

    const cfg = window.ANALYTICS_CONFIG || {};

    // ── Palette ───────────────────────────────────────────────
    const PAL = cfg.palette || [
        '#0a3d62', '#1D9E75', '#D85A30',
        '#BA7517', '#D4537E', '#378ADD',
        '#639922', '#5F5E5A',
    ];
    const SENT_COLS = ['#1D9E75', '#888780', '#E24B4A'];

    // ── State ─────────────────────────────────────────────────
    const charts  = {};
    const cache   = {};
    let trendData = null;
    let catData   = null;
    let sentData  = null;
    let benchData = null;
    let pivotData = null;

    const BASE = cfg.baseUrl || '/api/analytics';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Utilities ─────────────────────────────────────────────
    function destroyChart(id) {
        if (charts[id]) { charts[id].destroy(); delete charts[id]; }
    }

    function qs(p) {
        return '?' + new URLSearchParams(
            Object.fromEntries(Object.entries(p).filter(([, v]) => v))
        ).toString();
    }

    function params(extra = {}) {
        const p = {
            semester_id: document.getElementById('sel-semester')?.value || '',
            offering_id: document.getElementById('sel-course')?.value   || '',
            ...extra,
        };
        if (cfg.hasFacultyFilter) {
            p.faculty_id = document.getElementById('sel-faculty')?.value || '';
        }
        return p;
    }

    function makeLegend(id, labels, colors) {
        const el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = labels.map((l, i) =>
            `<span><span class="an-ld" style="background:${colors[i % colors.length]}"></span>${l}</span>`
        ).join('');
    }

    function fmtVal(v, metric) {
        if (v === null || v === undefined) return '—';
        if (metric === 'response_count') return Math.round(v).toLocaleString();
        if (typeof v === 'number') return v.toFixed(2);
        return v;
    }

    function clearCache() { Object.keys(cache).forEach(k => delete cache[k]); }

    async function apiFetch(endpoint, p = {}) {
        const key = endpoint + JSON.stringify(p);
        if (cache[key]) return cache[key];
        const r    = await fetch(BASE + '/' + endpoint + qs(p), {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        });
        const data = await r.json();
        cache[key] = data;
        return data;
    }

    // ── Shared chart defaults ─────────────────────────────────
    function gridOpts() {
        return { color: 'rgba(128,128,128,0.08)' };
    }

    // ── Course dropdown cascade ────────────────────────────────
    // Populates #sel-course based on the selected semester.
    // coursesBySemester comes from ANALYTICS_CONFIG (injected by Blade).
    // When "All Semesters" is selected we build a deduplicated union of all courses.
    function populateCourseDropdown(keepValue) {
        const courseEl = document.getElementById('sel-course');
        if (!courseEl) return;

        const map = cfg.coursesBySemester || {};
        const semId = document.getElementById('sel-semester')?.value || '';

        let courses;
        if (semId && map[semId]) {
            // Specific semester selected: show only that semester's courses
            courses = map[semId];
        } else {
            // "All Semesters": flatten + deduplicate by offering id
            const seen = new Set();
            courses = Object.values(map).flat().filter(c => {
                if (seen.has(c.id)) return false;
                seen.add(c.id);
                return true;
            }).sort((a, b) => a.code.localeCompare(b.code));
        }

        // Preserve the current selection if it still applies
        const current = keepValue ?? courseEl.value;

        courseEl.innerHTML = '<option value="">All Courses</option>';
        courses.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name ? `${c.code} — ${c.name}` : c.code;
            if (String(c.id) === String(current)) opt.selected = true;
            courseEl.appendChild(opt);
        });

        // If previously-selected offering is no longer in the list, reset it
        if (current && !courses.some(c => String(c.id) === String(current))) {
            courseEl.value = '';
        }
    }

    // ── Tab switching ─────────────────────────────────────────
    function showTab(t) {
        document.querySelectorAll('.an-tab').forEach(b => b.classList.remove('an-tab--active'));
        document.querySelectorAll('.an-panel').forEach(p => p.classList.remove('an-panel--active'));

        const btn = document.querySelector(`.an-tab[data-tab="${t}"]`);
        if (btn) btn.classList.add('an-tab--active');
        const panel = document.getElementById('tab-' + t);
        if (panel) panel.classList.add('an-panel--active');

        if (t === 'overview')   loadOverview();
        if (t === 'trends')     loadTrends();
        if (t === 'categories') loadCategories();
        if (t === 'sentiment')  loadSentiment();
        if (t === 'benchmark')  loadBenchmark();
        if (t === 'pivot')      loadPivot();
    }

    function onFilterChange() {
        clearCache();
        trendData = catData = sentData = benchData = pivotData = null;
        const active = document.querySelector('.an-tab--active')?.dataset.tab || 'overview';
        showTab(active);
    }

    // Called when the semester dropdown changes.
    // Re-populates the course list first, then refreshes charts.
    function onSemesterChange() {
        populateCourseDropdown(null); // reset course selection on semester change
        onFilterChange();
    }

    // ══════════════════════════════════════════════════════════
    // OVERVIEW
    // ══════════════════════════════════════════════════════════
    async function loadOverview() {
        const d   = await apiFetch('overview', params());
        const tab = document.getElementById('tab-overview');

        if (d.empty) {
            tab.innerHTML = '<p class="an-empty">No analytics data found for the selected filters.</p>';
            return;
        }

        document.getElementById('ov-avg').textContent   = d.summary.avg_rating + '/5';
        document.getElementById('ov-resp').textContent  = d.summary.total_responses.toLocaleString();
        document.getElementById('ov-pos').textContent   = d.summary.avg_positive_pct + '%';
        document.getElementById('ov-count').textContent = d.summary.surveys_count;

        // Distribution bar
        destroyChart('dist');
        charts['dist'] = new Chart(document.getElementById('c-dist'), {
            type: 'bar',
            data: {
                labels: ['1', '2', '3', '4', '5'],
                datasets: [{
                    label: 'Count',
                    data: Object.values(d.distribution),
                    backgroundColor: ['#E24B4A', '#EF9F27', '#888780', '#1D9E75', PAL[0]],
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: gridOpts() }, x: { grid: { display: false } } },
            },
        });

        // Sentiment doughnut
        makeLegend('leg-sent', ['Positive', 'Neutral', 'Negative'], SENT_COLS);
        destroyChart('sent');
        charts['sent'] = new Chart(document.getElementById('c-sent'), {
            type: 'doughnut',
            data: {
                labels: ['Positive', 'Neutral', 'Negative'],
                datasets: [{ data: [d.sentiment.positive, d.sentiment.neutral, d.sentiment.negative], backgroundColor: SENT_COLS, borderWidth: 0 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => c.label + ': ' + c.parsed.toFixed(1) + '%' } } },
            },
        });

        // Category horizontal bar
        const rawCatKeys = Object.keys(d.category_scores);
        const catKeys = rawCatKeys.map(key =>
            key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
        );
        const catVals = Object.values(d.category_scores);
        const pass    = cfg.passingThreshold || 3.0;
        destroyChart('cats-ov');
        charts['cats-ov'] = new Chart(document.getElementById('c-cats-ov'), {
            type: 'bar',
            data: {
                labels: catKeys,
                datasets: [
                    { label: 'Score', data: catVals, backgroundColor: catVals.map(v => v >= 4 ? '#1D9E75' : v >= pass ? PAL[0] : '#E24B4A'), borderRadius: 3 },
                    { label: 'Passing (' + pass + ')', data: Array(catKeys.length).fill(pass), type: 'line', borderColor: '#EF9F27', borderDash: [5, 4], borderWidth: 1.5, pointRadius: 0, fill: false },
                ],
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { min: 0, max: 5, grid: gridOpts() }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } },
            },
        });
    }

    // ══════════════════════════════════════════════════════════
    // TRENDS
    // ══════════════════════════════════════════════════════════
    async function loadTrends() {
        const metric = document.getElementById('trend-metric')?.value || 'avg_rating';
        trendData = await apiFetch('trends', params({ metric }));
        drawTrendChart();
    }

    function drawTrendChart() {
        if (!trendData) return;
        const type   = document.getElementById('trend-type')?.value || 'line';
        const metric = document.getElementById('trend-metric')?.value || 'avg_rating';
        const labels = { avg_rating: 'Avg rating', positive_sentiment_percent: 'Positive %', negative_sentiment_percent: 'Negative %', response_count: 'Response count' };
        const titleEl = document.getElementById('trend-title');
        if (titleEl) titleEl.textContent = labels[metric] + ' over time';

        const semLabels = trendData.semester_series.map(r => r.semester_label);
        const semVals   = trendData.semester_series.map(r => r.value);

        destroyChart('trend');
        charts['trend'] = new Chart(document.getElementById('c-trend'), {
            type,
            data: { labels: semLabels, datasets: [{ label: labels[metric], data: semVals, borderColor: PAL[0], backgroundColor: PAL[0] + '18', borderWidth: 2, pointRadius: 4, fill: type === 'line', tension: 0.35, borderRadius: type === 'bar' ? 3 : 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false, grid: gridOpts() }, x: { grid: { display: false }, ticks: { maxRotation: 35, font: { size: 10 } } } } },
        });

        // Semester-over-semester
        const el = document.getElementById('sem-changes');
        if (el) {
            el.innerHTML = '';
            for (let i = 1; i < trendData.semester_series.length; i++) {
                const prev = trendData.semester_series[i - 1].value;
                const curr = trendData.semester_series[i].value;
                const diff = (curr - prev).toFixed(2);
                const pct  = prev ? (((curr - prev) / prev) * 100).toFixed(1) : 0;
                const badge = diff > 0.01
                    ? `<span class="an-badge an-badge--up">+${pct}%</span>`
                    : diff < -0.01
                        ? `<span class="an-badge an-badge--dn">${pct}%</span>`
                        : `<span class="an-badge an-badge--eq">±0%</span>`;
                el.innerHTML += `<div class="an-rank-row"><span class="an-rname">${trendData.semester_series[i].semester_label}</span>${badge}<span class="an-rval">${fmtVal(curr, metric)}</span></div>`;
            }
        }

        // Per-course comparison
        const courses  = Object.keys(trendData.course_series || {});
        const allSems  = [...new Set(courses.flatMap(c => Object.keys(trendData.course_series[c])))];
        const lastFour = allSems.slice(-4);
        makeLegend('leg-course', courses, PAL);
        destroyChart('course-trend');
        charts['course-trend'] = new Chart(document.getElementById('c-course-trend'), {
            type: 'line',
            data: { labels: lastFour, datasets: courses.map((c, i) => ({ label: c, data: lastFour.map(s => trendData.course_series[c][s] ?? null), borderColor: PAL[i % PAL.length], borderWidth: 1.5, pointRadius: 3, fill: false, tension: 0.35 })) },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: false, grid: gridOpts() }, x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 30 } } } },
        });
    }

    // ══════════════════════════════════════════════════════════
    // CATEGORIES
    // ══════════════════════════════════════════════════════════
    async function loadCategories() {
        catData = await apiFetch('categories', params());
        drawCategoryCharts();
    }

    const formatLabel = (key) =>
        key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

    function drawCategoryCharts() {
        if (!catData) return;
        const sort = document.getElementById('cat-sort')?.value || 'score';
        const pass = catData.passing_threshold || 3.0;

        let cats = Object.entries(catData.latest_scores || {}).map(([k, v]) => ({ name: formatLabel(k), val: v, dept: (catData.dept_avg || {})[k] || 0 }));
        if (sort === 'score') cats.sort((a, b) => b.val - a.val);
        else cats.sort((a, b) => a.name.localeCompare(b.name));

        destroyChart('cats');
        charts['cats'] = new Chart(document.getElementById('c-cats'), {
            type: 'bar',
            data: {
                labels: cats.map(c => c.name),
                datasets: [
                    { label: 'Your avg', data: cats.map(c => c.val), backgroundColor: cats.map(c => c.val >= 4 ? '#1D9E75' : c.val >= pass ? PAL[0] : '#E24B4A'), borderRadius: 3 },
                    { label: 'Dept avg', data: cats.map(c => c.dept), backgroundColor: 'rgba(136,135,128,0.25)', borderRadius: 3 },
                    { label: 'Passing', data: Array(cats.length).fill(pass), type: 'line', borderColor: '#EF9F27', borderDash: [5, 4], borderWidth: 1.5, pointRadius: 0, fill: false },
                ],
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { min: 0, max: 5, grid: gridOpts() }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } } },
        });

        const sorted   = [...cats].sort((a, b) => b.val - a.val);
        const makeRows = (arr, col) => arr.map((c, i) =>
            `<div class="an-rank-row"><span class="an-rn">${i + 1}</span><span class="an-rname">${c.name}</span><div class="an-rbar-wrap"><div class="an-rbar" style="width:${(c.val / 5 * 100).toFixed(0)}%;background:${col}"></div></div><span class="an-rval">${c.val}</span></div>`
        ).join('');
        const topEl = document.getElementById('cat-top');
        const lowEl = document.getElementById('cat-low');
        if (topEl) topEl.innerHTML = makeRows(sorted.slice(0, 3), '#1D9E75');
        if (lowEl) lowEl.innerHTML = makeRows([...sorted].reverse().slice(0, 3), '#E24B4A');

        const overTime  = catData.over_time || {};
        const semLabels = Object.keys(overTime);
        const catKeys   = cats.map(c => c.name);
        makeLegend('leg-cat-time', catKeys, PAL);
        destroyChart('cat-time');
        charts['cat-time'] = new Chart(document.getElementById('c-cat-time'), {
            type: 'line',
            data: { labels: semLabels, datasets: catKeys.map((cat, i) => ({ label: cat, data: semLabels.map(s => (overTime[s] || {})[cat] ?? null), borderColor: PAL[i % PAL.length], borderWidth: 1.5, pointRadius: 3, fill: false, tension: 0.35 })) },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { min: 1, max: 5, grid: gridOpts() }, x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 35 } } } },
        });
    }

    // ══════════════════════════════════════════════════════════
    // SENTIMENT
    // ══════════════════════════════════════════════════════════
    async function loadSentiment() {
        sentData = await apiFetch('sentiment', params());
        const trend  = sentData.trend || {};
        const byC    = sentData.by_course || {};
        const kws    = sentData.keywords || {};
        const sems   = Object.keys(trend);
        const latest = sems[sems.length - 1];

        const m  = document.getElementById('sent-metrics');
        const lp = trend[latest]?.positive ?? 0;
        const ln = trend[latest]?.negative ?? 0;
        const lne= trend[latest]?.neutral  ?? 0;
        const pp = sems.length > 1 ? trend[sems[sems.length - 2]]?.positive ?? 0 : null;
        const pn = sems.length > 1 ? trend[sems[sems.length - 2]]?.negative ?? 0 : null;
        if (m) m.innerHTML = [
            ['Positive (latest)', lp.toFixed(1) + '%', pp !== null ? 'prev: ' + pp.toFixed(1) + '%' : ''],
            ['Neutral (latest)',  lne.toFixed(1) + '%', ''],
            ['Negative (latest)', ln.toFixed(1) + '%', pn !== null ? 'prev: ' + pn.toFixed(1) + '%' : ''],
        ].map(([l, v, s]) => `<div class="an-metric"><div class="an-metric-label">${l}</div><div class="an-metric-val">${v}</div><div class="an-metric-sub">${s}</div></div>`).join('');

        destroyChart('sent-trend');
        charts['sent-trend'] = new Chart(document.getElementById('c-sent-trend'), {
            type: 'bar',
            data: { labels: sems, datasets: [{ label: 'Positive', data: sems.map(s => trend[s].positive), backgroundColor: '#1D9E75', stack: 's' }, { label: 'Neutral', data: sems.map(s => trend[s].neutral), backgroundColor: '#888780', stack: 's' }, { label: 'Negative', data: sems.map(s => trend[s].negative), backgroundColor: '#E24B4A', stack: 's' }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 35 } }, y: { stacked: true, max: 100, grid: gridOpts(), ticks: { callback: v => v + '%' } } } },
        });

        const courses = Object.keys(byC);
        destroyChart('sent-course');
        charts['sent-course'] = new Chart(document.getElementById('c-sent-course'), {
            type: 'bar',
            data: { labels: courses, datasets: [{ label: 'Positive', data: courses.map(c => byC[c].positive), backgroundColor: '#1D9E75', borderRadius: 2 }, { label: 'Negative', data: courses.map(c => byC[c].negative), backgroundColor: '#E24B4A', borderRadius: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' }, grid: gridOpts() } } },
        });

        const kwSizes = [18, 16, 15, 14, 13, 13, 12, 12, 12, 12, 11, 11, 11, 11, 11, 11, 11, 11, 11, 11];
        const kwEl = document.getElementById('kw-cloud');
        if (kwEl) kwEl.innerHTML = Object.entries(kws).map(([w], i) =>
            `<span class="an-kw" style="font-size:${kwSizes[i] || 11}px;font-weight:${i < 3 ? 600 : 400};background:${PAL[i % PAL.length]}22;color:${PAL[i % PAL.length]}">${w}</span>`
        ).join('');
    }

    // ══════════════════════════════════════════════════════════
    // BENCHMARK
    // ══════════════════════════════════════════════════════════
    async function loadBenchmark() {
        const against = document.getElementById('bench-against')?.value || 'dept';
        benchData = await apiFetch('benchmark', params({ against }));

        const mySems    = Object.keys(benchData.my_series || {});
        const myVals    = Object.values(benchData.my_series || {});
        const benchVals = mySems.map(s => (benchData.benchmark_series || {})[s] ?? null);

        makeLegend('leg-bench', ['Your avg', benchData.benchmark_label], [PAL[0], 'rgba(136,135,128,0.5)']);
        destroyChart('bench');
        charts['bench'] = new Chart(document.getElementById('c-bench'), {
            type: 'bar',
            data: { labels: mySems, datasets: [{ label: 'Your avg', data: myVals, backgroundColor: PAL[0], borderRadius: 3 }, { label: benchData.benchmark_label, data: benchVals, backgroundColor: 'rgba(136,135,128,0.35)', borderRadius: 3 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 35 } }, y: { min: 0, max: 5, grid: gridOpts() } } },
        });

        const my  = benchData.my_category_avg   || {};
        const dep = benchData.dept_category_avg  || {};
        const detailEl = document.getElementById('bench-detail');
        if (detailEl) detailEl.innerHTML = Object.keys(my).map(cat => {
            const displayLabel = cat.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const mv = my[cat] ?? 0, dv = dep[cat] ?? 0;
            const diff  = (mv - dv).toFixed(2);
            const badge = diff > 0.05 ? `<span class="an-badge an-badge--up">+${diff}</span>` : diff < -0.05 ? `<span class="an-badge an-badge--dn">${diff}</span>` : `<span class="an-badge an-badge--eq">≈ same</span>`;
            const pct   = (mv / 5 * 100).toFixed(0);
            return `<div class="an-bench-cat"><div class="an-bench-cat-label">${displayLabel}</div><div class="an-bench-cat-row"><div class="an-rbar-wrap" style="flex:1"><div class="an-rbar" style="width:${pct}%;background:${mv >= dv ? PAL[0] : '#E24B4A'}"></div></div><span class="an-rval">${mv}</span>${badge}</div></div>`;
        }).join('');

        const rankEl = document.getElementById('rank-list');
        if (rankEl) rankEl.innerHTML = (benchData.ranking || []).map((r, i) =>
            `<div class="an-rank-row ${r.is_me ? 'an-rank-row--me' : ''}"><span class="an-rn">#${i + 1}</span><span class="an-rname">${r.faculty_name}${r.is_me ? ' <span class="an-you">(you)</span>' : ''}</span><div class="an-rbar-wrap"><div class="an-rbar" style="width:${((r.avg_rating / 5) * 100).toFixed(0)}%;background:${r.is_me ? PAL[0] : '#d1d5db'}"></div></div><span class="an-rval">${r.avg_rating}</span></div>`
        ).join('');
    }

    // ══════════════════════════════════════════════════════════
    // PIVOT EXPLORER
    // ══════════════════════════════════════════════════════════
    async function loadPivot() {
        const x = document.getElementById('piv-x')?.value     || 'semester';
        const y = document.getElementById('piv-y')?.value     || 'avg_rating';
        const g = document.getElementById('piv-group')?.value || 'none';
        pivotData = await apiFetch('pivot', params({ x, y, group: g }));
        drawPivotChart();
    }

    function drawPivotChart() {
        if (!pivotData) return;
        const rows = (pivotData.rows || []).map(r => ({
            ...r,
            cleanLabel: formatLabel(r.label),
        }));
        const ctype   = document.getElementById('piv-chart')?.value || 'bar';
        const yLabels = { avg_rating: 'Avg rating', response_count: 'Responses', positive_sentiment_percent: 'Positive %', negative_sentiment_percent: 'Negative %' };

        const titleEl = document.getElementById('piv-title');
        if (titleEl) titleEl.textContent = yLabels[pivotData.metric] + ' by ' + pivotData.x_axis + (pivotData.group_by !== 'none' ? ' — grouped by ' + pivotData.group_by : '');

        let datasets, labels;
        if (!rows[0]?.group) {
            labels   = rows.map(r => r.cleanLabel);
            datasets = [{ label: yLabels[pivotData.metric], data: rows.map(r => r.value), backgroundColor: rows.map((_, i) => PAL[i % PAL.length]), borderColor: PAL[0], borderWidth: ctype === 'line' ? 2 : 0, borderRadius: 3, fill: false, tension: 0.35, pointRadius: 4 }];
            makeLegend('leg-pivot', [], []);
        } else {
            const groups = [...new Set(rows.map(r => r.group))];
            labels   = [...new Set(rows.map(r => r.cleanLabel))];
            datasets = groups.map((grp, i) => ({
                label: grp,
                data: labels.map(l => { const r = rows.find(r => r.cleanLabel === l && r.group === grp); return r ? r.value : null; }),
                backgroundColor: PAL[i % PAL.length] + '44', borderColor: PAL[i % PAL.length],
                borderWidth: 2, borderRadius: 3, fill: false, tension: 0.35, pointRadius: 3,
            }));
            makeLegend('leg-pivot', groups, groups.map((_, i) => PAL[i % PAL.length]));
        }

        const shortLabels = labels.map(l => l.length > 16 ? l.slice(0, 15) + '…' : l);
        destroyChart('pivot');
        charts['pivot'] = new Chart(document.getElementById('c-pivot'), {
            type: ctype === 'radar' ? 'radar' : ctype,
            data: { labels: ctype === 'radar' ? labels : shortLabels, datasets },
            options: {
                responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: ctype === 'radar'
                    ? { r: { min: 0, max: 5 } }
                    : { x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 35, autoSkip: false } }, y: { beginAtZero: false, grid: gridOpts() } },
            },
        });

        // Data table
        const groups = rows[0]?.group ? [...new Set(rows.map(r => r.group))] : null;
        const tH = 'style="text-align:left;padding:5px 8px;font-size:11px;color:#6b7280;border-bottom:1px solid #e5e7eb"';
        const tHR= 'style="text-align:right;padding:5px 8px;font-size:11px;color:#6b7280;border-bottom:1px solid #e5e7eb"';
        let tbl = `<table style="width:100%;border-collapse:collapse"><thead><tr><th ${tH}>${pivotData.x_axis}</th>`;
        if (!groups) tbl += `<th ${tHR}>${yLabels[pivotData.metric]}</th>`;
        else groups.forEach(g => { tbl += `<th ${tHR}>${g}</th>`; });
        tbl += '</tr></thead><tbody>';
        labels.forEach(l => {
            tbl += `<tr><td style="padding:4px 8px;font-size:12px;border-bottom:1px solid #f3f4f6">${l}</td>`;
            if (!groups) { const r = rows.find(r => r.cleanLabel === l); tbl += `<td style="text-align:right;padding:4px 8px;font-size:12px;font-weight:500;border-bottom:1px solid #f3f4f6">${r?.value ?? '—'}</td>`; }
            else groups.forEach(g => { const r = rows.find(r => r.cleanLabel === l && r.group === g); tbl += `<td style="text-align:right;padding:4px 8px;font-size:12px;border-bottom:1px solid #f3f4f6">${r?.value ?? '—'}</td>`; });
            tbl += '</tr>';
        });
        const tableEl = document.getElementById('piv-table');
        if (tableEl) tableEl.innerHTML = tbl + '</tbody></table>';
    }

    // ── Wire up event listeners once DOM is ready ─────────────
    function init() {
        // Populate the course dropdown on page load (respects initial semester selection)
        populateCourseDropdown(null);

        // Tab buttons
        document.querySelectorAll('.an-tab').forEach(btn => {
            btn.addEventListener('click', function () { showTab(this.dataset.tab); });
        });

        // Semester change: cascade to course dropdown, then refresh
        document.getElementById('sel-semester')?.addEventListener('change', onSemesterChange);

        // Course change: just refresh (no cascade needed)
        document.getElementById('sel-course')?.addEventListener('change', onFilterChange);

        // Admin filter button
        document.getElementById('btn-filter')?.addEventListener('click', onFilterChange);

        // Admin faculty dropdown
        if (cfg.hasFacultyFilter) {
            document.getElementById('sel-faculty')?.addEventListener('change', onFilterChange);
        }

        // Trend controls
        document.getElementById('trend-metric')?.addEventListener('change', loadTrends);
        document.getElementById('trend-type')?.addEventListener('change', drawTrendChart);

        // Category sort
        document.getElementById('cat-sort')?.addEventListener('change', drawCategoryCharts);

        // Benchmark toggle
        document.getElementById('bench-against')?.addEventListener('change', loadBenchmark);

        // Pivot controls
        ['piv-x', 'piv-y', 'piv-group'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', loadPivot);
        });
        document.getElementById('piv-chart')?.addEventListener('change', drawPivotChart);

        // Load initial tab
        loadOverview();
    }

    window.refreshAnalyticsCharts = onFilterChange;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();