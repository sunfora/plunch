<?
namespace Videos;

require_once "Table.php";

const TABLE = 'video/videos';

final class Video {
    public function __construct(
        private string $link, 
        private bool $watched=false
    ) {}

    public function link() {
        return $this->link;
    }

    public function rename(string $link) {
        $this->link = $link;
    }

    public function is_watched() {
        return $this->watched;
    }

    public function watch() {
        $this->watched = true;
    }

    public function unwatch() {
        $this->watched = false;
    }

    public function update(string $dest, string $user, $db) {
        $params = [
            "watched" => $this->watched,
            "link" => $this->link
        ];
        
        return $db->update(
            TABLE, $params, 
            "link LIKE %s AND user=%s", 
            $dest, $user
        );
    }

    public function insert(string $user, $db) {
        return $db->insert(TABLE, [
            "user" => $user,
            "link" => $this->link
        ]);
    }

    public function delete(string $user, $db) {
        $old = static::load(
            link: $this->link,
            user: $user,
            db: $db
        );
        $db->delete(
            TABLE,
            "user=%s AND link LIKE %s", 
            $user, $this->link
        );
        return $old;
    }

    public static function load(string $user, $db, string $link) {
        
        $result = $db->queryFirstRow(
            "SELECT link, watched FROM `%l` AS t WHERE t.user=%s AND t.link LIKE %s",
            TABLE,
            $user, $link    
        );
        if ($result === null) {
            throw new \OutOfBoundsException("video $link does not exist");    
        }
        return new self(...$result);
    }
}

function load_all(string $user, $db) {
    $table = load_all_as_table($user, $db);
    $constr = fn ($entry) => new Video(...$entry);
    return array_map($constr, $table);
}

function load_all_as_table(string $user, $db) {
    $schema = ["link", "watched"];
    // retrieve data
    $params = \implode(", ", $schema);
    $results = $db->query(
        "SELECT %l FROM `%l` WHERE user=%s",
        $params, TABLE, $user
    );
    
    // then cast proper types
    $type_cast = fn ($value, $key) => match ($key) {
        "link" => $value,
        "watched" => \boolval(\intval($value))   
    };
     
    $type_cast_entry = fn ($entry) => \array_map($type_cast, $entry, $schema);
    

    $results = \Table\descheme($results, $schema);
    $results = \array_map($type_cast_entry, $results);
    $results = \Table\scheme($results, $schema);
    return $results;
}
