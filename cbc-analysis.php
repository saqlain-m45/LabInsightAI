<?php
$pageTitle = "CBC Analysis — LabInsight AI";
require_once 'includes/rules_engine.php';
require_once 'includes/pattern_engine.php';
require_once 'includes/ai_explain.php';

// Handle Server-Side PHP Form Processing if submitted via POST
$phpEvaluationResult = null;
$phpPatternResult = null;
$phpAiResult = null;
$postError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patient_age'])) {
    $age = $_POST['patient_age'];
    $sex = $_POST['patient_sex'] ?? null;
    $customApiKey = $_POST['gemini_api_key'] ?? null;
    $testKeys = ['hemoglobin', 'wbc', 'rbc', 'platelets', 'mcv', 'mch', 'mchc', 'rdw'];
    $testValues = [];
    $hasErrors = false;

    if (!is_numeric($age) || floatval($age) <= 0 || floatval($age) > 120) {
        $postError = "Valid patient age (1-120) is required.";
        $hasErrors = true;
    }

    if (!$sex || !in_array($sex, ['Male', 'Female'])) {
        $postError = "Patient sex selection is required.";
        $hasErrors = true;
    }

    foreach ($testKeys as $key) {
        if (!isset($_POST[$key]) || !is_numeric($_POST[$key]) || floatval($_POST[$key]) < 0) {
            $postError = "All 8 CBC laboratory values are required and must be non-negative numbers.";
            $hasErrors = true;
            break;
        }
        $testValues[$key] = floatval($_POST[$key]);
    }

    if (!$hasErrors) {
        $phpEvaluationResult = CBCRulesEngine::evaluate($age, $sex, $testValues);
        $phpPatternResult = CBCPatternEngine::detect($phpEvaluationResult);
        $phpAiResult = GeminiExplanationService::generateExplanation($phpEvaluationResult, $phpPatternResult, $customApiKey);
    }
}

include 'includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb & Header Title -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="fa-solid fa-house me-1"></i> Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">CBC Analysis</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">CBC (Complete Blood Count) Analysis</h2>
            <p class="text-muted small mb-0">Enter patient details and 8 CBC lab values for rule calculation, pattern detection, and Gemini AI grounded explanations.</p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#apiKeySettingsCollapse" aria-expanded="false">
                <i class="fa-solid fa-key me-1"></i> Gemini API Settings
            </button>
            <a href="index.php" class="btn btn-outline-teal btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Home
            </a>
        </div>
    </div>

    <!-- Gemini API Key Collapsible Input Box -->
    <div class="collapse mb-4" id="apiKeySettingsCollapse">
        <div class="api-key-collapse shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-wand-magic-sparkles text-primary"></i>
                <h6 class="fw-bold mb-0">Google Gemini API Configuration (Optional)</h6>
            </div>
            <p class="text-muted small mb-3">
                By default, LabInsight AI uses a grounded rule engine fallback if no API key is provided. Paste your Google Gemini API key below to enable live generative AI explanations via `gemini-1.5-flash`.
            </p>
            <div class="row align-items-center g-2">
                <div class="col-md-9">
                    <input type="password" id="geminiApiKeyInput" name="gemini_api_key" class="form-control form-control-sm" placeholder="Paste your Gemini API Key here (AIzaSy...)" value="<?php echo isset($_POST['gemini_api_key']) ? htmlspecialchars($_POST['gemini_api_key']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <span class="badge bg-light text-dark border w-100 py-2">Model: gemini-1.5-flash</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Demonstration Reference Range Disclaimer Note -->
    <div class="disclaimer-info mb-4">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-circle-info fs-5 mt-1"></i>
            <div>
                <strong class="d-block mb-1">Demonstration Reference Ranges Disclaimer</strong>
                <span>These demonstration reference ranges are used for the hackathon MVP and are not universal reference ranges for every laboratory or every patient.</span>
            </div>
        </div>
    </div>

    <!-- Validation Error Alert Container (Shown by JS or PHP validation) -->
    <div id="validationAlert" class="alert alert-danger <?php echo $postError ? '' : 'd-none'; ?> mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation fs-5 mt-1"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-1">Technical Input Validation Required</h6>
                <ul id="validationErrorList" class="mb-0 ps-3 small">
                    <?php if ($postError): ?>
                        <li><?php echo htmlspecialchars($postError); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Analysis Form -->
    <form id="cbcForm" action="cbc-analysis.php" method="POST" novalidate>
        <div class="row g-4">
            <!-- Patient Information Section -->
            <div class="col-12">
                <div class="custom-card">
                    <div class="card-header-custom d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-check text-primary fs-5"></i>
                        <h5 class="mb-0 fw-bold">1. Patient Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Patient Age Field -->
                            <div class="col-md-6">
                                <label for="patientAge" class="form-label-custom">
                                    Patient Age <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control form-control-custom" 
                                           id="patientAge" 
                                           name="patient_age" 
                                           placeholder="e.g. 35" 
                                           value="<?php echo isset($_POST['patient_age']) ? htmlspecialchars($_POST['patient_age']) : ''; ?>"
                                           min="1" 
                                           max="120" 
                                           required>
                                    <span class="input-group-text unit-badge">years</span>
                                </div>
                                <div class="invalid-feedback">Please enter a valid numeric age (1–120).</div>
                            </div>

                            <!-- Patient Sex Field -->
                            <div class="col-md-6">
                                <label for="patientSex" class="form-label-custom">
                                    Patient Sex <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-custom" id="patientSex" name="patient_sex" required>
                                    <option value="" disabled <?php echo !isset($_POST['patient_sex']) ? 'selected' : ''; ?>>-- Select Patient Sex --</option>
                                    <option value="Male" <?php echo (isset($_POST['patient_sex']) && $_POST['patient_sex'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo (isset($_POST['patient_sex']) && $_POST['patient_sex'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                </select>
                                <div class="invalid-feedback">Please select patient sex.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CBC Laboratory Results Section (8 Parameters) -->
            <div class="col-12">
                <div class="custom-card">
                    <div class="card-header-custom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-flask text-teal fs-5"></i>
                            <h5 class="mb-0 fw-bold">2. CBC Laboratory Results</h5>
                        </div>
                        <span class="badge bg-light text-dark border">8 Required Tests</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            
                            <!-- 1. Hemoglobin -->
                            <div class="col-md-6 col-lg-3">
                                <div class="p-3 border rounded bg-white h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="hemoglobin" class="form-label-custom mb-0">Hemoglobin</label>
                                        <span class="range-badge">12–16 g/dL</span>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control form-control-custom" id="hemoglobin" name="hemoglobin" placeholder="e.g. 13.5" value="<?php echo isset($_POST['hemoglobin']) ? htmlspecialchars($_POST['hemoglobin']) : ''; ?>" required>
                                        <span class="input-group-text unit-badge">g/dL</span>
                                    </div>
                                    <div class="form-text text-muted micro-text mt-1">Ref: 12–16 g/dL</div>
                                </div>
                            </div>

                            <!-- 2. WBC -->
                            <div class="col-md-6 col-lg-3">
                                <div class="p-3 border rounded bg-white h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="wbc" class="form-label-custom mb-0">WBC</label>
                                        <span class="range-badge">4–11 ×10⁹/L</span>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control form-control-custom" id="wbc" name="wbc" placeholder="e.g. 7.2" value="<?php echo isset($_POST['wbc']) ? htmlspecialchars($_POST['wbc']) : ''; ?>" required>
                                        <span class="input-group-text unit-badge">×10⁹/L</span>
                                    </div>
                                    <div class="form-text text-muted micro-text mt-1">Ref: 4–11 ×10⁹/L</div>
                                </div>
                            </div>

                            <!-- 3. RBC -->
                            <div class="col-md-6 col-lg-3">
                                <div class="p-3 border rounded bg-white h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="rbc" class="form-label-custom mb-0">RBC</label>
                                        <span class="range-badge">4.0–5.5 ×10¹²/L</span>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control form-control-custom" id="rbc" name="rbc" placeholder="e.g. 4.8" value="<?php echo isset($_POST['rbc']) ? htmlspecialchars($_POST['rbc']) : ''; ?>" required>
                                        <span class="input-group-text unit-badge">×10¹²/L</span>
                                    </div>
                                    <div class="form-text text-muted micro-text mt-1">Ref: 4.0–5.5 ×10¹²/L</div>
                                </div>
                            </div>

                            <!-- 4. Platelets -->
                            <div class="col-md-6 col-lg-3">
                                <div class="p-3 border rounded bg-white h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="platelets" class="form-label-custom mb-0">Platelets</label>
                                        <span class="range-badge">150–450 ×10⁹/L</span>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control form-control-custom" id="platelets" name="platelets" placeholder="e.g. 250" value="<?php echo isset($_POST['platelets']) ? htmlspecialchars($_POST['platelets']) : ''; ?>" required>
                                        <span class="input-group-text unit-badge">×10⁹/L</span>
                                    </div>
                                    <div class="form-text text-muted micro-text mt-1">Ref: 150–450 ×10⁹/L</div>
                                </div>
                            </div>

                            <!-- 5. MCV -->
                            <div class="col-md-6 col-lg-3">
                                <div class="p-3 border rounded bg-white h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="mcv" class="form-label-custom mb-0">MCV</label>
                                        <span class="range-badge">80–100 fL</span>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control form-control-custom" id="mcv" name="mcv" placeholder="e.g. 90" value="<?php echo isset($_POST['mcv']) ? htmlspecialchars($_POST['mcv']) : ''; ?>" required>
                                        <span class="input-group-text unit-badge">fL</span>
                                    </div>
                                    <div class="form-text text-muted micro-text mt-1">Ref: 80–100 fL</div>
                                </div>
                            </div>

                            <!-- 6. MCH -->
                            <div class="col-md-6 col-lg-3">
                                <div class="p-3 border rounded bg-white h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="mch" class="form-label-custom mb-0">MCH</label>
                                        <span class="range-badge">27–33 pg</span>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control form-control-custom" id="mch" name="mch" placeholder="e.g. 29.5" value="<?php echo isset($_POST['mch']) ? htmlspecialchars($_POST['mch']) : ''; ?>" required>
                                        <span class="input-group-text unit-badge">pg</span>
                                    </div>
                                    <div class="form-text text-muted micro-text mt-1">Ref: 27–33 pg</div>
                                </div>
                            </div>

                            <!-- 7. MCHC -->
                            <div class="col-md-6 col-lg-3">
                                <div class="p-3 border rounded bg-white h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="mchc" class="form-label-custom mb-0">MCHC</label>
                                        <span class="range-badge">32–36 g/dL</span>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control form-control-custom" id="mchc" name="mchc" placeholder="e.g. 34" value="<?php echo isset($_POST['mchc']) ? htmlspecialchars($_POST['mchc']) : ''; ?>" required>
                                        <span class="input-group-text unit-badge">g/dL</span>
                                    </div>
                                    <div class="form-text text-muted micro-text mt-1">Ref: 32–36 g/dL</div>
                                </div>
                            </div>

                            <!-- 8. RDW -->
                            <div class="col-md-6 col-lg-3">
                                <div class="p-3 border rounded bg-white h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="rdw" class="form-label-custom mb-0">RDW</label>
                                        <span class="range-badge">11.5–14.5 %</span>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" step="any" class="form-control form-control-custom" id="rdw" name="rdw" placeholder="e.g. 12.8" value="<?php echo isset($_POST['rdw']) ? htmlspecialchars($_POST['rdw']) : ''; ?>" required>
                                        <span class="input-group-text unit-badge">%</span>
                                    </div>
                                    <div class="form-text text-muted micro-text mt-1">Ref: 11.5–14.5 %</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="col-12 d-flex flex-wrap gap-3 align-items-center justify-content-between">
                <button type="button" id="clearFormBtn" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-rotate-left me-1"></i> Reset Fields
                </button>
                <button type="submit" id="analyzeBtn" class="btn btn-teal btn-lg px-5">
                    <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Analyze & Generate AI Explanation
                </button>
            </div>
        </div>
    </form>

    <!-- Combined Output Container (Rules + Patterns + Evidence + Gemini AI Explanation) -->
    <div id="rulesEvaluationResult" class="mt-5 <?php echo $phpEvaluationResult ? '' : 'd-none'; ?>">
        <?php if ($phpEvaluationResult): ?>
            <?php 
                $summary = $phpEvaluationResult['summary'];
                $patient = $phpEvaluationResult['patient'];
                $params = $phpEvaluationResult['parameters'];
                $evidence = $phpAiResult['evidence_grounds'];
                $aiExplanation = $phpAiResult['explanation'];
            ?>
            <!-- Workflow Step 1: Rules Calculation Card -->
            <div class="custom-card p-4 border shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 mb-3 border-bottom">
                    <div>
                        <span class="badge bg-teal-subtle text-primary border border-primary-subtle fw-semibold mb-1">Workflow Step 1: Rules Calculate</span>
                        <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-calculator text-primary me-2"></i> Deterministic CBC Rules Evaluation</h4>
                        <p class="text-muted small mb-0">Patient: <?php echo htmlspecialchars($patient['age']); ?> years, <?php echo htmlspecialchars($patient['sex']); ?> | Evaluated at: <?php echo htmlspecialchars($patient['evaluated_at']); ?></p>
                    </div>
                    <a href="#cbcForm" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Inputs
                    </a>
                </div>

                <!-- Summary Metric Counters -->
                <div class="row g-3 mb-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="metric-summary-card">
                            <div class="metric-number text-dark"><?php echo $summary['total_tests']; ?></div>
                            <div class="metric-label">Total Tests</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="metric-summary-card" style="border-top: 3px solid var(--success-color);">
                            <div class="metric-number text-success"><?php echo $summary['normal']; ?></div>
                            <div class="metric-label text-success">Normal Parameters</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="metric-summary-card" style="border-top: 3px solid var(--warning-color);">
                            <div class="metric-number text-warning"><?php echo $summary['low']; ?></div>
                            <div class="metric-label text-warning">Low Parameters</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="metric-summary-card" style="border-top: 3px solid var(--danger-color);">
                            <div class="metric-number text-danger"><?php echo $summary['high']; ?></div>
                            <div class="metric-label text-danger">High Parameters</div>
                        </div>
                    </div>
                </div>

                <!-- Parameter Table -->
                <div class="table-responsive mb-0">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Test Parameter</th>
                                <th>Result Value</th>
                                <th>Demonstration Ref. Range</th>
                                <th>Rule Status</th>
                                <th>Rule Explanation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($params as $item): ?>
                                <tr>
                                    <td class="fw-bold align-middle"><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td class="align-middle fw-semibold fs-6 text-dark"><?php echo htmlspecialchars($item['value']); ?> <span class="text-muted small"><?php echo htmlspecialchars($item['unit']); ?></span></td>
                                    <td class="align-middle"><span class="range-badge"><?php echo htmlspecialchars($item['range_display']); ?> <?php echo htmlspecialchars($item['unit']); ?></span></td>
                                    <td class="align-middle">
                                        <?php if ($item['status'] === 'NORMAL'): ?>
                                            <span class="badge-status-normal"><i class="fa-solid fa-circle-check"></i> NORMAL</span>
                                        <?php elseif ($item['status'] === 'LOW'): ?>
                                            <span class="badge-status-low"><i class="fa-solid fa-arrow-down"></i> LOW</span>
                                        <?php elseif ($item['status'] === 'HIGH'): ?>
                                            <span class="badge-status-high"><i class="fa-solid fa-arrow-up"></i> HIGH</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">UNKNOWN</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="align-middle text-muted small"><?php echo htmlspecialchars($item['message']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Workflow Step 2: Patterns Connect Card -->
            <?php if ($phpPatternResult): ?>
                <div class="custom-card p-4 border shadow-sm mb-4">
                    <div class="pb-3 mb-3 border-bottom">
                        <span class="badge bg-purple-subtle text-dark border fw-semibold mb-1" style="background-color: #f3e8ff; color: #6b21a8; border-color: #e9d5ff;">Workflow Step 2: Patterns Connect</span>
                        <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-network-wired me-2 text-primary"></i> CBC Pattern Detection & Connected Findings</h4>
                        <p class="text-muted small mb-0">Pattern engine connects related parameter findings to identify laboratory patterns.</p>
                    </div>

                    <div class="mb-0">
                        <?php if (!empty($phpPatternResult['patterns'])): ?>
                            <?php foreach ($phpPatternResult['patterns'] as $pat): ?>
                                <div class="pattern-item-card pattern-<?php echo htmlspecialchars($pat['badge_color'] ?? 'primary'); ?> mb-3">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                        <div>
                                            <span class="badge bg-light text-dark border me-2"><?php echo htmlspecialchars($pat['category']); ?></span>
                                            <span class="badge bg-<?php echo htmlspecialchars($pat['badge_color']); ?> text-white"><?php echo htmlspecialchars($pat['severity']); ?></span>
                                            <h5 class="fw-bold text-dark mt-2 mb-1"><?php echo htmlspecialchars($pat['title']); ?></h5>
                                        </div>
                                    </div>
                                    <p class="text-secondary small mb-3"><?php echo htmlspecialchars($pat['description']); ?></p>
                                    <div class="mb-2">
                                        <span class="fw-semibold text-muted micro-text d-block mb-1">CONNECTED CBC FINDINGS:</span>
                                        <div class="d-flex flex-wrap gap-1 align-items-center">
                                            <?php foreach ($pat['connected_findings'] as $finding): ?>
                                                <span class="finding-chip"><i class="fa-solid fa-link text-primary"></i> <?php echo htmlspecialchars($finding); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-top">
                                        <span class="text-muted micro-text me-2">Deterministic Match Rule:</span>
                                        <span class="pattern-logic-code"><?php echo htmlspecialchars($pat['pattern_logic']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-light border text-muted">No specific multi-parameter patterns detected for these laboratory values.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Workflow Step 3: Evidence Grounds Box -->
            <div class="evidence-citation-box mb-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="fa-solid fa-magnifying-glass-chart text-teal fs-5"></i>
                    <h5 class="fw-bold text-dark mb-0">Workflow Step 3: Evidence Grounds</h5>
                    <span class="badge bg-light text-dark border ms-auto">Grounded Citations</span>
                </div>
                <p class="text-muted small mb-3">
                    The evidence layer compiles explicit citations from calculated rules and detected patterns to ensure full transparency before generating the AI explanation.
                </p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-white border rounded h-100">
                            <h6 class="fw-bold text-dark small mb-2"><i class="fa-solid fa-list-check text-teal me-1"></i> Flagged Rule Citations (<?php echo $evidence['total_flagged']; ?>)</h6>
                            <ul class="mb-0 ps-3">
                                <?php if (!empty($evidence['flagged_rules'])): ?>
                                    <?php foreach ($evidence['flagged_rules'] as $r): ?>
                                        <li class="mb-1 small">
                                            <strong><?php echo htmlspecialchars($r['parameter']); ?> (<?php echo htmlspecialchars($r['value']); ?>):</strong> Marked as <span class="badge bg-secondary"><?php echo htmlspecialchars($r['status']); ?></span> — Ref Range: <?php echo htmlspecialchars($r['ref_range']); ?>.
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="small text-success">All 8 CBC parameters fall within standard demonstration reference ranges.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-white border rounded h-100">
                            <h6 class="fw-bold text-dark small mb-2"><i class="fa-solid fa-diagram-project text-teal me-1"></i> Detected Pattern Citations (<?php echo count($evidence['detected_patterns']); ?>)</h6>
                            <ul class="mb-0 ps-3 small">
                                <?php if (!empty($evidence['detected_patterns'])): ?>
                                    <?php foreach ($evidence['detected_patterns'] as $p): ?>
                                        <li><strong><?php echo htmlspecialchars($p['title']); ?>:</strong> <?php echo htmlspecialchars($p['findings']); ?></li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="text-muted">No multi-parameter pattern citations detected.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Step 4: AI Explains (Gemini AI Explanation Card) -->
            <div class="ai-explanation-card shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 mb-3 border-bottom">
                    <div>
                        <span class="badge-gemini-ai mb-1"><i class="fa-solid fa-wand-magic-sparkles"></i> <?php echo htmlspecialchars($aiExplanation['ai_source'] ?? 'Google Gemini AI'); ?></span>
                        <h4 class="fw-bold text-dark mb-0">Patient-Friendly Laboratory Explanation</h4>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="fa-solid fa-shield-check me-1"></i> Grounded in Evidence
                    </span>
                </div>

                <?php if (!empty($aiExplanation['api_notice'])): ?>
                    <div class="alert alert-warning py-2 px-3 small mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> <?php echo htmlspecialchars($aiExplanation['api_notice']); ?></div>
                <?php endif; ?>

                <!-- Summary Overview -->
                <div class="p-3 bg-white border rounded mb-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-comments text-primary me-2"></i> Executive Summary</h6>
                    <p class="mb-0 text-secondary"><?php echo htmlspecialchars($aiExplanation['summary']); ?></p>
                </div>

                <!-- Parameter Breakdown -->
                <?php if (!empty($aiExplanation['parameter_explanations'])): ?>
                    <div class="p-3 bg-white border rounded mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-stethoscope text-teal me-2"></i> Parameter Breakdown</h6>
                        <ul class="mb-0 text-secondary ps-3">
                            <?php foreach ($aiExplanation['parameter_explanations'] as $expl): ?>
                                <li class="mb-2"><?php echo $expl; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Pattern Context -->
                <?php if (!empty($aiExplanation['pattern_context'])): ?>
                    <div class="p-3 bg-white border rounded mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-book-medical text-primary me-2"></i> Educational Context of Findings</h6>
                        <p class="mb-0 text-secondary"><?php echo $aiExplanation['pattern_context']; ?></p>
                    </div>
                <?php endif; ?>

                <!-- Questions for Doctor -->
                <?php if (!empty($aiExplanation['questions_for_doctor'])): ?>
                    <div class="doctor-questions-list mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-circle-question text-primary me-2"></i> Recommended Questions to Ask Your Doctor</h6>
                        <ul>
                            <?php foreach ($aiExplanation['questions_for_doctor'] as $q): ?>
                                <li><?php echo htmlspecialchars($q); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="alert alert-light border mb-0 small text-muted">
                    <i class="fa-solid fa-user-doctor me-1 text-teal"></i> <strong>Medical Safety Note:</strong> LabInsight AI provides educational insights grounded in laboratory reference data. Always review these results with your healthcare provider for clinical evaluation.
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
