<?php
/*
* Application: Twitch API Library
* Author: Bigbaddevil666
*
*/
namespace devilLib;
require_once('ErrorHandler.php');
class TwitchAPILib {

  const V5URL = 'https://api.twitch.tv/kraken/';
  const HELIXURL = 'https://api.twitch.tv/helix/';
  const FLOWS = array(
    'OIDCImplict',
    'OIDCAuth',
    'OAuthImplict',
    'OAuthAuth',
    'OAuthClientCredntials'
  );

  const VERSIONS = array(
    'V5',
    'HELIX'
  );

  const SCOPES = array(
    'channel_check_subscription',
    'channel_commercial',
    'channel_editor',
    'channel_feed_edit',
    'channel_feed_read',
    'channel_read',
    'channel_stream',
    'channel_subscriptions',
    'chat_login',
    'collections_edit',
    'communities_edit',
    'communities_moderate',
    'openid',
    'user_blocks_edit',
    'user_blocks_read',
    'user_follows_edit',
    'user_read',
    'user_subscriptions',
    'viewing_activity_read',
    'user:edit',
    'user:read:email'
  );

  public function getClientID(){
    require 'config.php';
    if($twitchLib['clientID'] == ""){
      throw new configException('Client ID was not set in TwitchAPILib/config.php please set this!');
    }
    return $twitchLib['clientID'];
  }

  public function getClientSecret(){
    require 'config.php';
    if(empty($twitchLib['clientSecret'])){
      throw new configException('Client Secret was not set in TwitchAPILib/config.php please set this!');
    }
    return $twitchLib['clientSecret'];
  }

  public function getUri(){
    require 'config.php';
    if(empty($twitchLib['uri'])){
      throw new configException('URI was not set in TwitchAPILib/config.php please set this!');
    }
    return $twitchLib['uri'];
  }

  public function getStreamerOauth(){
    require 'config.php';
    if(empty($twitchLib['streamerOauth'])){
      throw new configException('streamerOauth was not set in TwitchAPILib/config.php please set this!');
    }
    return $twitchLib['streamerOauth'];
  }

  public function getDefaultChannel(){
    require 'config.php';
    if(!is_int($twitchLib['defaultChannel'])){
      throw new configException('defaultChannel needs to be an Int Example "44322889"');
    }
    if(empty($twitchLib['defaultChannel'])){
      throw new configException('defaultChannel was not set in TwitchAPILib/config.php please set this!');
    }
    return $twitchLib['defaultChannel'];
  }

  public function getCodeFlowType(){
    require 'config.php';
    if(empty($twitchLib['codeFlow'])){
      throw new configException('codeFlow was not set in TwitchAPILib/config.php please set this!');
    }
    if(!in_array($twitchLib['codeFlow'], self::FLOWS)){
      throw new configException($twitchLib['codeFlow'].', is not a valid Code Flow type! Accepted types are: '.implode(", ", self::FLOWS));
    }

    return $twitchLib['codeFlow'];
  }

  public function getAPIVersion(){
    require 'config.php';
    if(empty($twitchLib['apiVersion'])){
      throw new configException('apiVersion was not set in TwitchAPILib/config.php please set this!');
    }
    if(!in_array($twitchLib['apiVersion'], self::VERSIONS)){
      throw new configException($twitchLib['apiVersion'].', is not a valid API version! Accepted types are: '.implode(", ", self::VERSIONS));
    }
    return $twitchLib['apiVersion'];
  }

  public function getScopes(){
    require 'config.php';
    if(!$twitchLib['scopes']){
      throw new configException('scopes was not set in TwitchAPILib/config.php please set this!');
    }
    if(!is_array($twitchLib['scopes'])){
      throw new configException('scopes must be an array in config!');
    }
      foreach ($twitchLib['scopes'] as $scope) {
        if(!in_array($scope, self::SCOPES)){
          throw new configException($scope.', is not a valid Scope! Accepted V5 types are: '.implode(", ", self::SCOPES));
        }
      }
    return $twitchLib['scopes'];
  }

  public function getDebugMode(){
    if(!is_bool($twitchLib['debugMode'])){
      throw new configException('debugMode must be "True" or "False"!');
    }
    return $twitchLib['debugMode'];
  }


  public function getAuthURL($nonce=null, $state=null, $forceVerify=false){
    $baseurl='https://api.twitch.tv/kraken/oauth2/authorize?client_id='.$this->getClientID().
    '&redirect_uri='.$this->getUri().'&response_type=code&scope='.implode(" ",$this->getScopes());

    if(!is_bool($forceVerify)){
      throw new \InvalidArgumentException('Force Verify got "'.$forceVerify.'" This needs to be True or False');
    }

    if($this->getCodeFlowType() == 'OAuthAuth' || $this->getCodeFlowType() == 'OAuthImplict'){
      if(is_null($state)){
        $url=$baseurl.''.'&force_verify='.var_export($forceVerify, true);

      }elseif (!is_null($state)){
        $url = $baseurl.''.'&state='.$state.'&force_verify='.var_export($forceVerify, true);
      }

    }elseif ($this->getCodeFlowType() == 'OIDCImplict' || $this->getCodeFlowType() == 'OIDCAuth'){

      if(!in_array('openid', $this->getScopes())){
        throw new \Exception('You must include the "openid" scope in order to use OIDC code flows');
      }

      if(is_null($nonce) && is_null($state)){

        $url = $baseurl;

      }elseif (!is_null($nonce) && is_null($state)){

        $url = $baseurl.''.'&nonce='.$nonce;

      }elseif (is_null($nonce) && !is_null($state)) {

        $url = $baseurl.''.'&state='.$state;

      }elseif (!is_null($nonce) && !is_null($state)) {

        $url = $baseurl.''.'&nonce='.$nonce.'&state='.$state;

      }

    }elseif ($this->getCodeFlowType() == 'OAuthClientCredntials') {
      $url = $baseurl.''.$this->getClientID().'&redirect_uri='.$this->getUri().'&grant_type=client_credentials&scope='.implode(" ", $this->getScopes());
    }
    return $url;
  }


  protected function execCurl($curlInit, $query, $token){
    $baseURL = '';
    $headerArray = '';
    if($this->getAPIVersion() == 'V5'){
      $baseURL = self::V5URL;
      $headerArray = array('Accept: application/vnd.twitchtv.v5+json', 'Client-ID: ' . $this->getClientID(), 'Authorization: OAuth ' . $token);
    }elseif ($this->getAPIVersion() == 'HELIX') {
      $baseURL = self::HELIXURL;
      $headerArray = array('Accept: application/vnd.twitchtv.v5+json', 'Client-ID: ' . $this->getClientID(), 'Authorization: Bearer ' . $token);
    }
    curl_setopt_array($curlInit, array(
			CURLOPT_URL => $query,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPHEADER => array('Accept: application/vnd.twitchtv.v5+json', 'Client-ID: ' . $this->getClientID(), 'Authorization: OAuth ' . $token)
		));
		$response = curl_exec($curlInit);
    //echo "Debug: ".$reponse;
		$decodedResponse = json_decode($response, true);
		return $decodedResponse;
  }

//class bracket V
}


?>
