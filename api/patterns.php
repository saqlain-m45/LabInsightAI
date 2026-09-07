<?php
/**
 * LabInsight AI — Pattern Detection API Endpoint
 * POST /api/patterns.php
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/rules_engine.php';
require_once __DIR__ . '/../includes/pattern_engine.php';

// Accept JSON body or POST form data
$inputRaw = file_get_contents('php://input');
$inputData = json_decode($inputRaw, true);

if (!$inputData || !is_array($inputData)) {
    $inputData = $_POST;
}

// Extract Patient Info
$age = isset($inputData['patient_age']) ? $inputData['patient_age'] : null;
$sex = isset($inputData['patient_sex']) ? $inputData['patient_sex'] : null;

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

// Step 2: Deterministic Rule Calculation
$rulesEvaluation = CBCRulesEngine::evaluate($age, $sex, $testValues);

// Step 3: Pattern Detection & Connected Findings
$patternEvaluation = CBCPatternEngine::detect($rulesEvaluation);

echo json_encode([
    'success' => true,
    'stage' => 3,
    'stage_name' => 'CBC Pattern Detection & Connected Findings',
    'patient' => $rulesEvaluation['patient'],
    'summary' => $rulesEvaluation['summary'],
    'parameters' => $rulesEvaluation['parameters'],
    'pattern_data' => $patternEvaluation,
    'disclaimer' => 'Demonstration Reference Ranges used. Step 3 connects parameter findings into clinical laboratory patterns.'
]);
