<?php 

class Sats_query {
	
	function dbInsert($table,$data=array()) {

        if(count($data) < 1) {
            return false;
        }

        $first=true;
        $fields = "";
        $q = "";
        foreach($data as $k=>$v) {

            if($first)
                $fields .="`$k`";
            else
                $fields .=",`$k`";

            if($first)
	            $q .="'{$v}'";
	        else
	            $q .=",'{$v}'";

            $first=false;

        }

        $result = mysql_query("insert into $table ($fields) VALUES($q)");

        $id=mysql_insert_id($result);

        return $id;
    }

    function dbUpdate($table,$data=array(),$id=false) {


        if(!$id) {
            if(!isset($data['id'])) {
                return false;
            } else {
                $id = $data['id'];
                unset($data['id']);
            }
        }
        if(count($data) < 1)
            return false;


        $first=true;
        $fields = "";
        $q = "";
        foreach($data as $k=>$v) {

            if($first)
                $fields .="$k='$v'";
            else
                $fields .=",$k='$v'";

            $first=false;

        }

        $result = mysql_query("UPDATE $table SET $fields WHERE id={$id}");

        if(mysql_affected_rows($result))
            return true;
        else
            return false;


    }

}