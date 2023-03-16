### args syntax

`arg%s` — string 
(must be quoted if space is needed like so: `"some\n video"` or even `'some "video"'`)

`(arg%t)` — time, any of the three formats: `s`, `m:s`, `h:m:s` 

`(arg%n)` — number

`(: y%...x)` — whitespace separated list of type x
for example `: "video 1" video_2 'video3'` can be a list for `(: videos...%s)`

### managing videos

1. list videos
2. add video (url%s)
3. delete video (ulr%s)
4. [un]watch video (url%s)
5. rename video (url%s) (url%s)

### managing timestamps
1. add timestamp to (url%s) (time%t) (info%s)
2. delete timestamp from (url%s) (time%t)
3. rename timestamp for (url%s) (time%t) (new_info%s)
4. list timestamps of (url%s)
5. reset timestamp for (url%s) (old%t) (new%t)
6. grep timestamps (pattern%s)
 
### pinned video
1. pin (video%s)
2. unpin
3. pinned
4. delete
5. [un]watch
6. rename (new_url%s)
7. add timestamp (time%t) (info%s)
8. delete timestamp (time%t)
9. rename timestamp (time%t) (new_info%s)
10. list timestamps
11. reset timestamp (old%t) (new%t)

### managing playlists
1. list playlists
2. add playlist (name%s)
3. rename playlist (old%s) (new%s)
4. delete playlist (name%s)
5. list videos in (name%s)
6. mend playlist (name%s) (: videos%...s)
7. clear playlist (name%s)
8. status of (name%s)

### scheduling playlists
1. list plan
2. [un]plan (name%s) [(nice%d)]
2. renice (name%s) (nice%d)
3. pick
