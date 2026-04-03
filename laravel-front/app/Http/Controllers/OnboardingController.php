<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Subject;

class OnboardingController extends Controller
{
    public function showUploadForm()
    {
        return view('onboarding.upload');
    }

    public function processUpload(Request $request)
    {
        $request->validate([
            'study_load' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $file = $request->file('study_load');
        $base64 = base64_encode(file_get_contents($file));

        $prompt = <<<PROMPT
    You are a parser. Analyze this student's study load image.
    Extract ONLY:
    - course_code (e.g., "CS101", "IT202")
    - group or section if visible (e.g., "BSCS-3A")

    Return strictly a valid JSON array like:
    [
    {"course_code": "CS101", "group": "1"},
    {"course_code": "MATH202", "group": "2"}
    ]
    No explanations, no markdown, no text outside JSON.
    PROMPT;

        try {
            $response = Http::timeout(60)->withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . env('GEMINI_API_KEY'),
                [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $file->getMimeType(),
                                        'data' => $base64,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );

            
            // Log entire response for debugging
            Log::info('Gemini API Raw Response', ['body' => $response->body()]);

            $data = $response->json();
            $output = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            Log::info('Gemini Parsed Output', ['output' => $output]);

            if (empty($output)) {
                return back()->with('error', 'Gemini returned an empty response. Check logs for raw response.');
            }

            // Clean and extract JSON text
            $cleanOutput = trim(preg_replace('/```json|```/i', '', $output));
            if (preg_match('/\[.*\]/s', $cleanOutput, $matches)) {
                $jsonText = $matches[0];
            } else {
                $jsonText = $cleanOutput;
            }

            $jsonData = json_decode($jsonText, true);
            if (!is_array($jsonData)) {
                Log::error('Gemini JSON Decode Error', ['raw' => $output, 'cleaned' => $jsonText]);
                return back()->with('error', 'Gemini output was not valid JSON. Check logs for details.');
            }

            $user = Auth::user();

            foreach ($jsonData as $course) {
                $courseCode = trim($course['course_code'] ?? '');
                $group = $course['group'] ?? null;

                if (empty($courseCode)) continue;

                // Check if subject exists first
                $subject = Subject::where('course_code', $courseCode)->first();

                if (!$subject) {
                    // Create a new subject if it doesn’t exist
                    $subject = Subject::create([
                        'course_code' => $courseCode,
                        'name' => null, // prevent saving group in subject name
                        'description' => 'Extracted from study load',
                    ]);
                }

                // Attach relationship depending on role
                if ($user->hasRole('student')) {
                    $user->enrolledSubjects()->syncWithoutDetaching([
                        $subject->id => ['group' => $group],
                    ]);
                } elseif ($user->hasRole('teacher')) {
                    $user->teachingSubjects()->syncWithoutDetaching([
                        $subject->id => ['group' => $group],
                    ]);
                }
            }

            // Redirect based on role
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard')->with('success', 'Study load processed successfully!');
            } elseif ($user->hasRole('teacher')) {
                return redirect()->route('teacher.dashboard')->with('success', 'Study load processed successfully!');
            } elseif ($user->hasRole('student')) {
                return redirect()->route('student.dashboard')->with('success', 'Study load processed successfully!');
            } else {
                return redirect('/')->with('success', 'Study load processed successfully!');
            }
        } catch (\Exception $e) {
            Log::error('Gemini Exception', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error communicating with Gemini API.');
        }
    }

}
