<?php

header("Content-Type: application/json");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

function returnUserInfo($db, $input) {

    if (empty($input["Token"])) {
        http_response_code(400);
        echo json_encode(["message" => "Please provide a token for the user."]);
        exit();
    }

    $row = $db -> query("SELECT * FROM Tokens WHERE Token = ?", [$input["Token"]]) -> fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(["message" => "Token not found."]);
        exit();
    }

    $rows = $db -> query("SELECT * FROM PHP_Users WHERE ID = ?", [$row["User_ID"]]) -> fetch();

    if (!$rows) {
        http_response_code(404);
        echo json_encode(["message" => "User not found."]);
        exit();
    }

    http_response_code(200);
    echo json_encode(["ID" => $rows["ID"], "Username" => $rows["Username"]]);
}

function verifyPassword($db, $input) {
    if (empty($input["Username"])) {
        http_response_code(400);
        echo json_encode(["message" => "Please input your username."]);
        exit();
    }

    if (empty($input["Password"])) {
        http_response_code(400);
        echo json_encode(["message" => "Please input your password."]);
        exit();
    }

    $row = $db -> query("SELECT * FROM PHP_Users WHERE Username = ?", [$input["Username"]]) -> fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(["message" => "User not found."]);
        exit();
    }

    if (password_verify($input["Password"], $row["Password"])) {
        return true;
        exit();
    } else {
        return false;
        exit();
    }
}

function handleGet($db, $input) {

    if (empty($input["Token"])) {
        http_response_code(400);
        echo json_encode(["message" => "Please provide a token to verify."]);
        exit();
    }

    $row = $db -> query("SELECT * FROM Tokens WHERE Token = ?", [$input["Token"]]) -> fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(["message" => "Cannot verify token."]);
        exit();
    }

    $datetime = new DateTime();
    // $datetime -> setTimezone(new DateTimeZone("UTC"));
    $expires = new DateTime($row["Expires"]);
    $expires = $expires -> format("Y-m-d H:i:s");

    if ($expires < $datetime -> format('Y-m-d H:i:s')) {
        http_response_code(410);
        echo json_encode(["message" => "Token has expired."]);
        $db -> query("DELETE FROM Tokens WHERE Token = ?", [$input["Token"]]);
        exit();
    }

    http_response_code(200);
    echo json_encode(["message" => "Successfully verified."]);
}

function handlePost($db, $input) {
    if (!empty($input["GetInfo"])) {
        returnUserInfo($db, $input);
        exit();
    }

    if (!empty($input["Authorize"])) {
        $shouldAuth = verifyPassword($db, $input);
    }

    if (!empty($input["Token"])) {
        handleGet($db, $input);
        exit();
    }

    if (empty($input["Username"])) {
        http_response_code(400);
        echo json_encode(["message" => "Please provide a username."]);
        exit();
    }

    $row = $db -> query("SELECT * FROM PHP_Users WHERE Username = ?", [$input["Username"]]) -> fetch();
    
    if (!$row) {
        http_response_code(404);
        echo json_encode(["message" => "User not found."]);
        exit();
    }

    if (!$shouldAuth) {
        http_response_code(401);
        echo json_encode(["message" => "Incorrect password."]);
        exit();
    }

    $tokenRows = $db -> query("SELECT * FROM Tokens WHERE User_ID = ?", [$row["ID"]]) -> fetchAll();

    if (count($tokenRows) < 1) {
        $token = bin2hex(random_bytes(32));
        $db -> query("INSERT INTO Tokens (Token, User_ID, Expires) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY))", [$token, $row["ID"]]);

        http_response_code(201);
        echo json_encode(["message" => "Token created, take it.", "token" => $token]);
    } else if (count($tokenRows) > 0) {
        http_response_code(200);
        echo json_encode(["message" => "Token created, take it.", "token" => $tokenRows[0]["Token"]]);
    }
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