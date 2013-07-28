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

	// Default is no alert
	$alert = false;

        // Check if a password reset email was requested
        if (isset($_POST['send_reset_email']))
        {
                $username = $_POST['user'];

		// Try to generate a password reset token
		password_reset_by_username($username);

		// Send an alert message
		$alert = true;
		$alert_message = "If the user exists in the system, then a password reset e-mail should be on it's way.";
        }

        // Check if a password reset was requested
        if (isset($_POST['password_reset']))
        {
                $username = $_POST['user'];
		$token = $_POST['token'];
		$password = $_POST['password'];
		$repeat_password = $_POST['repeat_password'];

		// Send an alert message
		$alert = true;

		// If a password reset was submitted
		if (password_reset_by_token($username, $token, $password, $repeat_password))
		{
			$alert_message = "Your password has been reset successfully.";
		}
		else $alert_message = "There was a problem with your password reset request.  Please try again.";
        }

?>

<!doctype html>
<html>
  
  <head>
    <script src="/js/jquery.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>
    <title>SimpleRisk: Enterprise Risk Management Simplified</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <link rel="stylesheet" href="/css/bootstrap.css">
    <link rel="stylesheet" href="/css/bootstrap-responsive.css"> 
  </head>
  
  <body>
    <? if ($alert) echo "<script>alert(\"" . $alert_message . "\");</script>" ?>
    <title>SimpleRisk: Enterprise Risk Management Simplified</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <link rel="stylesheet" href="/css/bootstrap.css">
    <link rel="stylesheet" href="/css/bootstrap-responsive.css">
    <link rel="stylesheet" href="/css/divshot-util.css">
    <link rel="stylesheet" href="/css/divshot-canvas.css">
    <div class="navbar">
      <div class="navbar-inner">
        <div class="container">
          <a class="brand" href="http://code.google.com/p/simplerisk/">SimpleRisk</a>
          <div class="navbar-content">
            <ul class="nav">
              <li class="active">
                <a href="/index.php">Home</a> 
              </li>
              <li>
                <a href="/management/index.php">Risk Management</a> 
              </li>
              <li>
                <a href="/reports/index.php">Reporting</a> 
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span9">
          <div class="well">
            <p><label><u>Send Password Reset Email</u></label></p>
            <form name="send_reset_email" method="post" action="">
            Username: <input class="input-medium" name="user" id="user" type="text" maxlength="20" />
            <div class="form-actions">
              <button type="submit" name="send_reset_email" class="btn btn-primary">Send</button>
              <input class="btn" value="Reset" type="reset">
            </div>
            </form>
          </div>
        </div>
      </div>
      <div class="row-fluid">
        <div class="span9">
          <div class="well">
            <p><label><u>Password Reset</u></label></p>
            <form name="password_reset" method="post" action="">
            Username: <input class="input-medium" name="user" id="user" type="text" maxlength="20" /><br />
            Reset Token: <input class="input-medium" name="token" id="token" type="password" maxlength="20" /><br />
            Password: <input class="input-medium" name="password" id="password" type="password" maxlength="50" /><br />
            Repeat Password: <input class="input-medium" name="repeat_password" id="repeat_password" type="password" maxlength="50" />
            <div class="form-actions">
              <button type="submit" name="password_reset" class="btn btn-primary">Submit</button>
              <input class="btn" value="Reset" type="reset">
            </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>

</html>
