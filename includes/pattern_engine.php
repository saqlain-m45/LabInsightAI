<?php
/**
 * LabInsight AI — Deterministic CBC Pattern Engine
 * Step 3: Pattern Detection & Connected Findings ("Patterns Connect")
 */

require_once __DIR__ . '/rules_engine.php';

class CBCPatternEngine {

    /**
     * Evaluates rule output and connects related findings into clinical CBC patterns
     * 
     * @param array $rulesResult Evaluation array returned by CBCRulesEngine::evaluate()
     * @return array Array of detected patterns and metadata
     */
    public static function detect(array $rulesResult) {
        $params = $rulesResult['parameters'] ?? [];
        $detectedPatterns = [];

        if (empty($params)) {
            return [
                'count' => 0,
                'patterns' => []
            ];
        }

        // Helper closures for status retrieval
        $getStatus = function($key) use ($params) {
            return isset($params[$key]['status']) ? $params[$key]['status'] : 'UNKNOWN';
        };

        $getValue = function($key) use ($params) {
            return isset($params[$key]['value']) ? $params[$key]['value'] : null;
        };

        $hb = $getStatus('hemoglobin');
        $wbc = $getStatus('wbc');
        $rbc = $getStatus('rbc');
        $plt = $getStatus('platelets');
        $mcv = $getStatus('mcv');
        $mch = $getStatus('mch');
        $mchc = $getStatus('mchc');
        $rdw = $getStatus('rdw');

        // Pattern 1: Microcytic Hypochromic Pattern
        if ($hb === 'LOW' && $mcv === 'LOW' && ($mch === 'LOW' || $mchc === 'LOW')) {
            $findings = ['Hemoglobin LOW', 'MCV LOW', 'MCH LOW'];
            if ($rdw === 'HIGH') {
                $findings[] = 'RDW HIGH (Anisocytosis)';
            }
            $detectedPatterns[] = [
                'id' => 'microcytic_hypochromic',
                'title' => 'Microcytic Hypochromic Pattern',
                'category' => 'Red Blood Cell / Anemia Pattern',
                'badge_color' => 'warning',
                'severity' => 'Significant Finding',
                'connected_findings' => $findings,
                'description' => 'Low Hemoglobin combined with reduced red blood cell volume (Low MCV) and decreased hemoglobin concentration per cell (Low MCH/MCHC). Often observed in iron deficiency or thalassemia trait patterns.',
                'pattern_logic' => 'Hemoglobin (LOW) + MCV (LOW) + MCH/MCHC (LOW)'
            ];
        }

        // Pattern 2: Macrocytic Anemia Pattern
        if ($hb === 'LOW' && $mcv === 'HIGH') {
            $detectedPatterns[] = [
                'id' => 'macrocytic_anemia',
                'title' => 'Macrocytic Pattern',
                'category' => 'Red Blood Cell / Anemia Pattern',
                'badge_color' => 'warning',
                'severity' => 'Moderate Finding',
                'connected_findings' => ['Hemoglobin LOW', 'MCV HIGH'],
                'description' => 'Low Hemoglobin associated with enlarged red blood cell volume (High MCV). Commonly evaluated for Vitamin B12 / Folate level correlations or reticulocytosis.',
                'pattern_logic' => 'Hemoglobin (LOW) + MCV (HIGH)'
            ];
        }

        // Pattern 3: Normocytic Normochromic Anemia Pattern
        if ($hb === 'LOW' && $mcv === 'NORMAL' && ($mch === 'NORMAL' || $mchc === 'NORMAL')) {
            $detectedPatterns[] = [
                'id' => 'normocytic_anemia',
                'title' => 'Normocytic Normochromic Pattern',
                'category' => 'Red Blood Cell / Anemia Pattern',
                'badge_color' => 'info',
                'severity' => 'Moderate Finding',
                'connected_findings' => ['Hemoglobin LOW', 'MCV NORMAL', 'MCH NORMAL'],
                'description' => 'Low Hemoglobin with normal red blood cell size (Normal MCV) and color indices. Often associated with acute blood loss, chronic disease, or early bone marrow response.',
                'pattern_logic' => 'Hemoglobin (LOW) + MCV (NORMAL) + MCH (NORMAL)'
            ];
        }

        // Pattern 4: Erythrocytosis / Polycythemia Pattern
        if ($hb === 'HIGH' || $rbc === 'HIGH') {
            $findings = [];
            if ($hb === 'HIGH') $findings[] = 'Hemoglobin HIGH';
            if ($rbc === 'HIGH') $findings[] = 'RBC HIGH';
            $detectedPatterns[] = [
                'id' => 'erythrocytosis',
                'title' => 'Erythrocytosis / Polycythemia Pattern',
                'category' => 'Red Blood Cell Pattern',
                'badge_color' => 'secondary',
                'severity' => 'Moderate Finding',
                'connected_findings' => $findings,
                'description' => 'Elevated red blood cell count or hemoglobin concentration above demonstration reference ranges.',
                'pattern_logic' => 'Hemoglobin (HIGH) OR RBC (HIGH)'
            ];
        }

        // Pattern 5: Leukocytosis Pattern
        if ($wbc === 'HIGH') {
            $detectedPatterns[] = [
                'id' => 'leukocytosis',
                'title' => 'Leukocytosis Pattern',
                'category' => 'White Blood Cell Pattern',
                'badge_color' => 'danger',
                'severity' => 'Moderate Finding',
                'connected_findings' => ['WBC HIGH'],
                'description' => 'Elevated total White Blood Cell count (Leukocytosis). Frequently associated with physiological stress, tissue inflammation, or immune response.',
                'pattern_logic' => 'WBC (HIGH)'
            ];
        }

        // Pattern 6: Leukopenia Pattern
        if ($wbc === 'LOW') {
            $detectedPatterns[] = [
                'id' => 'leukopenia',
                'title' => 'Leukopenia Pattern',
                'category' => 'White Blood Cell Pattern',
                'badge_color' => 'warning',
                'severity' => 'Moderate Finding',
                'connected_findings' => ['WBC LOW'],
                'description' => 'Decreased total White Blood Cell count (Leukopenia). Indicates reduced circulating immune cell count.',
                'pattern_logic' => 'WBC (LOW)'
            ];
        }

        // Pattern 7: Thrombocytopenia Pattern
        if ($plt === 'LOW') {
            $detectedPatterns[] = [
                'id' => 'thrombocytopenia',
                'title' => 'Thrombocytopenia Pattern',
                'category' => 'Platelet Pattern',
                'badge_color' => 'danger',
                'severity' => 'Significant Finding',
                'connected_findings' => ['Platelets LOW'],
                'description' => 'Decreased circulating Platelet count (Thrombocytopenia). Platelets are essential blood elements involved in hemostasis and clot formation.',
                'pattern_logic' => 'Platelets (LOW)'
            ];
        }

        // Pattern 8: Thrombocytosis Pattern
        if ($plt === 'HIGH') {
            $detectedPatterns[] = [
                'id' => 'thrombocytosis',
                'title' => 'Thrombocytosis Pattern',
                'category' => 'Platelet Pattern',
                'badge_color' => 'secondary',
                'severity' => 'Moderate Finding',
                'connected_findings' => ['Platelets HIGH'],
                'description' => 'Elevated circulating Platelet count (Thrombocytosis). Frequently evaluated in reactive inflammatory states.',
                'pattern_logic' => 'Platelets (HIGH)'
            ];
        }

        // Pattern 9: Normal CBC Profile
        if (empty($detectedPatterns) && $rulesResult['summary']['flagged'] === 0) {
            $detectedPatterns[] = [
                'id' => 'normal_cbc_profile',
                'title' => 'Normal CBC Parameter Profile',
                'category' => 'Overall Profile',
                'badge_color' => 'success',
                'severity' => 'Normal Finding',
                'connected_findings' => ['All 8 CBC Parameters NORMAL'],
                'description' => 'All Complete Blood Count parameters evaluated fall within demonstration reference ranges.',
                'pattern_logic' => 'Hemoglobin, WBC, RBC, Platelets, MCV, MCH, MCHC, RDW (All NORMAL)'
            ];
        }

        return [
            'count' => count($detectedPatterns),
            'patterns' => $detectedPatterns
        ];
    }
}
