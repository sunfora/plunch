<?

interface DataBase 
{
    function set_user_name(string $id, string $name): void;
    function get_user_name(string $id): ?string;
}

