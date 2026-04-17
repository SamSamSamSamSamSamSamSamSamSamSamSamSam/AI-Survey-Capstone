<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Seeds programs, curricula, subjects, and prospectuses from the official
 * DCISM curriculum sheets.
 *
 * Run:  php artisan db:seed --class=CurriculumSeeder
 *
 * IMPORTANT: This seeder is idempotent-safe only if the tables are empty.
 * Wrap in a transaction to roll back on failure.
 * 
 * Included SubjectSeeder and ProspectusSeeder
 * 
 * 
 */
class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = Carbon::now();

            // ─────────────────────────────────────────
            // 1. PROGRAMS
            // ─────────────────────────────────────────
            $programs = [
                ['program_code' => 'BSIT', 'name' => 'Bachelor of Science in Information Technology'],
                ['program_code' => 'BSCS', 'name' => 'Bachelor of Science in Computer Science'],
                ['program_code' => 'BSIS', 'name' => 'Bachelor of Science in Information Systems'],
            ];

            $programIds = [];
            foreach ($programs as $p) {
                $existing = DB::table('programs')->where('program_code', $p['program_code'])->first();
                if ($existing) {
                    $programIds[$p['program_code']] = $existing->id;
                } else {
                    $programIds[$p['program_code']] = DB::table('programs')->insertGetId([
                        'program_code' => $p['program_code'],
                        'name'         => $p['name'],
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                }
            }

            // ─────────────────────────────────────────
            // 2. CURRICULA
            // ─────────────────────────────────────────
            $curricula = [
                ['code' => 'BSIT-2018', 'program' => 'BSIT', 'description' => 'Curriculum for BSIT effective 2018', 'year' => 2018],
                ['code' => 'BSIT-2024', 'program' => 'BSIT', 'description' => 'Curriculum for BSIT effective 2024', 'year' => 2024],
                ['code' => 'BSCS-2018', 'program' => 'BSCS', 'description' => 'Curriculum for BSCS effective 2018', 'year' => 2018],
                ['code' => 'BSCS-2024', 'program' => 'BSCS', 'description' => 'Curriculum for BSCS effective 2024', 'year' => 2024],
                ['code' => 'BSIS-2018', 'program' => 'BSIS', 'description' => 'Curriculum for BSIS effective 2018', 'year' => 2018],
                ['code' => 'BSIS-2024', 'program' => 'BSIS', 'description' => 'Curriculum for BSIS effective 2024', 'year' => 2024],
            ];

            $curriculumIds = [];
            foreach ($curricula as $c) {
                $existing = DB::table('curricula')->where('curriculum_code', $c['code'])->first();
                if ($existing) {
                    $curriculumIds[$c['code']] = $existing->id;
                } else {
                    $curriculumIds[$c['code']] = DB::table('curricula')->insertGetId([
                        'program_id'      => $programIds[$c['program']],
                        'curriculum_code' => $c['code'],
                        'description'     => $c['description'],
                        'effective_year'  => $c['year'],
                        'is_active'       => true,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]);
                }
            }

            // ─────────────────────────────────────────
            // 3. SUBJECTS  (deduplicated master list)
            // ─────────────────────────────────────────
            $subjectData = [
                ['CIS 1101', 'Programming I', 3],
                ['CIS 1102', 'Introduction to Computing', 3],
                ['CIS 1103', 'Discrete Structures I', 3],
                ['CIS 1104', 'Human-Computer Interaction', 3],
                ['GE-PC', 'Purposive Communication', 3],
                ['GE-UTS', 'Understanding the Self', 3],
                ['GE-MMW', 'Mathematics in the Modern World', 3],
                ['NSTP 1', 'National Service Training Program I', -3],
                ['TPE 1101', 'Path – Fit I – Movement Enhance', 2],
                ['CIS 1201', 'Programming II', 3],
                ['CIS 1202', 'Web Development I', 3],
                ['CIS 1203', 'Discrete Structures II', 3],
                ['CIS 1204', 'Information Management I', 3],
                ['CIS 1205', 'Networking I', 3],
                ['GE-Ethics', 'Ethics', 3],
                ['EDM1', 'Education with a Mission I', -3],
                ['NSTP2', 'National Service Training Program II', -3],
                ['TPE 1202', 'Path - Fit II – Fitness Exercis', 2],
                ['CIS 2101', 'Data Structures and Algorithms', 3],
                ['CIS 2102', 'Web Development II', 3],
                ['CIS 2103', 'Object-Oriented Programming', 3],
                ['CIS 2104', 'Information Management II', 3],
                ['CIS 2105', 'Networking II', 3],
                ['CIS 2106', 'Computer Hardware Servicing NC II', 1],
                ['EDM2', 'Education with a Mission II', -3],
                ['GE Fre Elec 1', 'GE Free Elective I', 3],
                ['CIS 2201', 'Systems Analysis & Design', 3],
                ['CIS 2202', 'Digital Logic Design and Digital Computer Circuits', 3],
                ['CIS 2203', 'Mobile Development', 3],
                ['CIS 2204', 'Technopreneurship', 3],
                ['CIS 2205', 'Design Project', 3],
                ['CIS 2206', 'Programming NC IV', 1],
                ['GE-LWR', 'Rizal Works and Writings', 3],
                ['GE Fre Elec 2', 'GE Free Elective II', 3],
                ['GE Fre Elec 3', 'GE Free Elective III', 3],
                ['IT 3101', 'Platform Technologies', 3],
                ['IT 3102', 'Probability and Statistics', 3],
                ['IT 3103', 'Systems Integration & Architecture', 3],
                ['IT 3104', 'Information Assurance & Security', 3],
                ['IT 3105', 'Application Development & Emerging Technologies', 3],
                ['IT 3106', 'Accounting for IT', 3],
                ['IT 3107', 'Fundamentals of Data Ware Housing and Data Mining', 3],
                ['GE-TCW', 'The Contemporary World', 3],
                ['TPE 2103', 'Path – Fit III – Movement Educati', 2],
                ['IT 3201', 'Capstone Project I', 3],
                ['IT 3202', 'Software Quality Assurance', 3],
                ['IT 3203', 'Quantitative Methods', 3],
                ['IT 3204', 'Research Methods in Computing', 3],
                ['IT 3205', 'Social and Professional Issues', 3],
                ['IT 3206', 'Integrative Programming & Tech', 3],
                ['GE-ART', 'Art Appreciation', 3],
                ['TPE 2204', 'Path – Fit IV – Movement Educatio', 2],
                ['IT 4101', 'Capstone Project II', 3],
                ['IT 4102', 'Practicum 1', 3],
                ['IT 4103', 'Seminars and Tours', 3],
                ['IT 4104', 'Linux Operating System', 3],
                ['IT FREE EL 3', 'IT Free Elective 3', 3],
                ['GE-RPH', 'Readings in the Philippine History', 3],
                ['IT 4201', 'Systems Administration & Maintenance', 3],
                ['IT 4202', 'Practicum 2', 6],
                ['IT ELEC 4', 'IT Elective 4', 3],
                ['GE-STS', 'Science, Technology and Society', 3],
                ['IT 5101', 'Business Analytics for IT', 3],
                ['IT 5102', 'Enterprise Analysis & Modelling', 3],
                ['IT 5103', 'Mobile Software Development', 3],
                ['IT 5104', 'Game Development', 3],
                ['IT 5105', 'Network Management', 3],
                ['IT 5106', 'Network Security', 3],
                ['IT 5107', 'Information Storage and Management', 3],
                ['IT 5108', 'Cloud Infrastructure and Services', 3],
                ['IT 5109', 'Scaling Networks', 3],
                ['IT 5110', 'Connecting Networks', 3],
                ['IT 5111', 'Internet of Things', 3],
                ['CIS 1102N', 'Introduction to Computing', 3],
                ['EDM 1', 'The Carolinian Missionary', -3],
                ['EDM 2', 'Education with a Mission II', -3],
                ['CIS 2106N', 'Computer Hardware Servicing NC II', 1],
                ['IT 3101N', 'Platform Technologies', 3],
                ['GE-FREELEC 1', 'General Education Free Elective 1', 3],
                ['GE-FREELEC 2', 'GE Free Elective II', 3],
                ['CIS 2203N', 'Mobile Development', 3],
                ['IT 3102N', 'Probability and Statistics', 3],
                ['IT 3103A', 'Systems Integration & Architecture', 3],
                ['IT 3202N', 'Software Quality Assurance', 3],
                ['GE-FREELEC 3', 'GE Free Elective III', 3],
                ['NSTP 2', 'National Service Training Program II', -3],
                ['IT 3104N', 'Information Assurance & Security', 3],
                ['IT 3201N', 'Capstone Project I', 3],
                ['IT 3204N', 'Research Methods in Computing', 3],
                ['IT 3105N', 'Application Development & Emerging Technologies', 3],
                ['IT 3203N', 'Quantitative Methods', 3],
                ['IT 3206N', 'Integrative Programming and Technology', 3],
                ['IT 4102N', 'Practicum 1', 3],
                ['IT ELEC 2', 'IT Elective 2', 3],
                ['IT FREELEC 2', 'IT Free Elective 2', 3],
                ['CIS 2206N', 'Programming NC IV', 1],
                ['IT 4202N', 'Practicum 2', 6],
                ['IT ELEC 3', 'IT Elective 3', 3],
                ['CS 3101N', 'Discrete Structures II', 3],
                ['CS 3103', 'Architecture and Organization with Assembly Language', 3],
                ['GE-ETHICS', 'Ethics', 3],
                ['CS 3102N', 'Algorithms and Complexity', 3],
                ['CS 3203N', 'Data Analytics', 3],
                ['MAT 3101', 'Advanced Calculus and its Applications to CS', 3],
                ['CS 3105N', 'Application Development and Emerging Technologies', 3],
                ['CS 3201N', 'CS Thesis I', 3],
                ['CS 3206', 'Social Issues and Professional Practices', 3],
                ['CS 3104N', 'Operating Systems', 3],
                ['CS 3106N', 'Information Assurance and Security', 3],
                ['CS 3205', 'Software Engineering', 3],
                ['CS 3202N', 'Automata Theory and Formal Languages', 3],
                ['CS 4101N', 'CS Thesis II', 3],
                ['MAT 4201', 'Math Elective', 3],
                ['CS 3204N', 'Programming Languages', 3],
                ['CS 4102N', 'Practicum', 3],
                ['CS 4103', 'Intelligent Systems', 3],
                ['CS 4201N', 'Seminars and Tours', 3],
                ['CS 5101', 'Natural Language Processing', 5],
                ['CS 5102', 'Digital Image Processing', 5],
                ['CS 5103', 'Robotics', 5],
                ['CS 5104', 'Parallel & Distributed Computing', 5],
                ['CS 5105', 'Game Development', 5],
                ['CS 5106', 'Fundamentals of Business Analytics', 5],
                ['CS 5107', 'Enterprise Data Management', 5],
                ['CS 5108', 'Analytics Modeling', 5],
                ['CS 5109', 'Analytics Technique and Tools', 5],
                ['CS 5110', 'Data Mining', 5],
                ['CS 5111', 'Information Storage and Management', 5],
                ['CS 5112', 'Cloud Infrastructure and Services', 5],
                ['CS 5113', 'Scaling Networks', 5],
                ['CS 5114', 'Connecting Networks', 5],
                ['CS 5115', 'Internet of Things', 5],
                ['CS 3101', 'Discrete Structures II', 3],
                ['CS 3102', 'Algorithms and Complexity', 3],
                ['CS 3104', 'Operating Systems', 3],
                ['CS 3105', 'Applications Dev. And Emerging Tech', 3],
                ['CS 3106', 'Information Assurance and Security', 3],
                ['CS 3201', 'CS Thesis I', 3],
                ['CS 3202', 'Automata Theory and Formal Languages', 3],
                ['CS 3203', 'Data Analytics', 3],
                ['CS 3204', 'Programming Languages', 3],
                ['CS 4101', 'CS Thesis 2', 3],
                ['CS 4102', 'Practicum', 6],
                ['CS FREE EL 3', 'CS Free Elective 3', 3],
                ['CS ELEC 4', 'CS Elective 4', 3],
                ['MAT 5101', 'Numerical Methods', 3],
                ['MAT 5102', 'Graph Theory', 3],
                ['MAT 5103', 'Linear Regression', 3],
                ['IS 3204', 'Accounting for IS', 3],
                ['IS 4103', 'Evaluation Of Business Performance', 3],
                ['IS 3103', 'Applications Development And Emerging Technologies', 3],
                ['IS 3101N', 'Introduction to Information Science', 3],
                ['IS 3102', 'Financial Management', 3],
                ['IS 3106', 'IS Project Management', 3],
                ['IS 4104N', 'Fundamentals of Information Systems', 3],
                ['IS 3105', 'Enterprise Architecture', 3],
                ['IS 3107', 'Organization and Management Concepts', 3],
                ['IS 3201', 'CApstone 1', 3],
                ['IS 3104', 'Collection Of Information Management And Resources', 3],
                ['IS 3205', 'Quantitative Methods', 3],
                ['IS 3206', 'IS Strategy Management And Acquisition', 3],
                ['IS 4101', 'Capstone 2', 3],
                ['IS 4102', 'Practicum A', 3],
                ['IS 4202', 'PRACTICUM B', 6],
                ['IS 4201', 'Seminars and Tours', 3],
                ['IS 3203', 'Information Resources and Services', 3],
                ['IS 3207', 'Professional Issues', 3],
                ['IS5101', 'Decision Support Systems', 3],
                ['IS5102', 'Business Process Engineering', 3],
                ['IS5104', 'Information Engineering', 3],
                ['IS5105', 'Business Laws', 3],
                ['IS5106', 'Information Assurance and Security', 3],
                ['IS5108', 'Supply Chain Management', 3],
                ['IS5109', 'Practical Data Science', 3],
                ['IS 3101', 'Fundamentals of Information Systems', 3],
                ['IS 3202', 'Business Process Management', 3],
                ['IS 4104', 'Professional Issues in Information Systems', 3],
                ['IS 5101', 'Decision Support Systems', 3],
                ['IS 5102', 'Business Process Engineering', 3],
                ['IS 5103', 'Technopreneurship', 3],
                ['IS 5104', 'Information Engineering', 3],
                ['IS 5105', 'Business Laws', 3],
                ['IS 5106', 'Information Assurance & Security I', 3],
                ['IS 5108', 'Supply Chain Management', 3],
                ['IS 5109', 'Practical Data Science', 3],
                ['IS 5110', 'Systems Quality, Testing and Assurance', 3],
                ['IS 5111', 'Decision-Analysis', 3],
                ['IS 5112', 'Information Storage and Management', 3],
                ['IS 5113', 'Cloud Infrastructure and Services', 3],
                ['IS 5114', 'Scaling Networks', 3],
                ['IS 5115', 'Connecting Networks', 3],
                ['IS 5116', 'Internet of Things', 3],
            ];

            $subjectIds = [];
            foreach ($subjectData as [$code, $name, $units]) {
                $existing = DB::table('subjects')->where('course_code', $code)->first();
                if ($existing) {
                    $subjectIds[$code] = $existing->id;
                } else {
                    $subjectIds[$code] = DB::table('subjects')->insertGetId([
                        'course_code' => $code,
                        'name'        => $name,
                        'description' => null,
                        'units'       => $units,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }
            }

            // ─────────────────────────────────────────
            // 4. prospectuses
            // ─────────────────────────────────────────
            $prospectuses = [];

            // BSIT-2018
            $cid = $curriculumIds['BSIT-2018'];
            $prospectuses[] = [$cid, $subjectIds['CIS 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1102'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1103'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1104'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-PC'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-UTS'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-MMW'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['NSTP 1'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1201'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1203'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1204'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1205'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-Ethics'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['EDM1'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['NSTP2'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2101'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2102'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2104'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2105'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2106'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['EDM2'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE Fre Elec 1'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2201'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2202'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2203'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2204'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2205'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2206'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-LWR'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE Fre Elec 2'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE Fre Elec 3'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3101'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3102'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3103'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3104'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3105'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3106'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3107'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-TCW'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 2103'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3201'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3202'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3203'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3204'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3205'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3206'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-ART'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 2204'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 4101'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 4102'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 4103'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 4104'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['IT FREE EL 3'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-RPH'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 4201'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 4202'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT ELEC 4'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-STS'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5101'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5102'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5103'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5104'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5105'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5106'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5107'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5108'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5109'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5110'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 5111'], 4, 2];

            // BSIT-2024
            $cid = $curriculumIds['BSIT-2024'];
            $prospectuses[] = [$cid, $subjectIds['CIS 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1102N'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1103'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1104'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['EDM 1'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['EDM 2'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-MMW'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-PC'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-UTS'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-Ethics'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1201'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1204'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1205'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2106N'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3106'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-LWR'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-ART'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-TCW'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-RPH'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2104'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2201'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2101'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2105'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3101N'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3107'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 4104'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-STS'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-FREELEC 1'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-FREELEC 2'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2203N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3102N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3103A'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3202N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-FREELEC 3'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['NSTP 1'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['NSTP 2'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3104N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3201N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3204N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3105N'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3203N'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 3206N'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 4102N'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT 4201'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT ELEC 2'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IT FREELEC 2'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 1101'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 1202'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2204'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2206N'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 3205'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 4101'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 4103'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT 4202N'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IT ELEC 3'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 2103'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 2204'], 3, 2];

            // BSCS-2024
            $cid = $curriculumIds['BSCS-2024'];
            $prospectuses[] = [$cid, $subjectIds['CIS 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1102N'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1103'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1104'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-MMW'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-PC'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-UTS'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['NSTP 1'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['EDM 1'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1201'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1203'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1204'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1205'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2106N'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-FREELEC 1'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['NSTP 2'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['EDM 2'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2104'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2201'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2101'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2102'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2105'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3101N'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 2103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-ETHICS'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-FREELEC 2'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2203N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2204'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2205'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2206N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3102N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3203N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 2204'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['MAT 3101'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-FREELEC 3'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-LWR'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3105N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3201N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3206'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3104N'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3106N'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3205'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3202N'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 4101N'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['MAT 4201'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-TCW'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-ART'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3204N'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 4102N'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 4103'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 4201N'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-STS'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-RPH'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5101'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5102'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5103'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5104'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5105'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5106'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5107'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5108'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5109'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5110'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5111'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5112'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5113'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5114'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5115'], 3, 2];

            // BSCS-2018
            $cid = $curriculumIds['BSCS-2018'];
            $prospectuses[] = [$cid, $subjectIds['CIS 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1102'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1103'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1104'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-PC'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-UTS'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-MMW'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['NSTP 1'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1201'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1203'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1204'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1205'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-ETHICS'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['EDM 1'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['NSTP 2'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2101'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2102'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2104'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2105'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2106'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['EDM 2'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE Fre Elec 1'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2201'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2202'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2203'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2204'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2205'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2206'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-LWR'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE Fre Elec 2'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE Fre Elec 3'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['MAT 3101'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3101'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3102'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3103'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3104'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3105'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3106'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-TCW'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 2103'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 3201'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3202'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3203'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3204'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3205'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 3206'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-ART'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 2204'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 4101'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 4102'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['CS 4103'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['CS FREE EL 3'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-RPH'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['MAT 4201'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 4201N'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS ELEC 4'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-STS'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5101'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5102'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5103'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5104'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5105'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5106'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5107'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5108'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5109'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5110'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5111'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5112'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5113'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5114'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CS 5115'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['MAT 5101'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['MAT 5102'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['MAT 5103'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1202'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2102'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2104'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2105'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2204'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2205'], 4, 2];

            // BSIS-2024
            $cid = $curriculumIds['BSIS-2024'];
            $prospectuses[] = [$cid, $subjectIds['CIS 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1102N'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1103'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1104'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['EDM 1'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-MMW'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-PC'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-UTS'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['NSTP 1'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1201'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1204'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1205'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2106N'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3204'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['EDM 2'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-FREELEC 1'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['NSTP 2'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2104'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2201'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2101'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2105'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2102'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 4103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-Ethics'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-FREELEC 2'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 2103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2203N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2205'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3101N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3102'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3106'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 4104N'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-LWR'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-FREELEC 3'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 2204'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3105'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3107'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3201'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3104'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3205'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3206'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 4101'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 4102'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-ART'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-TCW'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2204'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2206N'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1203'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 4202'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 4201'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3203'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3207'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-RPH'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-STS'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS5101'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS5102'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS5104'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS5105'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS5106'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS5108'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS5109'], 3, 2];

            // BSIS-2018
            $cid = $curriculumIds['BSIS-2018'];
            $prospectuses[] = [$cid, $subjectIds['CIS 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1102'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1103'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1104'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-MMW'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-PC'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-UTS'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['NSTP 1'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 1101'], 1, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 1201'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1203'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1204'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 1205'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-Ethics'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['EDM 1'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['NSTP 2'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 1202'], 1, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2101'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2102'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2103'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2104'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2105'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2106'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['EDM2'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['GE Fre Elec 1'], 2, 1];
            $prospectuses[] = [$cid, $subjectIds['CIS 2201'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2202'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2203'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2204'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2205'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['CIS 2206'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-LWR'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE Fre Elec 2'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['GE Fre Elec 3'], 2, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3101'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3102'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3103'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3104'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3105'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3106'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3107'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-TCW'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['TPE 2103'], 3, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 3201'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3202'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3203'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3204'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3205'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3206'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 3207'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-ART'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['TPE 2204'], 3, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 4101'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 4102'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 4103'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 4104'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['GE-RPH'], 4, 1];
            $prospectuses[] = [$cid, $subjectIds['IS 4201'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 4202'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['GE-STS'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5101'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5102'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5103'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5104'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5105'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5106'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5108'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5109'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5110'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5111'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5112'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5113'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5114'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5115'], 4, 2];
            $prospectuses[] = [$cid, $subjectIds['IS 5116'], 4, 2];

            foreach (array_chunk($prospectuses, 100) as $chunk) {
                $rows = array_map(fn($p) => [
                    'curriculum_id'   => $p[0],
                    'subject_id'      => $p[1],
                    'year_level'      => $p[2],
                    'semester_number' => $p[3],
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ], $chunk);
                DB::table('prospectuses')->insert($rows);
            }

            $this->command->info('✅  Seeded successfully.');
            $this->command->info('   Programs    : ' . count($programs));
            $this->command->info('   Curricula   : ' . count($curricula));
            $this->command->info('   Subjects    : ' . count($subjectData));
            $this->command->info('   prospectuses  : ' . count($prospectuses));
        });
    }
}