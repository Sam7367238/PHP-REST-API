<?php

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

function handleGet($db, $input) {

    if (empty($input["ID"])) {
        http_response_code(400);
        echo json_encode(["message" => "Please provide an ID."]);
        exit();
    }
    
    $row = $db -> query("SELECT * FROM PHP_Users WHERE ID = ?", [$input["ID"]]) -> fetch();
    
    if (!$row) {
        http_response_code(404);
        echo json_encode(["message" => "User not found."]);
        exit();
    }

    http_response_code(200);
    echo json_encode($row);
}

function handlePost($db, $input) {
    if (empty($input["Username"]) || empty($input["Password"])) {
        http_response_code(400);
        echo json_encode(["message" => "Please input all the fields."]);
        exit();
    }

    $row = $db -> query("SELECT * FROM PHP_Users WHERE Username = ?", [$input["Username"]]) -> fetch();

    if ($row) {
        http_response_code(409);
        echo json_encode(["message" => "A user already exists with the same username."]);
        exit();
    }

    $password = password_hash($input["Password"], PASSWORD_DEFAULT);

    $db -> query("INSERT INTO PHP_Users (Username, Password) VALUES (?, ?);", [$input["Username"], $password]);

    http_response_code(201);
    echo json_encode(["message" => "A new user has been created.", "id" => (int) $db -> getLastInsertId()]);
}

switch ($method) {
    case 'GET':
        handleGet($db, $input);
        break;
    case 'POST':
        handlePost($db, $input);
        break;
    default:
        echo json_encode(['message' => 'Invalid request method']);
        break;
}