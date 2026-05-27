<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Survey;
use App\Models\Enrollment;
use App\Models\SurveyAttempt;
use App\Models\SurveyTemplate;
use App\Models\CourseOffering;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SurveyEvaluationSimulationSeeder extends Seeder
{
    // ── Text Banks ────────────────────────────────────────────────────────────
    // 15 unique responder personas per sentiment (indices 0–6 = 7 open-ended questions).
    // Surveys 1–3 use these uniquely (one persona assigned per student).
    // Surveys 4–5 use $repeatedPositiveTextBanks / $repeatedNegativeTextBanks, where
    // all 15 students share the same single-persona answers to stress-test NLP consistency.

    private array $positiveTextBanks = [
        'responder_1' => [
            0 => 'The structured approach kept me motivated and fully engaged every single session.',
            1 => 'The interactive live-coding segments completely demystified the trickier parts of the syllabus.',
            2 => 'Absolutely, the exams were entirely reflective of the core class content.',
            3 => 'The instructor\'s genuine passion and willingness to help everyone succeed.',
            4 => 'Perhaps offering a few more optional deep-dive exercises for advanced learning.',
            5 => 'Phenomenal. Easily one of the most rewarding educational experiences I have had.',
            6 => 'Yes, without reservation. A stellar educational environment altogether.',
        ],
        'responder_2' => [
            0 => 'The collaborative atmosphere made it incredibly easy to learn and bounce ideas around.',
            1 => 'Using real-world case studies made abstract concepts immediately click for me.',
            2 => 'Yes, the grading criteria were perfectly fair and accurately mirrored our homework.',
            3 => 'The approachable nature of the professor and the focus on practical skills.',
            4 => 'It\'s already fantastic, but maybe post the lecture resources slightly earlier.',
            5 => 'Incredibly fulfilling. I gained direct, actionable knowledge for my future career.',
            6 => 'Absolutely. The professor makes even the most complex topics accessible.',
        ],
        'responder_3' => [
            0 => 'An excellent, fast-paced environment that pushed us to think critically and grow.',
            1 => 'The small group brainstorming sessions drastically helped clarify my misunderstandings.',
            2 => 'Yes, the assessments targeted the material we thoroughly covered during class.',
            3 => 'The outstanding clarity of the lectures and the high-quality feedback we received.',
            4 => 'Honestly, no major changes are needed; the course flow is highly optimized.',
            5 => 'Wonderful experience. I walked away feeling highly competent in every module.',
            6 => 'Yes, 100%. You will learn an immense amount under their guidance.',
        ],
        'responder_4' => [
            0 => 'The welcoming and inclusive setup made it safe to ask questions without hesitation.',
            1 => 'The step-by-step whiteboard breakdowns were masterful for clarifying tough logic.',
            2 => 'Completely. There were zero trick questions on the tests, which I highly appreciate.',
            3 => 'How organized the LMS page was—everything was incredibly easy to track.',
            4 => 'Maybe extending the optional open lab hours for even more hands-on time.',
            5 => 'Very positive and inspiring. It renewed my interest in the broader subject.',
            6 => 'Definitely. An excellent educator who genuinely cares about student milestones.',
        ],
        'responder_5' => [
            0 => 'The open-floor structure allowed for excellent dialogue and deep dive discussions.',
            1 => 'The collaborative peer-review workshops really elevated my understanding of the text.',
            2 => 'Yes, the evaluations perfectly captured the core competencies of the course.',
            3 => 'The instructor\'s brilliant use of industry-standard analogies to explain concepts.',
            4 => 'Slightly extending the time allowed for the final project submission would be nice.',
            5 => 'Top-tier. I feel very confident applying these methodologies going forward.',
            6 => 'Highly recommended. A masterclass in how to teach this material effectively.',
        ],
        'responder_6' => [
            0 => 'The systematic, modular layout of the course made it incredibly easy to stay on track.',
            1 => 'The optional weekend review sessions were a game-changer for mastering tough units.',
            2 => 'Yes, if you attended lectures and reviewed the slides, the tests were straightforward.',
            3 => 'The promptness and precision of the feedback given on our weekly assignments.',
            4 => 'Maybe incorporate a few more visual infographics into the slide presentations.',
            5 => 'Exceeded my expectations. I learned far more than I initially anticipated.',
            6 => 'Yes, absolutely. They are an asset to the department.',
        ],
        'responder_7' => [
            0 => 'The high-energy delivery from the professor made a typically dry subject very enjoyable.',
            1 => 'The quick, 5-minute summaries at the end of every lecture were perfect.',
            2 => 'Yes, everything on the assessments was pulled directly from our class discussions.',
            3 => 'The modern, relevant software tools and real-world contexts explored in class.',
            4 => 'The syllabus is solid, but maybe cut down a tiny bit on the minor reading quizzes.',
            5 => 'Thoroughly enjoyable. I looked forward to attending this class every single week.',
            6 => 'Yes, I would highly recommend this course to anyone in the major.',
        ],
        'responder_8' => [
            0 => 'The project-based curriculum structure kept the environment fresh and dynamic.',
            1 => 'The live Q&A sessions at the beginning of each class cleared up all confusion.',
            2 => 'Absolutely. The evaluations focused on core concepts rather than pure memorization.',
            3 => 'The teacher\'s talent for breaking down highly technical details into plain English.',
            4 => 'Providing an explicit checklist for the final exam format would be a helpful bonus.',
            5 => 'Excellent. I feel proud of the practical portfolio pieces I built here.',
            6 => 'Yes! You won\'t regret taking this class with this instructor.',
        ],
        'responder_9' => [
            0 => 'The professional, corporate-style environment prepared me well for industry demands.',
            1 => 'Analyzing historical failures in the field helped me understand what to avoid.',
            2 => 'Yes, the tests were a true diagnostic of whether we understood the weekly objectives.',
            3 => 'The incredible mentorship and career advice the professor offered during office hours.',
            4 => 'Keep everything exactly as it is; the current balance is perfect.',
            5 => 'Extremely productive. It was a rigorous but entirely fair learning journey.',
            6 => 'Yes, absolutely. An invaluable experience for career-minded individuals.',
        ],
        'responder_10' => [
            0 => 'The fluid, seminar-style setup allowed for great creative freedom in how we worked.',
            1 => 'Open-ended brainstorming workshops helped me look at problems from a new angle.',
            2 => 'Yes, the grading rubric was highly transparent, making expectations clear.',
            3 => 'The freedom to tailor our final assignments to our personal areas of interest.',
            4 => 'More group milestone check-ins would help teams stay organized.',
            5 => 'Incredibly stimulating and refreshing compared to standard lecture tracks.',
            6 => 'Yes, definitely. They encourage you to think outside the traditional box.',
        ],
        'responder_11' => [
            0 => 'The calm, low-pressure classroom environment significantly reduced my academic anxiety.',
            1 => 'The supplementary video guides allowed me to review tough logic at my own pace.',
            2 => 'Yes, the assessments accurately tracked the progression of our modules.',
            3 => 'The patient, respectful, and encouraging tone the instructor maintained all term.',
            4 => 'Adding an optional mid-semester feedback form would be a great inclusion.',
            5 => 'Very pleasant and successful. I felt supported through every single milestone.',
            6 => 'Yes, 100%. A wonderful professor who values student well-being.',
        ],
        'responder_12' => [
            0 => 'The meticulous organization and clean lecture outlines made note-taking seamless.',
            1 => 'The diagnostic pop-quizzes were great indicators of where I needed to study more.',
            2 => 'Yes, the tests perfectly matched the explicitly stated learning outcomes.',
            3 => 'The sheer depth of expertise and clarity of thought demonstrated by the instructor.',
            4 => 'Perhaps introduce a brief stretching break during the longer block sessions.',
            5 => 'Outstanding. A highly organized course that maximized every minute of class time.',
            6 => 'Without a doubt. One of the best organized classes I have ever taken.',
        ],
        'responder_13' => [
            0 => 'The media-rich environment kept me visually locked into the presentations.',
            1 => 'The colorful charts, mind-maps, and short documentary clips worked perfectly.',
            2 => 'Yes, the testing format matched the visual analysis style we practiced in class.',
            3 => 'The highly engaging visual materials and slide formatting used every day.',
            4 => 'Maybe provide the slide decks in a downloadable PDF format before class starts.',
            5 => 'Great experience. It was highly engaging and easy to conceptually digest.',
            6 => 'Yes, highly recommended. Especially for visual and creative learners.',
        ],
        'responder_14' => [
            0 => 'The collaborative, circle-seating setup really broke the ice and helped us form study groups.',
            1 => 'The structured debate sessions helped cement theoretical concepts into memory.',
            2 => 'Yes, the focus of the assessments was on broad understanding, which was fair.',
            3 => 'The wonderful community feeling and mutual respect shared among everyone.',
            4 => 'A tiny bit more individual desk space would improve the physical room setup.',
            5 => 'Incredibly positive. I built great peer connections while learning a lot.',
            6 => 'Yes, absolutely. The class atmosphere alone makes it worth taking.',
        ],
        'responder_15' => [
            0 => 'The uncompromising professional standards forced me to elevate my work quality.',
            1 => 'Deep-dive technical walkthroughs provided the exact precision I was looking for.',
            2 => 'Yes, the testing was rigorous but entirely truthful to the advanced lecture content.',
            3 => 'The deep technical authority and intellectual rigor brought by the professor.',
            4 => 'Provide a few more high-distinction code repositories from previous years as references.',
            5 => 'Remarkable. It pushed my technical limits in the best possible way.',
            6 => 'Yes, if you are serious about mastering this field, this is the instructor to choose.',
        ],
    ];

    private array $negativeTextBanks = [
        'responder_1' => [
            0 => 'The erratic and disorganized lecture delivery made it almost impossible to follow along.',
            1 => 'The theoretical slide monologues were ineffective; we needed concrete examples.',
            2 => 'Not at all. The exams contained complex edge cases that were never once mentioned.',
            3 => 'Honestly, nothing stands out as a positive aspect of this course format.',
            4 => 'The entire curriculum structure and pacing model need an immediate overhaul.',
            5 => 'Incredibly frustrating and stressful. I had to self-teach nearly everything.',
            6 => 'No. Avoid this section unless you are already an expert in the subject.',
        ],
        'responder_2' => [
            0 => 'The classroom was constantly bogged down by technical issues and poor time management.',
            1 => 'Rushing through code examples at lightning speed left the majority of us in the dust.',
            2 => 'No, the tests felt significantly more advanced than our simple homework tasks.',
            3 => 'The textbook itself was okay, but the actual lectures added zero value to it.',
            4 => 'Slow down the pace and actually explain the underlying logic before moving on.',
            5 => 'Disappointing. I felt like a number rather than a student being taught.',
            6 => 'No, I would definitely recommend taking this with a different professor.',
        ],
        'responder_3' => [
            0 => 'The mandatory group work structure was highly chaotic and completely unmanaged.',
            1 => 'Vague open discussions were a waste of time; we needed structured direction.',
            2 => 'No, my grade felt dependent on my group partners rather than my actual knowledge.',
            3 => 'The course content has potential, but the execution was completely lacking.',
            4 => 'Eliminate the excessive reliance on group grades for individual tracking.',
            5 => 'Unsatisfactory. It was a chaotic semester with very little real guidance.',
            6 => 'No, the current layout makes it a highly frustrating experience.',
        ],
        'responder_4' => [
            0 => 'The highly clinical, detached environment made the lectures incredibly dry and boring.',
            1 => 'Reading straight from text-heavy PowerPoint slides is not an effective strategy.',
            2 => 'The assessments relied entirely on rote memorization instead of conceptual understanding.',
            3 => 'The physical classroom was clean, but that is about the only positive thing.',
            4 => 'Incorporate interactive components to stop students from completely tuning out.',
            5 => 'Boring and uninspiring. It felt like a checklist requirement instead of a class.',
            6 => 'No, there are far more engaging instructors available for this specific topic.',
        ],
        'responder_5' => [
            0 => 'The rigid, high-pressure cold-calling style created a toxic and stressful environment.',
            1 => 'Socratic questioning just led to awkward silences instead of actual instruction.',
            2 => 'The exams were timed so brutally that they measured speed rather than actual competence.',
            3 => 'The topic itself is fascinating, but the delivery completely ruined it for me.',
            4 => 'Stop putting students on the spot and create a more encouraging space.',
            5 => 'Anxiety-inducing. I spent more time stressing over class than actually learning.',
            6 => 'No, the aggressive teaching style is completely counterproductive.',
        ],
        'responder_6' => [
            0 => 'The fast pacing assumes everyone already has an advanced background in the subject.',
            1 => 'Diving directly into complex setups without covering basics left beginners completely lost.',
            2 => 'No, the assignments did not prepare us for the brutal complexity of the midterms.',
            3 => 'The assignments forced me to learn on my own, which is a useful skill I guess.',
            4 => 'The instructor needs to calibrate the lessons for an introductory student level.',
            5 => 'Overwhelming and highly discouraging. The support system was nonexistent.',
            6 => 'No, unless you already have extensive prior knowledge of the material.',
        ],
        'responder_7' => [
            0 => 'The physical room setup was cramped and the old equipment made learning difficult.',
            1 => 'Trying to follow complex diagrams on an outdated projector system was impossible.',
            2 => 'The assessments were okay, but the grading felt incredibly arbitrary and subjective.',
            3 => 'The classmates were supportive because we were all struggling together.',
            4 => 'Update the classroom tech and establish a clear, transparent grading rubric.',
            5 => 'Mediocre at best. The material felt decades behind current industry realities.',
            6 => 'No, the lack of modern resources makes it a waste of tuition money.',
        ],
        'responder_8' => [
            0 => 'The absolute rigidity of the attendance and submission rules left no room for life events.',
            1 => 'Monotone lectures for two hours straight made it impossible to maintain focus.',
            2 => 'No, the test questions were written poorly and phrased in highly ambiguous ways.',
            3 => 'The syllabus was detailed, though it was used more as a rulebook than a guide.',
            4 => 'Show some basic empathy for student schedules and clarify exam wording.',
            5 => 'Exhausting. The unyielding structure sucked all the joy out of the subject.',
            6 => 'No, the unyielding atmosphere makes it an unpleasant experience.',
        ],
        'responder_9' => [
            0 => 'The classroom discussions constantly went completely off-track with no core structure.',
            1 => 'Letting random student tangents dictate the lecture hour left topics half-taught.',
            2 => 'No, we were tested on chapters that we didn\'t even finish covering in class.',
            3 => 'The instructor is nice, but niceness does not replace effective teaching.',
            4 => 'Keep the lectures on topic and actually finish the scheduled curriculum.',
            5 => 'Disorganized. I left the course feeling confused about the core framework.',
            6 => 'No, the complete lack of focus makes it very difficult to actually learn.',
        ],
        'responder_10' => [
            0 => 'The environment felt outdated and the assignments felt like busywork.',
            1 => 'Handwriting code/diagrams on paper during class is an obsolete way to learn.',
            2 => 'No, the tests focused on obscure trivia footnotes rather than major concepts.',
            3 => 'The class ended on time every day, which was the only consistency.',
            4 => 'Get rid of the pointless busywork and focus on modern, real-world tools.',
            5 => 'Regressive. I feel like I learned outdated habits that I will have to unlearn.',
            6 => 'No, this course desperately needs a younger or more updated perspective.',
        ],
        'responder_11' => [
            0 => 'The online/hybrid portion was incredibly unorganized and links were constantly broken.',
            1 => 'Pre-recorded audio clips from years ago with terrible microphone quality were useless.',
            2 => 'No, the online test system glitched constantly, ruining our focus and grades.',
            3 => 'The fact that I could do it from home was the only minor convenience.',
            4 => 'Fix the broken LMS portals and actually record fresh, clear lecture media.',
            5 => 'Very frustrating. It felt like an abandoned online module rather than an active class.',
            6 => 'No, the digital infrastructure for this section is completely broken.',
        ],
        'responder_12' => [
            0 => 'The grading feedback was incredibly vague, often just a number with zero comments.',
            1 => 'When I asked for clarification on confusing topics, I was just told to re-read the book.',
            2 => 'No, it\'s impossible to know if they reflect the material when grading is so secretive.',
            3 => 'The core subject is important, but that is a credit to the field, not the class.',
            4 => 'Provide actual, constructive criticism on assignments instead of just deducting points.',
            5 => 'Demoralizing. You never knew where you stood or how to actually improve.',
            6 => 'No, the complete lack of helpful feedback makes growth impossible.',
        ],
        'responder_13' => [
            0 => 'The pacing was incredibly uneven—weeks of nothing followed by a massive avalanche of work.',
            1 => 'Cramming three massive, heavy chapters into a single lecture block was a terrible choice.',
            2 => 'No, the final evaluation was drastically disproportionate to our daily workloads.',
            3 => 'The early weeks were manageable before the pacing fell off a cliff.',
            4 => 'Balance out the assignment distribution evenly across the entire semester timeline.',
            5 => 'Stressful and poorly managed. The workload spikes were completely unreasonable.',
            6 => 'No, the poor timeline planning makes it an unnecessary logistical nightmare.',
        ],
        'responder_14' => [
            0 => 'The environment felt hyper-competitive and hostile rather than cooperative.',
            1 => 'The instructor openly favored certain students, leaving others ignored during questions.',
            2 => 'The testing parameters felt designed to trick you into failing rather than showcasing knowledge.',
            3 => 'The physical location of the classroom was central, which was convenient.',
            4 => 'Foster an inclusive environment and stop writing intentionally deceptive test questions.',
            5 => 'Deeply discouraging. It completely alienated me from wanting to pursue this track further.',
            6 => 'Absolutely not. The classroom dynamic is entirely unwelcoming.',
        ],
        'responder_15' => [
            0 => 'The structure was too fluid to the point of being entirely aimless and experimental.',
            1 => 'Constantly changing assignment criteria mid-week created immense, unnecessary confusion.',
            2 => 'No, the exam rubrics changed at the last minute without any formal notice.',
            3 => 'The teacher is creative, but the execution was completely erratic.',
            4 => 'Pick a definitive plan, stick to it, and stop changing rules halfway through.',
            5 => 'Incredibly irritating. Moving goalposts made it impossible to secure a good grade.',
            6 => 'No, the constant structural instability is incredibly stressful to deal with.',
        ],
    ];

    private array $neutralTextBanks = [
        'responder_1' => [
            0 => 'The classroom environment was standard; it neither added to nor subtracted from the course.',
            1 => 'Lectures were okay, but I needed to rely on YouTube videos to fully grasp the harder units.',
            2 => 'They were generally fair, though a couple of questions felt a bit out of left field.',
            3 => 'The course followed the textbook exactly, making it very predictable.',
            4 => 'Adding a few more practical examples wouldn\'t hurt to break up the theory blocks.',
            5 => 'Average. It was an ordinary academic course that met basic requirements.',
            6 => 'Yes, if you just need the credit. It gets the job done without being amazing.',
        ],
        'responder_2' => [
            0 => 'The structure was fine, though two hours of slide presentations can get a bit dry.',
            1 => 'The code-alongs were helpful, but they often moved a bit too fast to take notes.',
            2 => 'For the most part yes, though the midterm was notably harder than the final.',
            3 => 'The convenience of having all assignment parameters published on day one.',
            4 => 'The professor could be slightly more responsive to emails during the week.',
            5 => 'Fine overall. Nothing spectacular, but I didn\'t hate my time there either.',
            6 => 'Yes, it\'s a safe choice. Just don\'t expect an intensely dynamic experience.',
        ],
        'responder_3' => [
            0 => 'The room dynamic depended entirely on who sat near you during the group parts.',
            1 => 'Peer reviews were hit-or-miss depending on how prepared your partner actually was.',
            2 => 'They reflected the lectures, but I would prefer a mix of projects instead of pure testing.',
            3 => 'The group discussions were decent when the topics were directly related to the field.',
            4 => 'Make group assignments optional or provide clearer team guidelines.',
            5 => 'It was okay. It had its high points and its definitely slow points.',
            6 => 'Sure, it\'s a decent option if you don\'t mind managing group dynamics.',
        ],
        'responder_4' => [
            0 => 'The physical classroom was comfortable, but the daily agenda felt a bit repetitive.',
            1 => 'The slide handouts were useful, but the delivery could use a bit more energy.',
            2 => 'Yes, they matched the homework, but the grading turnaround took a bit too long.',
            3 => 'The thoroughness of the course syllabus and the clear grading breakdown.',
            4 => 'Work on getting assignment grades back to students in a speedier manner.',
            5 => 'Acceptable. A standard, run-of-the-mill university course structure.',
            6 => 'Yes, they are a solid, capable instructor who covers the material adequately.',
        ],
        'responder_5' => [
            0 => 'The environment was highly professional, though it felt a bit distant at times.',
            1 => 'Analyzing real-world case studies was good, but we spent too much time on basic history.',
            2 => 'Yes, the tests were standard multiple-choice and directly matched the slides.',
            3 => 'The specific industry insights the instructor occasionally shared from experience.',
            4 => 'Spend less time on introductory history and dive into modern applications quicker.',
            5 => 'Decent. I learned the foundational concepts, which is what I needed.',
            6 => 'Yes, it\'s a reasonable class if you prefer a traditional lecture format.',
        ],
        'responder_6' => [
            0 => 'The pace started out entirely fine but got noticeably rushed in the final month.',
            1 => 'The interactive reviews were great, but we only did them right before major exams.',
            2 => 'They were fair, but the weight assigned to the final exam felt a bit too high.',
            3 => 'The structured worksheets we used during the middle weeks of the term.',
            4 => 'Distribute the course workload more evenly so the end of the term isn\'t a crunch.',
            5 => 'A mixed bag. Some weeks were highly engaging, while others felt like a drag.',
            6 => 'Yes, with the caveat that you should prepare for a heavy workload at the end.',
        ],
        'responder_7' => [
            0 => 'The setup was very tech-heavy, which is fine if you are comfortable with computers.',
            1 => 'The online submission tools worked well, but live instruction felt a bit secondary.',
            2 => 'Mostly yes, though some questions were worded in a somewhat confusing way.',
            3 => 'The fact that all class materials were archived cleanly online for easy review.',
            4 => 'Spend a bit more time explaining the concepts live rather than relying on links.',
            5 => 'Standard. It felt more like an online module that happened to meet in person.',
            6 => 'Yes, especially if you prefer navigating materials independently online.',
        ],
        'responder_8' => [
            0 => 'The environment was quiet and relaxed, almost to the point of being a bit sleepy.',
            1 => 'The open Q&A format was nice, but it required students to always speak up first.',
            2 => 'Yes, they were fair, though I prefer projects over heavy memorization exams.',
            3 => 'The low-stress environment and the lack of high-pressure pop quizzes.',
            4 => 'Incorporate a bit more structured activity to keep energy levels up.',
            5 => 'Ordinary. It didn\'t challenge me heavily, but it wasn\'t stressful either.',
            6 => 'Yes, if you are looking for a straightforward, low-drama classroom setting.',
        ],
        'responder_9' => [
            0 => 'The lecture structure shifted halfway through the term, which took some getting used to.',
            1 => 'Whiteboard diagrams worked best; the PowerPoint slides were far less effective.',
            2 => 'They aligned well enough, but the grading standards seemed to change near the end.',
            3 => 'The technical depth covered in the second half of the semester.',
            4 => 'Maintain a consistent teaching and grading methodology from day one to the end.',
            5 => 'Mediocre but useful. It got me through the prerequisite requirements.',
            6 => 'Yes, the professor is competent, even if the structure changes down the line.',
        ],
        'responder_10' => [
            0 => 'The classroom layout was formal, which limited casual peer interaction during class.',
            1 => 'Individual reading tracks were solid, but the lectures felt a bit redundant after reading.',
            2 => 'Yes, the questions were pulled straight from the assigned chapter readings.',
            3 => 'The high quality and relevance of the textbook chosen for the curriculum.',
            4 => 'Use lecture hours to expand on the book rather than just summarizing it.',
            5 => 'Passable. It felt like an extension of reading the textbook on my own.',
            6 => 'Yes, if you are a disciplined self-reader, you will do completely fine.',
        ],
        'responder_11' => [
            0 => 'The class sizes were quite large, so the environment lacked personal interaction.',
            1 => 'Broad conceptual overviews were clear, but we glossed over technical details too fast.',
            2 => 'The assessments were average, but the feedback notes were too sparse to learn from.',
            3 => 'The broad exposure to many different facets of the subject area.',
            4 => 'Slow down on the technical sections so students can absorb the specific steps.',
            5 => 'Fair. It serves as a good broad overview, but lacks specialized depth.',
            6 => 'Yes, as a general introductory course, it fits the description perfectly.',
        ],
        'responder_12' => [
            0 => 'The strict attendance policy felt unnecessary, but the actual daily structure was okay.',
            1 => 'Reviewing homework models in class helped clear up several major blind spots.',
            2 => 'Yes, the tests were entirely predictable based on our weekly study guides.',
            3 => 'The highly reliable schedule; class always started and ended exactly on time.',
            4 => 'Loosen the rigid attendance rules slightly for minor medical issues.',
            5 => 'Decent. It was a highly disciplined course with zero structural surprises.',
            6 => 'Yes, if you value extreme punctuality and a highly predictable routine.',
        ],
        'responder_13' => [
            0 => 'The classroom climate was fine, though discussions occasionally drifted off target.',
            1 => 'Analyzing contemporary news articles was interesting but cut into core lesson time.',
            2 => 'Yes, though the grading on essay questions felt slightly subjective at times.',
            3 => 'The casual, conversational tone of the lectures made it easy to sit through.',
            4 => 'Keep a tighter leash on the discussions to ensure all core content is met.',
            5 => 'Satisfactory. It was an easy-going class that covered the required syllabus.',
            6 => 'Yes, if you enjoy a more relaxed, conversational approach to learning.',
        ],
        'responder_14' => [
            0 => 'The workspace environment was adequate, though the lab computers were a bit slow.',
            1 => 'Hands-on practice sessions were vital, but we needed more debugging assistance.',
            2 => 'Yes, the practical portions of the test were fair, but written parts felt tedious.',
            3 => 'Having access to a fully dedicated lab space during class hours.',
            4 => 'Upgrading the lab infrastructure would significantly speed up our practical work.',
            5 => 'Unremarkable but solid. It provided standard technical training without frills.',
            6 => 'Yes, the lab component makes it worth it despite the slow hardware.',
        ],
        'responder_15' => [
            0 => 'The course structure was incredibly theoretical, which might not suit everyone\'s style.',
            1 => 'Deep conceptual lectures were fascinating, but actual implementation steps were missing.',
            2 => 'Yes, if you memorized the theories, the testing format was easy to navigate.',
            3 => 'The impressive academic scope and historic background provided by the instructor.',
            4 => 'Balance out the heavy abstract theory with a few real-world applications.',
            5 => 'Okay. A very academic course that lacks hands-on technical development.',
            6 => 'Yes, if you prefer learning the philosophy and theory behind the subject.',
        ],
    ];

    // ── Repeated banks: single persona, cloned across ALL 15 students ─────────
    // Purpose: verify NLP pipeline produces consistent results on identical input.

    private array $repeatedPositiveTextBanks = [
        0 => 'The structured approach kept me motivated and fully engaged every single session.',
        1 => 'The interactive live-coding segments completely demystified the trickier parts of the syllabus.',
        2 => 'Absolutely, the exams were entirely reflective of the core class content.',
        3 => 'The instructor\'s genuine passion and willingness to help everyone succeed.',
        4 => 'Perhaps offering a few more optional deep-dive exercises for advanced learning.',
        5 => 'Phenomenal. Easily one of the most rewarding educational experiences I have had.',
        6 => 'Yes, without reservation. A stellar educational environment altogether.',
    ];

    private array $repeatedNegativeTextBanks = [
        0 => 'The erratic and disorganized lecture delivery made it almost impossible to follow along.',
        1 => 'The theoretical slide monologues were ineffective; we needed concrete examples.',
        2 => 'Not at all. The exams contained complex edge cases that were never once mentioned.',
        3 => 'Honestly, nothing stands out of this course format. its very bad',
        4 => 'The entire curriculum structure and pacing model need an immediate overhaul.',
        5 => 'Incredibly frustrating and stressful. I had to self-teach nearly everything.',
        6 => 'No. Avoid this section unless you are already an expert in the subject.',
    ];

    // ─────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        // 0. Ensure the Official Questionnaire Template exists first
        $this->call(OfficialQuestionnaireSeeder::class);
        $template = SurveyTemplate::where('is_official', true)->first();

        if (!$template) {
            $this->command->error('Official Survey Template not found!');
            return;
        }

        // ── 1. Apply Question Weights ─────────────────────────────────────────
        DB::table('survey_template_questions')
            ->where('survey_template_id', $template->id)
            ->where('question_type', 'rating')
            ->update(['category_weight' => 20.00]);

        $questions = DB::table('survey_template_questions')
            ->where('survey_template_id', $template->id)
            ->get();

        // ── 2. Resolve role / subject / semester IDs ──────────────────────────
        $teacherRoleId = DB::table('roles')->where('name', 'teacher')->value('id') ?? 2;
        $studentRoleId = DB::table('roles')->where('name', 'student')->value('id') ?? 3;

        $subjectId  = DB::table('subjects')->value('id')
            ?? DB::table('subjects')->insertGetId(['name' => 'Sample Subject', 'code' => 'SUBJ101']);
        $semesterId = DB::table('semesters')->value('id')
            ?? DB::table('semesters')->insertGetId(['name' => '1st Semester 2026', 'is_active' => true]);

        // ── 3. Create Users ───────────────────────────────────────────────────
        $facultyUsers = [];
        $studentUsers = [];

        for ($i = 1; $i <= 5; $i++) {
            $name  = fake()->name();
            $email = strtolower(str_replace(' ', '', $name)) . $i . '@example.com';
            $user  = User::create([
                'user_id_number'       => fake()->numerify('########'),
                'name'                 => $name,
                'email'                => $email,
                'password'             => Hash::make('password'),
                'email_verified_at'    => now(),
                'must_change_password' => false,
            ]);
            DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => $teacherRoleId]);
            $facultyUsers[] = $user;
        }

        for ($i = 1; $i <= 15; $i++) {
            $name  = fake()->name();
            $email = strtolower(str_replace(' ', '', $name)) . $i . '@example.com';
            $user  = User::create([
                'user_id_number'       => fake()->numerify('########'),
                'name'                 => $name,
                'email'                => $email,
                'password'             => Hash::make('password'),
                'email_verified_at'    => now(),
                'must_change_password' => false,
            ]);
            DB::table('role_user')->insert(['user_id' => $user->id, 'role_id' => $studentRoleId]);
            $studentUsers[] = $user;
        }

        // ── 4. Survey Profile Config ──────────────────────────────────────────
        // Surveys 1–3 : unique responses (one responder persona per student, drawn from
        //               the matching sentiment bank).
        // Survey 4    : repeated positive — all 15 students share the same answers.
        // Survey 5    : repeated negative — all 15 students share the same answers.
        $surveyProfiles = [
            0 => ['label' => 'Positive (Unique)',           'sentiment' => 'positive',          'repeated' => false],
            1 => ['label' => 'Negative (Unique)',           'sentiment' => 'negative',          'repeated' => false],
            2 => ['label' => 'Neutral  (Unique)',           'sentiment' => 'neutral',           'repeated' => false],
            3 => ['label' => 'Repeated Positive (Cloned)',  'sentiment' => 'repeated_positive', 'repeated' => true],
            4 => ['label' => 'Repeated Negative (Cloned)',  'sentiment' => 'repeated_negative', 'repeated' => true],
        ];

        // ── 5. Generate Offerings, Attempts, and Responses ───────────────────
        foreach ($facultyUsers as $index => $faculty) {
            $profile   = $surveyProfiles[$index];
            $sentiment = $profile['sentiment'];
            $repeated  = $profile['repeated'];

            $offering = CourseOffering::create([
                'subject_id'   => $subjectId,
                'semester_id'  => $semesterId,
                'teacher_id'   => $faculty->id,
                'group_number' => $index + 1,
            ]);

            $survey = Survey::create([
                'offering_id'    => $offering->id,
                'created_by'     => $faculty->id,
                'template_id'    => $template->id,
                'target_role_id' => $studentRoleId,
                'title'          => "Evaluation for {$faculty->name} ({$profile['label']})",
                'description'    => "Simulation profile: {$profile['label']}",
                'is_active'      => true,
                'start_date'     => now()->subDays(2),
                'end_date'       => now()->addDays(7),
            ]);

            // Build the responder-key list once per survey.
            // Surveys 1–3: cycle through responder_1 … responder_15 (one per student).
            // Surveys 4–5: every student gets the same single repeated bank entry.
            $responderKeys = array_map(
                fn($n) => "responder_{$n}",
                range(1, count($studentUsers))
            );

            foreach ($studentUsers as $studentIndex => $student) {
                Enrollment::create([
                    'offering_id'        => $offering->id,
                    'student_id'         => $student->id,
                    'enrollment_type_id' => 1,
                ]);

                $attempt = SurveyAttempt::create([
                    'survey_id'        => $survey->id,
                    'student_id'       => $student->id,
                    'submitted_at'     => now(),
                    'notify_email'     => true,
                    'notify_dashboard' => true,
                ]);

                // Select this student's text bank.
                $textBank = $this->resolveTextBank($sentiment, $responderKeys[$studentIndex], $repeated);

                $textQuestionCounter = 0;

                foreach ($questions as $question) {
                    $scaleValue    = null;
                    $textResponse  = null;

                    if ($question->question_type === 'rating') {
                        $scaleValue = $this->resolveRating($sentiment);
                    } else {
                        $textResponse = $textBank[$textQuestionCounter]
                            ?? 'No feedback provided.';
                        $textQuestionCounter++;
                    }

                    DB::table('responses')->insert([
                        'id'                 => Str::ulid(),
                        'attempt_id'         => $attempt->id,
                        'survey_question_id' => $question->id,
                        'scale_value'        => $scaleValue,
                        'text_response'      => $textResponse,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                }
            }
        }

        $this->command->info('✓ Seeder complete.');
        $this->command->info('  Surveys 1–3 : unique per-student responses (positive / negative / neutral).');
        $this->command->info('  Survey 4    : repeated positive responses across all 15 students.');
        $this->command->info('  Survey 5    : repeated negative responses across all 15 students.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Return the text-answer array for a given student in a given survey run.
     *
     * @param  string  $sentiment      e.g. 'positive', 'negative', 'neutral',
     *                                 'repeated_positive', 'repeated_negative'
     * @param  string  $responderKey   e.g. 'responder_3'
     * @param  bool    $repeated       true → use the single cloned bank for every student
     * @return array<int, string>
     */
    private function resolveTextBank(string $sentiment, string $responderKey, bool $repeated): array
    {
        if ($repeated) {
            return $sentiment === 'repeated_positive'
                ? $this->repeatedPositiveTextBanks
                : $this->repeatedNegativeTextBanks;
        }

        return match ($sentiment) {
            'positive' => $this->positiveTextBanks[$responderKey] ?? $this->positiveTextBanks['responder_1'],
            'negative' => $this->negativeTextBanks[$responderKey] ?? $this->negativeTextBanks['responder_1'],
            'neutral'  => $this->neutralTextBanks[$responderKey]  ?? $this->neutralTextBanks['responder_1'],
            default    => $this->neutralTextBanks['responder_1'],
        };
    }

    /**
     * Resolve a rating scale value for a given sentiment.
     */
    private function resolveRating(string $sentiment): int
    {
        return match ($sentiment) {
            'positive'          => fake()->numberBetween(4, 5),
            'negative'          => fake()->numberBetween(1, 2),
            'neutral'           => 3,
            'repeated_positive' => fake()->numberBetween(4, 5),
            'repeated_negative' => fake()->numberBetween(1, 2),
            default             => fake()->numberBetween(1, 5),
        };
    }
}