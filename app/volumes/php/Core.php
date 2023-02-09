<?
require_once "DataBase.php";

final class Core {

    public function __construct(private DataBase $db) {}

    public function idle() {
        return null; 
    }

    public function set(string $id, string $name) {
        $this->db->set_user_name($id, $name);
        return "Successfully changed username";
    }

    public function get(string $id) {
        $name = $this->db->get_user_name($id);

        if ($name === null) {
            return "No such user found";
        } else {
            return $name;
        }
    }
}
