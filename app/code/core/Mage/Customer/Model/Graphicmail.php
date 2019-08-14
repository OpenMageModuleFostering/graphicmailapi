<?php
class Mage_Customer_Model_GraphicMail extends Mage_Core_Model_Abstract{
	function hello(){
		echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB); 
	}	
	public function CreateGraphicMailUser($data){
		$dir=$_SERVER['DOCUMENT_ROOT'].'/CSV';
		if(!is_dir($dir)){
			mkdir($dir,755);
		}
		$filename=$dir.'/file.csv';
		@unlink($filename);
		$baseurl=(isset($_SERVER['HTTPS']) ? 'https' :'http'). '://' . $_SERVER['HTTP_HOST'];
 	  	$fileurl=$baseurl.'/CSV/file.csv';
		if (file_exists($filename)):
    		if(!is_writable($filename)):
        		die ("The file: $filename is not writable");
    		endif;
		elseif( !is_writable( getcwd() ) ):
			die("you cannot create files in this directory.  Check the permissions");
		endif;
		//open the file for APPENDING
		//add the "t" terminator for windows
		//if using a mac then set the ini directive
		$fh = fopen($filename, "at");
		//Lock the file for the write operation
		flock($fh, LOCK_EX);
		//loop through the recordset
   		//db output manipulation
		if(isset($data['firstname'])){
   		$row[1]["firstname"]= $data['firstname'];
    	$row[1]["lastname"]= $data['lastname'];
		}
		$row[1]["email"]=$data['email'];
		if(isset($data['address_1'])){
		$row[1]["company"]= @$data['company'];
		$row[1]["telephone"]= $data['telephone'];
		$row[1]["fax"]= @$data['fax'];
		$row[1]["address_1"]= $data['address_1'];
		$row[1]["address_2"]= $data['address_2'];
		$row[1]["city"]= $data['city'];
		$row[1]["state"]= $data['state'];
		$row[1]["zip"]= $data['zip'];
		$row[1]["country_id"]= $data['country_id'];
		}
   		//use fputcsv by preference - we can just pass the row-array
    	if (function_exists("fputcsv")):
        	fputcsv($fh, $row[1], ",", "\"");
    	else:
        	//clean up the strings for escaping and add quotes
       		foreach ($row[1] as $val):
            	$b[] = '"'.addslashes($val).'"';
        	endforeach;
        	//comma separate the values
        	$string = implode(",",$b);
        	//write the data to the file
        	fwrite($fh, $string ."\n",strlen($string));
    	endif;
           
		//close the file handle outside the loop
		//this releases the lock too
		fclose($fh);
		//$user=$this->getUserDetail();
		if(isset($data['address_1'])){
		$this->editGraphicMailUser();
		}else{
		$this->registerGraphicMailUser();
		}
	}
	
	protected function sendPost($url){ 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, true); // remove body
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$head = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		//print_r($head) ;
	}
	protected function dataWrite(){
		/**
     	* Get the resource model
     	*/
    	$resource = Mage::getSingleton('core/resource');
    	/**
     	* Retrieve the write connection
     	*/
   		$writeConnection = $resource->getConnection('core_write');
		return $writeConnection;
	} 
	protected function dataRead(){
		/**
     	* Get the resource model
     	*/
    	$resource = Mage::getSingleton('core/resource');
    	/**
     	* Retrieve the read connection
     	*/
   		$readConnection = $resource->getConnection('core_read');
		return $readConnection;
	}
	protected function getUserDetail(){
		$Connection=$this->dataRead();
		$query='SELECT * FROM graphic_account';
		$results = $Connection->fetchAll($query);
		return $results;
	}
	public function updateGraphicMailAccount($oldEmail){
		$user=$this->getUserDetail();
		$url="https://".$user[0]['domain']."/api.aspx?Username=".$user[0]['username']."&Password=".$user[0]['password']."&Function=post_delete_emailaddress&MailinglistID=".$user[0]['maillist_id']."&EmailAddress=".$oldEmail."&Principal=blGnGdu9T4JLZWPAVI2D3sYSK2c9gT2Z";
		$this->sendPost($url);
	}
	public function subscribeGraphicMailNewsletter($email){
		$user=$this->getUserDetail();
		$url="https://".$user[0]['domain']."/api.aspx?Username=".$user[0]['username']."&Password=".$user[0]['password']."&Function=post_subscribe&Email=".$email."&MailinglistID=".$user[0]['maillist_id']."&Principal=blGnGdu9T4JLZWPAVI2D3sYSK2c9gT2Z";
		$this->sendPost($url);
	}
	public function unSubscribeGraphicMailNewsletter($email){
		$user=$this->getUserDetail();
		$url="https://".$user[0]['domain']."/api.aspx?Username=".$user[0]['username']."&Password=".$user[0]['password']."&Function=post_unsubscribe&Email=".$email."&MailinglistID=".$user[0]['maillist_id']."&Principal=blGnGdu9T4JLZWPAVI2D3sYSK2c9gT2Z";
		$this->sendPost($url);
	}
	public function registerGraphicMailUser(){
		$user=$this->getUserDetail();
		$baseurl=(isset($_SERVER['HTTPS']) ? 'https' :'http'). '://' . $_SERVER['HTTP_HOST'];
		$fileurl=$baseurl.'/CSV/file.csv';
		$url="https://".$user[0]['domain']."/api.aspx?Username=".$user[0]['username']."&Password=".$user[0]['password']."&Function=post_import_dataset&EmailCol=3&Col1=1&Col2=2&Col6=4&Col9=5&Col10=6&Col4=7&DatasetID=".$user[0]['dataset_id']."&MailingListID=".$user[0]['maillist_id']."&ImportMode=1&FileUrl=".$fileurl."&Principal=blGnGdu9T4JLZWPAVI2D3sYSK2c9gT2Z";
 		$this->sendPost($url);
	}
	public function editGraphicMailUser(){
		$user=$this->getUserDetail();
		$baseurl=(isset($_SERVER['HTTPS']) ? 'https' :'http'). '://' . $_SERVER['HTTP_HOST'];
		$fileurl=$baseurl.'/CSV/file.csv';
		$url="https://".$user[0]['domain']."/api.aspx?Username=".$user[0]['username']."&Password=".$user[0]['password']."&Function=post_import_dataset&EmailCol=3&Col1=1&Col2=2&Col4=4&Col6=5&Col7=6&Col9=7&Col10=8&Col11=9&Col12=10&Col13=11&Col14=12&DatasetID=".$user[0]['dataset_id']."&MailingListID=".$user[0]['maillist_id']."&ImportMode=1&FileUrl=".$fileurl."&Principal=blGnGdu9T4JLZWPAVI2D3sYSK2c9gT2Z";
		$this->sendPost($url);
	}
}