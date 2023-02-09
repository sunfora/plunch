<?
require_once "DataBase.php";

final class MariaDB implements DataBase 
{

    private mysqli $connection;

    public function __construct($host, $user, $pass, $name) 
    {
        $this->connection = new mysqli($host, $user, $pass, $name);
    }
    
    public function set_user_name(string $id, string $name): void 
    {
        $statement = $this->connection->prepare("CALL set_user_name(?, ?)");
        $statement->bind_param("ss", $id, $name);
        $statement->execute();
        $statement->close();
    }

    public function get_user_name(string $id): ?string 
    {
        $result = [...$this->connection->query("CALL get_user_name('$id')")];
        if ($result) {
            return $result[0]["user_name"];
        }
        return null;
    }
}

