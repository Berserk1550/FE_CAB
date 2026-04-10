<?php
/**
 * FUNCIONES DE SEGURIDAD - MICROSERVICIOS
 * Protecciones contra SQL injection, XSS, Path traversal, etc.
 */

// Protección contra SQL Injection
function safe_query($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if ($params) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Protección contra Path Traversal
function safe_path($path) {
    $base = realpath(BASE_PATH);
    $full = realpath(BASE_PATH . '/' . ltrim($path, '/'));
    
    if ($full === false || strpos($full, $base) !== 0) {
        log_action('Path traversal attempt: ' . $path, 'CRITICAL');
        throw new Exception('Acceso denegado');
    }
    
    return $full;
}

// Validar input
function validate_input($input, $type = 'string', $max_length = 1000) {
    if (strlen($input) > $max_length) {
        throw new Exception('Input demasiado largo');
    }
    
    switch ($type) {
        case 'int':
            if (!filter_var($input, FILTER_VALIDATE_INT)) {
                throw new Exception('Número inválido');
            }
            return (int)$input;
            
        case 'email':
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email inválido');
            }
            return $input;
            
        case 'url':
            if (!filter_var($input, FILTER_VALIDATE_URL)) {
                throw new Exception('URL inválida');
            }
            return $input;
            
        default:
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

// Verificar memoria disponible
function check_memory($required_mb = 10) {
    $limit = ini_get('memory_limit');
    $limit_mb = (int)$limit;
    $used_mb = memory_get_usage(true) / 1024 / 1024;
    
    if ($used_mb + $required_mb > $limit_mb) {
        throw new Exception('Memoria insuficiente');
    }
    
    return true;
}

// Sanitizar nombre de archivo
function sanitize_filename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
    $filename = preg_replace('/(\.){2,}/', '.', $filename);
    return substr($filename, 0, 255);
}

// Verificar extensión de archivo permitida
function allowed_file_extension($filename, $allowed = ['php', 'html', 'css', 'js', 'txt', 'md', 'json']) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowed);
}

// Escapar output HTML
function safe_html($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Escapar output JSON
function safe_json($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}