<?php

/**
 * Permet de déployer facilement un service REST
 */
class RestService{

    public $_allow = array();
    public $_content_type = "application/json";
    public $_request = array();
    private $_method = "";
    private $_code = 200;
    private $securedRest = false;
    private $securedKeys = array();
    
    private $requestPublicKey;
    private $requestSecure;
    private $requestService;

    /**
     * Constructeur
     */
    public function __construct() {
	if(isset($_REQUEST['request'])){
	    $this->_method = strtolower(trim(str_replace("/", "", $_REQUEST['request'])));
	}
	$this->inputs();
    }
    
    
    /**
     * Définit qu'il s'agit d'un service REST sécurisé, précise des couples clé publique / clé privée
     * @param array $keys   Tableau associatif. array('clepublique' => 'cleprivee')
     */
    public function setSecured($keys){
	$this->securedRest = true;
	$this->securedKeys = $keys;
    }

    
    /**
     * Get Referere
     * @return string
     */
    public function get_referer() {
	return $_SERVER['HTTP_REFERER'];
    }

    
    /**
     * Envoie une réponse dans le navigateur
     * @param string $data  Réponse à envoyer. (Typiquement au format JSON)
     * @param integer $status	Statut HTTP de la requete. (200 pour succès)
     */
    public function response($data, $status) {
	$this->_code = ($status) ? $status : 200;
	$this->set_headers();
	echo $data;
	exit;
    }

    
    /**
     * Retourne le message 'verbose' associé au code réponse renvoyé
     * @return string
     */
    private function get_status_message() {
	$status = array(
	    100 => 'Continue',
	    101 => 'Switching Protocols',
	    200 => 'OK',
	    201 => 'Created',
	    202 => 'Accepted',
	    203 => 'Non-Authoritative Information',
	    204 => 'No Content',
	    205 => 'Reset Content',
	    206 => 'Partial Content',
	    300 => 'Multiple Choices',
	    301 => 'Moved Permanently',
	    302 => 'Found',
	    303 => 'See Other',
	    304 => 'Not Modified',
	    305 => 'Use Proxy',
	    306 => '(Unused)',
	    307 => 'Temporary Redirect',
	    400 => 'Bad Request',
	    401 => 'Unauthorized',
	    402 => 'Payment Required',
	    403 => 'Forbidden',
	    404 => 'Not Found',
	    405 => 'Method Not Allowed',
	    406 => 'Not Acceptable',
	    407 => 'Proxy Authentication Required',
	    408 => 'Request Timeout',
	    409 => 'Conflict',
	    410 => 'Gone',
	    411 => 'Length Required',
	    412 => 'Precondition Failed',
	    413 => 'Request Entity Too Large',
	    414 => 'Request-URI Too Long',
	    415 => 'Unsupported Media Type',
	    416 => 'Requested Range Not Satisfiable',
	    417 => 'Expectation Failed',
	    500 => 'Internal Server Error',
	    501 => 'Not Implemented',
	    502 => 'Bad Gateway',
	    503 => 'Service Unavailable',
	    504 => 'Gateway Timeout',
	    505 => 'HTTP Version Not Supported');
	return ($status[$this->_code]) ? $status[$this->_code] : $status[500];
    }

    
    /**
     * Retourne le type de methode employé pour l'appel. (POST, GET, PUT ou DELETE)
     * @return string
     */
    public function get_request_method() {
	return $_SERVER['REQUEST_METHOD'];
    }
    
    
    /**
     * Met en place le service
     */
    public function processApi() {
	if($this->securedRest){
	    if(!isset($this->requestSecure) || !isset($this->requestPublicKey)){
		$this->response('Utilisateur non authentifié', 401);
	    }
	    if(!$this->verifyKeys($this->requestPublicKey, $this->requestSecure)){
		$this->response('Utilisateur non authentifié', 401);
	    }
	}
	
	$func = $this->_method;
	if ((int) method_exists($this, $func) > 0){
	    $this->$func();
	}else{
	    $this->response('', 404);
	}
	
    }
    
    
    /**
     * Vérifie la concordance entre la clé publique et la chaine de verification transmise. (Grace à la clé privée)
     * @param string $publicKey	Clé publique
     * @param string $secureString    Chaine de vérification
     * @return boolean
     */
    private function verifyKeys($publicKey, $secureString){
	if(!isset($this->securedKeys[$publicKey])){
	    return false;
	}
	
	$localHmac = hash_hmac('sha256', $this->getToEncodeString($publicKey), $this->securedKeys[$publicKey]);
	return $this->compareHMAC($localHmac, $secureString);
    }
    
    
    /**
     * Retourne la chaîne à encoder pour vérification de la chaîne de vérification sécurité.
     * @param string $publicKey	Clé publique
     * @return string
     */
    private function getToEncodeString($publicKey){
	$toEncode = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REDIRECT_URL'];
	$toEncode .= $this->get_request_method();
	$toEncode .= $publicKey;
	$toEncode .= json_encode($this->_request);
	
	return $toEncode;
    }
    
    
    /**
     * Compare deux valeurs HMAC
     * @param string $a	HMAC#1
     * @param string $b	HMAC#2
     * @return boolean
     */
    private function compareHMAC($a, $b){
	if (!is_string($a) || !is_string($b)) {
            return false;
        }
       
        $len = strlen($a);
        if ($len !== strlen($b)) {
            return false;
        }

        $status = 0;
        for ($i = 0; $i < $len; $i++) {
            $status |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $status === 0; 
    }

    
    /**
     * Transforme correctement les données POST/GET transmises
     */
    private function inputs() {
	if  (isset($_GET['publickey']) && isset($_GET['secure'])) {
	    $this->requestPublicKey = $_GET['publickey'];
	    $this->requestSecure = $_GET['secure'];
	    unset($_GET['publickey']);
	    unset($_GET['secure']);
	}
	if (isset($_GET['request'])) {
	    $this->requestService = $_GET['request'];
	    unset($_GET['request']);
	}

	switch ($this->get_request_method()) {
	    case "POST":
		$this->_request = $this->cleanInputs($_POST);
		break;
	    case "GET":
	    case "DELETE":
		$this->_request = $this->cleanInputs($_GET);
		break;
	    case "PUT":
		parse_str(file_get_contents("php://input"), $this->_request);
		$this->_request = $this->cleanInputs($_GET);
		break;
	    default:
		$this->response('', 406);
		break;
	}
    }

    
    /**
     * Nettoie une entrée
     * @param array $data   Données en entrée
     * @return array	Données en sortie
     */
    private function cleanInputs($data) {
	$clean_input = array();
	if (is_array($data)) {
	    foreach ($data as $k => $v) {
		$clean_input[$k] = $this->cleanInputs($v);
	    }
	} else {
	    if (get_magic_quotes_gpc()) {
		$data = trim(stripslashes($data));
	    }
	    $data = strip_tags($data);
	    $clean_input = trim($data);
	}
	return $clean_input;
    }

    
    /**
     * Définit les headers à envoyer au navigateur
     */
    private function set_headers() {
	header("HTTP/1.1 " . $this->_code . " " . $this->get_status_message());
	header("Content-Type:" . $this->_content_type);
    }

}