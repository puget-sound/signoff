<?php

require_once('config.php');

function db_connect() {
  global $DB_HOST;
	global $DB_USERNAME;
	global $DB_PASS;
  global $DB_DBNAME;

  $conn = mysqli_connect($DB_HOST, $DB_USERNAME, $DB_PASS, $DB_DBNAME);
  return $conn;
}

//function for getting id
function ldapGetFullName($username) {
  global $LDAP_AUTHNAME;
	global $LDAP_PASS;
	global $LDAP_SERVERS;

	$ldapconn = ldap_connect($LDAP_SERVERS)
	    or die("Could not connect to LDAP server.");
	$ldapbind = ldap_bind($ldapconn, $LDAP_AUTHNAME, $LDAP_PASS);
	$result = ldap_search($ldapconn, "ou=Accounts,dc=pugetsound,dc=edu", "(samaccountname=$username)") or die ("Error in search query: ".ldap_error($ldapconn));
    $data = ldap_get_entries($ldapconn, $result);
    $id =  $data[0]['cn'][0];
    ldap_close($ldapconn);
    return getFullName($id);
}

//function for getting full names
function getFullName($id) {
  global $LDAP_AUTHNAME;
	global $LDAP_PASS;
	global $LDAP_SERVERS;

	$ldapconn = ldap_connect($LDAP_SERVERS)
    or die("Could not connect to LDAP server.");
	$ldapbind = ldap_bind($ldapconn, $LDAP_AUTHNAME, $LDAP_PASS);
	$result = ldap_search($ldapconn, "ou=Accounts,dc=pugetsound,dc=edu", "(cn=$id)") or die ("Error in search query: ".ldap_error($ldapconn));
	$data = ldap_get_entries($ldapconn, $result);
	@$fullName = $data[0]['displayname'][0];
	if ($fullName != null) {
		return $fullName;
	} else {
		return "err";
	}
}

/*function ldapUsernameAuth($username, $pass, $requestId) {
  global $LDAP_AUTHNAME;
	global $LDAP_PASS;
	global $LDAP_SERVERS;

	$ldapconn = ldap_connect($LDAP_SERVERS)
	    or die("Could not connect to LDAP server.");
	$ldapbind = ldap_bind($ldapconn, $LDAP_AUTHNAME, $LDAP_PASS);
	$result = ldap_search($ldapconn, "ou=Accounts,dc=pugetsound,dc=edu", "(samaccountname=$username)") or die ("Error in search query: ".ldap_error($ldapconn));
    $data = ldap_get_entries($ldapconn, $result);
    @$userId =  $data[0]['cn'][0];
    ldap_close($ldapconn);
    $ldapconn = ldap_connect($LDAP_SERVERS)
    or die("Could not connect to LDAP server.");
    @$ldapbind = ldap_bind($ldapconn, "cn=$userId, ou=Accounts, dc=pugetsound, dc=edu", "$pass");
    if ($ldapbind) {
    	setcookie('requestResponseUser', $username, time() + 60*60*24*30, '/');
    	header("Location: respond.php?requestId=$requestId");
    } else {
    	header("Location: respond_login.php?error=wuwp&request=$requestId");
    }
}*/

?>
