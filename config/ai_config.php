<?php
/**
 * LabInsight AI — Gemini AI API Configuration
 */

// Retrieve API key from environment variable GEMINI_API_KEY or form input
$geminiApiKey = getenv('GEMINI_API_KEY') ?: '';

define('GEMINI_API_KEY', $geminiApiKey);
define('GEMINI_MODEL', 'gemini-3.6-flash');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent');
