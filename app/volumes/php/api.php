<?
require "/vendor/autoload.php";
require_once "CLI.php";

use VK\CallbackApi\Server\VKCallbackApiServerHandler;
use VK\Client\VKApiClient;

define('ENV_SECRET', getenv('SECRET'));
define('ENV_GROUP_ID', intval(getenv('GROUP_ID')));
define('ENV_CONFIRMATION_TOKEN', getenv('CONFIRMATION_TOKEN'));
define('ENV_ACCESS_TOKEN', getenv('ACCESS_TOKEN'));

class ServerHandler extends VKCallbackApiServerHandler {
    const SECRET = ENV_SECRET;
    const GROUP_ID = ENV_GROUP_ID;
    const CONFIRMATION_TOKEN = ENV_CONFIRMATION_TOKEN;
    const ACCESS_TOKEN = ENV_ACCESS_TOKEN;

    function confirmation(int $group_id, ?string $secret) {
        if ($secret === static::SECRET && $group_id === static::GROUP_ID) {
            echo static::CONFIRMATION_TOKEN;
        }
    }

    public function messageNew(int $group_id, ?string $secret, array $object) {
        $user = $object["peer_id"];
        $user_message = $object["text"];

        $plunch = new Plunch\CLI($user);
        $plunch_message = "{$plunch->run($user_message)}";
        $plunch_message = trim($plunch_message)? $plunch_message : "∅";
        $vk = new VKApiClient();
        $vk->messages()->send(static::ACCESS_TOKEN, [
            "random_id" => mt_rand(),
            "peer_id" => $user,
            "message" => $plunch_message 
        ]);
        echo 'ok';
    }
}

$handler = new ServerHandler();
$data = json_decode(file_get_contents('php://input'));
$handler->parse($data); 
