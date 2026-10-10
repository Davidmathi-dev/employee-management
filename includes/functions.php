<?php

function sendJsonResponse($status, $message, $data = [], $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json');
    $response = ['status' => $status];
    
    if ($message !== null) {
        $response['message'] = $message;
    }
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    exit;
}

function cleanInput($data) {
    if ($data === null) return '';
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
