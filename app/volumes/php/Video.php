<?
namespace Plunch;

final class Video {
    public function __construct(
        private string $link, 
        private bool $watched=false
    ) {}

    public function link() {
        return $this->link;
    }

    public function is_watched() {
        return $this->watched;
    }

    public function rename(string $link) {
        $this->link = $link;
        return $this;
    }
    
    public function watch() {
        $this->watched = true;
        return $this;
    }

    public function unwatch() {
        $this->watched = false;
        return $this;
    }

    public function __toString() {
        return $this->link();
    }
}

