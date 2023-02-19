<?
namespace Plunch;

final class Timestamp {
    public function __construct(
        private Array $time=[0, 0, 0],
        private string $name="" 
    ) {}
    
    public function time() {
        return $this->time;
    }

    public function name() {
        return $this->name;
    }

    public function rename(string $name) {
        $this->name = $name;
        return $this;
    }

    public function set(Array $time) {
        $this->time = $time;
        return $this;
    }
    
    public function strtime() {
        return \implode(":", $this->time);
    }

    public function __toString() {
        return $this->strtime();
    }

    public static function from_strtime(string $time, string $name="") {
        $time = \array_map(\intval(...), \explode(':', $time));
        return new self($time, $name);
    }
}
