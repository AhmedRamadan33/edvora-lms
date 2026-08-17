<?php

namespace Database\Seeders;

use App\Models\BankQuestion;
use App\Models\Course;
use App\Models\CourseTranslation;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class QuestionBankSeeder extends Seeder
{
    protected const DIFFICULTIES = ['easy', 'medium', 'hard'];

    public function run(): void
    {
        $course = $this->resolveCourse();

        if (! $course) {
            $this->command?->warn('QuestionBankSeeder: course "Mobile App Security" not found - skipping.');

            return;
        }

        foreach ($this->subjectsData() as $subjectName => $data) {
            $subject = Subject::query()->firstOrCreate([
                'course_id' => $course->id,
                'name' => $subjectName,
            ]);

            $this->seedMcqSingle($course, $subject, $data['mcq_single']);
            $this->seedTrueFalse($course, $subject, $data['true_false']);
            $this->seedMatching($course, $subject, $data['matching']);
            $this->seedFillBlank($course, $subject, $data['fill_blank']);
            $this->seedEssay($course, $subject, $data['essay']);
        }

        $this->command?->info('QuestionBankSeeder: seeded 3 subjects x 35 questions for "Mobile App Security".');
    }

    protected function resolveCourse(): ?Course
    {
        $translation = CourseTranslation::query()->where('title', 'Mobile App Security')->first();

        return $translation?->course;
    }

    protected function difficultyFor(int $index): string
    {
        return self::DIFFICULTIES[$index % 3];
    }

    protected function baseAttributes(Course $course, Subject $subject, string $type, string $question, int $index, int $points): array
    {
        return [
            'course_id' => $course->id,
            'subject_id' => $subject->id,
            'created_by' => $course->instructor_id,
            'type' => $type,
            'question' => $question,
            'difficulty' => $this->difficultyFor($index),
            'points' => $points,
            'is_active' => true,
            'sort_order' => $index + 1,
        ];
    }

    protected function seedMcqSingle(Course $course, Subject $subject, array $items): void
    {
        foreach ($items as $index => $item) {
            $bankQuestion = BankQuestion::query()->create(
                $this->baseAttributes($course, $subject, 'mcq_single', $item['text'], $index, 1)
            );

            foreach ($item['options'] as $optionIndex => $optionText) {
                $bankQuestion->choices()->create([
                    'text' => $optionText,
                    'is_correct' => $optionIndex === $item['correct'],
                    'sort_order' => $optionIndex + 1,
                ]);
            }
        }
    }

    protected function seedTrueFalse(Course $course, Subject $subject, array $items): void
    {
        foreach ($items as $index => $item) {
            $bankQuestion = BankQuestion::query()->create(
                $this->baseAttributes($course, $subject, 'true_false', $item['text'], $index, 1)
            );

            $bankQuestion->choices()->create(['text' => 'True', 'is_correct' => $item['answer'] === true, 'sort_order' => 1]);
            $bankQuestion->choices()->create(['text' => 'False', 'is_correct' => $item['answer'] === false, 'sort_order' => 2]);
        }
    }

    protected function seedMatching(Course $course, Subject $subject, array $items): void
    {
        foreach ($items as $index => $item) {
            $bankQuestion = BankQuestion::query()->create(
                $this->baseAttributes($course, $subject, 'matching', $item['text'], $index, 2)
            );

            $sort = 0;
            foreach ($item['pairs'] as $prompt => $match) {
                $bankQuestion->matches()->create([
                    'prompt_text' => $prompt,
                    'match_text' => $match,
                    'sort_order' => ++$sort,
                ]);
            }
        }
    }

    protected function seedFillBlank(Course $course, Subject $subject, array $items): void
    {
        foreach ($items as $index => $text) {
            BankQuestion::query()->create(
                $this->baseAttributes($course, $subject, 'fill_blank', $text, $index, 1)
            );
        }
    }

    protected function seedEssay(Course $course, Subject $subject, array $items): void
    {
        foreach ($items as $index => $text) {
            BankQuestion::query()->create(
                $this->baseAttributes($course, $subject, 'essay', $text, $index, 5)
            );
        }
    }

    protected function subjectsData(): array
    {
        return [
            'Authentication & Authorization' => [
                'mcq_single' => [
                    ['text' => "What is the recommended way to store user passwords in a mobile app's backend?", 'options' => ['Plain text', 'MD5 hash', 'Bcrypt/Argon2 hash', 'Base64 encoding'], 'correct' => 2],
                    ['text' => 'Which authentication factor category does a fingerprint scan belong to?', 'options' => ['Something you know', 'Something you have', 'Something you are', 'Somewhere you are'], 'correct' => 2],
                    ['text' => 'What does OAuth 2.0 primarily provide?', 'options' => ['Encryption', 'Authorization delegation', 'Data compression', 'Session storage'], 'correct' => 1],
                    ['text' => 'Which token type is typically short-lived and used to access protected resources?', 'options' => ['Refresh token', 'Access token', 'API key', 'Session cookie'], 'correct' => 1],
                    ['text' => 'What is the main risk of storing JWT tokens in unprotected local storage on mobile?', 'options' => ['Slower app performance', 'Vulnerability to token theft', 'Increased app size', 'Battery drain'], 'correct' => 1],
                    ['text' => 'Which of the following best describes multi-factor authentication (MFA)?', 'options' => ['Using the same password twice', 'Combining two or more independent credentials', 'Logging in from two devices', 'Resetting a password twice a year'], 'correct' => 1],
                    ['text' => 'What is biometric authentication primarily used to verify?', 'options' => ['Device location', 'Network speed', 'User identity', 'App version'], 'correct' => 2],
                ],
                'true_false' => [
                    ['text' => 'A session token should never expire.', 'answer' => false],
                    ['text' => 'Role-based access control (RBAC) restricts what actions a user can perform based on their assigned role.', 'answer' => true],
                    ['text' => "It is safe to hardcode an API secret key inside a mobile app's client-side code.", 'answer' => false],
                    ['text' => 'Biometric authentication alone is always sufficient for high-security banking apps.', 'answer' => false],
                    ['text' => 'OAuth 2.0 access tokens should be transmitted only over HTTPS.', 'answer' => true],
                    ['text' => 'A weak password policy increases the risk of brute-force attacks.', 'answer' => true],
                    ['text' => 'Refresh tokens should have a longer lifespan than access tokens.', 'answer' => true],
                ],
                'matching' => [
                    ['text' => 'Match each authentication factor to its category.', 'pairs' => ['Password' => 'Something you know', 'Security key' => 'Something you have', 'Fingerprint' => 'Something you are', 'Location' => 'Somewhere you are']],
                    ['text' => 'Match each authorization concept to its description.', 'pairs' => ['RBAC' => 'Access based on user role', 'ABAC' => 'Access based on attributes', 'ACL' => 'Access based on an explicit list', 'MAC' => 'Access based on system-enforced policy']],
                    ['text' => 'Match each token type to its purpose.', 'pairs' => ['Access token' => 'Grants access to a resource', 'Refresh token' => 'Obtains a new access token', 'ID token' => 'Carries user identity claims', 'API key' => 'Identifies the calling application']],
                    ['text' => 'Match each OAuth 2.0 grant type to its typical use case.', 'pairs' => ['Authorization Code' => 'Mobile and web apps with a backend', 'Client Credentials' => 'Server-to-server communication', 'PKCE' => 'Public clients without a client secret', 'Implicit' => 'Legacy browser-based apps']],
                    ['text' => 'Match each security control to its function.', 'pairs' => ['Rate limiting' => 'Prevents brute-force login attempts', 'Account lockout' => 'Blocks access after failed attempts', 'CAPTCHA' => 'Distinguishes humans from bots', 'MFA' => 'Adds an extra verification step']],
                    ['text' => 'Match each biometric method to its scanning target.', 'pairs' => ['Face ID' => 'Facial features', 'Touch ID' => 'Fingerprint pattern', 'Iris scan' => 'Eye iris pattern', 'Voice recognition' => 'Vocal characteristics']],
                    ['text' => 'Match each session concept to its meaning.', 'pairs' => ['Session timeout' => 'Automatic logout after inactivity', 'Session fixation' => 'Attacker forces a known session ID', 'Session hijacking' => 'Stealing an active session token', 'Token revocation' => 'Invalidating a token before expiry']],
                ],
                'fill_blank' => [
                    "The process of verifying a user's identity before granting access is called ______.",
                    'A ______ token is used to obtain a new access token without asking the user to log in again.',
                    '______ is the practice of granting users only the permissions they need to perform their job.',
                    'The OWASP Mobile Top 10 category for weak server-side controls is called ______.',
                    'A ______ attack tries many password combinations until the correct one is found.',
                    '______ authentication combines two or more independent credentials to verify identity.',
                    'A JSON-based, digitally signed token used to securely transmit identity claims is called a ______.',
                ],
                'essay' => [
                    "Explain the difference between authentication and authorization, and give a mobile app example of each.",
                    "Describe how OAuth 2.0's Authorization Code flow with PKCE protects mobile apps that cannot securely store a client secret.",
                    'Discuss the security trade-offs between storing session tokens in secure storage versus local/shared storage on a mobile device.',
                    'Explain why biometric authentication should typically be paired with a fallback mechanism, and describe one appropriate fallback.',
                    "Describe how role-based access control (RBAC) could be implemented for an app with 'student', 'instructor', and 'admin' roles.",
                    'Explain the risks of using long-lived access tokens without a refresh mechanism, and how short-lived tokens mitigate them.',
                    'Discuss best practices for handling failed login attempts to prevent brute-force and credential-stuffing attacks.',
                ],
            ],
            'Secure Data Storage' => [
                'mcq_single' => [
                    ['text' => 'Where should sensitive data like auth tokens be stored on iOS?', 'options' => ['UserDefaults', 'Keychain', 'Plist file', 'App bundle'], 'correct' => 1],
                    ['text' => 'What is the Android equivalent secure storage mechanism for sensitive credentials?', 'options' => ['SharedPreferences (plain)', 'Android Keystore', 'External storage', 'SQLite without encryption'], 'correct' => 1],
                    ['text' => 'Which encryption approach is most appropriate for encrypting a local SQLite database on mobile?', 'options' => ['No encryption needed', 'SQLCipher / AES-based encryption', 'Base64 encoding', 'ROT13'], 'correct' => 1],
                    ['text' => "What does 'data at rest' refer to?", 'options' => ['Data currently being transmitted', 'Data stored on a device or server', 'Data in RAM only', 'Deleted data'], 'correct' => 1],
                    ['text' => "Why should you avoid storing sensitive data in a mobile app's log files?", 'options' => ['Logs slow down the app', 'Logs may be readable by other apps or exposed in crash reports', 'Logs use too much storage', 'Logs are always encrypted anyway'], 'correct' => 1],
                    ['text' => 'What is the purpose of data masking?', 'options' => ['Compressing data for storage', 'Hiding sensitive parts of data from unauthorized view', 'Backing up data automatically', 'Converting data formats'], 'correct' => 1],
                    ['text' => 'Which of these is considered Personally Identifiable Information (PII)?', 'options' => ['App version number', 'National ID number', 'Screen resolution', 'Battery level'], 'correct' => 1],
                ],
                'true_false' => [
                    ['text' => 'Storing credit card numbers in plain text in a local database is acceptable if the device has a screen lock.', 'answer' => false],
                    ['text' => 'Encrypting data at rest protects it even if the device storage is physically accessed.', 'answer' => true],
                    ['text' => 'The iOS Keychain is accessible to any app installed on the device by default.', 'answer' => false],
                    ['text' => "Backups of a mobile app's data can also expose sensitive information if not properly protected.", 'answer' => true],
                    ['text' => 'SharedPreferences on Android automatically encrypts all stored values.', 'answer' => false],
                    ['text' => 'Data minimization means collecting only the data that is strictly necessary.', 'answer' => true],
                    ['text' => 'It is a good practice to clear sensitive data from memory as soon as it is no longer needed.', 'answer' => true],
                ],
                'matching' => [
                    ['text' => 'Match each platform to its secure storage mechanism.', 'pairs' => ['iOS' => 'Keychain', 'Android' => 'Keystore', 'Web' => 'HttpOnly secure cookie', 'Desktop' => 'OS credential manager']],
                    ['text' => 'Match each data state to its example.', 'pairs' => ['Data at rest' => 'Database file on disk', 'Data in transit' => 'API request over HTTPS', 'Data in use' => 'Data currently in app memory', 'Data in backup' => 'Cloud backup snapshot']],
                    ['text' => 'Match each encryption term to its definition.', 'pairs' => ['Symmetric encryption' => 'Same key for encryption and decryption', 'Asymmetric encryption' => 'Public and private key pair', 'Hashing' => 'One-way irreversible transformation', 'Salting' => 'Random data added before hashing']],
                    ['text' => 'Match each storage risk to its mitigation.', 'pairs' => ['Unencrypted database' => 'Use SQLCipher', 'Sensitive logs' => 'Disable verbose logging in production', 'Insecure backups' => 'Exclude sensitive files from backup', 'Rooted/jailbroken device' => 'Add root/jailbreak detection']],
                    ['text' => 'Match each compliance term to its focus.', 'pairs' => ['GDPR' => 'EU personal data protection', 'PCI DSS' => 'Payment card data security', 'HIPAA' => 'Healthcare data privacy', 'CCPA' => 'California consumer privacy']],
                    ['text' => 'Match each data classification to an example.', 'pairs' => ['Public' => 'Marketing brochure', 'Internal' => 'Employee handbook', 'Confidential' => 'Customer contract', 'Restricted' => 'Encryption keys']],
                    ['text' => 'Match each secure coding practice to its purpose.', 'pairs' => ['Input validation' => 'Rejects malformed or malicious input', 'Output encoding' => 'Prevents injection in rendered output', 'Least privilege' => "Limits access to what's necessary", 'Secure defaults' => 'Ships the app safe out of the box']],
                ],
                'fill_blank' => [
                    'The practice of converting readable data into an unreadable format using a key is called ______.',
                    'On iOS, the secure, encrypted storage container for small sensitive items like tokens is called the ______.',
                    '______ data refers to information stored on a device or server, as opposed to data being transmitted.',
                    'A ______ attack occurs when an attacker gains physical access to a lost or stolen device to extract stored data.',
                    '______ is the principle of collecting and retaining only the data that is strictly necessary.',
                    'SQLCipher is commonly used to add ______ to a local SQLite database.',
                    'Personally identifiable information is commonly abbreviated as ______.',
                ],
                'essay' => [
                    'Explain the difference between data at rest, data in transit, and data in use, with a mobile example of each.',
                    'Describe why storing sensitive tokens in Keychain/Keystore is more secure than storing them in plain preference files.',
                    'Discuss the risks of including sensitive data in application logs and crash reports, and how to prevent it.',
                    'Explain how encrypted local backups can still leak sensitive data, and what developers can do to prevent this.',
                    "Describe the concept of data minimization and how it reduces a mobile app's attack surface.",
                    'Explain the difference between symmetric and asymmetric encryption, and when each is appropriate for a mobile app.',
                    'Discuss how root/jailbreak detection can help protect sensitive data stored on a compromised device.',
                ],
            ],
            'Network & API Security' => [
                'mcq_single' => [
                    ['text' => 'What is the primary purpose of TLS/SSL in mobile app communication?', 'options' => ['Compress network traffic', 'Encrypt data in transit', 'Cache API responses', 'Reduce battery usage'], 'correct' => 1],
                    ['text' => 'What is certificate pinning designed to prevent?', 'options' => ['Slow network requests', 'Man-in-the-middle attacks', 'App crashes', 'Data duplication'], 'correct' => 1],
                    ['text' => 'Which HTTP header helps protect against cross-site scripting when an app also serves web content?', 'options' => ['Content-Security-Policy', 'Content-Length', 'User-Agent', 'Accept-Language'], 'correct' => 0],
                    ['text' => 'What does API rate limiting primarily protect against?', 'options' => ['Slow devices', 'Abuse and denial-of-service attempts', 'Data corruption', 'UI rendering issues'], 'correct' => 1],
                    ['text' => 'Why is it risky to disable SSL/TLS certificate validation during development and forget to re-enable it?', 'options' => ['It only affects app size', 'It allows man-in-the-middle attacks in production', 'It slows down builds', 'It has no security impact'], 'correct' => 1],
                    ['text' => 'What is the purpose of an API gateway in a secure architecture?', 'options' => ['Store user passwords', 'Centralize authentication, rate limiting, and routing', 'Render the mobile UI', 'Compile the mobile app'], 'correct' => 1],
                    ['text' => 'Which protocol version is considered outdated and insecure for encrypting network traffic?', 'options' => ['TLS 1.3', 'TLS 1.2', 'SSL 3.0', 'HTTPS'], 'correct' => 2],
                ],
                'true_false' => [
                    ['text' => 'Certificate pinning ties an app to a specific server certificate or public key to prevent MITM attacks.', 'answer' => true],
                    ['text' => 'Sending API requests over plain HTTP is as secure as HTTPS as long as the payload is small.', 'answer' => false],
                    ['text' => 'Rate limiting can help mitigate brute-force and denial-of-service attacks against an API.', 'answer' => true],
                    ['text' => 'Mobile apps should trust any SSL certificate presented by the server without validation.', 'answer' => false],
                    ['text' => "An exposed API key inside a mobile app's binary can be extracted through reverse engineering.", 'answer' => true],
                    ['text' => 'CORS (Cross-Origin Resource Sharing) policies are only relevant to web browsers, never to mobile app security.', 'answer' => false],
                    ['text' => 'Using HTTPS everywhere protects data in transit but does not protect data already stored on the device.', 'answer' => true],
                ],
                'matching' => [
                    ['text' => 'Match each network security term to its definition.', 'pairs' => ['TLS' => 'Encrypts data in transit', 'Certificate pinning' => 'Trusts only a known certificate', 'MITM' => 'Attacker intercepts communication', 'API gateway' => 'Centralizes API traffic control']],
                    ['text' => 'Match each HTTP status code to its meaning.', 'pairs' => ['401' => 'Unauthorized', '403' => 'Forbidden', '429' => 'Too many requests', '500' => 'Internal server error']],
                    ['text' => 'Match each attack type to its description.', 'pairs' => ['Man-in-the-middle' => 'Intercepting communication between two parties', 'Replay attack' => 'Resending a captured valid request', 'DDoS' => 'Overwhelming a service with traffic', 'Packet sniffing' => 'Capturing network traffic passively']],
                    ['text' => 'Match each API security control to its purpose.', 'pairs' => ['Rate limiting' => 'Throttles excessive requests', 'API key' => 'Identifies the calling client', 'OAuth token' => 'Authorizes access to resources', 'Input validation' => 'Rejects malformed requests']],
                    ['text' => 'Match each network layer concept to its example.', 'pairs' => ['Application layer' => 'HTTPS request', 'Transport layer' => 'TCP connection', 'Network layer' => 'IP routing', 'Physical layer' => 'Wi-Fi radio signal']],
                    ['text' => 'Match each secure communication protocol to its typical use.', 'pairs' => ['HTTPS' => 'Secure web/API traffic', 'WSS' => 'Secure WebSocket traffic', 'SFTP' => 'Secure file transfer', 'VPN' => 'Secure private network tunnel']],
                    ['text' => 'Match each mobile network risk to its mitigation.', 'pairs' => ['Public Wi-Fi snooping' => 'Enforce HTTPS and certificate pinning', 'Fake access points' => 'Warn users and validate certificates', 'DNS spoofing' => 'Use DNS over HTTPS', 'API abuse' => 'Apply rate limiting and authentication']],
                ],
                'fill_blank' => [
                    'The security practice of trusting only a specific server certificate or public key is called certificate ______.',
                    "______ is the standard protocol used to encrypt data transmitted between a mobile app and a server.",
                    'A ______ attack occurs when an attacker secretly intercepts and possibly alters communication between two parties.',
                    '______ limiting restricts how many requests a client can make to an API within a given time window.',
                    'An ______ acts as a single entry point that manages authentication, routing, and traffic control for backend services.',
                    'HTTP status code ______ indicates that a request lacked valid authentication credentials.',
                    'Using a ______ encrypts all network traffic between a device and a private network over an untrusted network.',
                ],
                'essay' => [
                    'Explain how certificate pinning works and discuss one operational risk it introduces, such as certificate rotation.',
                    "Describe the steps you would take to secure a mobile app's communication with its backend API.",
                    "Discuss why hardcoding API keys inside a mobile app's binary is risky, and suggest a more secure alternative.",
                    'Explain how a man-in-the-middle attack could be carried out against a mobile app on public Wi-Fi, and how to prevent it.',
                    'Describe the role of an API gateway in a microservices architecture and how it improves security.',
                    'Explain the difference between authentication and rate limiting as API protection mechanisms, and why both are needed.',
                    'Discuss the security implications of using outdated TLS versions and how to enforce modern protocol versions in a mobile app.',
                ],
            ],
        ];
    }
}
