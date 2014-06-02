<?php

/*
Project Description:
This class is the security implementation of my REST API framework.
Our challenge is that we want to maintain the REST architecture of the API, ie keep the 
authentication at the HTTP layer.  This is problematic since it inherently requires that
the client have access to part of the security aparatus.

What we are going to do here is the following:
1. The Server sends a token.  There is not a way to know what the token is, but is it
repeatable and predicatable.  We don't disclose the pattern of predicatability.
2. Client accepts the token, encrypts it, and sends it to the server.  The function used for
encryption (and it's really hashing) cannot be secured.
3. Server encrypts the token and compares it to the token the client encrypted
4. If they match, we authenticate 

In math terms, given:
a predicatable token, a
a client-side hashing algorithm, f(a).  This is public knowledge.
a result, b --> f(a) = b
a server-side result, c --> f(a) = c
If b == c, then we do the deed!


Author: Christopher Morgan
Contact: christopher.t.morgan -at- gmail
Copyright 2013
*/

require_once('dbcore.class.php');


class APISecure {
    
    // Class properties
	private $token;

	// Class Method
	
	public function test()
	{
		echo "Sucessful test of APISecure";
	
	} // End function test
	
	// Function used to generate the pseudo-random token
	public function generateToken($server_info)
	{
	
		echo "User Agent: " . $server_info['HTTP_USER_AGENT'] . " IP: " . $server_info['REMOTE_ADDR'] . " SHA1: " . sha1($server_info['HTTP_USER_AGENT']) . " Time: " . date('z');	
	
	} // End function generateToken

} // END Class APISecure

?>