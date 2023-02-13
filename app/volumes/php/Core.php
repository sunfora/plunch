<?
require "/vendor/autoload.php";
require_once "Table.php";
require_once "Video.php";

use Videos\Video as Video;

final class Core {

    public function __construct(private $db, private string $user) {}


    public function idle() {
        return null; 
    }

    public function list_videos() {
        $videos = Videos\load_all($this->user, $this->db); 
        
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
        $video = new Video($link);
        $video->insert($this->user, $this->db);
        return "added video $link"; 
    }

    public function video_delete(string $link) {
        $video = new Video($link);
        $video->delete($this->user, $this->db);
        return "deleted video $link"; 
    }

    private function video_update(string $link, callable $act) {
        $video = Video::load(
            link: $link, 
            user: $this->user, 
            db: $this->db
        );
       
        $result = $act($video);
       
        $video->update(
            dest: $link, 
            user: $this->user, 
            db: $this->db
        );
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
