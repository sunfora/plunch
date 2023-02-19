<?
namespace Plunch\CRUD;
require_once "GeneralCreate.php";
require_once "GeneralRead.php";
require_once "GeneralUpdate.php";
require_once "GeneralDelete.php";

trait GeneralCRUD {
    use GeneralCreate;
    use GeneralRead;
    use GeneralUpdate;
    use GeneralDelete;
}
