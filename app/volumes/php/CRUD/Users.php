<?
namespace Plunch\CRUD;

require_once "User.php";
require_once "CRUD/PinnedVideos.php";

use Plunch\User as User;

final class Users {

    public function __construct(private $db) {}
    
    private function pinned_videos_of(User $user) {
        return new PinnedVideos($user, $this->db); 
    }

    public function read(User $user) {
        $name = $user->name();
        $video = $this->pinned_videos_of($user)->read_if_exists();
        return new User($name, $video);
    }
}
