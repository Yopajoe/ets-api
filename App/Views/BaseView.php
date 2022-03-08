<?php

namespace App\Views;

class BaseView{

    private $input;

    public function __construct($_input){
        if(is_array($_input))
            $this->input = json_encode($_input,JSON_NUMERIC_CHECK);
        else
            $this->input = $_input;
    }

    public function send(){
        if(is_null($this->input)) return;
        $this->removeIndexesFromColl();
        echo $this->input;
    }



    private function findLastBracket(string $haystack){
        $brackets = [];
        $pointer = 0;
        $length = strlen($haystack);
        do{
            $tmp = strpos($haystack,"{",$pointer);
            if($tmp===false)break;
            $pointer=$tmp;
            $brackets[$pointer++]=1;
        }while($pointer<$length);
        $pointer=0;
        do{
            $tmp = strpos($haystack,"}",$pointer);
            if($tmp===false)break;
            $pointer=$tmp;
            $brackets[$pointer++]=-1;
        }while($pointer<$length);
        ksort($brackets);
        reset($brackets);
        $sum=0;
        foreach($brackets as $pos => $type){
            $sum+=$type;
            if($sum===0) return $pos;  
        }
        return false;
    }

    private function removeIndexesFromColl(){
        preg_match_all('/"[0-9]":\{/',$this->input,$match,PREG_OFFSET_CAPTURE);
        if(empty($match[0])) return;
        //first we insert closed array bracket on end of collection
        $start_of_item = end($match[0])[1]+4;
        $last_item = substr($this->input,$start_of_item);
        $end_of_item = $this->findLastBracket($last_item) + $start_of_item;
        $this->input=substr_replace($this->input,']',$end_of_item+1,0);
        //second we insert opened bracket on beginig of collection
        $this->input=preg_replace('/"0":\{/','[{',$this->input);
        //at last we remove remaining indexes
        $this->input=preg_replace('/"[0-9]":\{/','{',$this->input);
        
        
    }
    
}