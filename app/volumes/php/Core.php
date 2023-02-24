<?
namespace Plunch;
require "/vendor/autoload.php";
require_once "User.php";
require_once "Table.php";
require_once "Video.php";
require_once "Playlist.php";
require_once "CRUD/Videos.php";
require_once "CRUD/PinnedVideos.php";
require_once "CRUD/Timestamps.php";
require_once "CRUD/Playlists.php";
require_once "CRUD/PlaylistMaps.php";
require_once "CRUD/VideoByTimestamp.php";

use Plunch\Util\Table as Table;

final class Core {

    private CRUD\Videos $videos;
    private CRUD\PinnedVideos $pinned_videos;
    private CRUD\Playlists $playlists;
    private CRUD\VideoByTimestamp $videos_by_timestamp;

    public function __construct(private User $user, private $db) {
        $this->videos = new CRUD\Videos($user, $db);   
        $this->pinned_videos = new CRUD\PinnedVideos($user, $db);
        $this->playlists = new CRUD\Playlists($user, $db);
        $this->videos_by_timestamp = new CRUD\VideoByTimestamp($user, $db);
    }

    // Main [
    public function idle() {
        return null; 
    }

    public function grep_timestamps($pattern) {
        $grepped = $this->videos_by_timestamp->grep($pattern);
        
        $timestamps = $this->repr_timestamps(
            \array_column($grepped, "timestamp")
        );
        
        $videos = \array_map(
            fn ($x) => ["{$x->link()}"], 
            \array_column($grepped, "video")
        );
        $result = \array_map(\array_merge(...), $videos, $timestamps);
        return Table\to_string($result);
    }

    public function pinned() {
        $video = $this->user->pinned();
        
        $repr_video = $this->repr_video_list([$video]);
        $repr_ts = $this->list_video_timestamps($video->link());

        return \implode("\n\n", [$repr_video, $repr_ts]);
    }

    public function pin(string $link) {
        $video = $this->videos->read(new Video($link));
        $this->user->pin($video);
        $this->pinned_videos->replace($video);
        return "pinned video $link";
    }
    
    public function unpin() {
        if (! $this->user->has_pinned() ) {
            return "nothing to unpin";
        }
        $video = $this->user->pinned();
        $this->user->unpin();
        $this->pinned_videos->delete($video);
        return "unpinned video {$video->link()}";
    }
    
    private function repr_video_list(Array $videos) {
        $repr = function ($video) {
            $indicator = $video->is_watched()? '[*]' : '[ ]';
            return [$indicator, $video->link()];
        };

        $table = array_map($repr, $videos);
        
        return Table\to_string($table);
    } 

    public function list_videos() {
        $videos = $this->videos->read_all(); 
        return $this->repr_video_list($videos); 
    }

    // ]

    // Videos [
    public function add_video(string $link) {
        $this->videos->create(new Video($link));
        return "added video $link"; 
    }

    public function delete_video(string $link) {
        $video = $this->videos->read(new Video($link));
        $this->videos->delete($video);
        return "deleted video $link"; 
    }

    private function update_video(string $link, callable $act) {
        $video = $this->videos->read(new Video($link));
        $old = clone $video;
        $result = $act($video);
        $this->videos->update($old, $video);
        return $result;
    }

    public function watch_video(string $link) {
        $this->update_video(
            $link, 
            fn ($video) => $video->watch()
        );
        return "video $link is now watched";
    }

    public function unwatch_video(string $link) {
        $this->update_video(
            $link, 
            fn ($video) => $video->unwatch()
        );
        return "video $link is no longer watched";
    }

    public function rename_video(string $link_old, string $link_new) {
        $this->update_video(
            $link_old, 
            fn ($video) => $video->rename($link_new)
        );
        return "$link_old -> $link_new";
    }

    // ]

    // Pinned Video [

    private function pinned_as_first_arg(callable $action, mixed... $args) {
        if (! $this->user->has_pinned() ) {
            return "nothing is pinned";
        }
        $video = $this->user->pinned();

        $link = $video->link();
        $result = $action($link, ...$args);

        // things may have changed for user, so actualize
        $this->pinned_videos->actualize();
        return $result;
    }

    public function watch_pinned() {
        return $this->pinned_as_first_arg($this->watch_video(...));
    }

    public function unwatch_pinned() {
        return $this->pinned_as_first_arg($this->unwatch_video(...));
    }

    public function rename_pinned(string $link) {
        return $this->pinned_as_first_arg($this->rename_video(...), $link);
    }

    public function delete_pinned() {
        return $this->pinned_as_first_arg($this->delete_video(...));
    }

    // ]

    // Timestamps [

    private function timestamps(string $link) {
        $video = $this->videos->read(new Video($link));
        return new CRUD\Timestamps(
            video: $video, 
            user: $this->user, 
            db: $this->db
        );
    }

    public function add_timestamp_to_video(string $link, Array $time, string $name) {
        $timestamp = new Timestamp($time, $name);
        $this->timestamps($link)->create($timestamp);
        return "created timestamp $timestamp $name for $link"; 
    }

    public function delete_timestamp_from_video(string $link, Array $time) {
        $timestamp = new Timestamp($time);
        $this->timestamps($link)->delete($timestamp);
        return "deleted timestamp $timestamp $name for video $link"; 
    }

    private function act_on_timestamp(callable $action, string $link, $time) {
        $timestamps = $this->timestamps($link);
        $old_ts = $timestamps->read(new Timestamp($time));
        $act_on = (clone $old_ts);
        $action($act_on);
        $timestamps->update($old_ts, $act_on);
        return [$old_ts, $act_on];
    }

    public function rename_timestamp_for_video(string $link, $time, string $name) {
        [$old_ts, $new_ts] = $this->act_on_timestamp(
            fn ($ts) => $ts->rename($name),
            $link, $time
        );
        return "$old_ts: {$old_ts->name()} -> {$new_ts->name()}";
    }

    public function reset_timestamp_for_video(string $link, $time_old, $time_new) {
        [$old_ts, $new_ts] = $this->act_on_timestamp(
            fn ($ts) => $ts->set($time_new),
            $link, $time_old
        );
        return "$old_ts -> $new_ts";
    }

    private function repr_timestamps(Array $stamps) {
        $length = fn ($x) => \strlen("{$x->time()[0]}");

        $pad_up_to = \max([0, ...\array_map($length, $stamps)]);
        $pad_left = fn ($v, $x) => \str_pad("$v", $x, STR_PAD_LEFT);

        $pad_hours = fn ($h) => $pad_left($h, $pad_up_to);
        $pad_mins = fn ($m) => $pad_left($m, 2);
        $pad_secs = fn ($s) => $pad_left($s, 2);
        
        $pad_time = fn ($t) => [
            $pad_hours($t[0]), 
            $pad_mins($t[1]), 
            $pad_secs($t[2])
        ];
        
        $repr = fn ($ts) => [
            \implode(':', $pad_time($ts->time())), 
            $ts->name()
        ];

        return \array_map($repr, $stamps);
    }

    public function list_video_timestamps($link) {
        $stamps = $this->timestamps($link)->read_all();
        $stamps = $this->repr_timestamps($stamps);
        return Table\to_string($stamps);
    }

    // ]

    // Pinned Timestamps [

    public function add_timestamp(Array $time, string $name) {
        return $this->pinned_as_first_arg(
            $this->add_timestamp_to_video(...), $time, $name
        );
    }

    public function delete_timestamp(Array $time) {
        return $this->pinned_as_first_arg(
            $this->delete_timestamp_from_video(...), $time
        );
    }

    public function rename_timestamp(Array $time, string $name) {
        return $this->pinned_as_first_arg(
            $this->rename_timestamp_for_video(...), $time, $name
        );
    }

    public function reset_timestamp(Array $old, Array $new) {
        return $this->pinned_as_first_arg(
            $this->reset_timestamp_for_video(...), $old, $new
        );
    }

    public function list_timestamps() {
        return $this->pinned_as_first_arg(
            $this->list_video_timestamps(...)
        );
    }

    // ]

    // Playlists [
    private function repr_playlists(Array $pls): string {
        $repr = fn ($p) => [$p->name()];
        return Table\to_string(\array_map($repr, $pls));
    }

    public function list_playlists() {
        return $this->repr_playlists($this->playlists->read_all());
    }

    public function add_playlist(string $name) {
        $this->playlists->create(new Playlist($name));
        return "added a playlist $name";
    }

    public function rename_playlist(string $name, string $new_name) {
        $old = $this->playlists->read(new Playlist($name));
        $new = clone $old;
        $old->rename($new_name);
        $this->playlists->update($old, $new);
        return "$name -> $new_name";
    }

    public function delete_playlist(string $name) {
        $play = $this->playlists->read(new Playlist($name));
        $this->playlists->delete($play);
        return "deleted playlist $name";
    }

    public function list_playlist_videos(string $name) {
        $pls = $this->playlists->read_with_videos(new Playlist($name));
        return $this->repr_video_list([...$pls]);
    }

    public function clear_playlist(string $name) {
        $this->db->startTransaction();
        $this->delete_playlist($name);
        $this->add_playlist($name);
        $this->db->commit();
        return "cleared playlist $name";
    }

    private function status(Playlist $playlist) {
        $watched = 0;
        foreach ($playlist as $video) {
            $watched += (int) $video->is_watched();
        }
        $length = count($playlist);
        return $length === 0? 1 : $watched/$length; 
    }

    public function playlist_status(string $name) {
        $pls = $this->playlists->read_with_videos(new Playlist($name));
        $rate = sprintf("%.2f%%", $this->status($pls) * 100);
        return "playlist $name: $rate";
    }

    public function mend_playlist(string $name, Array $links) {
        $this->db->startTransaction();

        $this->clear_playlist($name); 

        if ($links) {
            $make_video = fn ($link) => new Video($link);
            $videos = \array_map($make_video, $links);
    
            $vmaps = CRUD\PlaylistMaps::vmaps_from($videos);
            $maps = new CRUD\PlaylistMaps(new Playlist($name), $this->user, $this->db);

            $maps->create(...$vmaps);
        }

        $this->db->commit();
        return $this->list_playlist_videos($name);
    }    
    // ] 
}
