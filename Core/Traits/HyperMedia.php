<?php

namespace Core\Traits;

trait HyperMedia {
    
    public function getLinks(array $uri, string $table, array $attributes){
        $links = null;
        $offset = \Core\Model::getPointerOfCollection();
        $limit = \Core\Model::getLenghtOfCollection();
        $page = intdiv($offset,$limit);
        $uri_length = count($uri);
        reset($uri);
        if($uri_length==1){
            $link= [
                "rel"=>"self",
                "href"=> "/".key($uri),
                "type"=>"GET"
            ];
            if(is_null(current($uri))){
                $link["href"].= "?limit=".$limit."&page=".$page;
            } else {
                $link["href"].= "/".current($uri);
            }
            $links = [$link];
            foreach($attributes as $_table => $_attribute){
                if($_table===key($uri)) continue;
                $id=is_null(current($uri))?"{id}":current($uri);
                $link = [
                    "rel"=>$_table,
                    "href"=> "/".key($uri)."/".$id."/".$_table,
                    "type"=> "GET"
                ];

                array_push($links,$link);
            }
            $link = [
                "rel"=> "self",
                "href"=> "/".key($uri),
                "type"=> "POST",
                "shema"=>"/shema/".key($uri)
            ];
            array_push($links,$link);
            $link = [
                "rel"=>$table,
                "href"=>"/".$table,
                "type"=> "POST",
                "shema"=>"/shema/".$table
            ];
            array_push($links,$link);
        } else {
            $links = array();
            foreach($uri as $_table => $_id){
                $link= [
                    "rel"=>array_key_last($uri)===$_table?"self":$_table,
                    "href"=> "/".$_table,
                    "type"=>"GET"
                ];
                if(is_null($_id)){
                    $link["href"].= "?limit=".$limit."&page=".$page;
                } else {
                    $link["href"].= "/".$_id;
                }
                array_push($links,$link);
            }
        }
        return $links;
    }

    public function getNextPreviousItems($class, $id = null, array $conditon = [])
    {
        $links = [];
        $attributeId = \Core\Model::getAttrIdName($class);
        $table = \Core\Model::getTableName($class);
        if (is_null($id)) {
            $offset = \Core\Model::getPointerOfCollection();
            $limit = \Core\Model::getLenghtOfCollection();
            $q_string="";
            if(count($conditon)!==0){
                foreach($conditon as $attr => $value)
                    $q_string .= "&".$attr."=".$value;
            }
            if($offset > 0)array_push($links, [
                "rel" => "previous",
                "href" => "/" . $table . "?limit=". $limit ."&page=". intdiv($offset-$limit,$limit).$q_string,
                "type" => "GET"
            ]);
            array_push($links, [
                "rel" => "next",
                "href" => "/" . $table . "?limit=". $limit ."&page=" . intdiv($offset+$limit,$limit).$q_string,
                "type" => "GET"
            ]);
            
        } else {
            $sql = "SELECT $attributeId FROM $table
                WHERE $attributeId = (SELECT max($attributeId) FROM $table WHERE $attributeId < :id );";
            $stmt = \App\App::$app->db->prepare($sql);
            $stmt->bindParam("id", $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                $previous_id = $stmt->fetch(\PDO::FETCH_ASSOC)[$attributeId];
            }
            if (!is_null($previous_id)) array_push($links, [
                "rel" => "previous",
                "href" => "/" . $table . "/" . $previous_id,
                "type" => "GET"
            ]);
            $stmt->closeCursor();
            $sql = "SELECT $attributeId FROM $table
                WHERE $attributeId = (SELECT min($attributeId) FROM $table WHERE $attributeId > :id );";
            $stmt = \App\App::$app->db->prepare($sql);
            $stmt->bindParam("id", $id, \PDO::PARAM_INT);
            if ($stmt->execute()) {
                $next_id = $stmt->fetch(\PDO::FETCH_ASSOC)[$attributeId];
            }
            if (!is_null($next_id)) array_push($links, [
                "rel" => "next",
                "href" => "/" . $table . "/" . $next_id,
                "type" => "GET"
            ]);
        }
        return $links;
    }
}