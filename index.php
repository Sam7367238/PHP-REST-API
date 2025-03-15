<?php
require_once("Database.php");

$config = require("Configuration.php");
$db = new Database($config["Database"], "root", "Ayman_Database");

$uri = parse_url($_SERVER["REQUEST_URI"])["path"];

$routes = [
    "/" => "Routes/Introduction.php",
    "/users" => "Routes/Users.php",
    "/tokens" => "Routes/Token.php"
];

if (array_key_exists($uri, $routes)) {
    require $routes[$uri];
} else {
    echo $uri;
    echo "<br>";
    http_response_code(404);
    echo "Page not found.";
    die();
}