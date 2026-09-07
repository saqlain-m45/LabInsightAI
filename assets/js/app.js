/**
 * LabInsight AI — Application JavaScript
 * Step 4: Rules, Patterns, Evidence Grounds & Gemini AI Explanation Integration
 */

document.addEventListener('DOMContentLoaded', function () {
    const cbcForm = document.getElementById('cbcForm');
    const validationAlert = document.getElementById('validationAlert');
    const validationErrorList = document.getElementById('validationErrorList');
    const rulesResultContainer = document.getElementById('rulesEvaluationResult');
    const clearBtn = document.getElementById('clearFormBtn');

    if (!cbcForm) return;

    // Real-time input cleaning and error clearing on input change
    const inputs = cbcForm.querySelectorAll('input, select');
    inputs.forEach(input => {
        input.addEventListener('input', function () {
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
            }
        });
    });

    // Handle Form Reset / Clear
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            cbcForm.reset();
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });
            if (validationAlert) validationAlert.classList.add('d-none');
            if (rulesResultContainer) rulesResultContainer.classList.add('d-none');
        });
    }

    // Handle Form Submit
    cbcForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Reset state
        let errors = [];
        inputs.forEach(input => input.classList.remove('is-invalid', 'is-valid'));
        if (validationAlert) validationAlert.classList.add('d-none');
        if (rulesResultContainer) rulesResultContainer.classList.add('d-none');

        // 1. Patient Age Validation
        const ageInput = document.getElementById('patientAge');
        const ageValue = ageInput ? ageInput.value.trim() : '';

        if (!ageValue) {
            errors.push('Patient Age is required.');
            markInvalid(ageInput);
        } else {
            const ageNum = Number(ageValue);
            if (isNaN(ageNum) || ageNum <= 0 || ageNum > 120) {
                errors.push('Patient Age must be a valid positive number between 1 and 120.');
                markInvalid(ageInput);
            } else {
                markValid(ageInput);
            }
        }

        // 2. Patient Sex Validation
        const sexInput = document.getElementById('patientSex');
        const sexValue = sexInput ? sexInput.value : '';

        if (!sexValue) {
            errors.push('Patient Sex selection is required.');
            markInvalid(sexInput);
        } else {
            markValid(sexInput);
        }

        // 3. CBC Test Values Validation (8 parameters)
        const cbcTests = [
            { id: 'hemoglobin', name: 'Hemoglobin' },
            { id: 'wbc', name: 'WBC (White Blood Cells)' },
            { id: 'rbc', name: 'RBC (Red Blood Cells)' },
            { id: 'platelets', name: 'Platelets' },
            { id: 'mcv', name: 'MCV' },
            { id: 'mch', name: 'MCH' },
            { id: 'mchc', name: 'MCHC' },
            { id: 'rdw', name: 'RDW' }
        ];

        let payload = {
            patient_age: Number(ageValue),
            patient_sex: sexValue
        };

        // Custom Gemini API Key if entered in settings field
        const apiKeyEl = document.getElementById('geminiApiKeyInput');
        if (apiKeyEl && apiKeyEl.value.trim() !== '') {
            payload.gemini_api_key = apiKeyEl.value.trim();
        }

        cbcTests.forEach(test => {
            const inputEl = document.getElementById(test.id);
            if (inputEl) {
                const val = inputEl.value.trim();
                if (!val) {
                    errors.push(`${test.name} is required.`);
                    markInvalid(inputEl);
                } else {
                    const numVal = Number(val);
                    if (isNaN(numVal)) {
                        errors.push(`${test.name} must be a valid numeric value.`);
                        markInvalid(inputEl);
                    } else if (numVal < 0) {
                        errors.push(`${test.name} cannot be a negative value.`);
                        markInvalid(inputEl);
                    } else {
                        markValid(inputEl);
                        payload[test.id] = numVal;
                    }
                }
            }
        });

        // Check if validation failed
        if (errors.length > 0) {
            if (validationErrorList) {
                validationErrorList.innerHTML = errors.map(err => `<li>${escapeHtml(err)}</li>`).join('');
            }
            if (validationAlert) {
                validationAlert.classList.remove('d-none');
                validationAlert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            return false;
        }

        // Show Loading State Spinner in Result Container
        if (rulesResultContainer) {
            rulesResultContainer.classList.remove('d-none');
            rulesResultContainer.innerHTML = `
                <div class="custom-card p-5 text-center shadow-sm">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Analyzing Laboratory Evidence & Querying Gemini AI...</h5>
                    <p class="text-muted small mb-0">Executing Rules Calculation → Pattern Detection → Evidence Citation → Gemini AI Explanation</p>
                </div>
            `;
            rulesResultContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Call Explain API Endpoint (api/explain.php)
        fetch('api/explain.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Analysis error: ' + (data.error || 'Unknown error'));
                return;
            }

            renderFullWorkflowResults(data);
        })
        .catch(err => {
            console.error('Error connecting to Explain API:', err);
            alert('Failed to connect to AI Explanation API.');
        });
    });

    function renderFullWorkflowResults(data) {
        if (!rulesResultContainer) return;

        const summary = data.summary;
        const patient = data.patient;
        const params = data.parameters;
        const patternData = data.pattern_data;
        const evidence = data.evidence_grounds;
        const aiExplanation = data.ai_explanation;

        // 1. Render Rules Table Rows
        let tableRows = '';
        for (const key in params) {
            const item = params[key];
            let badgeHtml = '';

            if (item.status === 'NORMAL') {
                badgeHtml = `<span class="badge-status-normal"><i class="fa-solid fa-circle-check"></i> NORMAL</span>`;
            } else if (item.status === 'LOW') {
                badgeHtml = `<span class="badge-status-low"><i class="fa-solid fa-arrow-down"></i> LOW</span>`;
            } else if (item.status === 'HIGH') {
                badgeHtml = `<span class="badge-status-high"><i class="fa-solid fa-arrow-up"></i> HIGH</span>`;
            } else {
                badgeHtml = `<span class="badge bg-secondary">UNKNOWN</span>`;
            }

            tableRows += `
                <tr>
                    <td class="fw-bold align-middle">${escapeHtml(item.name)}</td>
                    <td class="align-middle fw-semibold fs-6 text-dark">${escapeHtml(item.value)} <span class="text-muted small">${escapeHtml(item.unit)}</span></td>
                    <td class="align-middle"><span class="range-badge">${escapeHtml(item.range_display)} ${escapeHtml(item.unit)}</span></td>
                    <td class="align-middle">${badgeHtml}</td>
                    <td class="align-middle text-muted small">${escapeHtml(item.message)}</td>
                </tr>
            `;
        }

        // 2. Render Pattern Cards
        let patternCardsHtml = '';
        if (patternData && patternData.patterns && patternData.patterns.length > 0) {
            patternData.patterns.forEach(pat => {
                let borderClass = 'pattern-' + (pat.badge_color || 'primary');
                let findingChips = pat.connected_findings.map(f => `<span class="finding-chip"><i class="fa-solid fa-link text-primary"></i> ${escapeHtml(f)}</span>`).join(' ');

                patternCardsHtml += `
                    <div class="pattern-item-card ${borderClass} mb-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <div>
                                <span class="badge bg-light text-dark border me-2">${escapeHtml(pat.category)}</span>
                                <span class="badge bg-${pat.badge_color} text-white">${escapeHtml(pat.severity)}</span>
                                <h5 class="fw-bold text-dark mt-2 mb-1">${escapeHtml(pat.title)}</h5>
                            </div>
                        </div>
                        <p class="text-secondary small mb-3">${escapeHtml(pat.description)}</p>
                        <div class="mb-2">
                            <span class="fw-semibold text-muted micro-text d-block mb-1">CONNECTED CBC FINDINGS:</span>
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                ${findingChips}
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top">
                            <span class="text-muted micro-text me-2">Deterministic Match Rule:</span>
                            <span class="pattern-logic-code">${escapeHtml(pat.pattern_logic)}</span>
                        </div>
                    </div>
                `;
            });
        } else {
            patternCardsHtml = `<div class="alert alert-light border text-muted">No specific multi-parameter patterns detected for these laboratory values.</div>`;
        }

        // 3. Render Evidence Citations Box
        let evidenceRulesHtml = '';
        if (evidence && evidence.flagged_rules && evidence.flagged_rules.length > 0) {
            evidenceRulesHtml = evidence.flagged_rules.map(r => `
                <li class="mb-1 small">
                    <strong>${escapeHtml(r.parameter)} (${escapeHtml(r.value)}):</strong> Marked as <span class="badge bg-secondary">${escapeHtml(r.status)}</span> — Demonstration Range: ${escapeHtml(r.ref_range)}.
                </li>
            `).join('');
        } else {
            evidenceRulesHtml = `<li class="small text-success">All 8 CBC parameters fall within standard demonstration reference ranges.</li>`;
        }

        // 4. Render Gemini AI Explanation Content
        let paramExplHtml = '';
        if (aiExplanation.parameter_explanations && aiExplanation.parameter_explanations.length > 0) {
            paramExplHtml = aiExplanation.parameter_explanations.map(p => `<li class="mb-2">${p}</li>`).join('');
        }

        let doctorQHtml = '';
        if (aiExplanation.questions_for_doctor && aiExplanation.questions_for_doctor.length > 0) {
            doctorQHtml = aiExplanation.questions_for_doctor.map(q => `<li>${escapeHtml(q)}</li>`).join('');
        }

        // Assemble Combined View
        rulesResultContainer.innerHTML = `
            <!-- Workflow Step 1: Rules Calculate Card -->
            <div class="custom-card p-4 border shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 mb-3 border-bottom">
                    <div>
                        <span class="badge bg-teal-subtle text-primary border border-primary-subtle fw-semibold mb-1">Workflow Step 1: Rules Calculate</span>
                        <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-calculator text-primary me-2"></i> Deterministic CBC Rules Evaluation</h4>
                        <p class="text-muted small mb-0">Patient: ${escapeHtml(patient.age)} years, ${escapeHtml(patient.sex)} | Evaluated at: ${escapeHtml(patient.evaluated_at)}</p>
                    </div>
                    <a href="#cbcForm" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Inputs
                    </a>
                </div>

                <!-- Summary Metric Counters -->
                <div class="row g-3 mb-4 text-center">
                    <div class="col-6 col-md-3">
                        <div class="metric-summary-card">
                            <div class="metric-number text-dark">${summary.total_tests}</div>
                            <div class="metric-label">Total Tests</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="metric-summary-card" style="border-top: 3px solid var(--success-color);">
                            <div class="metric-number text-success">${summary.normal}</div>
                            <div class="metric-label text-success">Normal Parameters</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="metric-summary-card" style="border-top: 3px solid var(--warning-color);">
                            <div class="metric-number text-warning">${summary.low}</div>
                            <div class="metric-label text-warning">Low Parameters</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="metric-summary-card" style="border-top: 3px solid var(--danger-color);">
                            <div class="metric-number text-danger">${summary.high}</div>
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
                            ${tableRows}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Workflow Step 2: Patterns Connect Card -->
            <div class="custom-card p-4 border shadow-sm mb-4">
                <div class="pb-3 mb-3 border-bottom">
                    <span class="badge bg-purple-subtle text-dark border fw-semibold mb-1" style="background-color: #f3e8ff; color: #6b21a8; border-color: #e9d5ff;">Workflow Step 2: Patterns Connect</span>
                    <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-network-wired me-2 text-primary"></i> CBC Pattern Detection & Connected Findings</h4>
                    <p class="text-muted small mb-0">Pattern engine connects related parameter findings to identify laboratory patterns.</p>
                </div>
                <div class="mb-0">
                    ${patternCardsHtml}
                </div>
            </div>

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
                            <h6 class="fw-bold text-dark small mb-2"><i class="fa-solid fa-list-check text-teal me-1"></i> Flagged Rule Citations (${evidence.total_flagged})</h6>
                            <ul class="mb-0 ps-3">
                                ${evidenceRulesHtml}
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-white border rounded h-100">
                            <h6 class="fw-bold text-dark small mb-2"><i class="fa-solid fa-diagram-project text-teal me-1"></i> Detected Pattern Citations (${evidence.detected_patterns ? evidence.detected_patterns.length : 0})</h6>
                            <ul class="mb-0 ps-3 small">
                                ${evidence.detected_patterns && evidence.detected_patterns.length > 0 
                                    ? evidence.detected_patterns.map(p => `<li><strong>${escapeHtml(p.title)}:</strong> ${escapeHtml(p.findings)}</li>`).join('') 
                                    : '<li class="text-muted">No multi-parameter pattern citations detected.</li>'}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflow Step 4: AI Explains (Gemini AI Explanation Card) -->
            <div class="ai-explanation-card shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 mb-3 border-bottom">
                    <div>
                        <span class="badge-gemini-ai mb-1"><i class="fa-solid fa-wand-magic-sparkles"></i> ${escapeHtml(aiExplanation.ai_source || 'Google Gemini AI')}</span>
                        <h4 class="fw-bold text-dark mb-0">Patient-Friendly Laboratory Explanation</h4>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                        <i class="fa-solid fa-shield-check me-1"></i> Grounded in Evidence
                    </span>
                </div>

                ${aiExplanation.api_notice ? `<div class="alert alert-warning py-2 px-3 small mb-3"><i class="fa-solid fa-triangle-exclamation me-1"></i> ${escapeHtml(aiExplanation.api_notice)}</div>` : ''}

                <!-- Summary Overview -->
                <div class="p-3 bg-white border rounded mb-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-comments text-primary me-2"></i> Executive Summary</h6>
                    <p class="mb-0 text-secondary">${aiExplanation.summary}</p>
                </div>

                <!-- Parameter Breakdown -->
                ${paramExplHtml ? `
                    <div class="p-3 bg-white border rounded mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-stethoscope text-teal me-2"></i> Parameter Breakdown</h6>
                        <ul class="mb-0 text-secondary ps-3">
                            ${paramExplHtml}
                        </ul>
                    </div>
                ` : ''}

                <!-- Pattern Context -->
                ${aiExplanation.pattern_context ? `
                    <div class="p-3 bg-white border rounded mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-book-medical text-primary me-2"></i> Educational Context of Findings</h6>
                        <p class="mb-0 text-secondary">${aiExplanation.pattern_context}</p>
                    </div>
                ` : ''}

                <!-- Questions for Doctor -->
                ${doctorQHtml ? `
                    <div class="doctor-questions-list mb-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-circle-question text-primary me-2"></i> Recommended Questions to Ask Your Doctor</h6>
                        <ul>
                            ${doctorQHtml}
                        </ul>
                    </div>
                ` : ''}

                <div class="alert alert-light border mb-0 small text-muted">
                    <i class="fa-solid fa-user-doctor me-1 text-teal"></i> <strong>Medical Safety Note:</strong> LabInsight AI provides educational insights grounded in laboratory reference data. Always review these results with your healthcare provider for clinical evaluation.
                </div>
            </div>
        `;

        rulesResultContainer.classList.remove('d-none');
        rulesResultContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function markInvalid(element) {
        if (element) element.classList.add('is-invalid');
    }

    function markValid(element) {
        if (element) element.classList.add('is-valid');
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
});
