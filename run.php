<?php


include 'restservice_class.php';
include 'wpconfig_class.php';

class DiatemWpGetConfig extends RestService{
    public function __construct() {
	parent::__construct();
    }
    
    public function _get(){
	if($this->get_request_method() != 'GET'){
	    $this->response('', 405);
	}
	
	$retStr = WpConfig::getJSon();
	$this->response($retStr, 200);
    }
}

$api = new DiatemWpGetConfig();
$api->setSecured(WpConfig::getSecuredKeys());
$api->processApi();


