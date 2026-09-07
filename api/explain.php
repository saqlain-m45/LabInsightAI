<?php
/**
 * LabInsight AI — Evidence Layer & Gemini AI Explanation API Endpoint
 * POST /api/explain.php
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/rules_engine.php';
require_once __DIR__ . '/../includes/pattern_engine.php';
require_once __DIR__ . '/../includes/ai_explain.php';

// Accept JSON body or POST form data
$inputRaw = file_get_contents('php://input');
$inputData = json_decode($inputRaw, true);

if (!$inputData || !is_array($inputData)) {
    $inputData = $_POST;
}

// Extract Patient Info & Custom Gemini API Key if provided
$age = isset($inputData['patient_age']) ? $inputData['patient_age'] : null;
$sex = isset($inputData['patient_sex']) ? $inputData['patient_sex'] : null;
$customApiKey = isset($inputData['gemini_api_key']) ? trim($inputData['gemini_api_key']) : null;

// Validate basic inputs
if ($age === null || $age === '' || !is_numeric($age) || floatval($age) <= 0 || floatval($age) > 120) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Valid patient age between 1 and 120 is required.'
    ]);
    exit;
}

if (!$sex || !in_array($sex, ['Male', 'Female'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Patient sex must be selected (Male or Female).'
    ]);
    exit;
}

// Extract 8 CBC parameters
$testKeys = ['hemoglobin', 'wbc', 'rbc', 'platelets', 'mcv', 'mch', 'mchc', 'rdw'];
$testValues = [];
$missingOrInvalid = [];

foreach ($testKeys as $key) {
    if (!isset($inputData[$key]) || $inputData[$key] === '' || !is_numeric($inputData[$key])) {
        $missingOrInvalid[] = $key;
    } else {
        $val = floatval($inputData[$key]);
        if ($val < 0) {
            $missingOrInvalid[] = $key;
        } else {
            $testValues[$key] = $val;
        }
    }
}

if (count($missingOrInvalid) > 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'All 8 CBC test parameters must be non-negative numeric values.',
        'invalid_fields' => $missingOrInvalid
    ]);
    exit;
}

// 1. Step 2: Deterministic Rule Calculation
$rulesResult = CBCRulesEngine::evaluate($age, $sex, $testValues);

// 2. Step 3: Pattern Detection & Connected Findings
$patternResult = CBCPatternEngine::detect($rulesResult);

// 3. Step 4: Evidence Layer & Gemini AI Explanation
$aiResult = GeminiExplanationService::generateExplanation($rulesResult, $patternResult, $customApiKey);

echo json_encode([
    'success' => true,
    'stage' => 4,
    'stage_name' => 'Evidence Layer & Gemini AI Explanation',
    'patient' => $rulesResult['patient'],
    'summary' => $rulesResult['summary'],
    'parameters' => $rulesResult['parameters'],
    'pattern_data' => $patternResult,
    'evidence_grounds' => $aiResult['evidence_grounds'],
    'ai_explanation' => $aiResult['explanation']
]);
