<?php
require_once("Utilities/Database.php");

$config = require("Utilities/Configuration.php");
$db = new Database($config["Database"], "root", "Ayman_Database");

$uri = parse_url($_SERVER["REQUEST_URI"])["path"];

$routes = [
    "/" => "Routes/Introduction.php",
    "/users" => "Routes/Users.php",
    "/tokens" => "Routes/Token.php",
    "/register" => "FrontEnd/HTML/Register.html",
    "/dashboard" => "FrontEnd/HTML/Dashboard.html"
];

if (array_key_exists($uri, $routes)) {
    require $routes[$uri];
} else {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found."]);
    die();
}