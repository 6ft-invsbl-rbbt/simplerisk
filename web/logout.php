<?
        /* This Source Code Form is subject to the terms of the Mozilla Public
         * License, v. 2.0. If a copy of the MPL was not distributed with this
         * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

        // Include required functions file
        require_once('includes/functions.php');
	require_once('includes/authenticate.php');

        // Session handler is database
        session_set_save_handler('db_open', 'db_close', '_read', '_write', '_destroy', '_clean');

	// Start session
	session_start('SimpleRisk');

	// Audit log
	$risk_id = 1000;
	$message = "Username \"" . $_SESSION['user'] . "\" logged out successfully.";
	write_log($risk_id, $_SESSION['uid'], $message);

	// Deny access
	$_SESSION["access"] = "denied";

	// Reset the session data
	$_SESSION = array();

	// Send a Set-Cookie to invalidate the session cookie
	if (isset($_COOKIES[session_name90]))
	{
        	$params = session_get_cookie_params();
        	setcookie(session_name(), '', 1, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
	}

	// Destroy the session
	session_destroy();

	// Redirect to the index
	header( 'Location: /' );

?>
