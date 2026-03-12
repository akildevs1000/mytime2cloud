<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class AITrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'days',
        'run_time',
        'frequency',
        'weekdays',
        'month_day',
        'message_hash',
        'fingerprint',
        'company_id'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function buildPrompt($message)
    {
        $prompt = "
Convert this message into JSON.

Fields:
type: absent|late|early|present
days: integer
time: HH:MM
frequency: daily|weekly|monthly
weekdays: optional array
month_day: optional integer

Message: {$message}

Return ONLY JSON.
";

        return $prompt;
    }


    public static function parseMessage(string $message): ?array
    {
        $types = ['absent', 'late', 'early_leave', 'present'];
        $type = null;

        foreach ($types as $t) {
            if (stripos($message, $t) !== false) {
                $type = $t;
                break;
            }
        }

        // Minimal validation: type + days/time
        $hasDays = preg_match('/\d+/', $message);
        $hasTime = preg_match('/(\d+)\s*(am|pm)/i', $message);

        if (!$type || (!$hasDays && !$hasTime)) {
            return null; // invalid/unrelated message
        }

        // Detect days
        if (preg_match('/(\d+)\s+(consecutive\s+)?(day|days)/i', $message, $daysMatch)) {
            $days = (int)$daysMatch[1];
        } else {
            $days = 1;
        }

        // Detect time
        if (preg_match('/(\d+)\s*(am|pm)/i', $message, $timeMatch)) {
            $time = date('H:i', strtotime("{$timeMatch[1]} {$timeMatch[2]}"));
        } else {
            $time = '06:00';
        }

        // Detect weekday
        $weekday = null;
        if (preg_match('/Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday/i', $message, $dayMatch)) {
            $weekday = substr($dayMatch[0], 0, 3);
        }

        return [
            'type' => $type,
            'days' => $days,
            'run_time' => $time,
            'weekday' => $weekday
        ];
    }

    public static function generateFingerprint(array $parsedData): string
    {
        return strtolower("{$parsedData['type']}_{$parsedData['days']}_weekly_{$parsedData['weekday']}_{$parsedData['run_time']}");
    }

    public static function callAI(string $message): ?array
    {
        $response = Http::withHeaders([
            'x-api-key' => env('CLAUDE_API_KEY'),
            'anthropic-version' => '2023-06-01'
        ])->post('https://api.anthropic.com/v1/messages', [
            "model" => "claude-3-sonnet-20240229",
            "max_tokens" => 200,
            "messages" => [
                [
                    "role" => "user",
                    "content" => self::buildPrompt($message)
                ]
            ]
        ]);

        $json = $response->json();
        $content = $json['content'][0]['text'] ?? '{}';
        $content = preg_replace('/```json|```/', '', trim($content));

        return json_decode($content, true);
    }

    public static function createFromMessage(string $message, int $companyId = 2): ?self
    {
        $hash = md5(strtolower(trim($message)));

        $parsed = self::parseMessage($message);
        if (!$parsed) {
            return null; // invalid/unrelated message
        }

        $fingerprint = self::generateFingerprint($parsed);

        // Check for duplicates
        $exists = self::where('message_hash', $hash)
            ->orWhere('fingerprint', $fingerprint)
            ->exists();

        if ($exists) {
            return null; // already exists
        }

        $aiData = self::callAI($message) ?? [];

        return self::create([
            'type' => $aiData['type'] ?? $parsed['type'],
            'days' => $aiData['days'] ?? $parsed['days'],
            'run_time' => $aiData['time'] ?? $parsed['run_time'],
            'frequency' => $aiData['frequency'] ?? 'daily',
            'weekdays' => isset($aiData['weekdays']) ? implode(',', $aiData['weekdays']) : $parsed['weekday'],
            'month_day' => $aiData['month_day'] ?? null,
            'message_hash' => $hash,
            'fingerprint' => $fingerprint,
            'company_id' => $companyId
        ]);
    }
}
