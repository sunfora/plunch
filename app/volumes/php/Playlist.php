<?
namespace Plunch;

require_once "InternalException.php";
require_once "Video.php";

final class Node {
    public function __construct(
        public Video $data, 
        public ?Node $prev=null, 
        public ?Node $next=null
    ) {}
}

final class Playlist implements \Iterator, \ArrayAccess, \Countable {
    private string $name;
    
    private int $pos = 0;
    private int $len = 0;
    private ?Node $root = null;
    private ?Node $last = null;
    private ?Node $cur = null;

    public function __construct(string $name, iterable $videos=[]) {
        $this->clear();
        $this->name = $name;
        foreach ($videos as $video) {
            $this[] = $video;
        }
    }

    public function count(): int {
        return $this->len;
    }

    public function name(): string {
        return $this->name;
    }

    public function rename(string $name): Playlist {
        $this->name = $name;
        return $this;
    }

    public function insert(int $position, Video $value): void {
        if ($postion === 0) {
            $this->push_front($value);
        } else if ($position === count($this)) {
            $this->push($value);
        }

        $next_node = $this->nth($position);
        $prev_node = $next_node->prev;
        
        $node->next = $next_node;
        $node->prev = $prev_node;
        $node->next->prev = $node;
        $node->prev->next = $node;

        $this->len++;
    }

    public function clear(): void {
        [$this->len, $this->pos] = [0, 0];
        [$this->root, $this->cur, $this->last] = [null, null, null];
    }

    public function is_empty(): bool {
        return $this->len === 0;
    }

    public function push(Video $video): Playlist {
        $node = new Node($video);

        if ($this->is_empty()) {
            $this->root = $node;
            $this->cur = $node;
        } else {
            $this->last->next = $node;
            $node->prev = $this->last;
        }

        $this->last = $node;
        $this->len++;

        return $this;
    }

    public function push_front(Video $value): Playlist {
        $node = new Node($video);

        if ($this->is_empty()) {
            $this->root = $node;
            $this->cur = $node;
        } else {
            $this->root->prev = $node;
            $node->next = $this->root;
        }

        $this->root = $node;
        $this->len++;

        return $this;
    }

    public function concat(iterable $list): Playlist {
        foreach($list as $value) {
            $this->push($value);
        }
        return $this;
    }

    public function foncat(iterable $list): Playlist {
        foreach($list as $value) {
            $this->push_front($value);
        }
        return $this;
    }

    public function pop(): Video {
        if ($this->is_empty()) {
            throw new InternalException("empty playlist");
        }

        $result = $this->last->data;
        $this->last = $this->last->prev;

        if ($this->last === null) {
            $this->clear();
        } else {
            $this->len--;
        }

        return $result;    
    }

    public function pop_front(): Video {
        if ($this->is_empty()) {
            throw new InternalException("empty playlist");
        }

        $result = $this->root->data;
        $this->root = $this->root->next;

        if ($this->root === null) {
            $this->clear();
        } else {
            $this->len--;
        }

        return $result;    
    }

    public function offsetExists(mixed $offset): bool {
        if (! is_int($offset) ) {
            return false;
        }
        return 0 <= $offset && $offset < $this->len;
    }

    private function nth(int $offset): Node {
        if ( ! $this->offsetExists($offset) ) {
            throw new \OutOfRangeException("no value for key $offset");
        }

        $x = $this->root;
        while ($offset-- > 0) {
            $x = $x->next;
        }
        return $x;
    }
    
    public function offsetSet(mixed $offset, $value): void {
        if ($offset !== null && ! is_int($offset) ) {
            throw new \InvalidArgumentException("offset must be int or null");
        } else if (! $value instanceof Video) {
            throw new \InvalidArgumentException("value must be a video");
        }

        if ($offset === null) {
            $this->push($value);            
        } else {
            $this->nth($offset)->data = $value;
        }
    }
    
    public function offsetGet(mixed $offset): mixed {
        return $this->nth($offset)->data;
    }

    public function offsetUnset(mixed $offset): void {
        throw new \LogicException("playlist cannot contain empty videos");
    }

    public function rewind(): void {
        $this->pos = 0;
        $this->cur = $this->root;
    }

    public function current(): mixed {
        return $this->cur->data;
    }

    public function key(): mixed {
        return $this->pos;
    }

    public function next(): void {
        $this->pos++;
        $this->cur = $this->cur->next;
    }

    public function valid(): bool {
        return $this->cur !== null;
    }
}
