<?
namespace Plunch;

require_once "InternalException.php";

final class PlaylistPlanner {
    private Array $planned;

    public function __construct(iterable $planned) {
        $this->planned = [...$planned];
    }

    public function pick(): Playlist {
        if (! $this->planned ) {
            throw new InternalException("plan is done");
        }

        $total_length = \array_sum(
            \array_map(fn ($p) => $p["priority"], $this->planned)
        );

        $chosen = \mt_rand(0, $total_length - 1);

        $left = 0;
        $rigth = 0;

        foreach ($this->planned as ["playlist" => $playlist, "priority" => $priority]) {
            $left = $right;
            $right += $priority;
            if ($left <= $chosen && $chosen < $right) {
                return $playlist;
            }
        }
    }
}
