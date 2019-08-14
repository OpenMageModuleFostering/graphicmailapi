<?php
$installer = $this;
$installer->startSetup();
$installer->run("create table graphic_account(id int not null auto_increment, domain text,username text,password text,maillist_id int(11),dataset_id int(11), primary key(id));insert into graphic_account values (NULL,'Domain','Username','Password','','');
    ");
//demo 
Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 