<?php
	// Start a session and load the Facebook library.
	session_start();
	
	// Get the current network from PATH_INFO.
	$server_url = ltrim($_SERVER["PATH_INFO"], "/");
	
	$servers = new stdClass();
	//$servers->{$url}		= Array("id" => $client_id, "secret" => $client_secret, "scope" => Array($a_permission), "class" => $oauth_class, "file" => $oauth_file);
	$servers->facebook		= Array("id" => "0000000000000000", "secret" => "0000000000000000000000000000000000000000", "scope" => Array("email", "user_friends"), "class" => "OAuthFacebook", "file" => "facebook.class.php");
	$servers->google		= Array("id" => "0000000000000000", "secret" => "0000000000000000000000000000000000000000", "class" => "OAuthGoogle", "file" => "google.class.php");
	$servers->microsoft		= Array("id" => "0000000000000000", "secret" => "0000000000000000000000000000000000000000", "class" => "OAuthMicrosoft", "file" => "microsoft.class.php");
	$servers->yahoo			= Array("id" => "0000000000000000", "secret" => "0000000000000000000000000000000000000000", "class" => "OAuthYahoo", "file" => "yahoo.class.php");
	
	if(!isset($servers->{$server_url})) {
		echo "Server was not found.<br />\n\n";
		goto session;
	}
	
	$server = (object)$servers->{$server_url};
	$server->url = $server_url;
	if(!isset($server->name)) $server->name = ucfirst(strtolower($server->url));
	if(!isset($server->scope)) $server->scope = Array();
	require_once __DIR__ . '/testclient/client/' . ltrim($server->file, "/");
	$oauth = new $server->class($server->id, $server->secret, Array("errors" => Array("throw" => false)));
	
	// Delete the access token if needed.
	if(isset($_GET["del_token"])) $oauth->accessToken(false);
	
	// Try to fetch an access token with the code in $_GET["code"]. Also check the state in $_GET["state"].
	try {
		$oauth->getAccessTokenFromCode("http://example.com/code.php/{$server->url}");
		
		// --------
		// If we got here, no error was thrown and an access token was successfully retrieved from the code.
		// Output the code and access token.
		echo "Success! Click the link at the bottom of the page to return home and fetch data using the access token.<br />\n";
		echo "Code: " . htmlspecialchars($_GET["code"]) . "<br />\n";
		echo "Access Token: " . htmlspecialchars($server->accessToken()) . "\n<br /><br />\n\n";
	} catch(Exception $error) {
		echo "Error - Click the link at the bottom of the page to return home and try again: " . $error->getMessage() . "\n<br /><br />\n\n";
	}
	
	// Output a link to the homepage to fetch data using the access token.
	echo "<a href=\"../default.php/{$server->url}\">Home</a>\n";
	