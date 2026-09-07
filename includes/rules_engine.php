<?php
/**
 * LabInsight AI — Deterministic CBC Rules Engine
 * Step 2: Parameter Classification (LOW / NORMAL / HIGH)
 */

class CBCRulesEngine {

    /**
     * Official Demonstration Reference Ranges for MVP
     */
    public static $referenceRanges = [
        'hemoglobin' => [
            'name' => 'Hemoglobin',
            'unit' => 'g/dL',
            'min' => 12.0,
            'max' => 16.0,
            'range_display' => '12–16'
        ],
        'wbc' => [
            'name' => 'WBC (White Blood Cells)',
            'unit' => '×10⁹/L',
            'min' => 4.0,
            'max' => 11.0,
            'range_display' => '4–11'
        ],
        'rbc' => [
            'name' => 'RBC (Red Blood Cells)',
            'unit' => '×10¹²/L',
            'min' => 4.0,
            'max' => 5.5,
            'range_display' => '4.0–5.5'
        ],
        'platelets' => [
            'name' => 'Platelets',
            'unit' => '×10⁹/L',
            'min' => 150.0,
            'max' => 450.0,
            'range_display' => '150–450'
        ],
        'mcv' => [
            'name' => 'MCV (Mean Corpuscular Volume)',
            'unit' => 'fL',
            'min' => 80.0,
            'max' => 100.0,
            'range_display' => '80–100'
        ],
        'mch' => [
            'name' => 'MCH (Mean Corpuscular Hemoglobin)',
            'unit' => 'pg',
            'min' => 27.0,
            'max' => 33.0,
            'range_display' => '27–33'
        ],
        'mchc' => [
            'name' => 'MCHC (Mean Corpuscular Hemoglobin Conc.)',
            'unit' => 'g/dL',
            'min' => 32.0,
            'max' => 36.0,
            'range_display' => '32–36'
        ],
        'rdw' => [
            'name' => 'RDW (Red Cell Distribution Width)',
            'unit' => '%',
            'min' => 11.5,
            'max' => 14.5,
            'range_display' => '11.5–14.5'
        ]
    ];

    /**
     * Evaluates patient info & CBC parameters using deterministic rules
     * 
     * @param int|float $age
     * @param string $sex
     * @param array $values Key-value array of test parameters
     * @return array Evaluation results
     */
    public static function evaluate($age, $sex, array $values) {
        $results = [];
        $countNormal = 0;
        $countLow = 0;
        $countHigh = 0;

        foreach (self::$referenceRanges as $key => $config) {
            $valRaw = isset($values[$key]) ? $values[$key] : null;

            if ($valRaw === null || $valRaw === '' || !is_numeric($valRaw)) {
                $status = 'MISSING';
                $val = null;
                $message = 'No valid value provided.';
            } else {
                $val = floatval($valRaw);

                if ($val < $config['min']) {
                    $status = 'LOW';
                    $countLow++;
                    $message = sprintf('Below demonstration lower limit of %s %s', $config['min'], $config['unit']);
                } elseif ($val > $config['max']) {
                    $status = 'HIGH';
                    $countHigh++;
                    $message = sprintf('Above demonstration upper limit of %s %s', $config['max'], $config['unit']);
                } else {
                    $status = 'NORMAL';
                    $countNormal++;
                    $message = sprintf('Within demonstration reference range (%s %s)', $config['range_display'], $config['unit']);
                }
            }

            $results[$key] = [
                'key' => $key,
                'name' => $config['name'],
                'unit' => $config['unit'],
                'min' => $config['min'],
                'max' => $config['max'],
                'range_display' => $config['range_display'],
                'value' => $val,
                'status' => $status,
                'message' => $message
            ];
        }

        return [
            'patient' => [
                'age' => floatval($age),
                'sex' => htmlspecialchars($sex),
                'evaluated_at' => date('Y-m-d H:i:s')
            ],
            'summary' => [
                'total_tests' => count(self::$referenceRanges),
                'normal' => $countNormal,
                'low' => $countLow,
                'high' => $countHigh,
                'flagged' => $countLow + $countHigh
            ],
            'parameters' => $results
        ];
    }
}
