<?
namespace Plunch\CRUD;

require "/vendor/autoload.php";

require_once "User.php";
require_once "Video.php";
require_once "Playlist.php";

require_once "CRUD/Videos.php";
require_once "CRUD/GeneralCRUD.php";
require_once "CRUD/DataBaseTable.php";
require_once "CRUD/ErrorCode.php";
require_once "InternalException.php";

require_once "CRUD/MeekroCreator.php";
require_once "CRUD/Creates.php";

require_once "CRUD/MeekroDeleter.php";
require_once "CRUD/Deletes.php";

require_once "CRUD/MeekroUpdater.php";
require_once "CRUD/Updates.php";

require_once "CRUD/MeekroReader.php";
require_once "CRUD/Reads.php";

use Plunch\{User, Video, Playlist, InternalException};

final class MapsReader extends MeekroReader {
    public function __construct(private string $name, $table, $db) {
        parent::__construct($table, $db);
    }

    protected function table_expr(): string {
        $list = '`' . PlaylistMaps::TABLE . '`';
        $videos = '`' . Videos::TABLE . '`';
        return <<<SQL
            $list LEFT JOIN $videos
                ON $list.user=$videos.user AND $list.link=$videos.link
        SQL;
    }

    protected function read_schema() {
        $add_prefix = fn ($table) => fn ($field) => "`$table`." . $field;
        $video = \array_map($add_prefix(Videos::TABLE), Videos::SCHEMA);
        $rest = \array_map($add_prefix(PlaylistMaps::TABLE), ["parent"]);
        $result = $this->make_schema_from([...$rest, ...$video]);
        return $result;
    }

    protected function not_exists_message($video): string {
        $link = \array_keys($video)[0];
        return "no $link in playlist {$this->name}"; 
    }
}

final class PlaylistMaps implements DataBaseTable {
    public const TABLE = "playlist/linked_list";
    
    use GeneralCRUD;

    private Videos $videos;
    private Creates $creator;
    private Deletes $deleter;
    private Updates $updater;
    private Reads $reader;

    public function __construct(
        private Playlist $playlist,
        private User $user, 
        private $db
    ) {
        $this->videos = new Videos($user, $db);
        $this->creator = new MeekroCreator($this, $db);
        $this->deleter = new MeekroDeleter($this, $db);
        $this->updater = new MeekroUpdater($this, $db);
        $this->reader = new MapsReader($this->playlist->name(), $this, $db);
    }

    // DataBaseTable Interface [
    
    public function locate_all() {
        $where = new \WhereClause("and");
        $where->add('`%l`.name=%s', self::TABLE, $this->playlist->name());
        $where->add("`%l`.user=%s", self::TABLE, $this->user->name());
        return $where;
    }

    public function locate($vmap) {
        $video = \array_values($vmap)[0];
        $where = $this->locate_all();
        $where->add('`%l`.link LIKE %s', self::TABLE, $video->link()); 
        return $where; 
    }
   
    public function row_from_entity($entity): Array {
        [[$video, $parent]] = \array_values($entity);
        return [
            "user" => $this->user->name(),
            "name" => $this->playlist->name(),
            "link" => $video->link(),
            "parent" => $parent
        ];
    }

    public function entity_from_row(Array $data) {
        $video = $this->videos->entity_from_row($data);
        $parent = $data["parent"];
        return [$video->link() => [$video, $parent]];
    }
        
    public function name(): string {
        return self::TABLE;
    }

    public function schema(): Array {
        return self::SCHEMA;
    }

    // ]

    // GeneralRead Trait [
    

    public function read_if_exists(Array $vmap): ?Array {
        return $this->reader->read_if_exists($vmap);
    }

    public function read(Array $vmap): Array {
        return $this->reader->read($vmap);
    }

    public function does_exist(Array $vmap): bool {
        return $this->read_if_exists($vmap) !== null;
    }

    public function read_all(): Array {
        return $this->reader->read_where($this->locate_all());
    } 

    // ]


    // GeneralCreate Trait [
    public function create(Array... $vmaps) {

        return $this->creator->create(...$vmaps);
    }
    // ]
    
    // GeneralDelete Trait [
    public function delete(Array $vmap) {
        return $this->deleter->delete($vmap);
    }
    // ]
    
    // GeneralUpdate Trait [
    public function update(Array $old, Array $new) {
        return $this->updater->update($old, $new);
    }
    // ]
    
    // Others [

    public static function vmaps_from(iterable $list): Array {
        $vmaps = [];
        $parent = null;

        foreach ($list as $video) {
            $vmaps[] = [$video->link() => [$video, $parent]];
            $parent = $video->link();
        }

        return $vmaps;
    }

    public static function videos_from(Array $vmaps): Array {
        $videos = [];
        $child = [];

        foreach (\array_merge(...$vmaps) as [$video, $parent]) {
            if ($parent !== null) {
                $child[$parent] = $video;
            } else {
                $videos[] = $video;
            }
        }
        
        while (count($videos) !== count($vmaps)) {
            $last = \end($videos)->link();
            $videos[] = $child[$last];
        }
            
        return $videos;
    }

    public function read_videos(): Array {
        return $this->videos_from($this->read_all());
    }
    
    // ]
}
