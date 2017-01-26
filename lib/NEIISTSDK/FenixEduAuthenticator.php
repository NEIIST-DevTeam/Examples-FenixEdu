<?php
require_once("Authenticator.php");
require_once("fenixedu/RestRequest.php");
require_once("fenixedu/FenixEduException.php");

class FenixEduAuthenticator extends Authenticator {
    private $accessKey;
	private $secretKey;

	private $user;

	private $code;

	private $accessToken;
	private $refreshToken;
	private $expirationTime;
	
	private $callbackUrl;
	private $apiBaseUrl;
    
    protected function init($config) {
        global $_SESSION;
		if(session_id() === "") {
			session_start();
		}
		$this->accessKey = $config["access_key"];
		$this->secretKey = $config["secret_key"];
		$this->accessToken = isset($config["access_token"]) ? $config["access_token"] : null;
		$this->refreshToken = isset($config["refresh_token"]) ? $config["access_token"] : null;
		$this->callbackUrl = isset($config["callback_url"]) ? $config["callback_url"] : null;
		$this->apiBaseUrl = isset($config["api_base_url"]) ? $config["api_base_url"] : "http://fenix.ist.utl.pt";
		if(isset($_SESSION['accessToken'])){
			$this->accessToken = $_SESSION['accessToken'];
			$this->refreshToken = $_SESSION['refreshToken'];
			$this->expirationTime = $_SESSION['expires'];
			$this->refreshTokenIfExpired();
		}
    }
    
    //returns authorization url based on the api base url
	protected function getAuthUrl() {
		$params = array(
			'client_id'	=> $this->accessKey,
			'redirect_uri' => $this->callbackUrl
		);
		$query = http_build_query($params, '', '&');
		return $this->apiBaseUrl . "/oauth/userdialog?" . $query;
	}
	
	
	protected function getAccessTokenFromCode($code){
        global $_SESSION;
		$reqbody = array( 'client_id' => $this->accessKey,  'client_secret' => $this->secretKey, 'redirect_uri' => $this->callbackUrl, 'code' => $code, 'grant_type' => 'authorization_code');
		$url = $this->apiBaseUrl . "/oauth/access_token";
		$req = new RestRequest($url, 'POST', $reqbody);
		$req->execute();
		$info = $req->getResponseInfo();
		if($info['http_code'] == 200){
			$json = json_decode($req->getResponseBody());
			$this->accessToken = $json->access_token;
			$this->refreshToken = $json->refresh_token;
			$this->expirationTime = time() + $json->expires_in;
            $_SESSION['accessToken'] = $this->accessToken;
            $_SESSION['refreshToken'] = $this->refreshToken;
            $_SESSION['expires'] = $this->expirationTime;
			header('Location: ' . $this->callbackUrl);
		} else {
            $result = json_decode('{"error":"Error getting Access Token!","errorDescription":"Connection to API failed or invalid client secret."}');
            throw new FenixEduException($result);
		}
	}

	protected function hasToken() {
		return $this->refreshToken !== null;
	}

	protected function isTokenExpired() {
		if($this->expirationTime != null) return time() >= $this->expirationTime;
		else return false;
	}

	function refreshTokenIfExpired() {
		if($this->isTokenExpired()) $this->refreshAccessToken();
	}

	protected function buildURL($endpoint, $public){
		$url = $this->apiBaseUrl . "/api/fenix/v1/" . $endpoint;
		if(!$public){
			$url .= '?access_token='. urlencode($this->getAccessToken());
		}
		return $url;
	}

	protected function getAccessToken(){
		if($this->expirationTime <= time()){
			$this->refreshAccessToken();
		}
		return $this->accessToken;
	}

	protected function refreshAccessToken(){
        global $_SESSION;
		$reqbody = array('client_id' => $this->accessKey,  'client_secret' => $this->secretKey, 'refresh_token' => $this->refreshToken);
		$url = $this->apiBaseUrl . "/oauth/refresh_token";
		$req = new RestRequest($url, 'POST', $reqbody);
		$req->execute();
		$info = $req->getResponseInfo();
		$result = json_decode($req->getResponseBody());
		if($info['http_code'] == 200){
			$this->accessToken = $result->access_token;
			$this->expirationTime = time() + $result->expires_in;
            $_SESSION['accessToken'] = $this->accessToken;
            $_SESSION['expires'] = $this->expirationTime;
		} elseif($info['http_code'] == 401) {
			throw new FenixEduException($result);
		}
	}
	
	protected function get($endpoint, $public = false){
		$url = $this->buildURL($endpoint, $public);
		$req = new RestRequest($url, 'GET');
		$req->execute();
		$result = json_decode($req->getResponseBody());
		$info = $req->getResponseInfo();
		if($info['http_code'] == 401)
			throw new FenixEduException($result);
		elseif($info['http_code'] == 200)
			return $result;
	}

	protected function put($endpoint, $data = ""){
		$url = $this->buildURL($endpoint, $public);
		$req = new RestRequest($url, 'POST', $data);
		$req->execute();
		return json_decode($req->getResponseBody());
	}

    public function login() {
        if(isset($_GET['error'])) {
            return false;
        } else if(isset($_GET['code'])) {
            $code = $_GET['code'];
            $this->getAccessTokenFromCode($code);
            return true;
        } else if(!$this->hasToken()) {
            $authorizationUrl = $this->getAuthURL();
            header(sprintf("Location: %s", $authorizationUrl));
            exit;
        }
        return false;
    }
    
    public function isAuthenticated() {
        return $this->hasToken();
    }

    public function logout() {
        //TODO need to figure this one out
    }
    
    public function getUsername() {
        return $this->getIstId();
    }
	
	public function getIstId() {
		return $this->getPerson()->username;
	}

	public function getAboutInfo() {
		return $this->get("about");
	}

	public function getCourse($id) {
		return $this->get("courses/".$id);
	}

	public function getCourseEvaluations($id) {
		return $this->get("courses/".$id."/evaluations");
	}

	public function getCourseGroups($id) {
		return $this->get("courses/".$id."/groups");
	}

	public function getCourseSchedule($id) {
		return $this->get("courses/".$id."/schedule");
	}

	public function getCourseStudents($id) {
		return $this->get("courses/".$id."/students");
	}

	public function getDegrees() {
		return $this->get("degrees");
	}

	public function getDegree($id) {
		return $this->get("degrees/".$id);
	}

	public function getDegreeCourses($id) {
		return $this->get("degrees/".$id."/courses");
	}

	public function getPerson() {
		return $this->get("person");
	}

	public function getPersonCalendarClasses() {
		return $this->get("person/calendar/classes");
	}

	public function getPersonCalendarEvaluations() {
		return $this->get("person/calendar/evaluations");
	}

	public function getPersonCourses() {
		return $this->get("person/courses");
	}

	public function getCurriculum() {
		return $this->get("person/curriculum");
	}

	public function getPersonEvaluations() {
		return $this->get("person/evaluations");
	}

	public function enrollPersonEvaluation($id) {
		return $this->put("person/evaluations/".$id, "enrol=yes");
	}

	public function disenrollPersonEvaluation($id) {
		return $this->put("person/evaluations/".$id, "enrol=no");
	}

	public function getPersonPayments() {
		return $this->get("person/payments");
	}

	public function getSpaces() {
		return $this->get("spaces");
	}

	public function getSpace($id) {
		return $this->get("spaces/".$id);
	}
}
?>
