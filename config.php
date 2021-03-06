<?php
namespace devilLib;
$twitchLib['clientID'] = ""; //Add your Client ID Here
$twitchLib['clientSecret'] = ""; //Add your Client Secret Here
$twitchLib['uri'] = "http://localhost/TwitchAPILib/TwitchAPILib.php"; //Set your URI to the one you have registered to your Twitch APP
$twitchLib['scopes'] = array(); //set all scoped needed. https://dev.twitch.tv/docs/authentication#scopes
$twitchLib['streamerOauth'] = ""; //Add your Oauth Token to make requests on user behaf.
$twitchLib['defaultChannel'] = 44322889; // Add your Channel/User ID for default channel can be found here -> https://www.twitchtools.com/channel/<Twitchname>
$twitchLib['codeFlow'] = "OAuthAuth"; //Code Flow type. OAuthAuth is recommened for normal channel and user info pulling.
//Valid Code Flows 'OIDCImplict', 'OIDCAuth','OAuthImplict','OAuthAuth','OAuthClientCredntials' https://dev.twitch.tv/docs/authentication#getting-tokens
$twitchLib['apiVersion'] = "V5"; // Can user 'HELIX' or 'V5'
$twitchLib['debugMode']=false; // sets debug mode for the API to print out what its doing.

?>
