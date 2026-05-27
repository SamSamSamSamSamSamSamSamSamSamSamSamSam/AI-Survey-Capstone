<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = setting('ai.gemini_api_key', config('services.gemini.api_key', ''));
        $this->model   = setting('ai.gemini_model',   config('services.gemini.model', 'gemini-2.5-flash'));
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    }

    /**
     * Generate CQI report content from structured analytics data.
     *
     * Returns structured JSON with keys:
     *   analysis, identified_gaps, strengths, areas_for_improvement,
     *   root_cause_analysis, action_plan, monitoring, conclusion
     */
    public function generateCqiReport(array $analyticsData): array
    {
        $prompt = $this->buildCqiPrompt($analyticsData);

        try {
            $response = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.4,
                        'maxOutputTokens' => 4096,
                        'responseMimeType'=> 'application/json',
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                throw new \RuntimeException('Gemini API request failed: ' . $response->status());
            }

            $raw  = $response->json('candidates.0.content.parts.0.text', '{}');
            $data = json_decode($raw, true);

            if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
                throw new \RuntimeException('Gemini returned invalid JSON response.');
            }

            return $data;

        } catch (\Throwable $e) {
            Log::error('GeminiService::generateCqiReport failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    // -------------------------------------------------------------------------
    // Prompt builder
    // -------------------------------------------------------------------------

    private function buildCqiPrompt(array $d): string
    {
        // ── Existing: category scores block ───────────────────────────────────
        $categoryScores = '';
        foreach ($d['category_scores'] ?? [] as $cat => $score) {
            if (is_array($score)) {
                continue;
            }
            $interp = $this->interpretScore((float) $score, $d['scale_max'] ?? 5);
            $categoryScores .= "   - {$cat}: {$score}/{$d['scale_max']} ({$interp})\n";
        }

        // ── Existing: open-ended samples block ────────────────────────────────
        $openEndedSamples = '';
        foreach ($d['open_ended_samples'] ?? [] as $question => $responses) {
            $openEndedSamples .= "  Question: {$question}\n";
            foreach (array_slice($responses, 0, 8) as $r) {
                $openEndedSamples .= "    * {$r}\n";
            }
        }

        // ── NEW: course-context classification ────────────────────────────────
        $courseContext       = $this->resolveCourseContext($d);
        $instructionalNature = $this->resolveInstructionalNature($d);
        $courseDescription   = $d['course_description'] ?? '';
        // ─────────────────────────────────────────────────────────────────────

        // ── Computed analytical signals ───────────────────────────────────────────
        $categoryScoresRaw = [];
        foreach ($d['category_scores'] ?? [] as $cat => $score) {
            if (! str_starts_with($cat, '_') && ! is_array($score)) {
                $categoryScoresRaw[$cat] = (float) $score;
            }
        }

        $strongestCategory = ! empty($categoryScoresRaw)
            ? array_key_first(array_filter($categoryScoresRaw, fn($v) => $v === max($categoryScoresRaw)))
            : 'N/A';
        $weakestCategory   = ! empty($categoryScoresRaw)
            ? array_key_first(array_filter($categoryScoresRaw, fn($v) => $v === min($categoryScoresRaw)))
            : 'N/A';

        $pos = (float) ($d['positive_pct'] ?? 0);
        $neu = (float) ($d['neutral_pct']  ?? 0);
        $neg = (float) ($d['negative_pct'] ?? 0);
        $dominantSentiment = match(true) {
            $pos >= $neu && $pos >= $neg => "Positive ({$pos}%)",
            $neg >= $pos && $neg >= $neu => "Negative ({$neg}%)",
            default                       => "Neutral ({$neu}%)",
        };

        $avg      = (float) ($d['avg_rating']  ?? 0);
        $scaleMax = (int)   ($d['scale_max']   ?? 5);
        $pct      = $scaleMax > 0 ? $avg / $scaleMax : 0;
        $severityLevel = match(true) {
            $pct >= 0.90 => 'Excellent — no critical concerns',
            $pct >= 0.80 => 'Very Good — minor refinements recommended',
            $pct >= 0.70 => 'Good — moderate improvement warranted',
            $pct >= 0.60 => 'Fair — targeted interventions needed',
            default       => 'Needs Improvement — immediate structured support required',
        };

        $responseCount    = (int) ($d['response_count'] ?? 0);
        $responseReliability = match(true) {
            $responseCount >= 30 => "High reliability ({$responseCount} respondents) — findings are statistically representative.",
            $responseCount >= 15 => "Moderate reliability ({$responseCount} respondents) — trends are indicative but should be interpreted with caution.",
            $responseCount >= 5  => "Low reliability ({$responseCount} respondents) — findings are directional only; avoid strong generalizations.",
            default               => "Very low sample ({$responseCount} respondents) — interpret all findings with significant caution.",
        };

        $severityContext = match(true) {
            $pct >= 0.80 => 'Performance is above the institutional satisfactory threshold. Focus on sustaining and refining existing practices.',
            $pct >= 0.70 => 'Performance meets the acceptable threshold. Targeted improvements in weaker categories are recommended.',
            $pct >= 0.60 => 'Performance is approaching but below the satisfactory threshold. Structured improvement plans should be prioritized.',
            default       => 'Performance is significantly below expectations. Immediate faculty support and a structured intervention plan are warranted.',
        };

        $alignmentRisk = match(true) {
            $pct >= 0.80 && $pos >= 60 => 'Low — instructional delivery appears well-aligned with student expectations.',
            $pct >= 0.70 && $neg <= 30 => 'Moderate — some alignment gaps may exist in specific areas.',
            $neg >= 40                  => 'High — significant negative sentiment suggests possible misalignment between delivery and student needs.',
            default                     => 'Moderate — mixed signals; review weakest category for alignment concerns.',
        };

        $engagementIndicator = match(true) {
            $pos >= 70  => 'Strong — majority of students expressed positive experiences.',
            $pos >= 50  => 'Moderate — more than half responded positively, with room for improvement.',
            $neg >= 50  => 'Weak — majority of responses indicate disengagement or dissatisfaction.',
            default      => 'Mixed — student engagement varied; qualitative responses should be examined closely.',
        };

        // These fields are not yet computed server-side; use safe defaults
        $feedbackThemes         = $d['feedback_themes']          ?? 'No recurring themes extracted — refer to open-ended responses above.';
        $historicalTrends       = $d['historical_trends']        ?? 'No historical comparison data available for this offering.';
        $scoreDistributionCtx   = $d['score_distribution_context'] ?? 'Score distribution breakdown not available.';

        return <<<PROMPT
You are a senior Higher Education Academic Quality Assurance Auditor specializing in Outcomes-Based Education (OBE), Continuous Quality Improvement (CQI), institutional analytics, and evidence-based pedagogical evaluation.

Your responsibility is to transform student evaluation analytics into a contextualized, balanced, evidence-driven CQI audit report for academic improvement purposes.

## CONTEXT
- Institution: {$d['institution']}
- Faculty: {$d['faculty_name']}
- Course: {$d['course_code']} — {$d['course_name']}
- Program: {$d['program_name']}
- Semester: {$d['semester']}
- Academic Year: {$d['academic_year']}
- Group: {$d['group_number']}
- Total Respondents: {$d['response_count']}

## COURSE DESCRIPTION
{$courseDescription}

## INSTRUCTIONAL NATURE
{$instructionalNature}

## COURSE-SPECIFIC PEDAGOGICAL CONTEXT
{$courseContext}

## RESPONSE RELIABILITY
{$responseReliability}

## PERFORMANCE SEVERITY CONTEXT
{$severityContext}

## QUANTITATIVE RESULTS
- Overall Average Rating: {$d['avg_rating']}/{$d['scale_max']}
- Sentiment Distribution: {$d['positive_pct']}% positive, {$d['neutral_pct']}% neutral, {$d['negative_pct']}% negative

## CATEGORY SCORES
{$categoryScores}

## ANALYTICAL SIGNALS
- Strongest Category: {$strongestCategory}
- Weakest Category: {$weakestCategory}
- Dominant Sentiment: {$dominantSentiment}
- Evaluation Severity Level: {$severityLevel}
- Instructional Alignment Risk: {$alignmentRisk}
- Student Engagement Indicator: {$engagementIndicator}

## RECURRING FEEDBACK THEMES
{$feedbackThemes}

## HISTORICAL TREND CONTEXT
{$historicalTrends}

## SCORE DISTRIBUTION CONTEXT
{$scoreDistributionCtx}

## STUDENT OPEN-ENDED RESPONSES
{$openEndedSamples}

## MANDATORY ANALYSIS & CROSS-REFERENCING RULES

1. DATA SYNTHESIS REQUIREMENT:
- Cross-reference quantitative scores, sentiment distribution, recurring themes, and open-ended responses before drawing conclusions.
- Do not analyze qualitative and quantitative evidence independently.
- Conclusions must emerge from combined evidence patterns.

2. EVIDENCE-BASED INTERPRETATION:
- Never make conclusions unsupported by the dataset.
- Distinguish clearly between:
  a. Explicitly observed issues from student responses
  b. Reasonable pedagogical interpretations
  c. Possible contributing factors
- Avoid presenting assumptions or speculation as factual findings.

3. BALANCED CQI ANALYSIS:
- Maintain a professional institutional tone focused on improvement, not blame.
- Low ratings alone do not automatically indicate faculty ineffectiveness.
- Consider contextual contributors such as:
  - course difficulty,
  - technical complexity,
  - student preparedness,
  - adjustment challenges,
  - laboratory limitations,
  - curriculum pacing,
  - and learning transition factors where relevant.
- If strengths are supported by evidence, discuss them proportionally.

4. NON-REPETITIVE REPORTING:
- Avoid repeating identical issues across sections.
- Each section must contribute distinct analytical value:
  - identified_gaps = operational deficiencies
  - root_cause_analysis = contributing systemic factors
  - areas_for_improvement = refinement priorities
  - action_plan = implementation strategies

5. CONFIDENCE-AWARE LANGUAGE:
- If evidence is mixed, limited, or based on smaller response patterns, use cautious language such as:
  - "may indicate"
  - "suggests possible"
  - "appears associated with"
  - "could reflect"
- Avoid absolute claims unless strongly supported by repeated evidence patterns.

6. PEDAGOGICAL FRAMEWORK INTEGRATION:
- Frame the analysis.summary using OBE and discipline-appropriate pedagogical reasoning.
- Evaluate whether instructional delivery appears aligned with the intended applied learning outcomes of the course.

7. REALISTIC RECOMMENDATIONS:
- Recommendations must be feasible within a normal higher education institutional environment.
- Prefer scalable instructional improvements over unrealistic institutional transformations.
- Recommendations should preferably reference practical instructional methods appropriate to the course context.

8. GRANULAR TIMELINES:
- Do not use vague implementation timelines such as "Next Semester".
- Use actionable academic phases such as:
  - "Weeks 1–4: Instructional Planning"
  - "Midterm Implementation Phase"
  - "Ongoing Bi-Weekly Monitoring"
  - "Post-Semester Review"

9. REALISTIC INSTITUTIONAL RESPONSIBILITY:
- Do not assign all responsibility solely to the faculty member.
- Where appropriate, distribute accountability among:
  - Faculty Member
  - Department Chair
  - Program Coordinator
  - Laboratory Coordinator
  - Academic Affairs Unit
  - Curriculum Committee

## REPORT GENERATION INSTRUCTIONS

Generate a structured CQI audit report in VALID JSON ONLY.

STRICT REQUIREMENTS:
- Preserve the exact JSON structure and keys below.
- Do not add new keys.
- Do not remove keys.
- Do not rename keys.
- Do not include markdown formatting.
- Do not wrap the JSON in code blocks.
- Do not include explanations outside the JSON object.
- Ensure all JSON values are properly escaped and valid.

{
  "overall_interpretation": "A concise institutional performance interpretation synthesizing overall rating trends, sentiment distribution, course context, and instructional implications.",
  "analysis": {
    "summary": "An evidence-based OBE and pedagogical evaluation discussing likely instructional alignment strengths and concerns using combined quantitative and qualitative evidence.",
    "highlights": [
      "A major quantitative or sentiment finding tied directly to recurring student feedback patterns.",
      "An important contrast or relationship between category performance areas.",
      "A contextualized instructional insight grounded in the course nature and available evidence."
    ]
  },
  "identified_gaps": [
    {
      "area": "The identified operational or instructional area needing improvement.",
      "gap": "An evidence-supported description of the instructional or pedagogical gap.",
      "impact": "The likely effect of this gap on student competency, engagement, or course-level outcomes."
    }
  ],
  "strengths": [
    "A verified instructional, structural, or engagement-related strength supported by evidence."
  ],
  "areas_for_improvement": [
    "A specific instructional or assessment refinement priority linked to observed evidence patterns."
  ],
  "root_cause_analysis": [
    {
      "issue": "A major instructional or systemic concern identified from the dataset.",
      "possible_cause": "Possible contributing pedagogical, structural, environmental, or assessment-related factors."
    }
  ],
  "action_plan": [
    {
      "area": "The corresponding improvement category.",
      "action": "A realistic and course-appropriate instructional or institutional intervention.",
      "responsible_person": "The appropriate academic stakeholder(s) responsible for implementation or oversight.",
      "timeline": "A specific academic implementation phase or monitoring cycle.",
      "expected_outcome": "A measurable or observable indicator suggesting successful implementation."
    }
  ],
  "monitoring": [
    "A measurable monitoring or evaluation mechanism appropriate to the course context and identified concerns."
  ],
  "conclusion": "A formal CQI-oriented closing statement summarizing the overall instructional interpretation, contextual findings, and importance of continued monitoring and refinement."
}

Return ONLY the raw JSON object.
PROMPT;
    }

    // -------------------------------------------------------------------------
    // NEW: Course-context classification
    // -------------------------------------------------------------------------

    /**
     * Analyse course_name, course_code, and course_description to return
     * discipline-specific pedagogical guidance injected into the prompt.
     * No database access — pure keyword-based runtime classification.
     */
    private function resolveCourseContext(array $d): string
    {
        $haystack = $this->buildSearchHaystack($d);

        // ── Programming / Software / Technical ───────────────────────────────
        if ($this->matchesAny($haystack, [
            'programming', 'software', 'coding', 'java', 'python', 'php',
            'javascript', 'web development', 'web dev', 'database', 'dbms',
            'algorithm', 'data structure', 'network', 'operating system',
            'object oriented', 'oop', 'mobile', 'android', 'system analysis',
            'computer science', 'information technology', 'machine learning',
            'artificial intelligence', 'cybersecurity', 'it ', 'cis ',
        ])) {
            return <<<CONTEXT
This is a technical/programming course. All recommendations must be grounded in software development pedagogy.

Emphasize the following in your output:
- Coding exercises and hands-on laboratory activities as the primary mode of skill building
- Debugging practice and code review sessions to develop analytical problem-solving
- Project-based learning (individual or collaborative) to simulate real-world development
- Active laboratory engagement and participation in practical sessions
- Use of version control, IDEs, and real-world development tools during instruction
- Incremental complexity in assignments — scaffold from guided examples to open-ended projects
- Peer code review and collaborative problem-solving activities

Avoid recommendations centered on passive learning (e.g., reading-only, lecture-only). Every action plan item must reference a specific technical activity, lab exercise, or software development practice.
CONTEXT;
        }

        // ── Mathematics / Statistics / Quantitative ───────────────────────────
        if ($this->matchesAny($haystack, [
            'math', 'mathematics', 'algebra', 'calculus', 'trigonometry',
            'geometry', 'statistics', 'probability', 'numerical', 'discrete',
            'linear', 'differential', 'integral', 'quantitative', 'arithmetic',
            'number theory', 'actuarial',
        ])) {
            return <<<CONTEXT
This is a mathematics or quantitative reasoning course. All recommendations must reflect the precision-oriented, sequential nature of mathematical instruction.

Emphasize the following in your output:
- Step-by-step problem demonstrations before independent practice
- Guided examples progressing from basic to complex within each topic
- Frequent low-stakes formative assessments (seatwork, board work) to catch misconceptions early
- Visual representations (graphs, diagrams, number lines) alongside procedural practice
- Error analysis — students identify and correct common mistakes as a learning activity
- Structured practice with immediate feedback during class sessions
- Real-world application problems that contextualize abstract mathematical concepts

Avoid vague engagement suggestions. Action plans must reference specific mathematical techniques, problem-solving sequences, or formative assessment methods.
CONTEXT;
        }

        // ── Physical Education / Sports / Fitness ─────────────────────────────
        if ($this->matchesAny($haystack, [
            'physical education', 'pe ', 'p.e.', 'fitness', 'sports',
            'exercise', 'health', 'wellness', 'gym', 'athletics',
            'movement', 'motor', 'dance', 'swimming', 'badminton',
            'basketball', 'volleyball', 'aerobics',
        ])) {
            return <<<CONTEXT
This is a physical education or sports course. All recommendations must reflect the activity-based, kinesthetic, and participatory nature of PE instruction.

Emphasize the following in your output:
- Active physical participation as the core measure of engagement
- Clear demonstrations of movement techniques before student practice
- Structured warm-up, skill-building, and cool-down phases within each session
- Inclusive activity design accommodating varying fitness levels and abilities
- Motor skills progression — from foundational movements to sport-specific applications
- Safety protocols and proper form instruction before intensifying activities
- Team and individual activity balance for collaborative and independent skill development

Avoid lecture-heavy recommendations. Action plans must reference specific physical activities, movement progressions, or sports skill sequences.
CONTEXT;
        }

        // ── Religious / Values / Ethics Education ─────────────────────────────
        if ($this->matchesAny($haystack, [
            'religion', 'religious', 'christian', 'christianity', 'theology',
            'ethics', 'values', 'moral', 'spiritual', 'faith', 'bible',
            'sacred', 'catechism', 'philosophy', 'social justice', 'formation',
            'character', 'virtue', 'conscience',
        ])) {
            return <<<CONTEXT
This is a religious, values, or ethics education course. All recommendations must reflect the reflective, dialogical, and formative nature of this discipline.

Emphasize the following in your output:
- Reflective learning activities (journaling, personal reflection papers, contemplative exercises)
- Discussion-based engagement inviting students to examine their own values and beliefs
- Ethical reasoning through case studies and real-world moral dilemmas
- Value formation through narrative, lived examples, and community engagement exercises
- Safe, respectful dialogue welcoming diverse viewpoints while guiding critical reflection
- Integration of course themes with students' personal experiences and social realities
- Collaborative meaning-making through group discernment or community service components

Avoid performance-metric-heavy recommendations. Action plans must reference reflective, discussion-based, or formative methods appropriate for spiritual and ethical development.
CONTEXT;
        }

        // ── Natural Sciences / Laboratory ─────────────────────────────────────
        if ($this->matchesAny($haystack, [
            'science', 'biology', 'chemistry', 'physics', 'anatomy',
            'laboratory', 'lab', 'microbiology', 'ecology', 'biochemistry',
            'geology', 'botany', 'zoology', 'natural science', 'environmental',
            'forensic', 'pharmacology', 'physiology',
        ])) {
            return <<<CONTEXT
This is a natural science or laboratory course. All recommendations must reflect the empirical, inquiry-based, and experimental nature of scientific instruction.

Emphasize the following in your output:
- Laboratory safety protocols and proper experimental technique as foundational skills
- Inquiry-based learning progressing from structured labs to open-ended investigations
- Scientific method reinforcement: hypothesis, observation, data collection, analysis, conclusion
- Pre-lab preparation and post-lab reflection to deepen conceptual understanding
- Data interpretation exercises using real experimental results from class sessions
- Connection between theoretical concepts and observable phenomena
- Lab report writing as a scientific communication skill development activity

Avoid reducing the practical component. Action plans must reference specific lab activities, experimental procedures, or scientific reasoning skills.
CONTEXT;
        }

        // ── Humanities / Social Sciences / Communication ──────────────────────
        if ($this->matchesAny($haystack, [
            'history', 'humanities', 'literature', 'social science', 'sociology',
            'psychology', 'communication', 'journalism', 'political', 'economics',
            'anthropology', 'cultural', 'arts', 'writing', 'reading', 'language',
            'english', 'filipino', 'linguistics', 'media', 'rhetoric',
        ])) {
            return <<<CONTEXT
This is a humanities, social science, or communication course. All recommendations must reflect the interpretive, critical thinking, and expressive nature of this discipline.

Emphasize the following in your output:
- Critical reading and text analysis as core skill development activities
- Discussion-based and Socratic teaching methods for argumentation and perspective-taking
- Written and oral expression tasks (essays, presentations, debates, creative writing)
- Primary source engagement to develop analytical and evaluative skills
- Comparative analysis examining multiple viewpoints, texts, or cultural contexts
- Student-led discussions and seminars to build independent critical thinking
- Reflective assignments connecting course content to contemporary social issues

Avoid purely content-delivery recommendations. Action plans must reference specific reading, writing, discussion, or analytical activities.
CONTEXT;
        }

        // ── Business / Management / Accounting ───────────────────────────────
        if ($this->matchesAny($haystack, [
            'business', 'management', 'marketing', 'accounting', 'finance',
            'entrepreneurship', 'organization', 'human resource', 'operations',
            'supply chain', 'logistics', 'auditing', 'taxation', 'commerce',
            'administration', 'strategic', 'leadership',
        ])) {
            return <<<CONTEXT
This is a business or management course. All recommendations must reflect the applied, case-based, and professional nature of business education.

Emphasize the following in your output:
- Case study analysis using real or simulated business scenarios
- Role-playing and simulation activities (mock negotiations, business planning, financial modeling)
- Industry-linked examples connecting theoretical frameworks to current business practice
- Group projects developing collaborative decision-making and professional communication
- Business document writing (reports, plans, analyses) as applied skill outputs
- Formative feedback on professional business outputs to develop industry-standard work
- Guest speaker integration or industry exposure activities where feasible

Avoid generic motivational recommendations. Action plans must reference specific business skills, analytical frameworks (e.g., SWOT, financial ratios), or professional competencies.
CONTEXT;
        }

        // ── Engineering / Architecture / Design ───────────────────────────────
        if ($this->matchesAny($haystack, [
            'engineering', 'architecture', 'design', 'civil', 'mechanical',
            'electrical', 'electronics', 'structural', 'construction',
            'autocad', 'drafting', 'blueprint', 'cad', 'robotics',
            'instrumentation', 'thermodynamics', 'fluid', 'circuit',
        ])) {
            return <<<CONTEXT
This is an engineering, architecture, or technical design course. All recommendations must reflect the applied, problem-solving, and design-oriented nature of this discipline.

Emphasize the following in your output:
- Design-build-test cycles as the core learning loop (identify → design → prototype → evaluate)
- Technical drawing, CAD, or modeling practice as foundational skill activities
- Real-world engineering problems or design briefs to motivate and ground abstract theory
- Iterative design feedback — emphasizing revision as part of the professional process
- Laboratory or workshop sessions with hands-on material, tool, or software use
- Integration of engineering standards, codes, and professional ethics into instruction
- Peer review of technical outputs (drawings, calculations, prototypes) to develop evaluation skills

Avoid lecture-only recommendations. Action plans must reference specific design processes, technical tools, or engineering problem-solving methods.
CONTEXT;
        }

        // ── Default — General / Unclassified ─────────────────────────────────
        return <<<CONTEXT
This course could not be automatically classified into a specific discipline category.

Regardless, the following is MANDATORY:
- Recommendations must still be specific to {$d['course_name']} ({$d['course_code']}) and must not be generic advice applicable to any course.
- Examine the student open-ended responses and feedback keywords provided above and build every recommendation around the specific issues or strengths those responses reveal.
- Action plans must reference instructional strategies realistically applicable to this course's level, type, and student population.
- Consider whether the course appears to be theoretical, applied, seminar-based, or skills-focused based on its name and code, and adjust all recommendations accordingly.
CONTEXT;
    }

    /**
     * Returns a short instructional nature label for the INSTRUCTIONAL NATURE
     * section of the prompt. Uses the same keyword classification logic.
     */
    private function resolveInstructionalNature(array $d): string
    {
        $haystack = $this->buildSearchHaystack($d);

        if ($this->matchesAny($haystack, [
            'programming', 'software', 'coding', 'java', 'python', 'php',
            'javascript', 'web development', 'database', 'algorithm',
            'data structure', 'network', 'operating system', 'machine learning',
            'artificial intelligence', 'cybersecurity', 'it ', 'cis ',
        ])) return 'Technical / Programming / Laboratory-based';

        if ($this->matchesAny($haystack, [
            'math', 'mathematics', 'algebra', 'calculus', 'statistics',
            'trigonometry', 'geometry', 'numerical', 'discrete', 'quantitative',
        ])) return 'Quantitative / Analytical / Problem-solving';

        if ($this->matchesAny($haystack, [
            'physical education', 'pe ', 'fitness', 'sports', 'exercise',
            'health', 'wellness', 'motor', 'dance', 'athletics',
        ])) return 'Activity-based / Kinesthetic / Participatory';

        if ($this->matchesAny($haystack, [
            'religion', 'religious', 'christian', 'ethics', 'values',
            'moral', 'spiritual', 'theology', 'philosophy', 'formation',
        ])) return 'Reflective / Discussion-based / Formative';

        if ($this->matchesAny($haystack, [
            'science', 'biology', 'chemistry', 'physics', 'laboratory',
            'lab', 'ecology', 'anatomy', 'microbiology',
        ])) return 'Empirical / Laboratory / Inquiry-based';

        if ($this->matchesAny($haystack, [
            'history', 'humanities', 'literature', 'social science',
            'communication', 'psychology', 'writing', 'language', 'english',
        ])) return 'Interpretive / Critical-thinking / Expressive';

        if ($this->matchesAny($haystack, [
            'business', 'management', 'marketing', 'accounting', 'finance',
            'entrepreneurship', 'economics', 'commerce',
        ])) return 'Applied / Case-based / Professional';

        if ($this->matchesAny($haystack, [
            'engineering', 'architecture', 'design', 'civil', 'mechanical',
            'electrical', 'electronics', 'structural', 'cad', 'robotics',
        ])) return 'Design / Problem-solving / Workshop-based';

        return 'General Academic — Classification Undetermined';
    }

    /**
     * Concatenates and lowercases all available course metadata fields
     * into a single string for keyword matching.
     */
    private function buildSearchHaystack(array $d): string
    {
        return strtolower(implode(' ', array_filter([
            $d['course_code']        ?? '',
            $d['course_name']        ?? '',
            $d['course_description'] ?? '',
        ])));
    }

    /**
     * Returns true if the haystack contains ANY of the given keywords.
     */
    private function matchesAny(string $haystack, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, strtolower($keyword))) {
                return true;
            }
        }
        return false;
    }

    // -------------------------------------------------------------------------
    // Existing helper — unchanged
    // -------------------------------------------------------------------------

    private function interpretScore(mixed $score, int $max): string
    {
        if (is_array($score)) {
            return 'N/A';
        }
        $pct = $score / $max;
        return match(true) {
            $pct >= 0.90 => 'Excellent',
            $pct >= 0.80 => 'Very Good',
            $pct >= 0.70 => 'Good',
            $pct >= 0.60 => 'Fair',
            default      => 'Needs Improvement',
        };
    }
}
