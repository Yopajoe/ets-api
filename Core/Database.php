<?php

namespace Core;

class Database extends \PDO
{


    public function __construct(string $_host, string $_db, string $_username, string $_password)
    {
 
        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $dsn = "mysql:host=".$_host.";dbname=".$_db.";charset=utf8mb4";
        try{
        parent::__construct($dsn, $_username, $_password, $options);
        }
        catch(\PDOException $e){
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
        
    }

    public function getType($value):int
    {
        if(is_string($value)) return self::PARAM_STR;
        if(is_integer($value)) return self::PARAM_INT;
        if(is_bool($value)) return self::PARAM_BOOL;

    }
    
    
}