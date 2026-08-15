<?php
require_once __DIR__ . '/config.php';

/**
 * Safely validate and move an uploaded image.
 * Returns ['ok' => true, 'path' => 'uploads/xxx.jpg'] or ['ok' => false, 'error' => '...']
 *
 * $uploadDir must be an absolute path ending in a slash.
 * $webPath is the path to store in the DB / use in <img src>, e.g. "uploads/".
 */
function handleImageUpload(array $file, string $uploadDir, string $webPath) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null]; // no file supplied, not an error
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed. Please try again.'];
    }
    if ($file['size'] > MAX_UPLOAD_SIZE_BYTES) {
        return ['ok' => false, 'error' => 'Image is too large (max 5MB).'];
    }

    // Check the real MIME type (not the client-supplied one, which can be spoofed)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_MIME_TYPES, true)) {
        return ['ok' => false, 'error' => 'Only JPG, PNG, GIF, or WEBP images are allowed.'];
    }

    // Check extension too (belt and suspenders)
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_IMAGE_EXTENSIONS, true)) {
        return ['ok' => false, 'error' => 'Unsupported file extension.'];
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate a random, non-guessable filename — never trust the original name
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetFile = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        return ['ok' => false, 'error' => 'Could not save the uploaded file.'];
    }

    return ['ok' => true, 'path' => $webPath . $filename];
}
