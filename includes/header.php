<?php
// Default page title if not set before including header
if (!isset($pageTitle)) {
    $pageTitle = "LabInsight AI — Understand Your Laboratory Results";
}

// Current active page detection
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LabInsight AI helps users understand laboratory results through structured analysis and AI-assisted explanations.">
    <meta name="author" content="LabInsight AI Team">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts (Poppins) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Main Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <i class="fa-solid fa-microscope brand-icon fs-4"></i>
                <span class="navbar-brand-title">LabInsight <span class="text-primary">AI</span></span>
                <span class="badge-hackathon ms-1">Hackathon MVP</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-3">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>" href="index.php">
                            <i class="fa-solid fa-house me-1 text-muted"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage === 'cbc-analysis.php') ? 'active' : ''; ?>" href="cbc-analysis.php">
                            <i class="fa-solid fa-vial me-1 text-muted"></i> CBC Analysis
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-teal btn-sm" href="cbc-analysis.php">
                            <i class="fa-solid fa-stethoscope me-1"></i> Analyze CBC Results
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
