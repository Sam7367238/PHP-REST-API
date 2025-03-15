<?php

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

function handleIndex() {
    http_response_code(200);
    echo json_encode(["message" => "Hello, you are communicating with the REST API."]);
}

switch ($method) {
    case 'GET':
        handleIndex();
        break;
    case 'POST':
        handleIndex();
        break;
    case 'PUT':
        handleIndex();
        break;
    case 'DELETE':
        handleIndex();
        break;
    default:
        echo json_encode(['message' => 'Invalid request method']);
        break;
}