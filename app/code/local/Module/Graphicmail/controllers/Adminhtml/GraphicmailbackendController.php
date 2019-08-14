<?php
class Module_Graphicmail_Adminhtml_GraphicmailbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
        $this->loadLayout();
	   	$this->_title($this->__("GraphicMail Setting"));
	    
		 
		if(isset($_POST['domain'])){
			$_SESSION['message']=$this->updateGraphAccount($_POST);
		}	
      
	
		$userinfo=$this->getGraphAccount();
		$_SESSION['GraphAccount']=$userinfo;
		 $condt=$this->getMaillingList($userinfo[0]);
	 if($condt!=1)
	 {
		$_SESSION['mailinglist']=$this->getMaillingList($userinfo[0]);
	 }
	  
		$_SESSION['dataset']=$this->getDataset($userinfo[0]);
	 
	   	$this->renderLayout();
    }
		
	public function saveAction() {
		$this->loadLayout();
		$this->_title($this->__("GraphicMail Setting"));
		
		 $this->renderLayout();  
	  
	} 
	
	 
	
	
	public function importAction(){
	
	/* Get customer model, run a query */
	$collection = Mage::getModel('customer/customer')
				  ->getCollection()
				  ->addAttributeToSelect('*');

	$result = array();
	foreach ($collection as $customer) {
		$result[] = $customer->toArray();
	}
	$this->createCSV($result);
	$graphicmail = Mage::getModel('customer/graphicmail');
	$graphicmail->registerGraphicMailUser();
	$_SESSION['message']='Users has been imported sucessfully';
	$this->_redirect('*/*/');
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
	
	
	
	protected function updateGraphAccount($data){
		
		$Connection=$this->dataWrite();
		$fields="username='$data[username]', password='$data[password]', domain='$data[domain]'";
		
			$userinfo=$this->getGraphAccount();
		 
		 $condt=$this->getMaillingList($userinfo[0]);
	 
	 
				if($condt!=1)
				{
					if($data['maillist_id']=="" || $data['maillist_id']==0)
					{
						$fields.=" ,dataset_id='$data[dataset_id]',maillist_id='$data[maillist_id]'";
						$message='<li class="success-msg">Account Information has been saved. Mailling List need to select</li></ul></li>';
					}
					else
					{
						$fields.=" ,dataset_id='$data[dataset_id]',maillist_id='$data[maillist_id]'";
						$message='<li class="success-msg"><ul><li>Mailling List has been set</li></ul></li>';	
					}	
				}
				if($condt==1)
				{
					$fields.=" ,dataset_id='$data[dataset_id]',maillist_id='$data[maillist_id]'";
					$message='<li class="error-msg">Authentication failed.Check domain name, username and password.</li></ul></li>';
				}
				
				
		
		$query="UPDATE graphic_account SET ".$fields;
		$Connection->query($query);
		return $message;
	}
	
	public function resetAction() {
		$Connection=$this->dataWrite();
		$fields="username='', password='', domain='', maillist_id=''";
		$query="UPDATE graphic_account SET ".$fields;
		
		$Connection->query($query);
		$this->_redirect('*/*');
	}
	
	protected function getGraphAccount(){
		$Connection=$this->dataRead();
		$query='SELECT * FROM graphic_account';
		$results = $Connection->fetchAll($query);
		return $results;
	}
	protected function getMaillingList($user){
		$path="https://".$user['domain']."/api.aspx?Username=".$user['username']."&Password=".$user['password']."&Function=get_mailinglists&Principal=blGnGdu9T4JLZWPAVI2D3sYSK2c9gT2Z";
		
		$doc = new DomDocument();
		$doc->load($path);
        $mailinglists = $doc->getElementsByTagName( "mailinglist" );
		
		$count=1;
		
		$_select='<select name="maillist_id">';
		$_select.="<option selected='selected' value='0'>--------------------Select MailingList--------------------</option>";
		foreach( $mailinglists as $mailinglist ){
 		  	$mailinglistid = $mailinglist->getElementsByTagName("mailinglistid" );
			$mailinglistid = $mailinglistid->item(0)->nodeValue;
			$description = $mailinglist->getElementsByTagName("description" );
			$description = $description->item(0)->nodeValue;
				if($mailinglistid){
					$count++;
					
					if(@$user['maillist_id']==$mailinglistid)	
						$_select.="<option selected='selected' value='$mailinglistid'>$description</option>";
					else
						$_select.="<option value='$mailinglistid'>$description</option>";
				}
			 
			
		}
	
			$_select.='</select>';
		
		
		if($count>1)
		{	
			return $_select;
		}
		else 
		{
			return $count;
			
		}
	
	}
	protected function getDataset($user){
		$path="https://".$user['domain']."/api.aspx?Username=".$user['username']."&Password=".$user['password']."&Function=get_datasets&Principal=blGnGdu9T4JLZWPAVI2D3sYSK2c9gT2Z";
		$doc = new DomDocument();
		$doc->load($path);
        $datasets = $doc->getElementsByTagName( "dataset" );
		$count=0;
		$_dataset='<select name="dataset_id">';
		
		foreach( $datasets as $dataset ){
			$datasetid = $dataset->getElementsByTagName("datasetid" );
			$datasetid = $datasetid->item(0)->nodeValue;
			$name = $dataset->getElementsByTagName("name" );
			$name = $name->item(0)->nodeValue;
			 
			if(@$user['dataset_id']==$datasetid)
				$_dataset.="<option selected='selected' value='$datasetid'>$name</option>";
			else
				$_dataset.="<option value='$datasetid'>$name</option>";
				$count++;
		}
		
		$_dataset.='</select>';
		return $_dataset;
	}
	protected function createCSV($datas){
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
		foreach($datas as $data){
			++$count;
			if(isset($data['firstname'])){
			$row[$count]["firstname"]= $data['firstname'];
			$row[$count]["lastname"]= $data['lastname'];
			}
			$row[$count]["email"]=$data['email'];  
		}                     
		 
			foreach ($row as $fields) {
    			fputcsv($fh, $fields);	
			}
		
		//close the file handle outside the loop
		//this releases the lock too
		fclose($fh);
					
	}
}
?>