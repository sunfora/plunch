<?
namespace Plunch\CRUD;
require_once "/vendor/autoload.php";
require_once "Video.php";
require_once "User.php";
require_once "Timestamp.php";
require_once "CRUD/Timestamps.php";
require_once "CRUD/Videos.php";
require_once "CRUD/DataBaseTable.php";

require_once "CRUD/MeekroReader.php";
require_once "CRUD/Reads.php";

use Plunch\{Video, User, Timestamp};

final class VideoByTimestampReader extends MeekroReader {
    protected function table_expr(): string {
        $join = <<<'SQL'
            `%l` AS a CROSS JOIN `%l` AS b 
                ON a.user=b.user AND a.link LIKE b.link
        SQL;
        return $this->db->parse(
            $join, Timestamps::TABLE, Videos::TABLE
        );
    }
}

final class VideoByTimestamp implements DataBaseTable {

    private Reads $reader;

    public function __construct(private User $user, private $db) {
        $this->reader = new VideoByTimestampReader($this, $db);
        $this->videos = new Videos($user, $db);
    }

    // DataBaseTable Interface [
    public function entity_from_row(Array $row) {
        $video = $this->videos->entity_from_row($row);
        $timestamps = new Timestamps($this->user, $video, $this->db);
        $timestamp = $timestamps->entity_from_row($row);
        return ["video" => $video, "timestamp" => $timestamp];
    }

    public function row_from_entity($entity): Array {
        ["video" => $video, "timestamp" => $timestamp] = $entity;
        $timestamps = new Timestamps($this->user, $video, $this->db);
        $row_video =$this->videos->row_from_entity($video);
        $row_ts = $timestamps->row_from_entity($timestamp);
        return [...$row_video, ...$row_ts];
    }
    
    public function locate($entity) {
        $where = new \WhereClause('AND');
        $where->add('b.link LIKE %s', $entity["video"]->link());
        $where->add('a.name LIKE %s', $entity["timestamp"]->name());
        $where->add('a.user=%s', $this->user->name());
        return $where;
    }

    public function name(): string {
        return 'video_by_timestamp';
    }

    public function schema(): Array {
        $schema_stamp = \array_map(fn ($x) => "a.".$x, Timestamps::SCHEMA);
        $schema_video = \array_map(fn ($x) => "b.".$x, Videos::SCHEMA);
        return [...$schema_video, ...$schema_stamp];
    }

    public function grep(string $pattern) {
        $where = new \WhereClause("AND");
        $where->add("a.user=%s", $this->user->name());
        $where->add("a.name RLIKE %s", $pattern);

        return $this->reader->read_where($where);
    }
}

