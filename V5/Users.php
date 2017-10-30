<?php
/*
* Application: Twitch API Library
* Author: Bigbaddevil666
*
*/
namespace devilLib;
require_once('TwitchAPILib.php');
class Users extends TwitchAPILib{
  //$token = 'xo5x20ms9spupdhf2mlii65mk4eu40';
  public function getUser($id){
    $ch = curl_init();
		$response = $this->execCurl($ch, self::V5URL . 'users/'.$id, $this->getStreamerOauth());
		return $response['name'];
  }
}

 ?>
