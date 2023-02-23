<?
namespace Plunch\CRUD;
require_once "/vendor/autoload.php";
require_once "Table.php";
require_once "Video.php";
require_once "User.php";
require_once "CRUD/GeneralCRUD.php";
require_once "CRUD/Timestamps.php";
require_once "CRUD/TypecastRow.php";
require_once "CRUD/DataBaseTable.php";

require_once "CRUD/MeekroCreator.php";
require_once "CRUD/Creates.php";

require_once "CRUD/MeekroDeleter.php";
require_once "CRUD/Deletes.php";

require_once "CRUD/MeekroUpdater.php";
require_once "CRUD/Updates.php";

use Plunch\{Video, User};
use Plunch\Util\Table as Table;

final class Videos implements DataBaseTable {
    public const TABLE = 'video/videos';
    public const SCHEMA = ['link', 'watched'];

    use GeneralCRUD; 
    use TypecastRow;

    private Creates $creator;
    private Deletes $deleter;
    private Updates $updater;

    public function __construct(private User $user, private $db) {
        $this->creator = new MeekroCreator($this, $db);
        $this->deleter = new MeekroDeleter($this, $db);
        $this->updater = new MeekroUpdater($this, $db);
    }

    // TypecastRow Trait [
    private function typecast_kv($key, $value) {
        return match ($key) {
            "link" => $value,
            "watched" => \boolval(\intval($value))   
        };
    }
    // ]

    // DataBaseTable Interface [
    public function entity_from_row(Array $row) {
        $row = $this->typecast_row(Table\shed_row($row, self::SCHEMA));
        return new Video(...$row);
    }

    public function row_from_entity($video): Array {
        return [
            "user" => "{$this->user->name()}",
            "watched" => \strval(\intval($video->is_watched())),
            "link" => "{$video->link()}"
        ];
    }
    
    public function locate($video) {
        $where = new \WhereClause('AND');
        $where->add('link LIKE %s', $video->link());
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
    public function read_if_exists(Video $video): ?Video {
        return $this->general_read_if_exists($video);
    }

    public function does_exist(Video $video): bool {
        return $this->general_does_exist($video);
    }
    
    public function read(Video $video): Video {
        return $this->general_read("video $video does not exist", $video);
    }

    public function read_all(): Array {
        $where = new \WhereClause('and');
        $where->add('user=%s', $this->user->name());
        return $this->general_read_where($where);
    }

    // ]

    // GeneralUpdate Trait [
    public function update(Video $dest, Video $new) {
        return $this->updater->update($dest, $new);
    }
    // ]

    // GeneralCreate Trait [
    public function create(Video $video) {
        return $this->creator->create($video);
    }
    // ]

    // GeneralDelete Trait [
    public function delete(Video $video) {
        return $this->deleter->delete($video);
    }
    // ]
    
    // Other [
    public function grep_timestamps(string $pattern) {
        $schema_stamp = \array_map(fn ($x) => "a.".$x, Timestamps::SCHEMA);
        $schema_video = \array_map(fn ($x) => "b.".$x, self::SCHEMA);
        $comb_schema = \implode(', ', [...$schema_video, ...$schema_stamp]);

        $query=<<<'SQL'
            SELECT %l FROM `%l` AS a
                CROSS JOIN `%l` AS b ON a.user=b.user AND a.link LIKE b.link
                    WHERE a.user=%s AND a.name RLIKE %s
        SQL;
        
        $results = $this->db->query(
            $query,
            $comb_schema,
            Timestamps::TABLE,
            self::TABLE,
            $this->user->name(),
            $pattern
        );
        
        $cast = function ($row) {
            $video = $this->entity_from_row($row);
            $timestamps = new Timestamps($this->user, $video, $this->db);
            $timestamp = $timestamps->entity_from_row($row);
            return ["video" => $video, "timestamp" => $timestamp];
        };

        return \array_map($cast, $results);
    }
    // ]
}

