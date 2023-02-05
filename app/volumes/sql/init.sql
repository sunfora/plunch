CREATE DATABASE IF NOT EXISTS 
    hello;

USE hello;

CREATE TABLE IF NOT EXISTS 
    users(
        dialog_id CHAR(128)
                  NOT NULL,
        user_name CHAR(128)
                  NOT NULL,
        PRIMARY KEY (dialog_id)
    );

