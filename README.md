# LabInsight AI — AI-Powered Laboratory Interpretation Platform

[![Live Demo](https://img.shields.io/badge/Live%20Demo-labinsightai.nexsoft.site-0e7490?style=for-the-badge&logo=googlechrome&logoColor=white)](http://labinsightai.nexsoft.site)
[![GitHub Repository](https://img.shields.io/badge/GitHub-LabInsightAI-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/saqlain-m45/LabInsightAI)
[![Technology](https://img.shields.io/badge/Stack-PHP%20%7C%20Bootstrap%205%20%7C%20Gemini%20AI-0284c7?style=for-the-badge)](http://labinsightai.nexsoft.site)

**LabInsight AI** is an explainable AI-powered laboratory interpretation platform designed to help patients, students, and non-specialists understand laboratory results in clear, accessible language.

The initial hackathon Proof of Concept (PoC) focuses on **Complete Blood Count (CBC)** results.

---

## 🌐 Live Application & Demo

- **Live Platform**: [http://labinsightai.nexsoft.site](http://labinsightai.nexsoft.site)
- **Source Code**: [https://github.com/saqlain-m45/LabInsightAI](https://github.com/saqlain-m45/LabInsightAI)

---

## 🔬 System Architecture — Controlled & Explainable Workflow

LabInsight AI is intentionally designed so that artificial intelligence is **not responsible for core laboratory classification or medical calculation**.

Instead, the platform enforces a 4-stage controlled pipeline:

> **Rules Calculate → Patterns Connect → Evidence Grounds → AI Explains**

```text
[ Patient Input ]
       │
       ▼
┌─────────────────────────┐
│ 1. Rules Calculate      │  --> Deterministically classifies 8 CBC values (LOW / NORMAL / HIGH)
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│ 2. Patterns Connect     │  --> Connects findings into clinical CBC patterns (Microcytic Hypochromic, etc.)
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│ 3. Evidence Grounds     │  --> Compiles explicit rule citations & pattern match logic
└────────────┬────────────┘
             │
             ▼
┌─────────────────────────┐
│ 4. AI Explains          │  --> Google Gemini AI translates evidence into patient-friendly explanations
└─────────────────────────┘
```

1. **Rules Calculate (Step 2)**: Deterministic medical rules compare laboratory values against demonstration reference ranges and classify parameters into `LOW`, `NORMAL`, or `HIGH`.
2. **Patterns Connect (Step 3)**: Pattern logic connects related CBC parameter findings into clinical patterns (e.g. Microcytic Hypochromic Anemia Pattern, Leukocytosis Pattern, Thrombocytopenia Pattern).
3. **Evidence Grounds (Step 4)**: An evidence layer compiles transparent citations of triggering rules and pattern match logic.
4. **AI Explains (Step 4 Gemini API Integration)**: Google's **Gemini AI** (`gemini-3.6-flash`) translates validated evidence into plain-language summaries, parameter breakdowns, and recommended questions to ask a doctor.

---

## 📊 CBC Demonstration Reference Ranges

| Test Parameter | Unit | Demonstration Reference Range | Rule Classification Logic |
| :--- | :--- | :--- | :--- |
| **Hemoglobin** | g/dL | `12–16` | `< 12` (LOW) \| `12–16` (NORMAL) \| `> 16` (HIGH) |
| **WBC** (White Blood Cells) | ×10⁹/L | `4–11` | `< 4` (LOW) \| `4–11` (NORMAL) \| `> 11` (HIGH) |
| **RBC** (Red Blood Cells) | ×10¹²/L | `4.0–5.5` | `< 4.0` (LOW) \| `4.0–5.5` (NORMAL) \| `> 5.5` (HIGH) |
| **Platelets** | ×10⁹/L | `150–450` | `< 150` (LOW) \| `150–450` (NORMAL) \| `> 450` (HIGH) |
| **MCV** (Mean Corpuscular Volume) | fL | `80–100` | `< 80` (LOW) \| `80–100` (NORMAL) \| `> 100` (HIGH) |
| **MCH** (Mean Corpuscular Hemoglobin) | pg | `27–33` | `< 27` (LOW) \| `27–33` (NORMAL) \| `> 33` (HIGH) |
| **MCHC** (Mean Corpuscular Hemoglobin Conc.) | g/dL | `32–36` | `< 32` (LOW) \| `32–36` (NORMAL) \| `> 36` (HIGH) |
| **RDW** (Red Cell Distribution Width) | % | `11.5–14.5` | `< 11.5` (LOW) \| `11.5–14.5` (NORMAL) \| `> 14.5` (HIGH) |

---

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript, Bootstrap 5, Poppins Font, FontAwesome
- **Backend**: PHP
- **AI Service**: Google Gemini API (`gemini-3.6-flash`) with Grounded Fallback Engine
- **Architecture**: Modular REST API + View Controllers

---

## 📁 Repository Structure

```text
LabInsightAI/
├── README.md                 # Project Overview & Architecture Guide
├── index.php                 # Home Page & 4-Step Workflow Concept
├── cbc-analysis.php          # Interactive CBC Input Form & 4-Stage Results View
├── assets/
│   ├── css/
│   │   └── style.css         # Custom Healthcare Design System & Poppins Font
│   └── js/
│       └── app.js            # Validation, AJAX API Handler & Dynamic Rendering
├── includes/
│   ├── header.php            # Shared Navbar & Google Font Import
│   ├── footer.php            # Medical Safety Disclaimer & Footers
│   ├── rules_engine.php      # Deterministic CBC Rules Evaluation Engine
│   ├── pattern_engine.php    # CBC Pattern Matching & Connected Findings Engine
│   └── ai_explain.php        # Evidence Citation & Gemini AI Explanation Service
├── api/
│   ├── rules.php             # Rules Evaluation REST API
│   ├── patterns.php          # Rules + Pattern Detection REST API
│   └── explain.php           # Full 4-Stage Workflow REST API
├── config/
│   ├── ai_config.php         # Gemini Model & API Configuration
│   └── README.md
└── database/
    └── README.md             # MySQL Schema Architecture Guide
```

---

## 🚀 Local Installation & Running Guide

### Option A: Using XAMPP / WAMP / MAMP / Laragon
1. Clone or download the repository into your web server's root directory:
   ```bash
   cd /Applications/XAMPP/xamppfiles/htdocs
   git clone https://github.com/saqlain-m45/LabInsightAI.git
   ```
2. Start the **Apache** server.
3. Open your browser and visit: `http://localhost/LabInsightAI/`

### Option B: Using PHP Built-in Server
1. Open a terminal inside the project directory:
   ```bash
   cd LabInsightAI
   php -S localhost:8000
   ```
2. Open your browser and visit: `http://localhost:8000/`

---

## 🛡️ Medical Safety Disclaimer

**LabInsight AI** is an educational and laboratory-result understanding platform. It is not a doctor, does not replace healthcare professionals, and does not provide medical diagnosis, clinical decisions, or treatment recommendations. Always consult a qualified medical professional for clinical interpretation of laboratory results.

---

## 📜 License

Developed for the AI Healthcare Hackathon MVP. Open for educational and research purposes.
