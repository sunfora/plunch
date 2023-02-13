<?
require "api.php";
require_once "Interpreter.php";
require_once "Core.php";
require "cmd-form.php";

if (! $_POST) {
    display(fn () => "Welcome!" );
    exit;
}

$user_name = $_POST["user_name"];

$connection = new MeekroDB("mariadb", "root", "root", "plunch");

display(function () use ($connection) {
    print_r([...$connection->query("SHOW TABLES")]);
});

$core = new Core($connection, $user_name);
// need modules :(  

$video_syntax = [
    "video_add" => [
        ["add", "video"], 
        ["quoted_string"]
    ],
    "video_delete" => [
        ["delete", "video"],
        ["quoted_string"]
    ],
    "video_watch" => [
        ["watch", "video"],
        ["quoted_string"]
    ],
    "video_unwatch" => [
        ["unwatch", "video"],
        ["quoted_string"]
    ],
    "video_rename" => [
        ["rename", "video"],
        ["quoted_string", "quoted_string"]
    ]
];

$syntax = [
    "idle" => [
        [], []
    ],
    "list" => [
        ["list"], ["listable"]
    ],
    ...$video_syntax,    
];

$interpreter = new Interpreter($syntax, $core);

display(function () use ($interpreter) {
        ["cmd" => $cmd] = $_POST;
        try {
            echo $interpreter->execute($cmd) . "\n";
        } catch (Throwable $e) {
            echo $e->getMessage() . "\n";
        }
});
