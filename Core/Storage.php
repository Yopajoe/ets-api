<?php

namespace Core;

class  Storage{

    private static $storage = [];

    static function put(string $key, $data)
    {
        self::$storage[$key] = $data;
    }

    static function get(string $key)
    {
        if(key_exists($key, self::$storage)) return self::$storage[$key];
        return null;
    }

    static function exists(string $key)
    {
        return key_exists($key, self::$storage);
    }

    static function delete(string $key)
    {
        if(key_exists($key, self::$storage))
        {
            unset(self::$storage[$key]);
            return true;
        } else {
            return false;
        }    
    }
}