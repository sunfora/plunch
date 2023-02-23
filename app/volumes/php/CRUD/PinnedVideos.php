<?
namespace Plunch\CRUD;
require_once "/vendor/autoload.php";
require_once "User.php";
require_once "Video.php";
require_once "Table.php";
require_once "CRUD/DataBaseTable.php";
require_once "CRUD/GeneralCRUD.php";
require_once "CRUD/GeneralReplace.php";
require_once "CRUD/Videos.php";

require_once "CRUD/MeekroCreator.php";
require_once "CRUD/Creates.php";

require_once "CRUD/MeekroDeleter.php";
require_once "CRUD/Deletes.php";

use Plunch\{Video, User};
use Plunch\Util\Table as Table;

final class PinnedVideos implements DataBaseTable {
    use GeneralCRUD;
    use GeneralReplace;

    public const TABLE = 'user/pinned_videos';
    public const SCHEMA = ["link"];

    private Videos $videos;
    private Creates $creator;
    private Deletes $deleter;

    public function __construct(
        private User $user, 
        private $db
    ) {
        $this->videos = new Videos($user, $db);
        $this->creator = new MeekroCreator($this, $db);
        $this->deleter = new MeekroDeleter($this, $db);
    }

    // DataBaseTable Interface [
    public function entity_from_row(Array $row) {
        $row = Table\shed_row($row, self::SCHEMA);
        return $this->videos->read(new Video($row["link"]));
    }

    public function row_from_entity($video): Array {
        return [
            "user" => "{$this->user->name()}",
            "link" => "{$video->link()}"
        ];
    }
    
    public function locate($video) {
        $where = new \WhereClause('AND');
        $where->add('user=%s', $this->user->name());
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
    public function read_if_exists(): ?Video {
        return $this->general_read_if_exists(null);
    }

    public function does_exist(): bool {
        return $this->general_does_exist(null);
    }
    
    public function read(): Video {
        return $this->general_read("nothing is pinned", null);
    }
    // ]

    // GeneralUpdate Trait [
    public function update(Video $new) {
        return $this->general_update(null, $new);
    }
    // ]

    // GeneralCreate Trait [
    public function create(Video $video) {
        return $this->creator->create($video);
    }
    // ]

    // GeneralDelete Trait [
    public function delete() {
        return $this->deleter->delete(null);
    }
    // ]

    // GeneralReplace Trait  [
    public function replace(Video $video) {
        return $this->general_replace($video);
    }
    // ]

    // Other [
    public function actualize() {
        $video = $this->read_if_exists();
        if ($video !== null) {
            $this->user->pin($video);
        } else {
            $this->user->unpin();
        }
    }
    // ]
}
