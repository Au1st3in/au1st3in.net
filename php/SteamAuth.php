<?php
require('config.php');

if(session_id() == ''){
	ob_start();
	session_start();
}

require("OpenID.php");

class SteamAuth{

	private $OpenID;
	private $OnLoginCallback;
	private $OnLoginFailedCallback;
	private $OnLogoutCallback;

	public $SteamID;

	public function __construct($Server = 'DEFAULT'){
		if($Server = 'DEFAULT') $Server = $_SERVER['SERVER_NAME'];
		$this->OpenID = new LightOpenID($Server);
		$this->OpenID->identity = 'http://steamcommunity.com/openid';

		$this->OnLoginCallback = function(){};
		$this->OnLoginFailedCallback = function(){};
		$this->OnLogoutCallback = function(){};
	}

	public function __call($closure, $args){
		return call_user_func_array($this->$closure, $args);
	}

	public function Init(){
		if($this->IsUserLoggedIn()){
			$this->SteamID = $_SESSION['steamid'];
			return;
		}

		if($this->OpenID->mode == 'cancel'){
			$this->OnLoginFailedCallback();
		}else if($this->OpenID->mode){
			if($this->OpenID->validate()){
				$this->SteamID = basename($this->OpenID->identity);
				if($this->OnLoginCallback($this->SteamID)){
					$_SESSION['steamid'] = $this->SteamID;
				}
			}else{
				$this->OnLoginFailedCallback();
			}
		}
	}

	public function IsUserLoggedIn(){
		return isset($_SESSION['steamid']) && strpos($_SESSION['steamid'], "7656") === 0 ? true : false;
	}

	public function RedirectLogin(){
		header("Location: " . $this->GetLoginURL());
	}

	public function GetLoginURL(){
		return $this->OpenID->authUrl();
	}

	public function Logout(){
		$this->OnLogoutCallback($this->SteamID);

		unset($_SESSION['steamid']);
		header("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}

	public function SetOnLoginCallback($OnLoginCallback){
		$this->OnLoginCallback = $OnLoginCallback;
	}

	public function SetOnLogoutCallback($OnLogoutCallback){
		$this->OnLogoutCallback = $OnLogoutCallback;
	}

	public function SetOnLoginFailedCallback($OnLoginFailedCallback){
		$this->OnLoginFailedCallback = $OnLoginFailedCallback;
	}
}

if (empty($_SESSION['steam_uptodate']) or empty($_SESSION['steam_personaname'])) {
	$url = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$SteamAPIKey."&steamids=".$_SESSION['steamid']);
	$content = json_decode($url, true);
	$_SESSION['steam_steamid'] = $content['response']['players'][0]['steamid'];
	$_SESSION['steam_communityvisibilitystate'] = $content['response']['players'][0]['communityvisibilitystate'];
	$_SESSION['steam_profilestate'] = $content['response']['players'][0]['profilestate'];
	$_SESSION['steam_personaname'] = $content['response']['players'][0]['personaname'];
	$_SESSION['steam_lastlogoff'] = $content['response']['players'][0]['lastlogoff'];
	$_SESSION['steam_profileurl'] = $content['response']['players'][0]['profileurl'];
	$_SESSION['steam_avatar'] = $content['response']['players'][0]['avatar'];
	$_SESSION['steam_avatarmedium'] = $content['response']['players'][0]['avatarmedium'];
	$_SESSION['steam_avatarfull'] = $content['response']['players'][0]['avatarfull'];
	$_SESSION['steam_personastate'] = $content['response']['players'][0]['personastate'];
	if (isset($content['response']['players'][0]['realname'])){
		$_SESSION['steam_realname'] = $content['response']['players'][0]['realname'];
	}else{
		$_SESSION['steam_realname'] = "";
	}
	$_SESSION['steam_primaryclanid'] = $content['response']['players'][0]['primaryclanid'];
	$_SESSION['steam_timecreated'] = $content['response']['players'][0]['timecreated'];
	$_SESSION['steam_uptodate'] = time();
}

$steamprofile['steamid'] = $_SESSION['steam_steamid'];
$steamprofile['communityvisibilitystate'] = $_SESSION['steam_communityvisibilitystate'];
$steamprofile['profilestate'] = $_SESSION['steam_profilestate'];
$steamprofile['personaname'] = $_SESSION['steam_personaname'];
$steamprofile['lastlogoff'] = $_SESSION['steam_lastlogoff'];
$steamprofile['profileurl'] = $_SESSION['steam_profileurl'];
$steamprofile['avatar'] = $_SESSION['steam_avatar'];
$steamprofile['avatarmedium'] = $_SESSION['steam_avatarmedium'];
$steamprofile['avatarfull'] = $_SESSION['steam_avatarfull'];
$steamprofile['personastate'] = $_SESSION['steam_personastate'];
$steamprofile['realname'] = $_SESSION['steam_realname'];
$steamprofile['primaryclanid'] = $_SESSION['steam_primaryclanid'];
$steamprofile['timecreated'] = $_SESSION['steam_timecreated'];
$steamprofile['uptodate'] = $_SESSION['steam_uptodate'];

?>
