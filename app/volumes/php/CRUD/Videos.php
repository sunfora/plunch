<?
namespace Plunch\CRUD;

require_once "Table.php";
require_once "Video.php";
require_once "User.php";
require_once "CRUD/DataBaseLayerException.php";

use Plunch\Video as Video;
use Plunch\Util\Table as Table;
use Plunch\User as User;

final class Videos {
    const TABLE = 'video/videos';
    const SCHEMA = ['link', 'watched'];

    public function __construct(private User $user, private $db) {}

    public function update(Video $dest, Video $new) {
        $params = [
            "watched" => $new->is_watched(),
            "link" => $new->link()
        ];
        
        return $this->db->update(
            static::TABLE, $params, 
            "link LIKE %s AND user=%s", 
            $dest->link(), $this->user->name()
        );
    }

    public function create(Video $video) {
        return $this->db->insert(static::TABLE, [
            "user" => $this->user->name(),
            "link" => $video->link()
        ]);
    }

    public function delete(Video $video) {
        return $this->db->delete(
            static::TABLE,
            "user=%s AND link LIKE %s", 
            $this->user->name(), $video->link()
        );
    }
    
    public function read_if_exists(Video $video) {
        $query =<<<'SQL'
            SELECT link, watched FROM `%l`
                WHERE user=%s AND link LIKE %s
        SQL;

        $link = $video->link();
        $user = $this->user->name();

        $data = $this->db->queryFirstRow(
            $query, 
            static::TABLE, $user, $link
        );
        if ($data === null) {
            return null;
        }
        return new Video(...$data);
    }

    public function does_exist(Video $video) {
        return $this->read_if_exists($video) === null;
    }

    public function read(Video $video) {
        $result = $this->read_if_exists($video);
        if ($result === null) {
            $link = $video->link();
            throw new NoSuchDataException("video $link does not exist");    
        }
        return $result;
    }

    private function cast_types(Array $results) {
        $type_cast = fn ($value, $key) => match ($key) {
            "link" => $value,
            "watched" => \boolval(\intval($value))   
        };
         
        $type_cast_entry = fn ($entry) => \array_map(
            $type_cast, $entry, static::SCHEMA
        );

        $results = Table\descheme($results, static::SCHEMA);
        $results = \array_map($type_cast_entry, $results);
        $results = Table\scheme($results, static::SCHEMA);

        return $results;
    }

    private function read_all_as_table() {
        $db = $this->db;
        $user = $this->user->name();

        // retrieve data
        $params = \implode(", ", static::SCHEMA);
        $results = $db->query(
            "SELECT %l FROM `%l` WHERE user=%s",
            $params, static::TABLE, $user
        );
        
        return $this->cast_types($results);
    }

    public function read_all() {
        $table = $this->read_all_as_table();
        $constr = fn ($entry) => new Video(...$entry);
        return \array_map($constr, $table);
    }
}

