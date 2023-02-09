<?
require "api.php";
require_once "MariaDB.php";
require_once "Interpreter.php";
require_once "Core.php";
require "cmd-form.php";

$connection = new MariaDB("mariadb", "root", "root", "hello");

$core = new Core($connection);

$syntax = [
    "idle" => [[], []],
    "get" => [["get"], ["identifier"]],
    "set" => [["set"], ["identifier", "quoted_string"]]
];

$interpreter = new Interpreter($syntax, $core);

display(function () use ($interpreter) {
    if ($_POST) {
        ["cmd" => $cmd] = $_POST;
        try {
            echo $interpreter->execute($cmd) . "\n";
        } catch (Throwable $e) {
            echo $e->getMessage() . "\n";
        }
    }
});
