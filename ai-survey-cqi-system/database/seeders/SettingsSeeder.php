<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [

            // ── APP IDENTITY ───────────────────────────────────────────────
            ['group' => 'app', 'key' => 'app.name',        'type' => 'string',  'label' => 'Application Name',       'description' => 'The name displayed in the browser tab and emails.',               'value' => 'CQI System',                   'is_sensitive' => false],
            ['group' => 'app', 'key' => 'app.tagline',     'type' => 'string',  'label' => 'Tagline',                 'description' => 'Short subtitle shown on the login page.',                        'value' => 'Continuous Quality Improvement','is_sensitive' => false],
            ['group' => 'app', 'key' => 'app.institution', 'type' => 'string',  'label' => 'Institution Name',        'description' => 'Used in PDF reports and email headers.',                         'value' => 'University of San Carlos',     'is_sensitive' => false],
            ['group' => 'app', 'key' => 'app.department',  'type' => 'string',  'label' => 'Department / College',    'description' => 'Printed under the institution name in reports.',                  'value' => 'School of Arts and Sciences',  'is_sensitive' => false],
            // ['group' => 'app', 'key' => 'app.logo',        'type' => 'file',    'label' => 'App Logo',                'description' => 'Displayed in the sidebar and PDF reports. PNG/JPG, max 2 MB.',   'value' => null,                           'is_sensitive' => false],
            // ['group' => 'app', 'key' => 'app.favicon',     'type' => 'file',    'label' => 'Favicon',                 'description' => 'Browser tab icon. ICO or PNG, 32×32px.',                         'value' => null,                           'is_sensitive' => false],
            // ['group' => 'app', 'key' => 'app.primary_color','type'=> 'string',  'label' => 'Primary Color',           'description' => 'Hex color used for accents (future Bootstrap theme).',            'value' => '#4f46e5',                      'is_sensitive' => false],
            // ['group' => 'app', 'key' => 'app.url',         'type' => 'string',  'label' => 'Application URL',         'description' => 'Base URL used in emails and PDF footers.',                       'value' => 'http://localhost',             'is_sensitive' => false],

            // ── AI & NLP ───────────────────────────────────────────────────
            ['group' => 'ai',  'key' => 'ai.gemini_api_key',    'type' => 'string',  'label' => 'Gemini API Key',          'description' => 'Get your key at https://aistudio.google.com',             'value' => '',             'is_sensitive' => true],
            ['group' => 'ai',  'key' => 'ai.gemini_model',      'type' => 'string',  'label' => 'Gemini Model',            'description' => 'Recommended: gemini-2.5-flash - Fast, recommended',       'value' => 'gemini-2.5-flash','is_sensitive' => false],
            ['group' => 'ai',  'key' => 'ai.gemini_temperature','type' => 'string',  'label' => 'Gemini Temperature',      'description' => 'Creativity level: 0.0 (precise) to 1.0 (creative). Recommended: 0.4.','value' => '0.4','is_sensitive' => false],
            // ['group' => 'ai',  'key' => 'ai.nlp_server_url',    'type' => 'string',  'label' => 'NLP Server URL',          'description' => 'Flask sentiment server base URL.',                         'value' => 'http://127.0.0.1:5000','is_sensitive' => false],
            // ['group' => 'ai',  'key' => 'ai.nlp_model_name',    'type' => 'string',  'label' => 'NLP Model Name',          'description' => 'Model identifier stored in sentiment records.',            'value' => 'cqi-sentiment','is_sensitive' => false],
            // ['group' => 'ai',  'key' => 'ai.nlp_model_version', 'type' => 'string',  'label' => 'NLP Model Version',       'description' => 'Version string stored in sentiment records.',             'value' => '1.0',          'is_sensitive' => false],
            // ['group' => 'ai',  'key' => 'ai.nlp_timeout',       'type' => 'integer', 'label' => 'NLP Timeout (seconds)',   'description' => 'Max seconds to wait for NLP server response.',            'value' => '30',           'is_sensitive' => false],
            // ['group' => 'ai',  'key' => 'ai.cqi_report_institution','type'=>'string','label'=> 'CQI Report — Institution', 'description' => 'Overrides app.institution in PDF reports if set.',         'value' => '',             'is_sensitive' => false],
            // ['group' => 'ai',  'key' => 'ai.cqi_report_department','type'=>'string', 'label' => 'CQI Report — Department', 'description' => 'Overrides app.department in PDF reports if set.',          'value' => '',             'is_sensitive' => false],

            // ── SURVEY & ACADEMIC LOGIC ────────────────────────────────────
            ['group' => 'survey', 'key' => 'survey.passing_threshold',      'type' => 'string',  'label' => 'Passing Rating Threshold',        'description' => 'Minimum average rating considered "passing". Used in CQI analysis.',   'value' => '3.0',       'is_sensitive' => false],
            ['group' => 'survey', 'key' => 'survey.default_scale_max',      'type' => 'integer', 'label' => 'Default Rating Scale Max',         'description' => 'Maximum rating value for display and interpretation (e.g. 5 or 10).', 'value' => '5',         'is_sensitive' => false],
            ['group' => 'survey', 'key' => 'survey.excellent_threshold',    'type' => 'string',  'label' => 'Excellent Score Threshold',        'description' => 'Score ÷ max ≥ this % = Excellent.',                                   'value' => '0.90',      'is_sensitive' => false],
            ['group' => 'survey', 'key' => 'survey.very_good_threshold',    'type' => 'string',  'label' => 'Very Good Score Threshold',        'description' => 'Score ÷ max ≥ this % = Very Good.',                                   'value' => '0.80',      'is_sensitive' => false],
            ['group' => 'survey', 'key' => 'survey.good_threshold',         'type' => 'string',  'label' => 'Good Score Threshold',             'description' => 'Score ÷ max ≥ this % = Good.',                                        'value' => '0.70',      'is_sensitive' => false],
            ['group' => 'survey', 'key' => 'survey.fair_threshold',         'type' => 'string',  'label' => 'Fair Score Threshold',             'description' => 'Score ÷ max ≥ this % = Fair. Below = Needs Improvement.',             'value' => '0.60',      'is_sensitive' => false],
            ['group' => 'survey', 'key' => 'survey.allow_anonymous',        'type' => 'boolean', 'label' => 'Anonymous Responses',             'description' => 'When enabled, respondent identities are hidden from faculty analytics.','value' => '1',        'is_sensitive' => false],
            ['group' => 'survey', 'key' => 'survey.reminder_days_before',   'type' => 'integer', 'label' => 'Reminder Days Before Deadline',    'description' => 'Days before end_date to send survey reminder emails.',               'value' => '3',         'is_sensitive' => false],
            ['group' => 'survey', 'key' => 'survey.academic_year_start_month','type'=>'integer', 'label' => 'Academic Year Start Month',        'description' => 'Month number when the academic year begins (e.g. 8 = August).',       'value' => '8',         'is_sensitive' => false],
            ['group' => 'survey', 'key' => 'survey.sem1_label',             'type' => 'string',  'label' => 'Semester 1 Label',                 'description' => 'Display label for semester number 1.',                                'value' => '1st Semester','is_sensitive'=> false],
            ['group' => 'survey', 'key' => 'survey.sem2_label',             'type' => 'string',  'label' => 'Semester 2 Label',                 'description' => 'Display label for semester number 2.',                                'value' => '2nd Semester','is_sensitive'=> false],
            ['group' => 'survey', 'key' => 'survey.sem3_label',             'type' => 'string',  'label' => 'Summer Label',                     'description' => 'Display label for semester number 3 (summer).',                       'value' => 'Summer',    'is_sensitive' => false],

            // ── LOCALIZATION & REGIONAL ────────────────────────────────────
            ['group' => 'locale', 'key' => 'locale.timezone',      'type' => 'string', 'label' => 'Timezone',            'description' => 'System timezone for all date/time operations.',                         'value' => 'Asia/Manila',  'is_sensitive' => false],
            ['group' => 'locale', 'key' => 'locale.date_format',   'type' => 'string', 'label' => 'Date Display Format', 'description' => 'PHP date() format string. e.g. M d, Y = "Jan 01, 2025".',              'value' => 'M d, Y',       'is_sensitive' => false],
            ['group' => 'locale', 'key' => 'locale.time_format',   'type' => 'string', 'label' => 'Time Display Format', 'description' => 'PHP date() format. e.g. h:i A = 12-hour, H:i = 24-hour.',              'value' => 'h:i A',        'is_sensitive' => false],
            // ['group' => 'locale', 'key' => 'locale.language',      'type' => 'string', 'label' => 'System Language',     'description' => 'Application locale code. Currently: en.',                              'value' => 'en',           'is_sensitive' => false, 'is_readonly' => true],
            // ['group' => 'locale', 'key' => 'locale.currency',      'type' => 'string', 'label' => 'Currency Symbol',     'description' => 'Used in any cost-related features (future).',                          'value' => '₱',            'is_sensitive' => false],

            // ── MAIL & NOTIFICATIONS ───────────────────────────────────────
            ['group' => 'mail', 'key' => 'mail.from_name',         'type' => 'string',  'label' => 'Mail From Name',      'description' => 'Sender name shown in outgoing emails.',                    'value' => 'CQI System',         'is_sensitive' => false],
            ['group' => 'mail', 'key' => 'mail.from_address',      'type' => 'string',  'label' => 'Mail From Address',   'description' => 'Sender email address for outgoing mail.',                  'value' => 'no-reply@cqi.edu',   'is_sensitive' => false],
            // ['group' => 'mail', 'key' => 'mail.send_survey_reminders','type'=>'boolean', 'label' => 'Send Survey Reminders','description'=> 'Email students before survey deadline.',                   'value' => '1',                  'is_sensitive' => false],
            // ['group' => 'mail', 'key' => 'mail.send_cqi_notifications','type'=>'boolean','label' => 'Notify Faculty on CQI','description'=>'Email faculty when their CQI report is ready.',             'value' => '1',                  'is_sensitive' => false],
            // ['group' => 'mail', 'key' => 'mail.driver',            'type' => 'string',  'label' => 'Mail Driver (info)',  'description' => 'Current MAIL_MAILER from .env. Change in .env directly.', 'value' => '',                   'is_sensitive' => false, 'is_readonly' => true],

            // ── SECURITY ───────────────────────────────────────────────────
            ['group' => 'security', 'key' => 'security.session_lifetime',   'type' => 'integer', 'label' => 'Session Lifetime (minutes)',   'description' => 'How long before an inactive session expires.',              'value' => '120',  'is_sensitive' => false],
            ['group' => 'security', 'key' => 'security.max_login_attempts', 'type' => 'integer', 'label' => 'Max Login Attempts',           'description' => 'Failed attempts before temporary lockout.',                 'value' => '5',    'is_sensitive' => false],
            ['group' => 'security', 'key' => 'security.lockout_duration',   'type' => 'integer', 'label' => 'Lockout Duration (seconds)',   'description' => 'How long a user is locked out after too many attempts.',    'value' => '60',   'is_sensitive' => false],
            ['group' => 'security', 'key' => 'security.require_email_verification','type'=>'boolean','label'=>'Require Email Verification', 'description' => 'Force email verification before users can log in.',         'value' => '1',    'is_sensitive' => false],
            ['group' => 'security', 'key' => 'security.password_min_length','type' => 'integer', 'label' => 'Minimum Password Length',     'description' => 'Applied when users change their password.',                 'value' => '8',    'is_sensitive' => false],

            // ── MAINTENANCE ────────────────────────────────────────────────
            ['group' => 'maintenance', 'key' => 'maintenance.mode',         'type' => 'boolean', 'label' => 'Maintenance Mode',          'description' => 'Puts the system in maintenance mode. Only admins can log in.', 'value' => '0',  'is_sensitive' => false],
            ['group' => 'maintenance', 'key' => 'maintenance.message',      'type' => 'string',  'label' => 'Maintenance Message',       'description' => 'Message shown to non-admin users during maintenance.',         'value' => 'The system is currently under maintenance. Please check back soon.', 'is_sensitive' => false],
            ['group' => 'maintenance', 'key' => 'maintenance.banner_enabled','type'=> 'boolean', 'label' => 'Show Announcement Banner',  'description' => 'Show a site-wide banner to all logged-in users.',             'value' => '0',  'is_sensitive' => false],
            ['group' => 'maintenance', 'key' => 'maintenance.banner_text',  'type' => 'string',  'label' => 'Announcement Banner Text',  'description' => 'Text shown in the announcement banner when enabled.',          'value' => '',   'is_sensitive' => false],
            ['group' => 'maintenance', 'key' => 'maintenance.banner_type',  'type' => 'string',  'label' => 'Banner Type',               'description' => 'Visual style: info, success, warning, error.',                 'value' => 'info','is_sensitive' => false],
        ];

        foreach ($settings as $data) {
            Setting::firstOrCreate(
                ['key' => $data['key']],
                [
                    'group'        => $data['group'],
                    'value'        => $data['value'],
                    'type'         => $data['type'],
                    'label'        => $data['label'],
                    'description'  => $data['description'] ?? null,
                    'is_sensitive' => $data['is_sensitive'] ?? false,
                    'is_readonly'  => $data['is_readonly']  ?? false,
                ]
            );
        }
    }
}
