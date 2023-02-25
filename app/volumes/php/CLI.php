<?
namespace Plunch;

require_once "/vendor/autoload.php";
require_once "Interpreter.php";
require_once "Core.php";
require_once "CRUD/Users.php";
require_once "User.php";
require_once "InternalException.php";

final class CLI {
    const VIDEO_SYNTAX = [
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

    const VIDEO_TIMESTAMPS_SYNTAX = [
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

    const PINNED_SYNTAX = [
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

    const PINNED_TIMESTAMPS_SYNTAX = [
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

    const PLAYLIST_SYNTAX = [
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

    const PLANNER_SYNTAX = [
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

    const SYNTAX = [
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
        ...CLI::VIDEO_SYNTAX,
        ...CLI::PINNED_SYNTAX,
        ...CLI::VIDEO_TIMESTAMPS_SYNTAX,
        ...CLI::PINNED_TIMESTAMPS_SYNTAX,
        ...CLI::PLAYLIST_SYNTAX,
        ...CLI::PLANNER_SYNTAX
    ];

    private Interpreter $interpreter;

    public function __construct(string $user) {
        $connection = new \MeekroDB("mariadb", "root", "root", "plunch");

        $users = new CRUD\Users($connection);
        $user = $users->read(new User($user));
        $core = new Core($user, $connection);

        $this->interpreter = new Interpreter(static::SYNTAX, $core);
    }

    public function run(string $cmd): string {
        try {
            return $this->interpreter->execute($cmd);
        } catch (InternalException $e) {
            return $e->getMessage();
        } catch (\Parsica\Parsica\ParserHasFailed $e) {
            return $e->getMessage();
        } catch (mysqli_sql_exception $e) {
            return "unexpected db failure: {$e->getCode()}";
        } catch (\Throwable $e) {
            return "unexpected unknown failure: {$e->getMessage()}";
        }
    }
}
