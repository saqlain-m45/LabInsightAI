<?php
/**
 * LabInsight AI — Gemini AI Explanation Service
 * Step 4: Evidence Layer & Gemini AI Explanation ("Evidence Grounds -> AI Explains")
 */

require_once __DIR__ . '/../config/ai_config.php';

class GeminiExplanationService {

    /**
     * Generates a grounded AI explanation using Google's Gemini API
     * 
     * @param array $rulesResult Evaluation array from CBCRulesEngine
     * @param array $patternResult Pattern array from CBCPatternEngine
     * @param string|null $customApiKey Optional user-supplied Gemini API key
     * @return array Explanation results including evidence grounds and AI text
     */
    public static function generateExplanation(array $rulesResult, array $patternResult, $customApiKey = null) {
        $apiKey = !empty($customApiKey) ? trim($customApiKey) : (defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '');

        // 1. Build Transparent Evidence Layer (Evidence Grounds)
        $evidenceGrounds = self::buildEvidenceGrounds($rulesResult, $patternResult);

        // 2. If no API key provided or environment empty, return deterministic grounded fallback
        if (empty($apiKey)) {
            $fallbackExplanation = self::generateGroundedFallback($rulesResult, $patternResult, $evidenceGrounds);
            $fallbackExplanation['ai_source'] = 'Grounded Rule Engine (Offline/Fallback Mode - No API Key Provided)';
            return [
                'success' => true,
                'evidence_grounds' => $evidenceGrounds,
                'explanation' => $fallbackExplanation
            ];
        }

        // 3. Construct System Prompt for Gemini API
        $prompt = self::buildGeminiPrompt($rulesResult, $patternResult, $evidenceGrounds);

        // 4. Send cURL request to Google Gemini API
        $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent?key=' . urlencode($apiKey);

        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2, // Low temperature for high factual grounding
                'maxOutputTokens' => 2048
            ]
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200 || empty($response)) {
            // API call failed -> Fallback to grounded deterministic response
            $fallbackExplanation = self::generateGroundedFallback($rulesResult, $patternResult, $evidenceGrounds);
            $fallbackExplanation['ai_source'] = 'Grounded Rule Engine (Fallback due to Gemini API Connection Error)';
            $fallbackExplanation['api_notice'] = 'Gemini API call failed (HTTP ' . $httpCode . '). Fallback explanation generated automatically.';
            return [
                'success' => true,
                'evidence_grounds' => $evidenceGrounds,
                'explanation' => $fallbackExplanation
            ];
        }

        $responseData = json_decode($response, true);
        $rawText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($rawText)) {
            $fallbackExplanation = self::generateGroundedFallback($rulesResult, $patternResult, $evidenceGrounds);
            $fallbackExplanation['ai_source'] = 'Grounded Rule Engine (Fallback - Empty Gemini Response)';
            return [
                'success' => true,
                'evidence_grounds' => $evidenceGrounds,
                'explanation' => $fallbackExplanation
            ];
        }

        $parsedExplanation = self::parseGeminiResponse($rawText, $rulesResult, $patternResult, $evidenceGrounds);
        $parsedExplanation['ai_source'] = 'Google Gemini AI (Model: ' . GEMINI_MODEL . ')';

        return [
            'success' => true,
            'evidence_grounds' => $evidenceGrounds,
            'explanation' => $parsedExplanation
        ];
    }

    /**
     * Builds transparent evidence citation payload
     */
    private static function buildEvidenceGrounds(array $rulesResult, array $patternResult) {
        $patient = $rulesResult['patient'];
        $params = $rulesResult['parameters'];
        $patterns = $patternResult['patterns'] ?? [];

        $flaggedRules = [];
        foreach ($params as $key => $p) {
            if ($p['status'] !== 'NORMAL') {
                $flaggedRules[] = [
                    'parameter' => $p['name'],
                    'value' => $p['value'] . ' ' . $p['unit'],
                    'status' => $p['status'],
                    'ref_range' => $p['range_display'] . ' ' . $p['unit'],
                    'explanation' => $p['message']
                ];
            }
        }

        $detectedPatternCitations = [];
        foreach ($patterns as $pat) {
            $detectedPatternCitations[] = [
                'title' => $pat['title'],
                'category' => $pat['category'],
                'findings' => implode(', ', $pat['connected_findings']),
                'logic' => $pat['pattern_logic']
            ];
        }

        return [
            'patient_info' => $patient['age'] . ' y/o ' . $patient['sex'],
            'total_flagged' => count($flaggedRules),
            'flagged_rules' => $flaggedRules,
            'detected_patterns' => $detectedPatternCitations
        ];
    }

    /**
     * Builds structured prompt for Gemini API
     */
    private static function buildGeminiPrompt(array $rulesResult, array $patternResult, array $evidence) {
        $patient = $rulesResult['patient'];
        $paramsStr = json_encode($rulesResult['parameters'], JSON_PRETTY_PRINT);
        $patternsStr = json_encode($patternResult['patterns'], JSON_PRETTY_PRINT);

        return <<<EOT
You are LabInsight AI, an educational laboratory result interpretation assistant.
Your goal is to explain calculated Complete Blood Count (CBC) findings to a patient in clear, reassuring, patient-friendly language.

CRITICAL MEDICAL SAFETY & GROUNDING RULES:
1. You MUST NOT make medical diagnoses, prescribe treatments, or act as a physician.
2. You MUST stay strictly grounded in the calculated evidence provided below. Do not invent unverified findings.
3. Keep tone empathetic, clear, educational, and structured.

PATIENT CONTEXT:
- Age: {$patient['age']} years
- Sex: {$patient['sex']}

CALCULATED LABORATORY RULES EVIDENCE:
{$paramsStr}

DETECTED LABORATORY PATTERNS EVIDENCE:
{$patternsStr}

Please provide your response strictly formatted as a valid JSON object with the following 4 keys:
{
  "summary": "Warm, educational 2-3 sentence overview of the CBC results.",
  "parameter_explanations": ["Bullet point explaining parameter 1", "Bullet point explaining parameter 2"],
  "pattern_context": "Clear explanation of why detected patterns were identified based on the connected lab values.",
  "questions_for_doctor": ["Question 1 to ask physician", "Question 2 to ask physician", "Question 3 to ask physician"]
}
Only output the JSON object.
EOT;
    }

    /**
     * Parses Gemini API JSON response
     */
    private static function parseGeminiResponse($rawText, array $rulesResult, array $patternResult, array $evidence) {
        // Extract JSON structure using regex pattern match
        $data = null;
        if (preg_match('/\{[\s\S]*\}/', $rawText, $matches)) {
            $data = json_decode($matches[0], true);
        }

        if (!$data || !is_array($data)) {
            // Strip markdown code fences if present as fallback
            $cleanJson = preg_replace('/^```json\s*|\s*```$/i', '', trim($rawText));
            $data = json_decode($cleanJson, true);
        }

        if (is_array($data) && isset($data['summary'])) {
            return [
                'summary' => $data['summary'],
                'parameter_explanations' => is_array($data['parameter_explanations'] ?? null) ? $data['parameter_explanations'] : [$data['parameter_explanations'] ?? 'Calculated CBC parameters evaluated.'],
                'pattern_context' => $data['pattern_context'] ?? '',
                'questions_for_doctor' => is_array($data['questions_for_doctor'] ?? null) ? $data['questions_for_doctor'] : [$data['questions_for_doctor'] ?? 'What do these results mean for my health?']
            ];
        }

        // If JSON parsing failed, use raw text summary
        return [
            'summary' => $rawText,
            'parameter_explanations' => ['Calculated CBC parameters evaluated successfully.'],
            'pattern_context' => 'Pattern connections derived from calculated reference ranges.',
            'questions_for_doctor' => [
                'What do these demonstration CBC laboratory results mean for my general health?',
                'Should any follow-up tests be considered based on these findings?'
            ]
        ];
    }


    /**
     * Generates a 100% deterministic grounded explanation when API Key is missing or unavailable
     */
    public static function generateGroundedFallback(array $rulesResult, array $patternResult, array $evidence) {
        $patient = $rulesResult['patient'];
        $summary = $rulesResult['summary'];
        $patterns = $patternResult['patterns'] ?? [];

        if ($summary['flagged'] === 0) {
            $summaryText = "Your Complete Blood Count (CBC) values fall within the demonstration reference ranges for a " . $patient['age'] . "-year-old " . strtolower($patient['sex']) . ". This indicates balanced cell counts across red cells, white cells, and platelets.";
        } else {
            $summaryText = "Your CBC analysis identifies " . $summary['flagged'] . " parameter(s) outside demonstration reference ranges (" . $summary['low'] . " Low, " . $summary['high'] . " High). These findings have been connected into structured laboratory patterns to help you understand your results.";
        }

        $paramExpls = [];
        foreach ($rulesResult['parameters'] as $p) {
            if ($p['status'] !== 'NORMAL') {
                $paramExpls[] = sprintf(
                    "<strong>%s (%s %s):</strong> Marked as <strong>%s</strong> (Demonstration reference range: %s %s). %s",
                    $p['name'],
                    $p['value'],
                    $p['unit'],
                    $p['status'],
                    $p['range_display'],
                    $p['unit'],
                    $p['message']
                );
            }
        }

        if (empty($paramExpls)) {
            $paramExpls[] = "All 8 CBC test parameters (Hemoglobin, WBC, RBC, Platelets, MCV, MCH, MCHC, and RDW) are within standard demonstration limits.";
        }

        $patternContexts = [];
        foreach ($patterns as $pat) {
            $patternContexts[] = sprintf("<strong>%s:</strong> %s", $pat['title'], $pat['description']);
        }
        $patternContextText = implode('<br><br>', $patternContexts);

        $doctorQuestions = [
            "What could be causing the specific variations observed in my CBC results?",
            "Are there any additional blood tests or nutritional evaluations recommended based on these findings?",
            "How do these demonstration reference ranges compare to your clinic's laboratory standards?"
        ];

        return [
            'summary' => $summaryText,
            'parameter_explanations' => $paramExpls,
            'pattern_context' => $patternContextText,
            'questions_for_doctor' => $doctorQuestions
        ];
    }
}
