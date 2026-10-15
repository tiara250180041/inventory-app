<?php
// helpers/validation.php

/**
 * Memeriksa apakah nilai merupakan angka bulat positif (> 0)
 */
function validate_positive_number($value) {
    return is_numeric($value) && (int)$value > 0;
}

/**
 * Memeriksa apakah nilai merupakan angka non-negatif (>= 0)
 */
function validate_non_negative_number($value) {
    return is_numeric($value) && (int)$value >= 0;
}

/**
 * Memeriksa apakah teks tidak kosong setelah di-trim
 */
function validate_required($value) {
    return !empty(trim($value));
}

/**
 * Membersihkan input string dari karakter berbahaya (Sanitization)
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}