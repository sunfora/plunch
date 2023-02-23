<?
namespace Plunch\CRUD;
require_once "/vendor/autoload.php";
require_once "User.php";
require_once "Playlist.php";
require_once "CRUD/DataBaseTable.php";
require_once "CRUD/GeneralCRUD.php";
require_once "CRUD/PlaylistMaps.php";

require_once "CRUD/MeekroCreator.php";
require_once "CRUD/Creates.php";

require_once "CRUD/MeekroDeleter.php";
require_once "CRUD/Deletes.php";

require_once "CRUD/MeekroUpdater.php";
require_once "CRUD/Updates.php";

use Plunch\{User, Playlist};

final class Playlists implements DataBaseTable {
    
    public const TABLE = "playlist/playlists";
    public const SCHEMA = ["name"];

    use GeneralCRUD;

    private Creates $creator;
    private Deletes $deleter;
    private Updates $updater;

    public function __construct(
        private User $user, 
        private $db
    ) {
        $this->creator = new MeekroCreator($this, $db);
        $this->deleter = new MeekroDeleter($this, $db);
        $this->updater = new MeekroUpdater($this, $db);
    }

    // DataBaseTable Interface [
    public function entity_from_row(Array $row) {
        return new Playlist($row["name"]);
    }

    public function row_from_entity($playlist): Array {
        return [
            "user" => "{$this->user->name()}",
            "name" => "{$playlist->name()}"
        ];
    }

    private function locate_all() {
        $where = new \WhereClause('and');
        $where->add('user=%s', $this->user->name());
        return $where;
    }

    public function locate($playlist) {
        $where = $this->locate_all();
        $where->add('name LIKE %s', $playlist->name());
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
    public function read_if_exists(Playlist $playlist): ?Playlist {
        return $this->general_read_if_exists($playlist);
    }

    public function does_exist(Playlist $playlist): bool {
        return $this->general_does_exist($playlist);
    }
    
    public function read(Playlist $playlist): Playlist {
        return $this->general_read(
            "playlist {$playlist->name()} does not exist", $playlist
        );
    }

    public function read_all(): Array {
        return $this->general_read_where($this->locate_all());
    }
    // ]

    // GeneralUpdate Trait [
    public function update(Playlist $dest, Playlist $new) {
        return $this->updater->update($dest, $new);
    }
    // ]

    // GeneralCreate Trait [
    public function create(Playlist $playlist) {
        return $this->creator->create($playlist);
    }
    // ]

    // GeneralDelete Trait [
    public function delete(Playlist $playlist) {
        return $this->deleter->delete($playlist);
    }
    // ]

    // Other [
    public function read_with_videos(Playlist $playlist) {
        $pls = $this->read($playlist);
        $maps = new PlaylistMaps($pls, $this->user, $this->db);
        return $pls->concat($maps->read_videos());
    } 
    // ]
}
