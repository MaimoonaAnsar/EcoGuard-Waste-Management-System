<?php
// ============================================================
// EcoGuard Configuration
// Keep this file OUT of version control (add to .gitignore).
// ============================================================

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecoguard');

// --- Maps ---
// Using Leaflet + OpenStreetMap — no API key needed.
// (Nominatim, the free geocoder, is used for address search.)

// --- Uploads ---
define('MAX_UPLOAD_SIZE_BYTES', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_IMAGE_MIME_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
