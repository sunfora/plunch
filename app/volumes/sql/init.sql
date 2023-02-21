CREATE DATABASE IF NOT EXISTS 
    plunch;

USE plunch;

-- I thought and then reduced the link size to 512
-- to debloat tables from unnatural things like INT ids 
-- I don't think I really need them, at least now

CREATE TABLE IF NOT EXISTS
    `video/videos` 
    (
        user        CHAR(128),        
        link        VARCHAR(512),
        watched     BOOLEAN
                    NOT NULL
                    DEFAULT FALSE,
        PRIMARY KEY (user, link)
    );

CREATE TABLE IF NOT EXISTS
    `video/timestamps` 
    (
        user        CHAR(128),
        link        VARCHAR(512),
        stamp       TIME
                    NOT NULL,
        name        TINYTEXT,

        PRIMARY KEY (user, link, stamp),

        CONSTRAINT `video/timestamps/fk_user_video` 
            FOREIGN KEY (user, link)
                REFERENCES `video/videos` (user, link)
                    ON DELETE CASCADE 
                    ON UPDATE CASCADE 
    );

CREATE TABLE IF NOT EXISTS
    `user/pinned_videos`
    (
        user CHAR(128),
        link VARCHAR(512),

        PRIMARY KEY (user),
        CONSTRAINT `user/pinned/fk_video` FOREIGN KEY (user, link)
            REFERENCES `video/videos` (user, link)
                ON DELETE CASCADE 
                ON UPDATE CASCADE
    );

CREATE TABLE IF NOT EXISTS
    `playlist/playlists`
    (
        user CHAR(128),
        name VARCHAR(128),

        PRIMARY KEY (user, name)
    );

CREATE TABLE IF NOT EXISTS
    `playlist/linked_list`
    (
        user CHAR(128),
        name VARCHAR(128),
        link VARCHAR(512),
        parent VARCHAR(512) NULL,

        PRIMARY KEY (user, name, link),

        CONSTRAINT `playlist/linked_list/fk_playlist` FOREIGN KEY (user, name)
            REFERENCES `playlist/playlists` (user, name)
                ON DELETE CASCADE 
                ON UPDATE CASCADE,

        CONSTRAINT `playlist/linked_list/fk_video` FOREIGN KEY (user, link)
            REFERENCES `video/videos` (user, link)
                ON DELETE RESTRICT 
                ON UPDATE CASCADE,
       
        CONSTRAINT `playlist/linked_list/fk_parent` FOREIGN KEY (user, parent)
            REFERENCES `video/videos` (user, link)
                ON DELETE RESTRICT 
                ON UPDATE CASCADE
    );
