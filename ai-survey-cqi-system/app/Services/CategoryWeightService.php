<?php

namespace App\Services;

use Illuminate\Support\Collection;

/**
 * CategoryWeightService
 *
 * Handles all weight distribution logic for survey templates and surveys.
 * Weights only apply to rating-type questions, grouped by category.
 * Text questions are always ignored for weighting purposes.
 */
class CategoryWeightService
{
    /**
     * Auto-distribute 100% equally across distinct rating categories.
     *
     * Given a collection of questions (each having category_id and question_type),
     * return a map of [category_id => weight] that sums to exactly 100.00.
     *
     * The last category absorbs any rounding remainder to guarantee 100.00.
     *
     * @param  Collection $questions  Collection of objects/arrays with category_id, question_type
     * @return array<int, float>      [category_id => weight]
     */
    public function distributeEqually(Collection $questions): array
    {
        $categoryIds = $questions
            ->filter(fn ($q) => $this->isRating($q) && $this->categoryId($q))
            ->pluck(fn ($q) => $this->categoryId($q))
            ->unique()
            ->values();

        if ($categoryIds->isEmpty()) {
            return [];
        }

        $count      = $categoryIds->count();
        $base       = round(100 / $count, 2);
        $distributed = [];

        foreach ($categoryIds as $i => $catId) {
            $distributed[$catId] = $base;
        }

        // Adjust last to guarantee exactly 100.00
        $sum  = array_sum($distributed);
        $diff = round(100 - $sum, 2);
        if ($diff !== 0.0) {
            $lastId = $categoryIds->last();
            $distributed[$lastId] = round($distributed[$lastId] + $diff, 2);
        }

        return $distributed;
    }

    /**
     * Validate that the provided weights total exactly 100.
     * Only considers categories with rating questions.
     *
     * @param  array $weights  [category_id => weight]
     * @return array{valid: bool, total: float, message: string}
     */
    public function validate(array $weights): array
    {
        if (empty($weights)) {
            return ['valid' => true, 'total' => 0.0, 'message' => ''];
        }

        $total = round(array_sum($weights), 2);

        if ($total !== 100.00) {
            return [
                'valid'   => false,
                'total'   => $total,
                'message' => "Category weights must total exactly 100%. Current total: {$total}%.",
            ];
        }

        return ['valid' => true, 'total' => 100.00, 'message' => ''];
    }

    /**
     * Build a [category_id => weight] map from a collection of questions
     * that already have category_weight set. Falls back to equal distribution
     * if weights are null.
     *
     * @param  Collection $questions
     * @return array<int, float>
     */
    public function resolveWeights(Collection $questions): array
    {
        $ratingQuestions = $questions->filter(
            fn ($q) => $this->isRating($q) && $this->categoryId($q)
        );

        if ($ratingQuestions->isEmpty()) {
            return [];
        }

        // Group by category and take the first non-null weight per category
        $weights = [];
        foreach ($ratingQuestions as $q) {
            $catId  = $this->categoryId($q);
            $weight = $this->weight($q);

            if (! isset($weights[$catId]) || $weights[$catId] === null) {
                $weights[$catId] = $weight;
            }
        }

        // If any weight is null, redistribute equally
        if (in_array(null, $weights, true)) {
            return $this->distributeEqually($ratingQuestions);
        }

        return $weights;
    }

    /**
     * Compute weighted scores from category means and weights.
     *
     * Formula per category:
     *   weighted_contribution = (category_mean / scale_max) * weight * 100
     *   normalised_achievement = (category_mean / scale_max) * 100
     *
     * @param  array $categoryMeans  [category_name => mean_score]
     * @param  array $categoryWeights [category_name => weight]
     * @param  float $scaleMax
     * @return array{
     *   weights: array,
     *   weighted_scores: array,
     *   achievements: array,
     *   overall_weighted_score: float
     * }
     */
    public function computeWeightedScores(
        array $categoryMeans,
        array $categoryWeights,
        float $scaleMax = 5.0
    ): array {
        $weightedScores = [];
        $achievements   = [];
        $overallScore   = 0.0;

        foreach ($categoryMeans as $category => $mean) {
            $weight = $categoryWeights[$category] ?? null;

            if ($weight === null) {
                continue;
            }

            $achievement              = round(($mean / $scaleMax) * 100, 2);
            $contribution             = round(($mean / $scaleMax) * $weight, 2);

            $weightedScores[$category] = $contribution;
            $achievements[$category]   = $achievement;
            $overallScore             += $contribution;
        }

        return [
            'weights'               => $categoryWeights,
            'weighted_scores'       => $weightedScores,
            'achievements'          => $achievements,
            'overall_weighted_score'=> round($overallScore, 2),
        ];
    }

    // -------------------------------------------------------------------------
    // Interpretation — replaces every hard-coded match(true) in the codebase
    // -------------------------------------------------------------------------

    /**
     * Interpret an achievement percentage using admin-configurable thresholds.
     *
     * Thresholds are stored in the `settings` table under the `survey` group:
     *   survey.excellent_threshold  (default 90)
     *   survey.very_good_threshold  (default 80)
     *   survey.good_threshold       (default 70)
     *   survey.fair_threshold       (default 60)
     *
     * Returns an array with:
     *   label   — human-readable string  e.g. "Very Good"
     *   color   — foreground hex         e.g. "#1d4ed8"
     *   bg      — background hex         e.g. "#dbeafe"
     *   cls     — CSS modifier string    "high" | "mid" | "low"
     *
     * @param  float $pct  Achievement as a percentage (0–100)
     * @return array{label: string, color: string, bg: string, cls: string}
     */
    public function interpret(float $pct): array
    {
        $excellent = (float) setting('survey.excellent_threshold', 90);
        $veryGood  = (float) setting('survey.very_good_threshold', 80);
        $good      = (float) setting('survey.good_threshold',      70);
        $fair      = (float) setting('survey.fair_threshold',      60);

        return match (true) {
            $pct >= $excellent => ['label' => 'Excellent',          'color' => '#065f46', 'bg' => '#d1fae5', 'cls' => 'high'],
            $pct >= $veryGood  => ['label' => 'Very Good',          'color' => '#1d4ed8', 'bg' => '#dbeafe', 'cls' => 'high'],
            $pct >= $good      => ['label' => 'Good',               'color' => '#1e3a5f', 'bg' => '#e0f2fe', 'cls' => 'mid'],
            $pct >= $fair      => ['label' => 'Fair',               'color' => '#92400e', 'bg' => '#fef3c7', 'cls' => 'mid'],
            default            => ['label' => 'Needs Improvement',  'color' => '#b91c1c', 'bg' => '#fee2e2', 'cls' => 'low'],
        };
    }

    // -------------------------------------------------------------------------
    // Private helpers — handle both Eloquent models and stdClass/arrays
    // -------------------------------------------------------------------------

    private function isRating(mixed $q): bool
    {
        if (is_array($q)) return ($q['question_type'] ?? '') === 'rating';
        return ($q->question_type ?? '') === 'rating';
    }

    private function categoryId(mixed $q): ?int
    {
        if (is_array($q)) return $q['category_id'] ?? null;
        return $q->category_id ?? null;
    }

    private function weight(mixed $q): ?float
    {
        if (is_array($q)) return isset($q['category_weight']) ? (float)$q['category_weight'] : null;
        return isset($q->category_weight) ? (float)$q->category_weight : null;
    }
}