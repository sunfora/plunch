CREATE DATABASE IF NOT EXISTS 
    plunch;

USE plunch;

CREATE TABLE IF NOT EXISTS
    `video/videos` 
    (
        user        CHAR(128),
        link        VARCHAR(2048)
        watched     BOOLEAN
                    NOT NULL
                    DEFAULT FALSE,
        PRIMARY KEY (user, link)
    );

CREATE TABLE IF NOT EXISTS
    `video/timestamps` 
    (
        user    CHAR(128),
        link    VARCHAR(2048),
        stamp   TIME,
        name    TINYTEXT,
        info    TEXT,

        PRIMARY KEY (user, link, stamp),

        CONSTRAINT `video/timestamps/fk_video` FOREIGN KEY (user, link)
            REFERENCES `video/videos` (user, link)
                ON DELETE CASCADE 
                ON UPDATE CASCADE 
    );
