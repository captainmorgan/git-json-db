<?php

/*
Project Description:
This is an API framework to interact with a database using REST web services.
You can use this API to perform standard CRUD (Create, Retreive, Update and Delete functions.

This API will also interact with the database using various users, each having been granted
lease priviledge for the requested function.  


Author: Christopher Morgan
Contact: christopher.t.morgan -at- gmail
Copyright 2013
*/

/*

API Object Test is an experiment to move all but the core methods out of the original class definition.
This would allow for greater flexability and customization of the API.

*/

require_once('api_object.php');
require_once('create_record.php');
require_once('update_record.php');
require_once('update_all_record.php');
require_once('get_record.php');
require_once('get_record_count.php');
require_once('get_record_inner_join.php');
require_once('build_questions.php');
// Include Helper Classes
require_once('dbcore.class.php');

class APIObjectTest extends APIObject{

	public function addDelegateWithEmail() {
       	echo "hi";
       	
       	$s = $this->getBody();
       	$s = str_replace("owner_email", "owner_id", $s);
       	$sLength = strlen($s);
       	$stringLeft = substr($s, 0, strpos($s, "owner_id")+11);
       	$stringRight = "\"" . substr($s, -1);
       	$sEmailLength = strlen($s) - (strpos($s, "owner_id") + 13);
       												// email is 11 char in our example

       	$email = mb_substr($s, strpos($s, "owner_id")+11, $sEmailLength);
       	echo "hi2";
       	
       	$stringMiddle = parent::getCustomerIdByEmail($email, 0);
       	$combinedString = $stringLeft . $stringMiddle . $stringRight;
       	echo $combinedString;
       	
       	parent::connectDB();
       	echo "hi3";
       	
        $del = new CreateRecord($this);
        
        $del->setRequestBody($this->RequestBody);
        echo "hi4";
        
        echo "schema label" . $this->schemaLabel_delegate;
       	echo "hi5";
       	
       	$del->oregon();
       	$del->ssjp($this->schemaLabel_delegate);
       
       	//$del->setSchemaJSON($this->schemaLabel_delegate);
       	echo "hi6";
       	
       	//$del->setDataJSON($combinedString);
       	$del->sdjp($combinedString);
		echo "hi8";
		
		//$del->dBInsert($this->Request);
		$del->dpi($this->Request);
		
       	echo $dog->createdID;
       	return $dog->createdID;	
       		
	} // End function addDogWithEmail

} // End Class
 
?>