<?
namespace Plunch\CRUD;
require_once "/vendor/autoload.php";
require_once "Table.php";
require_once "Video.php";
require_once "User.php";
require_once "Timestamp.php";
require_once "CRUD/DataBaseTable.php";
require_once "CRUD/GeneralCRUD.php";

require_once "CRUD/MeekroCreator.php";
require_once "CRUD/Creates.php";

require_once "CRUD/MeekroDeleter.php";
require_once "CRUD/Deletes.php";

require_once "CRUD/MeekroUpdater.php";
require_once "CRUD/Updates.php";

require_once "CRUD/MeekroReader.php";
require_once "CRUD/Reads.php";

use Plunch\{User, Video, Timestamp};
use Plunch\Util\Table as Table;

final class TimestampReader extends MeekroReader {
    protected function not_exists_message($timestamp): string {
        return "timestamp $timestamp does not exist";
    }
}

final class Timestamps implements DataBaseTable {
    
    public const TABLE = "video/timestamps";
    public const SCHEMA = ["stamp", "name"];

    use GeneralCRUD;

    private Creates $creator;
    private Deletes $deleter;
    private Updates $updater;
    private Reads $reader;

    public function __construct(
        private User $user, 
        private Video $video, 
        private $db
    ) {
        $this->creator = new MeekroCreator($this, $db);
        $this->deleter = new MeekroDeleter($this, $db);
        $this->updater = new MeekroUpdater($this, $db);
        $this->reader = new TimestampReader($this, $db);
    }

    // DataBaseTable Interface [
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

    public function name(): string {
        return self::TABLE;
    }

    public function schema(): Array {
        return self::SCHEMA;
    }

    // ]

    // GeneralRead Trait [
    public function read_if_exists(Timestamp $timestamp): ?Timestamp {
        return $this->reader->read_if_exists($timestamp);
    }

    public function does_exist(Timestamp $timestamp): bool {
        return $this->read_if_exists($timestamp) !== null;
    }
    
    public function read(Timestamp $timestamp): Timestamp {
        return $this->reader->read($timestamp);
    }

    public function read_all(): Array {
        $where = new \WhereClause('and');
        $where->add('user=%s', $this->user->name());
        $where->add('link LIKE %s', $this->video->link());
        return $this->reader->read_where($where, $tail="ORDER BY stamp ASC");
    }

    // ]

    // GeneralUpdate Trait [
    public function update(Timestamp $dest, Timestamp $new) {
        return $this->updater->update($dest, $new);
    }
    // ]
    
    // GeneralCreate Trait [
    public function create(Timestamp $timestamp) {
        return $this->creator->create($timestamp);
    }
    // ]

    // GeneralDelete Trait [
    public function delete(Timestamp $timestamp) {
        return $this->deleter->delete($timestamp);
    }
    // ]
}
