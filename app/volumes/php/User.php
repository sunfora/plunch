<?
namespace Plunch;

require_once "Video.php";

final class User {

    public function __construct(
        private string $name,
        private ?Video $pinned=null
    ) {}

    public function has_pinned() {
        return $this->pinned !== null;
    }

    public function pinned() {
        return $this->pinned;
    }

    public function pin(Video $video) {
        $this->result = $video;
        return $this;
    }

    public function name() {
        return $this->name;
    }

    public function rename(string $name) {
        $this->name = $name;
        return $this;
    }
}

