<?
namespace Plunch\CRUD;

require_once "User.php";
require_once "CRUD/DataBaseLayerException.php";

use Plunch\User as User;

final class PinnedVideos {
    const TABLE = 'user/pinned_videos';

    public function __construct(
        private User $user, 
        private $db
    ) {}

    public function read_if_exists() {
        $query = <<<'SQL'
            SELECT link FROM `%l` 
                WHERE user=%s
        SQL;

        $name = $this->user->name();
        $link = $this->db->queryFirstField(
            $query, 
            static::TABLE, $name
        );
        return $link? new Video($link) : $link;
    }

    public function does_exist() {
        $video = $this->read_if_exists();
        return $video !== null;
    }

    public function read(): Video {
        $video = $this->read_if_exists();
        if ($video === null) {
            throw new NoSuchDataException("nothing is pinned");
        }
        return $video;
    }

    private function form_data(Video $video) {
        return [
            "user" => $this->user->name(),
            "link" => $video->link()
        ];
    }

    private function with_data(string $method, Video $video) {
        $data = $this->form_data($video);
        return $this->db->$method(static::TABLE, $data);
    }

    public function create(Video $video) {
        return $this->with_data('insert', $video);
    }


    public function replace(Video $video) {
        return $this->with_data('replace', $video);
    }

    public function update(Video $video) {
        return $this->with_data('update', $video);
    }

    public function delete() {
        return $this->db->delete(
            static::TABLE, "user=%s", $this->user->name()
        );
    }
}

