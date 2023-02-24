<?
namespace Plunch\CRUD;
require_once "/vendor/autoload.php";
require_once "User.php";
require_once "Playlist.php";
require_once "CRUD/DataBaseTable.php";
require_once "CRUD/Playlists.php";

require_once "CRUD/MeekroCreator.php";
require_once "CRUD/Creates.php";

require_once "CRUD/MeekroDeleter.php";
require_once "CRUD/Deletes.php";

require_once "CRUD/MeekroUpdater.php";
require_once "CRUD/Updates.php";

require_once "CRUD/MeekroReader.php";
require_once "CRUD/Reads.php";

use Plunch\{User, Playlist};

final class PlannedReader extends MeekroReader {
    protected function not_exists_message($planned): string {
        return "playlist {$planned["playlist"]->name()} is not planned";
    }   
}

final class PlannedPlaylists implements DataBaseTable {
    
    public const TABLE = "planner/playlists";
    public const SCHEMA = ["name", "priority"];

    private Creates $creator;
    private Deletes $deleter;
    private Updates $updater;
    private Reads $reader;

    public function __construct(
        private User $user, 
        private $db
    ) {
        $this->playlists = new Playlists($user, $db);
        $this->creator = new MeekroCreator($this, $db);
        $this->deleter = new MeekroDeleter($this, $db);
        $this->updater = new MeekroUpdater($this, $db);
        $this->reader = new PlannedReader($this, $db);
    }

    // DataBaseTable Interface [
    public function entity_from_row(Array $row) {
        return [
            "playlist" => $this->playlists->entity_from_row($row), 
            "priority" => \intval($row["priority"])
        ];
    }

    public function row_from_entity($planned): Array {
        return [
            "priority" => $planned["priority"],
            ...$this->playlists->row_from_entity($planned["playlist"]), 
        ];
    }

    private function locate_all() {
        $where = new \WhereClause('and');
        $where->add('user=%s', $this->user->name());
        return $where;
    }

    public function locate($planned) {
        return $this->playlists->locate($planned["playlist"]);
    }

    public function name(): string {
        return self::TABLE;
    }
    public function schema(): Array {
        return self::SCHEMA;
    }

    // ]

    // GeneralRead Trait [
    public function read_if_exists(Array $planned): ?Array {
        return $this->reader->read_if_exists($planned);
    }

    public function does_exist(Array $planned): bool {
        return $this->read_if_exists($planned) !== null;
    }
    
    public function read(Array $planned): Array {
        return $this->reader->read($planned);
    }

    public function read_all(): Array {
        return $this->reader->read_where($this->locate_all(), "ORDER BY priority DESC");
    }
    // ]

    // GeneralUpdate Trait [
    public function update(Array $dest, Array $new) {
        return $this->updater->update($dest, $new);
    }
    // ]

    // GeneralCreate Trait [
    public function create(Array $planned) {
        return $this->creator->create($planned);
    }
    // ]

    // GeneralDelete Trait [
    public function delete(Array $planned) {
        return $this->deleter->delete($planned);
    }
    // ]
}
