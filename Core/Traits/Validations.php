<?php

namespace Core\Traits;


trait Validations {

    
    protected static function check_domen($_rule, $_value)
    {
        if(key_exists('domain',$_rule)){
            $domain = $_rule['domain'];
        } else {
            return [true];
        }
        $res = [true];
        if(key_exists('type',$domain)){
        switch($domain['type'])
        {
            case 'datetime':  {
                $d = \DateTime::createFromFormat($domain['format'], $_value);
                if($d && $d->format($domain['format']) === $_value){
                    $res[0] = $res[0] && true;
                } else {
                    $res[0] = false;
                    array_push($res, 'Required valid DateTime format - '.$domain['format']);
                };
                break;
            };
            case 'decimal': {
                if(is_float($_value)) {
                    $res[0] = $res[0] && true;
                } else {
                    $res[0] = false;
                    array_push($res, 'Required decimal format: ');
                };
                break;
            };
            case 'enum': {
                if(array_search($_value, $domain['format']) !== false ){
                    $res[0] = $res[0] && true;
                } else {
                    $res[0] = false;
                    array_push($res, 'Required valid values: '.implode(', ', $domain['format']));
                };
                break;
            }
        }
    }
        //range valdidation  range for values
        if(key_exists('min',$domain)){
            if($_value >= $domain['min'] ){
                $res[0] = $res[0] && true;
            } else {
                $res[0] = false;
                array_push($res, $_value.' has to be greater then '.$domain['min']);
            }
        }

        if(key_exists('max',$domain)){
            if($_value <= $domain['max'] ){
                $res[0] = $res[0] && true;
            } else {
                $res[0] = false;
                array_push($res, $_value.' has to be lesser then '.$domain['max']);
            }
        }

        if(key_exists('min_length',$domain)){
            if(strlen($_value) >= $domain['min_length'] ){
                $res[0] = $res[0] && true;
            } else {
                $res[0] = false;
                array_push($res, $_value.' number of letters has to be less than '.$domain['max_length']);
            } 
        }

        //range valdidation  range for text length
        if(key_exists('max_length',$domain)){
            if(strlen($_value) <= $domain['max_length'] ){
                $res[0] = $res[0] && true;
            } else {
                $res[0] = false;
                array_push($res, $_value.' number of letters has to be less than '.$domain['max_length']);
            } 
        }

        if(key_exists('min_length',$domain)){
            if(strlen($_value) >= $domain['min_length'] ){
                $res[0] = $res[0] && true;
            } else {
                $res[0] = false;
                array_push($res, $_value.' number of letters has to be less than '.$domain['min_length']);
            } 
        }
        return $res;


    }

    protected static function check_primary_key($_rule){
        $res = [true];
        if(!key_exists('primary_key', $_rule)) $res = [false, 'is not primary key'];
        return $res;
    }

    protected static function check_auto_increment($_rule){
        $res = [true];
        if(!key_exists('auto_increment', $_rule)) $res = [false, 'is not auto-increment'];
        return $res;
    }

    protected static function check_foreign_key($_rule){
        $res = [true];
        if(!key_exists('foreign_key', $_rule)) $res = [false, 'is not foreign key'];
        return $res;
    }

    protected static function check_unique($_rule){
        $res = [true];
        if(!key_exists('unique', $_rule)) $res = [false, 'is not unique'];
        return $res;
    }

}