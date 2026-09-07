<?php
$pageTitle = "LabInsight AI — Understand Your Laboratory Results";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section text-center text-lg-start">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <span class="badge badge-hackathon mb-3 d-inline-inline-flex align-items-center">
                    <i class="fa-solid fa-flask-vial me-1"></i> AI Healthcare Hackathon MVP
                </span>
                <h1 class="hero-title mb-2">LabInsight <span class="text-primary">AI</span></h1>
                <p class="hero-tagline mb-3">Understand Your Laboratory Results</p>
                <p class="hero-desc mb-4">
                    LabInsight AI helps users understand laboratory results through structured analysis and AI-assisted explanations in clear, accessible language.
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                    <a href="cbc-analysis.php" class="btn btn-teal btn-lg">
                        <i class="fa-solid fa-stethoscope me-2"></i> Analyze CBC Results
                    </a>
                    <a href="#architecture" class="btn btn-outline-teal btn-lg">
                        <i class="fa-solid fa-diagram-project me-2"></i> Explore System Architecture
                    </a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="custom-card p-4 text-center bg-white">
                    <div class="step-icon-wrapper mx-auto mb-3" style="width:64px; height:64px; font-size:1.75rem;">
                        <i class="fa-solid fa-vial-circle-check text-teal"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Complete Blood Count (CBC)</h5>
                    <p class="text-muted small mb-3">
                        Initial Proof of Concept (PoC) for multi-laboratory result interpretation.
                    </p>
                    <div class="p-3 bg-light rounded text-start mb-3 border">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="fw-semibold">Hemoglobin, WBC, RBC</span>
                            <span class="badge bg-secondary">PoC Scope</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1 small">
                            <span class="fw-semibold">Indices (MCV, MCH, MCHC)</span>
                            <span class="badge bg-secondary">PoC Scope</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="fw-semibold">Platelets & RDW</span>
                            <span class="badge bg-secondary">PoC Scope</span>
                        </div>
                    </div>
                    <a href="cbc-analysis.php" class="btn btn-teal w-100 btn-sm">
                        Open CBC Analysis Tool <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- System Architecture Concept Section -->
<section id="architecture" class="py-5">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill mb-2">Controlled & Explainable Workflow</span>
            <h2 class="fw-bold">How LabInsight AI Works</h2>
            <p class="text-muted">
                Our platform uses a controlled 4-step workflow to ensure medical explainability, accuracy, and safety without relying on AI for core laboratory calculations.
            </p>
        </div>

        <!-- 4 Step Architecture Grid -->
        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3">
                <div class="workflow-step-card">
                    <div class="step-icon-wrapper">
                        <i class="fa-solid fa-calculator"></i>
                    </div>
                    <span class="text-uppercase text-muted fw-bold small d-block mb-1">Step 1</span>
                    <h5 class="fw-bold mb-2">Rules Calculate</h5>
                    <p class="text-muted small mb-0">
                        Deterministic medical rules calculate and classify laboratory values accurately based on target parameters.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="workflow-step-card">
                    <div class="step-icon-wrapper">
                        <i class="fa-solid fa-network-wired"></i>
                    </div>
                    <span class="text-uppercase text-muted fw-bold small d-block mb-1">Step 2</span>
                    <h5 class="fw-bold mb-2">Patterns Connect</h5>
                    <p class="text-muted small mb-0">
                        Pattern logic correlates related CBC findings to identify clinical parameter associations.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="workflow-step-card">
                    <div class="step-icon-wrapper">
                        <i class="fa-solid fa-magnifying-glass-chart"></i>
                    </div>
                    <span class="text-uppercase text-muted fw-bold small d-block mb-1">Step 3</span>
                    <h5 class="fw-bold mb-2">Evidence Grounds</h5>
                    <p class="text-muted small mb-0">
                        An evidence layer provides explicit clinical rationale explaining why specific patterns were detected.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="workflow-step-card">
                    <div class="step-icon-wrapper">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <span class="text-uppercase text-muted fw-bold small d-block mb-1">Step 4</span>
                    <h5 class="fw-bold mb-2">AI Explains</h5>
                    <p class="text-muted small mb-0">
                        Google Gemini AI converts validated evidence into clear, patient-friendly explanations.
                    </p>
                </div>
            </div>
        </div>

        <!-- Architectural Vision Banner -->
        <div class="custom-card p-4 bg-white border mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h5 class="fw-bold text-dark mb-2"><i class="fa-solid fa-layer-group text-primary me-2"></i> Future Platform Vision</h5>
                    <p class="text-muted mb-0">
                        Starting with CBC as proof of concept, LabInsight AI is designed to scale across clinical chemistry, electrolytes, HbA1c, thyroid panels, longitudinal laboratory trends, and automated LIS report extraction.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="cbc-analysis.php" class="btn btn-teal">
                        Try CBC Analysis <i class="fa-solid fa-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
