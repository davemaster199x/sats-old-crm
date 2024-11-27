<?php 

class Sats_query extends Propertyme_api{
	
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

    function dbUpdate($table,$data=array(),$where=array()) {

        if(count($data) < 1)
            return false;

        if(count($where) < 1)
            return false;

        $first=true;
        $fields = "";
        $q = "";
        foreach($data as $k => $v) {
            if($first)
                $fields .="$k='$v'";
            else
                $fields .=",$k='$v'";
            $first=false;
        }

        $first_where=true;
        $fields_where = "";
        foreach($where as $key => $value) {
            if($first_where)
                $fields_where .="$key='$value'";
            else
                $fields_where .=" AND $key='$value'";
            $first_where=false;
        }

        $result = mysql_query("UPDATE $table SET $fields WHERE $fields_where");

        if($result)
            return true;
        else
            return false;


    }

    function addJSONResponseHeader() {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Content-Type: application/json");
    }

    function getTenantsFromJobs($job_id)
    {
        $sql = "SELECT  B.`property_tenant_id` as 'tenant_id', B.`tenant_firstname`, B.`tenant_lastname`, B.`tenant_mobile`, B.`tenant_landline`, B.`tenant_email`, B.`tenant_worknumber`,B.`pm_tenant_id`
                FROM `jobs` A 
                INNER JOIN `property_tenants` B ON B.`property_id` = A.`property_id` 
                WHERE A.`id`=".$job_id;
        $query = mysql_query($sql);
        if($query){
            $tenant = [];
            while($rs = mysql_fetch_array($query)){
                $tenant[] = $rs;
            }
            return $tenant;
            exit();
        }
        return FALSE;
    }

    function getTenantsFromPM_Job($job_id)
    {
        $propertyme_api = new Propertyme_api();
        $sqlGet_propme = mysql_query("SELECT B.`propertyme_prop_id`,B.`agency_id` FROM `jobs` A INNER JOIN `property` B ON B.`property_id` = A.`property_id` WHERE A.`id`=".$job_id);
        $rsGet_propme = mysql_fetch_array($sqlGet_propme);
        if($rsGet_propme['propertyme_prop_id'] != "" OR !empty($rsGet_propme['propertyme_prop_id'])){
            $getA = mysql_query("SELECT `propertyme_agency_id` FROM `agency` WHERE `agency_id`=".$rsGet_propme['agency_id']);
            $rsA = mysql_fetch_array($getA);
            
            $propertyme_api->getAgencyDetails($rsA['propertyme_agency_id']);
            $prop = $propertyme_api->getPropertyDetails($rsGet_propme['propertyme_prop_id']);

            if(!empty($prop['Tenancy']) AND count($prop['Tenancy']) > 0){
                $tenantsPM = $propertyme_api->getContactDetails($prop['Tenancy']['ContactId']);
                return $tenantsPM;
                exit();
            } else {
                return FALSE;
            }

        } else {
            return FALSE;
        }

        

    }

}