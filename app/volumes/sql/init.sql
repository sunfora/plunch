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
