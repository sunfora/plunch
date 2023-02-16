<?
namespace Plunch;
require "/vendor/autoload.php";
require_once "Table.php";
require_once "Video.php";
require_once "CRUD/Videos.php";
require_once "CRUD/PinnedVideos.php";
require_once "User.php";

use Plunch\Util\Table as Table;

final class Core {

    private CRUD\Videos $videos;

    public function __construct(private User $user, private $db) {
        $this->videos = new CRUD\Videos($user, $db);   
        $this->pinned_videos = new CRUD\PinnedVideos($user, $db);
    }


    public function idle() {
        return null; 
    }

    private function repr_video_list(Array $videos) {
        $repr = function ($video) {
            $indicator = $video->is_watched()? '[*]' : '[ ]';
            return [$indicator, $video->link()];
        };

        $table = array_map($repr, $videos);
        
        return Table\to_string($table);
    } 

    public function list_videos() {
        $videos = $this->videos->read_all(); 
        return $this->repr_video_list($videos); 
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

    public function pin(string $link) {
        $video = $this->videos->read(new Video($link));
        $this->user->pin($video);
        $this->pinned_videos->replace($video);
        return "pinned video $link";
    }

    public function unpin() {
        if (! $this->user->has_pinned() ) {
            return "nothing to unpin";
        }
        $video = $this->user->pinned();
        $this->user->unpin();
        $this->pinned_videos->delete($video);
        return "unpinned video {$video->link()}";
    }

    private function pinned_as_first_arg(callable $action, mixed... $args) {
        if (! $this->user->has_pinned() ) {
            return "nothing is pinned";
        }
        $video = $this->user->pinned();
        $link = $video->link();
        $result = $action($link, ...$args);

        // things may have changed for user, so actualize
        $this->pinned_videos->actualize();
        return $result;
    }

    public function watch_pinned() {
        return $this->pinned_as_first_arg($this->video_watch(...));
    }

    public function unwatch_pinned() {
        return $this->pinned_as_first_arg($this->video_unwatch(...));
    }

    public function rename_pinned(string $link) {
        return $this->pinned_as_first_arg($this->video_rename(...), $link);
    }

    public function delete_pinned() {
        return $this->pinned_as_first_arg($this->video_delete(...));
    }

    public function pick() {
        return $this->user->pinned()->link();
    }
}
