<?
require_once "CLI.php";
require "cmd-form.php";

if (! $_POST) {
    display(fn () => "Welcome!" );
    exit;
}

$user_name = $_POST["user_name"];

$plunch = new Plunch\CLI("$user_name");

display(function () use ($plunch) {
    ["cmd" => $cmd] = $_POST;
    return $plunch->run($cmd);
});
