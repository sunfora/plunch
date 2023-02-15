<?
namespace Plunch;
require "/vendor/autoload.php";
require_once "Table.php";
require_once "Video.php";
require_once "CRUD/Videos.php";
require_once "User.php";

use Plunch\Util\Table as Table;

final class Core {

    private CRUD\Videos $videos;

    public function __construct(private User $user, private $db) {
        $this->videos = new CRUD\Videos($user, $db);    
    }


    public function idle() {
        return null; 
    }

    public function list_videos() {
        $videos = $this->videos->read_all(); 
        
        $repr = function ($video) {
            $indicator = $video->is_watched()? '[*]' : '[ ]';
            return [$indicator, $video->link()];
        };

        $table = array_map($repr, $videos);
        
        return Table\to_string($table);
    }

    public function list(string $listable) {
        $method = "list_" . $listable;
        return $this->$method();
    }

    public function video_add(string $link) {
        $this->videos->create(new Video($link));
        return "added video $link"; 
    }

    public function video_delete(string $link) {
        $video = $this->videos->read(new Video($link));
        $this->videos->delete($video);
        return "deleted video $link"; 
    }

    private function video_update(string $link, callable $act) {
        $video = $this->videos->read(new Video($link));
        $old = clone $video;
        $result = $act($video);
        $this->videos->update($old, $video);
        return $result;
    }

    public function video_watch(string $link) {
        $this->video_update(
            $link, 
            fn ($video) => $video->watch()
        );
        return "video $link is now watched";
    }

    public function video_unwatch(string $link) {
        $this->video_update(
            $link, 
            fn ($video) => $video->unwatch()
        );
        return "video $link is no longer watched";
    }

    public function video_rename(string $link_old, string $link_new) {
        $this->video_update(
            $link_old, 
            fn ($video) => $video->rename($link_new)
        );
        return "$link_old -> $link_new";
    }
}
