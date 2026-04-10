<?php
// config/cqi.php
// Institution details used in PDF generation and Gemini prompts

return [
    'institution' => env('CQI_INSTITUTION', 'University of San Carlos'),
    'department'  => env('CQI_DEPARTMENT',  'School of Arts and Sciences'),
];
