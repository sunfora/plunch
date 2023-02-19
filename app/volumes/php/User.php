<?
namespace Plunch;

require_once "Video.php";
require_once "InternalException.php";

final class User {

    public function __construct(
        private string $name,
        private ?Video $pinned=null
    ) {}

    public function has_pinned() {
        return $this->pinned !== null;
    }

    public function pinned() {
        if (! $this->has_pinned() ) {
            throw new InternalException("nothing is pinned");
        }
        return $this->pinned;
    }

    public function unpin() {
        $this->pinned = null;
        return $this;
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

