<?

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// Include required configuration files
require_once('config.php');
require_once('functions.php');

/***************************
 * FUNCTION: GENERATE SALT *
 ***************************/
function generateSalt($username)
{
	$salt = '$2a$15$';
	$salt = $salt . md5(strtolower($username));
	return $salt;
}

/***************************
 * FUNCTION: GENERATE HASH *
 ***************************/
function generateHash($salt, $password)
{
	$hash = crypt($password, $salt);
	return $hash;
}

/***************************
 * FUNCTION: IS VALID USER *
 ***************************/
function is_valid_user($user, $pass)
{
	// By default we just use SimpleRisk users
	// Check for AD add-on
	if (file_exists("../extras/active_directory.php"))
	{
		// Add the AD functionality
		require_once("../extras/active_directory.php");

		//$valid_ad = is_valid_ad_user($user, $pass);
		$valid_ad = false;
	}

	// Check for a valid SimpleRisk user
	$valid_simplerisk = is_valid_simplerisk_user($user, $pass);

	// If either the AD or SimpleRisk user are valid
	if ($valid_ad || $valid_simplerisk)
	{
		// Open the database connection
        	$db = db_open();

        	// Query the DB for the users information
        	$stmt = $db->prepare("SELECT value, name, admin, review_high, review_medium, review_low, submit_risks, modify_risks, plan_mitigations FROM user WHERE username = :user");
        	$stmt->bindParam(":user", $user, PDO::PARAM_STR, 20);
        	$stmt->execute();

        	// Store the list in the array
        	$array = $stmt->fetchAll();

        	// Set the session values
		$_SESSION['uid'] = $array[0]['value'];
		$_SESSION['user'] = $user;
        	$_SESSION['name'] = htmlentities($array[0]['name']);
		$_SESSION['admin'] = $array[0]['admin'];
		$_SESSION['review_high'] = $array[0]['review_high'];
		$_SESSION['review_medium'] = $array[0]['review_medium'];
		$_SESSION['review_low'] = $array[0]['review_low'];
		$_SESSION['submit_risks'] = $array[0]['submit_risks'];
		$_SESSION['modify_risks'] = $array[0]['modify_risks'];
		$_SESSION['plan_mitigations'] = $array[0]['plan_mitigations'];

		return true;
	}
	else return false;
}

/**************************************
 * FUNCTION: IS VALID SIMPLERISK USER *
 **************************************/
function is_valid_simplerisk_user($user, $pass)
{
	$salt = generateSalt($user);
	$providedPassword = generateHash($salt, $pass);

        // Open the database connection
        $db = db_open();

        // Query the DB for a matching user and hash
        $stmt = $db->prepare("SELECT password FROM user WHERE username = :user");
        $stmt->bindParam(":user", $user, PDO::PARAM_STR, 20);
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

	// Get the stored password
	$storedPassword = $array[0]['password'];

        // Close the database connection
        db_close($db);

	// If the passwords are equal
	if ($providedPassword == $storedPassword)
	{
		return true;
	}
	else return false;
}

/********************************
 * FUNCTION: IS SIMPLERISK USER *
 ********************************/
function is_simplerisk_user($username)
{
        // Open the database connection
        $db = db_open();

        // Query the DB for a matching user and hash
        $stmt = $db->prepare("SELECT value FROM user WHERE username = :username");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR, 20);
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

	$value = $array[0]['value'];

	// Close the database connection
        db_close($db);

	// If the user does not exist return 0
	if (empty($array))
	{
		return 0;
	}
	// Otherwise, return the user id value
	else return $value;
}

/*********************
 * FUNCTION: IS USER *
 *********************/
function is_user($username)
{
	// By default we just use SimpleRisk users
        // Check for AD add-on
        if (file_exists("../extras/active_directory.php"))
        {
                // Add the AD functionality
                require_once("../extras/active_directory.php");
                
                //$valid_ad = is_ad_user($username, $pass);
                $valid_ad = false;
        }       

        // Check for a valid SimpleRisk user
        $valid_simplerisk = is_simplerisk_user($username);
        
        // If either the SimpleRisk user is valid
	if ($valid_simplerisk != 0)
	{
		return $valid_simplerisk;
	}
	else return 0;
}

/****************************
 * FUNCTION: GENERATE TOKEN *
 ****************************/
function generate_token($size)
{               
        $token = "";
        $values = array_merge(range(0, 9), range('a', 'z'), range('A
', 'Z'));
        
        for ($i = 0; $i < $size; $i++)
        {
                $token .= $values[array_rand($values)];
        }
 
        return $token;
}

/****************************************
 * FUNCTION: PASSWORD RESET BY USERNAME *
 ****************************************/
function password_reset_by_username($username)
{               
        $userid = is_simplerisk_user($username);
        
        // Check if the username exists
        if ($userid != 0)
        {       
                password_reset_by_userid($userid);
        
                return true;
        }
        else return false;
}

/**************************************
 * FUNCTION: PASSWORD RESET BY USERID *
 **************************************/
function password_reset_by_userid($userid)
{
        
        // Generate a 20 character reset token
        $token = generate_token(20);

        // Open the database connection
        $db = db_open();

        // Get the users e-mail address
        $stmt = $db->prepare("SELECT username, name, email FROM user WHERE value=:userid");

        $stmt->bindParam(":userid", $userid, PDO::PARAM_INT);

        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        $username = $array[0]['username'];
        $name = $array[0]['name'];
        $email = $array[0]['email'];

        // Insert into the password reset table
        $stmt = $db->prepare("INSERT INTO password_reset (`username`, `token`) VALUES (:username, :token)");

        $stmt->bindParam(":username", $username, PDO::PARAM_STR, 20);
        $stmt->bindParam(":token", $token, PDO::PARAM_STR, 20);

        $stmt->execute();

        // Close the database connection
        db_close($db);

        // Send the reset e-mail
        send_reset_email($username, $name, $email, $token);
}

/******************************
 * FUNCTION: SEND RESET EMAIL *
 ******************************/
function send_reset_email($username, $name, $email, $token)
{
        $to = $email;
        $subject = "SimpleRisk Password Reset";
        $body = $name.",\n\nA request was submitted to reset your SimpleRisk password.  Your username is \"".$username."\" and your password reset token is \"".$token."\".  You may now use the \"Forgot your password\" link on the SimpleRisk log in page to reset your password.";
        mail($to, $subject, $body);
}

/*************************************
 * FUNCTION: PASSWORD RESET BY TOKEN *
 *************************************/
function password_reset_by_token($username, $token, $password, $repeat_password)
{
	$userid = is_simplerisk_user($username);

	// Verify that the passwords match
	if ($password == $repeat_password)
	{
        	// If the username exists
        	if ($userid != 0)
        	{
			// If the reset token is valid
			if (is_valid_reset_token($username, $token))
			{
			        // Open the database connection
        			$db = db_open();

                        	// Create the new password hash
                        	$salt = generateSalt($username);
                        	$hash = generateHash($salt, $password);

                        	// Update the password
                        	$stmt = $db->prepare("UPDATE user SET password=:hash WHERE username=:username");
                        	$stmt->bindParam(":hash", $hash, PDO::PARAM_STR, 60);
                        	$stmt->bindParam(":username", $username, PDO::PARAM_STR, 20);
                        	$stmt->execute();

			        // Close the database connection
        			db_close($db);

				return true;
			}
		}
		else return false;
	}
	else return false;
}

/**********************************
 * FUNCTION: IS VALID RESET TOKEN *
 **********************************/
function is_valid_reset_token($username, $token)
{
        // Open the database connection
        $db = db_open();

	// Delete tokens older than 15 minutes
	$stmt = $db->prepare("DELETE FROM password_reset WHERE timestamp < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
	$stmt->execute();

	// Increment the attempts for the username
	$stmt = $db->prepare("UPDATE password_reset SET attempts=attempts+1 WHERE username=:username");
	$stmt->bindParam(":username", $username, PDO::PARAM_STR, 20);
	$stmt->execute();

        // Search for a valid token
        $stmt = $db->prepare("SELECT attempts FROM password_reset WHERE username=:username AND token=:token");

        $stmt->bindParam(":username", $username, PDO::PARAM_STR, 20);
	$stmt->bindParam(":token", $token, PDO::PARAM_STR, 20);
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        $attempts = $array[0]['attempts'];

        // Close the database connection
        db_close($db);

	// If there is not a match for the username and token
	if (empty($array))
	{
		return false;
	}
	else
	{
		// Remove the matching token
		$stmt = $db->prepare("DELETE FROM password_reset WHERE token=:token");
                $stmt->bindParam(":token", $token, PDO::PARAM_STR, 20);
                $stmt->execute();

		// Matching token has been attempted <= 5 times
		if ($attempts < 5)
		{
			return true;
		}
		// Matching token has been attempted > 5 times
		else return false;
	}
}

/***************************
 * FUNCTION: SESSION CHECK *
 ***************************/
function session_check()
{
        // Last request was more $last_activity
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > LAST_ACTIVITY_TIMEOUT))
        {
                // unset $_SESSION variable for the run-time
                session_unset();

                // destroy session data in storage
                session_destroy();

		// Return false
		return false;
        }
        // update last activity time stamp
        $_SESSION['LAST_ACTIVITY'] = time();

        // If the session created value has not been set
        if (!isset($_SESSION['CREATED']))
        {
                // Set it with the current time
                $_SESSION['CREATED'] = time();
        }
        // Otherwise check if it was created more than $created
        else if (time() - $_SESSION['CREATED'] > SESSION_RENEG_TIMEOUT)
        {
                // change session ID for the current session an invalidate old session ID
                session_regenerate_id(true);

                // update creation time
                $_SESSION['CREATED'] = time();
        }

	// Return true
	return true;
}

/*******************************
 * FUNCTION: READ SESSION DATA *
 *******************************/
function _read($id)
{
        // Open the database connection
        $db = db_open();

	$stmt = $db->prepare("SELECT data FROM sessions WHERE `id`=:id");
	$stmt->bindParam(":id", $id, PDO::PARAM_STR, 32);
	$stmt->execute();

	// Store the list in the array
	$array = $stmt->fetchAll();

	// Close the database connection
	db_close($db);

	return $array[0]['data'];
}

/********************************
 * FUNCTION: WRITE SESSION DATA *
 ********************************/
function _write($id, $data)
{
	$access = time();

	// Open the database connection
        $db = db_open();

        $stmt = $db->prepare("REPLACE INTO sessions VALUES (:id, :access, :data)");
	$stmt->bindParam(":id", $id, PDO::PARAM_STR, 32);
	$stmt->bindParam(":access", $access, PDO::PARAM_INT);
	$stmt->bindParam(":data", $data, PDO::PARAM_STR);
	$return = $stmt->execute();

        // Close the database connection
        db_close($db);

	return $return;
}

/**********************************
 * FUNCTION: DESTROY SESSION DATA *
 **********************************/
function _destroy($id)
{
        // Open the database connection
        $db = db_open();

        $stmt = $db->prepare("DELETE FROM sessions WHERE `id`=:id");
        $stmt->bindParam(":id", $id, PDO::PARAM_STR, 32);
        $return = $stmt->execute();

        // Close the database connection
        db_close($db);

        return $return;
}

/***************************************
 * FUNCTION: CLEAN OLD SESSION RECORDS *
 ***************************************/
function _clean($max)
{
	$old = time() - $max;

        // Open the database connection
        $db = db_open();

        $stmt = $db->prepare("DELETE FROM sessions WHERE access < :old");
        $stmt->bindParam(":old", $old, PDO::PARAM_INT, 10);
        $return = $stmt->execute();

        // Close the database connection
        db_close($db);

        return $return;
}

?>
