<?php
// config/cors.php
header("Access-Control-Allow-Origin: *"); // Change '*' to your Vite dev server URL (e.g., 'http://localhost:5173') in production
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
