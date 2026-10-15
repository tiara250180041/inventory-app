<?php
// helpers/escape.php

/**
 * Fungsi helper untuk meng-escape output HTML (Mencegah XSS)
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}