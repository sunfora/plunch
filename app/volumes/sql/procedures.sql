USE hello;

DELIMITER ;;

CREATE OR REPLACE PROCEDURE
    set_user_name(
        IN dialog_id CHAR (128), 
        IN user_name CHAR (128)
    )
    BEGIN
        INSERT INTO users 
            VALUE (dialog_id, user_name)
        ON DUPLICATE KEY UPDATE
            users.user_name = user_name;
    END;
;;

CREATE OR REPLACE PROCEDURE 
    get_user_name(
        IN dialog_id CHAR (128)
    )
    BEGIN
        SELECT (user_name) FROM users
           WHERE (users.dialog_id = dialog_id);
    END;
;;

DELIMITER ;
