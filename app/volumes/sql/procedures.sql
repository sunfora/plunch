USE plunch;

DELIMITER ;;

CREATE OR REPLACE FUNCTION 
    `video/id`(user CHAR(128), link VARCHAR(2048)) RETURNS INT
    BEGIN
        SELECT id INTO @id FROM `video/videos` AS t
            WHERE t.user=user AND t.link=link;
        RETURN @id;
    END;
;;

CREATE OR REPLACE FUNCTION 
    `video/link`(user CHAR(128), id INT) RETURNS VARCHAR(2048)
    BEGIN
        SELECT link INTO @link FROM `video/videos` AS t
            WHERE t.user=user AND t.id=id;
        RETURN @link;
    END;
;;
DELIMITER ;
