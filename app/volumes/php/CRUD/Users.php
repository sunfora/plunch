<?
namespace Plunch\CRUD;

require_once "User.php";
require_once "CRUD/PinnedVideos.php";

use Plunch\User as User;

final class Users {

    public function __construct(private $db) {}
    
    public function read(User $user) {
        $pinned_videos = new PinnedVideos($user, $this->db);
        $name = $user->name();
        $video = $pinned_videos->read_if_exists();
        return new User($name, $video);
    }
}
