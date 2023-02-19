<?
namespace Plunch\CRUD;
require_once "/vendor/autoload.php";
require_once "Table.php";
require_once "Video.php";
require_once "User.php";
require_once "Timestamp.php";
require_once "CRUD/DataBaseTable.php";
require_once "CRUD/GeneralCRUD.php";

use Plunch\{User, Video, Timestamp};
use Plunch\Util\Table as Table;

final class Timestamps implements DataBaseTable {
    
    public const TABLE = "video/timestamps";
    public const SCHEMA = ["stamp", "name"];

    use GeneralCRUD;

    public function __construct(
        private User $user, 
        private Video $video, 
        private $db
    ) {}

    // DataBaseTable Interface
    public function entity_from_row(Array $row) {
        $row = Table\descheme_row($row, self::SCHEMA);
        return Timestamp::from_strtime(...$row);
    }

    public function row_from_entity($timestamp): Array {
        return [
            "user" => "{$this->user->name()}",
            "link" => "{$this->video->link()}",
            "stamp" => "{$timestamp->strtime()}",
            "name" => "{$timestamp->name()}"
        ];
    }
    
    public function locate($timestamp) {
        $where = new \WhereClause('AND');
        $where->add('user=%s', $this->user->name());
        $where->add('link LIKE %s', $this->video->link());
        $where->add('stamp=%s', $timestamp->strtime());
        return $where;
    }
    
    // GeneralRead Trait
    public function read_if_exists(Timestamp $timestamp): ?Timestamp {
        return $this->general_read_if_exists($timestamp);
    }

    public function does_exist(Timestamp $timestamp): bool {
        return $this->general_does_exist($timestamp);
    }
    
    public function read(Timestamp $timestamp): Timestamp {
        return $this->general_read(
            "timestamp $timestamp does not exist", $timestamp
        );
    }

    public function read_all(): Array {
        $where = new \WhereClause('and');
        $where->add('user=%s', $this->user->name());
        $where->add('link LIKE %s', $this->video->link());
        return $this->general_read_where($where, $tail="ORDER BY stamp ASC");
    }

    // GeneralUpdate Trait
    public function update(Timestamp $dest, Timestamp $new) {
        return $this->general_update($dest, $new);
    }
    
    // GeneralCreate Trait
    public function create(Timestamp $timestamp) {
        return $this->general_create($timestamp);
    }

    // GeneralDelete Trait
    public function delete(Timestamp $timestamp) {
        return $this->general_delete($timestamp);
    }
}
