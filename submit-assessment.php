<?php
header('Content-Type: application/json');

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);
$profile = preg_replace('/[^a-z]/', '', strtolower($input['profile'] ?? ''));

if (!$email) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

// --- Profile definitions ---
$profiles = [
    'hormonal' => [
        'name'    => 'The Hormonal Rebuilder',
        'tagline' => 'Your body is asking for balance, not more band-aids.',
        'desc'    => 'Your answers point to hormonal imbalance as the root cause. Whether it\'s your cycle, your skin, your mood, or your energy — the solution lies in addressing what\'s disrupting your internal chemistry, not managing symptoms one by one.',
        'focus'   => [
            'Hormonal mapping & Ayurvedic constitution',
            'Nutrition for cycle syncing',
            'Detox pathways & liver support',
            'Reducing hormonal triggers naturally',
        ],
        'cta'     => 'Book a Hormonal Health Consultation',
    ],
    'metabolic' => [
        'name'    => 'The Metabolic Transformer',
        'tagline' => 'Your metabolism isn\'t broken. It\'s waiting for the right signals.',
        'desc'    => 'Your profile shows classic signs of metabolic imbalance — the kind that builds quietly over years of the wrong diet, stress, and overmedication. The good news: metabolism responds powerfully to the right protocol.',
        'focus'   => [
            'Metabolic reset through food timing',
            'Blood sugar & cholesterol naturally',
            'Reducing dependence on medication',
            'Sustainable weight & energy balance',
        ],
        'cta'     => 'Book a Metabolic Health Consultation',
    ],
    'gut' => [
        'name'    => 'The Gut Healer',
        'tagline' => 'All healing begins in the gut. Yours is ready.',
        'desc'    => 'Your digestive symptoms are signals, not inconveniences. Bloating, acidity, and irregular digestion are your gut\'s way of asking for help. Heal the gut and you\'ll be amazed at how much else resolves on its own.',
        'focus'   => [
            'Gut microbiome restoration',
            'Anti-inflammatory nutrition plan',
            'Identifying food sensitivities',
            'Naturopathic digestive healing',
        ],
        'cta'     => 'Book a Digestive Health Consultation',
    ],
    'vitality' => [
        'name'    => 'The Vitality Seeker',
        'tagline' => 'Exhaustion is not your baseline. Let\'s find your energy.',
        'desc'    => 'Chronic fatigue, poor sleep, and low energy are not just lifestyle problems. They are signs that your body\'s recovery systems are overwhelmed. A targeted protocol can bring your vitality back, sustainably.',
        'focus'   => [
            'Circadian rhythm reset',
            'Adrenal & cortisol support',
            'Sleep architecture repair',
            'Yoga & movement for energy restoration',
        ],
        'cta'     => 'Book an Energy & Vitality Consultation',
    ],
    'explorer' => [
        'name'    => 'The Wellness Explorer',
        'tagline' => 'You\'re asking the right questions. That\'s where healing begins.',
        'desc'    => 'You\'re at the beginning of your wellness journey, and that\'s a powerful place to be. You haven\'t been conditioned by years of failed approaches. Together, we can build the right foundation from the very start.',
        'focus'   => [
            'Foundational health assessment',
            'Understanding your body\'s signals',
            'Building sustainable daily habits',
            'Preventive naturopathic care',
        ],
        'cta'     => 'Book a Foundational Wellness Consultation',
    ],
];

// Fallback if profile key is unexpected
if (!isset($profiles[$profile])) {
    $profile = 'explorer';
}
$p = $profiles[$profile];

// --- Build Q&A summary for Richa ---
$q_labels = [
    1  => 'Primary concern',
    2  => 'Morning routine',
    3  => 'Gut symptoms',
    4  => 'Food relationship',
    5  => 'Hormonal symptoms',
    6  => 'Sleep quality',
    7  => 'Medication duration',
    8  => 'Previous attempts',
    9  => 'Dream outcome',
    10 => 'Commitment level',
];

$qa_lines = '';
for ($i = 1; $i <= 10; $i++) {
    $label = $q_labels[$i] ?? "Q$i";
    $val   = $input[$i] ?? 'N/A';
    $qa_lines .= "  Q{$i} — {$label}: {$val}\n";
}

$focus_list = implode("\n", array_map(fn($f) => "  * $f", $p['focus']));

// --- Email to user ---
$user_subject = 'Your Free Health Profile — Dr. Richa Arora';
$user_body    = <<<EOT
Hi there,

Thank you for completing your free health assessment with Dr. Richa Arora.

Based on your answers, your health profile is:

{$p['name']}
"{$p['tagline']}"

{$p['desc']}

Your Personalised Focus Areas:
{$focus_list}

--------
What's Next?

{$p['cta']}

Book a consultation with Dr. Richa and begin your root-cause healing journey:
https://richarora.co.in/contact.html

With warmth,
Dr. Richa Arora
Integrative Wellness | Naturopathy | Ayurveda

---
The Longevity Project by Dr. Richa Arora
richarora.co.in | richa24.ra@gmail.com
EOT;

// --- Email to Richa ---
$richa_subject = "New Assessment: {$p['name']} ({$email})";
$richa_body    = <<<EOT
A new health assessment has been submitted on richarora.co.in.

Email:   {$email}
Profile: {$p['name']}

Responses:
{$qa_lines}
---
Submitted via richarora.co.in/submit-assessment.php
EOT;

// --- Send emails ---
$headers  = "From: Dr. Richa Arora <no-reply@richarora.co.in>\r\n";
$headers .= "Reply-To: richa24.ra@gmail.com\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$sent_user  = mail($email, $user_subject, $user_body, $headers);
$sent_richa = mail('richa24.ra@gmail.com', $richa_subject, $richa_body, $headers);

echo json_encode([
    'success'     => true,
    'profile'     => $profile,
    'sent_user'   => $sent_user,
    'sent_richa'  => $sent_richa,
]);
