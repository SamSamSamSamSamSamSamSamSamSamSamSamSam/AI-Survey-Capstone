<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Faculty Improvement Dashboard</h2>
        
        <div class="d-flex align-items-center">
            <small class="text-muted me-3">Last updated: <?php echo e(now()->toDateTimeString()); ?></small>
            <div class="admin-controls">
                <a href="<?php echo e(route('admin.surveys.create')); ?>" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-plus-circle me-1"></i> Create Survey
                </a>
                <a href="<?php echo e(route('admin.surveys.index')); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-eye me-1"></i> View Surveys
                </a>
                <a href="<?php echo e(route('admin.reports.filter')); ?>" 
                    class="btn btn-sm btn-success ms-2">
                    <i class="bi bi-file-earmark-pdf"></i> Generate CQI Report
                </a>
            </div>
        </div>
    </div>
    
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <label for="survey-filter" class="form-label me-2 mb-0">
                <strong>Current View:</strong>
            </label>
            <select id="survey-filter"
                    class="form-select form-select-sm w-auto d-inline-block"
                    onchange="window.location.href = this.value;">
                <option value="<?php echo e(route('admin.dashboard')); ?>">
                    Overall (All Surveys)
                </option>
                <?php $__currentLoopData = $allSurveys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $survey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e(route('admin.dashboard', ['survey_id' => $survey->id])); ?>"
                        <?php echo e(request('survey_id') == $survey->id ? 'selected' : ''); ?>>
                        <?php echo e($survey->title); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        
        <div class="ms-3">
            <label class="form-label me-2 mb-0"><strong>Course:</strong></label>
            <select id="course-filter" class="form-select form-select-sm w-auto d-inline-block"
                    onchange="window.location.href = this.value;">
                <option value="<?php echo e(route('admin.dashboard', ['survey_id' => request('survey_id')])); ?>">
                    All Courses
                </option>

                <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e(route('admin.dashboard', [
                        'survey_id' => request('survey_id'),
                        'course' => $course
                    ])); ?>"
                        <?php echo e(request('course') == $course ? 'selected' : ''); ?>>
                        <?php echo e($course); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>

        <div>
            <a href="<?php echo e(route('admin.analysis.surveys')); ?>" class="btn btn-sm btn-outline-primary me-2">
                <i class="bi bi-bar-chart"></i> Question Analysis
            </a>
            <a href="<?php echo e(route('admin.analysis.wordCloud')); ?>" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-cloud"></i> Word Cloud
            </a>
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3 border-start border-primary border-4">
                <h6>Total Responses</h6>
                <h3><?php echo e($distinct_evaluators ?? 0); ?></h3>
                <small class="text-muted">
                    Participation: <strong><?php echo e($participation_pct ?? 'N/A'); ?>%</strong> 
                    <?php if($eligible_evaluators): ?> 
                        (of <?php echo e($eligible_evaluators); ?> eligible)
                    <?php endif; ?>
                </small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-start border-success border-4">
                <h6>Overall Mean Rating</h6>
                <h3 class="<?php echo e($mean >= 4.0 ? 'text-success' : ($mean < 3.0 ? 'text-danger' : 'text-warning')); ?>">
                    <?php echo e($mean !== null ? number_format($mean, 2) : 'N/A'); ?>

                </h3>
                <small class="text-muted">Target: 4.0. Higher is better.</small>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card p-3 border-start border-info border-4">
                <h6>Overall Positive Sentiment</h6>
                <h3><?php echo e($overallPositivePct ?? 'N/A'); ?>%</h3>
                <small class="text-muted">Percentage of positive qualitative comments.</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 border-start border-secondary border-4">
                <h6>Standard Deviation</h6>
                <h3 class="<?php echo e($stddev < 0.8 ? 'text-success' : 'text-warning'); ?>">
                    <?php echo e($stddev !== null ? number_format($stddev, 2) : 'N/A'); ?>

                </h3>
                <small class="text-muted">Lower = more consistent ratings.</small>
            </div>
        </div>
    </div>

    
    <div class="card p-3 mb-4">
        <h5>Category Performance Summary</h5>
        <table class="table table-sm mt-2">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Average Score</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $categoryScores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($cat['category']); ?></td>
                    <td class="<?php echo e($cat['avg'] >= 4.0 ? 'text-success fw-bold' : 'text-warning'); ?>">
                        <?php echo e(number_format($cat['avg'], 2)); ?>

                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="2">No category rating data available.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card p-3 mb-3">
                <h5>Monthly Performance Trend (Rating & Sentiment)</h5>
                <canvas id="monthlyCombinedChart" height="120"></canvas>
                <p class="mt-2 small text-muted">
                    Interpretation: Consistent upward trends indicate continuous improvement.
                </p>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card p-3 mb-3">
                <h5>Top Performing Faculty <i class="bi bi-star-fill text-warning"></i></h5>
                <table class="table table-sm mt-2">
                    <thead><tr><th>Name</th><th>Avg</th><th>Positive %</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $topPerformers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <a href="<?php echo e(route('admin.evaluatee.evaluateeDetails', ['id' => $p['evaluatee_id']])); ?>" 
                                   class="text-decoration-none">
                                    <?php echo e($p['name']); ?>

                                </a>
                            </td>
                            <td class="<?php echo e($p['avg_rating'] >= 4.5 ? 'fw-bold text-success' : ''); ?>">
                                <?php echo e(number_format($p['avg_rating'], 2)); ?>

                            </td>
                            <td class="<?php echo e($p['positive_pct'] >= 80 ? 'text-primary' : ''); ?>">
                                <?php echo e($p['positive_pct']); ?>%
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="3">Not enough data (≥3 rating responses required).</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <p class="small text-muted">Top 10 based on rating count & sentiment.</p>
            </div>
        </div>
    </div>
    
    
    <div class="card p-3">
        <h5>Faculty Sentiment Breakdown</h5>
        <table class="table table-sm mt-2">
            <thead>
                <tr>
                    <th>Faculty</th>
                    <th>Total</th>
                    <th class="text-success">Positive %</th>
                    <th class="text-danger">Negative %</th>
                    <th class="text-warning">Neutral %</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $sentimentPerPerson; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <a href="<?php echo e(route('admin.evaluatee.evaluateeDetails', ['id' => $s['evaluatee_id']])); ?>">
                            <?php echo e($s['name']); ?>

                        </a>
                    </td>
                    <td><?php echo e($s['total']); ?></td>
                    <td class="text-success"><?php echo e($s['positive_pct']); ?>%</td>
                    <td class="text-danger"><?php echo e($s['negative_pct']); ?>%</td>
                    <td class="text-warning"><?php echo e($s['neutral_pct']); ?>%</td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="5">No qualitative data available.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p class="small text-muted">Top 10 faculty with the most qualitative responses.</p>
    </div>

</div>

<style>
    #monthlyCombinedChart {
        width: 100% !important;
        height: 350px !important;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    const dashboardData = {
        monthlyLabels: <?php echo json_encode($monthlyLabels ?? [], 15, 512) ?>,
        monthlyAvg: <?php echo json_encode($monthlyAvg ?? [], 15, 512) ?>,
        monthlyPosPct: <?php echo json_encode($monthlyPositivePct ?? [], 15, 512) ?>,
    };
</script>
<script src="<?php echo e(asset('js/admin/dashboard.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.default', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\arjoy\Desktop\DESKTOP\Capstone\AI-Survey-Capstone\laravel-front\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>