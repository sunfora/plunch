<?
require "api.php";
require_once "Interpreter.php";
require_once "Core.php";
require_once "CRUD/Users.php";
require_once "User.php";
require_once "InternalException.php";
require "cmd-form.php";

if (! $_POST) {
    display(fn () => "Welcome!" );
    exit;
}

$user_name = $_POST["user_name"];

$connection = new MeekroDB("mariadb", "root", "root", "plunch");

$users = new Plunch\CRUD\Users($connection);
$user = $users->read(new Plunch\User($user_name));

$core = new Plunch\Core($user, $connection);
// need modules :(  

$video_syntax = [
    "list_videos" => [
        ["list", "videos"], []
    ],
    "add_video" => [
        ["add", "video"], ["arg"]
    ],
    "delete_video" => [
        ["delete", "video"], ["arg"]
    ],
    "watch_video" => [
        ["watch", "video"], ["arg"]
    ],
    "unwatch_video" => [
        ["unwatch", "video"], ["arg"]
    ],
    "rename_video" => [
        ["rename", "video"], ["arg", "arg"]
    ]
];

$video_timestamps_syntax = [
    "add_timestamp_to_video" => [
        ["add", "timestamp", "to"], 
        ["arg", "time", "arg"]
    ],
    "delete_timestamp_from_video" => [
        ["delete", "timestamp", "from"], 
        ["arg", "time"]
    ],
    "rename_timestamp_for_video" => [
        ["rename", "timestamp", "for"], 
        ["arg", "time", "arg"]
    ],
    "list_video_timestamps" => [
        ["list", "timestamps", "of"],
        ["arg"]
    ],
    "reset_timestamp_for_video" => [
        ["reset", "timestamp", "for"],
        ["arg", "time", "time"]
    ]
];

$pinned_syntax = [
    "delete_pinned" => [
        ["delete"], []
    ],
    "watch_pinned" => [
        ["watch"], []
    ],
    "unwatch_pinned" => [
        ["unwatch"], []
    ],
    "rename_pinned" => [
        ["rename"], ["arg"]
    ],
];

$pinned_timestamps_syntax = [
    "add_timestamp" => [
        ["add", "timestamp"], 
        ["time", "arg"]
    ],
    "delete_timestamp" => [
        ["delete", "timestamp"], 
        ["time"]
    ],
    "rename_timestamp" => [
        ["rename", "timestamp"], 
        ["time", "arg"]
    ],
    "list_timestamps" => [
        ["list", "timestamps"],
        []
    ],
    "reset_timestamp" => [
        ["reset", "timestamp"],
        ["time", "time"]
    ]
];

$playlist_syntax = [
    "list_playlists" => [
        ["list", "playlists"], []
    ],
    "add_playlist" => [
        ["add", "playlist"], ["arg"]
    ],
    "rename_playlist" => [
        ["rename", "playlist"], ["arg", "arg"]
    ],
    "delete_playlist" => [
        ["delete", "playlist"], ["arg"]
    ],
    "list_playlist_videos" => [
        ["list", "videos", "in"],
        ["arg"]
    ],
    "mend_playlist" => [
        ["mend", "playlist"],
        ["arg", "video_list"]
    ],
    "clear_playlist" => [
        ["clear", "playlist"],
        ["arg"]
    ],
    "playlist_status" => [
        ["status", "of"], ["arg"]
    ],
];

$planner_syntax = [
    "list_plan" => [
        ["list", "plan"], []
    ],
    "plan" => [
        ["plan"], ["planned"]
    ],
    "unplan" => [
        ["unplan"], ["planned"]
    ],
    "renice" => [
        ["renice"], ["planned"]
    ],
    "pick" => [
        ["pick"], []
    ]
];

$syntax = [
    "idle" => [
        [], []
    ],
    "pin" => [
        ["pin"], ["arg"]
    ],
    "unpin" => [ 
        ["unpin"], []
    ]
    ,
    "pinned" => [
        ["pinned"], []
    ],
    "grep_timestamps" => [
        ["grep", "timestamps"],
        ["arg"]
    ],
    ...$video_syntax,
    ...$pinned_syntax,
    ...$video_timestamps_syntax,
    ...$pinned_timestamps_syntax,
    ...$playlist_syntax,
    ...$planner_syntax
];

$interpreter = new Interpreter($syntax, $core);

display(function () use ($interpreter) {
        ["cmd" => $cmd] = $_POST;
        try {
            echo $interpreter->execute($cmd) . "\n";
        } catch (Plunch\InternalException $e) {
            echo $e->getMessage();
        } catch (Parsica\Parsica\ParserHasFailed $e) {
            echo $e->getMessage();
        } catch (mysqli_sql_exception $e) {
            var_dump($e);
            echo "unexpected db failure: {$e->getCode()}";
        }
});
