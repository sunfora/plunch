
<?
require "api.php";
require "cmd-form.php";
?>

<?
// ok, I think it is a time for a first commit
interface DataBase 
{
    function set_user_name(string $id, string $name): void;
    function get_user_name(string $id): string; 
}
/*
final class MariaDB implements DataBase {

    private string $host;
    private string $user;
    private string $pass;
    private string $name;
    private mysqli $connection;

    public function __constructor($host, $user, $pass, $name) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->name = $name;
    }

    private 

    public function set_user_name(string $id, string $name): void {
        $connection->query("CALL set_user_name(\"$id\", \"$name\")")
    }
}
*/
display(function () { 
//    var_dump(new mysqli("mariadb", "root", "root", "hello"));
});
