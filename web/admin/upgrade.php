<?
        /* This Source Code Form is subject to the terms of the Mozilla Public
         * License, v. 2.0. If a copy of the MPL was not distributed with this
         * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

        // Include required functions file
        require_once('../includes/functions.php');
        require_once('../includes/authenticate.php');
	require_once('../includes/config.php');

        // Database version to upgrade
        $version_to_upgrade = "20130501-001";

        // Database version upgrading to
        $version_upgrading_to = "20130717-001";

        // Start the session
        session_start('SimpleRiskDBUpgrade');

        // Check for session timeout or renegotiation
        session_check();

        // If the user requested a logout
        if ($_GET['logout'] == "true")
        {
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

        	// Redirect to the upgrade login form
        	header( 'Location: /admin/upgrade.php' );
	}

	// Default is no alert
	$alert = false;

        // If the login form was posted
        if (isset($_POST['submit']))
        {
                $user = $_POST['user'];
                $pass = $_POST['pass'];

                // If the user is valid
                if (is_valid_user($user, $pass))
                {
                        // Check if the user is an admin
                        if ($_SESSION["admin"] == "1")
                        {
                                // Grant access
                                $_SESSION["access"] = "granted";
                        }
                        // The user is not an admin
                        else
                        {
				$alert = true;
                                $alert_message = "You need to log in as an administrative user in order to upgrade the database.";

                                // Deny access
                                $_SESSION["access"] = "denied";
                        }
                }
                // The user was not valid
                else
                {
			// Send an alert
			$alert = true;

                        // Invalid username or password
                        $alert_message = "Invalid username or password.";

                        // Deny access
                        $_SESSION["access"] = "denied";
                }
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
              <li>
                <a href="/admin/upgrade.php">Database Upgrade Script</a>
              </li>
              <li>
                <a href="/admin/upgrade.php?logout=true">Logout</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span12">
          <div class="row-fluid">
            <div class="span12">
              <div class="hero-unit">
<?
	// If access was not granted display the login form
	if ($_SESSION["access"] != "granted")
	{
      		echo "<p><label><u>Log In Here</u></label></p>\n";
      		echo "<form name=\"authenticate\" method=\"post\" action=\"\">\n";
      		echo "Username: <input class=\"input-medium\" name=\"user\" id=\"user\" type=\"text\" /><br />\n";
      		echo "Password: <input class=\"input-medium\" name=\"pass\" id=\"pass\" type=\"password\" />\n";
		echo "<br />\n";
      		echo "<button type=\"submit\" name=\"submit\" class=\"btn btn-primary\">Login</button>\n";
      		echo "</form>\n";
	}
	// Otherwise access was granted so check if the user is an admin
	else if ($_SESSION["admin"] == "1")
        {
		// If CONTINUE was not pressed
		if (!isset($_POST['upgrade_database']))
		{
			// Get the current application version
			$app_version = current_version("app");

			echo "The current application version is: " . $app_version . "<br />\n";

			// Get the current database version
			$db_version = current_version("db");

			echo "The current database version is: " . $db_version . "<br />\n";

			// If the version to upgrade is the current version
			if ($db_version == $version_to_upgrade)
			{
				echo "This script will ugprade your database from version " . $version_to_upgrade . " to the version that goes with these application files.  Click &quot;CONTINUE&quot; to proceed.<br />\n";
				echo "<br />\n";
				echo "<form name=\"upgrade_database\" method=\"post\" action=\"\">\n";
				echo "<button type=\"submit\" name=\"upgrade_database\" class=\"btn btn-primary\">CONTINUE</button>\n";
				echo "</form>\n";
			}
			// Otherwise if the db version matches the app version
			else if ($db_version == $app_version)
			{
				echo "Your database is already upgraded to the version that matches your application files.  No additional upgrade is necessary to make it work properly.<br />\n";
			}
			// Otherwise this is not the right database version to upgrade
			else
			{
				echo "This script was meant to upgrade database version " . $version_to_upgrade . " but your current database version is " . $db_version . ".  You will need to use a different database upgrade script instead.<br />\n";
			}
		}
		// Otherwise, CONTINUE was pressed
		else
		{
			// Connect to the database
			echo "Connecting to the SimpleRisk database.<br />\n";
			$db = db_open();

			echo "Beginning upgrade of SimpleRisk database.<br />\n";

			/****************************
                 	* DATABASE CHANGES GO HERE *
		 	****************************/

			// Create new risk_scoring table
			echo "Creating the new risk scoring table.<br />\n";
        		$stmt = $db->prepare("
				CREATE TABLE `risk_scoring` (
				`id` INT NOT NULL ,
				`scoring_method` INT NOT NULL ,
				`calculated_risk` FLOAT NOT NULL ,
				`CLASSIC_likelihood` FLOAT NOT NULL ,
				`CLASSIC_impact` FLOAT NOT NULL ,
				`CVSS_AccessVector` FLOAT NOT NULL ,
				`CVSS_AccessComplexity` FLOAT NOT NULL ,
				`CVSS_Authentication` FLOAT NOT NULL ,
				`CVSS_ConfImpact` FLOAT NOT NULL ,
				`CVSS_IntegImpact` FLOAT NOT NULL ,
				`CVSS_AvailImpact` FLOAT NOT NULL ,
				`CVSS_Exploitability` FLOAT NOT NULL ,
				`CVSS_RemediationLevel` FLOAT NOT NULL ,
				`CVSS_ReportConfidence` FLOAT NOT NULL ,
				`CVSS_CollateralDamagePotential` FLOAT NOT NULL ,
				`CVSS_TargetDistribution` FLOAT NOT NULL ,
				`CVSS_ConfidentialityRequirement` FLOAT NOT NULL ,
				`CVSS_IntegrityRequirement` FLOAT NOT NULL ,
				`CVSS_AvailabilityRequirement` FLOAT NOT NULL
				) ENGINE = MYISAM ;
			");
        		$stmt->execute();

			// Risks can now be a float value
			echo "Changing risks to now use float values.<br />\n";
			$stmt = $db->prepare("ALTER TABLE `risk_lookup` CHANGE `risk` `risk` FLOAT( 11 ) NOT NULL");
			$stmt->execute();

			// Create tables to define CVSS scoring values
			echo "Creating the tables to define CVSS scoring values.<br />\n";
			$stmt = $db->prepare("
				CREATE TABLE `CVSS_AccessVector` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_AccessComplexity` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_Authentication` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_ConfImpact` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_IntegImpact` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_AvailImpact` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_Exploitability` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_RemediationLevel` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_ReportConfidence` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_CollateralDamagePotential` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_TargetDistribution` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_ConfidentialityRequirement` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_IntegrityRequirement` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;

				CREATE TABLE `CVSS_AvailabilityRequirement` (
				`name` VARCHAR( 30 ) NOT NULL ,
				`value` FLOAT NOT NULL
				) ENGINE = MYISAM ;
			");
			$stmt->execute();

			// Populate tables for CVSS ratings
			echo "Populating tables for CVSS ratings.<br />\n";
			$stmt = $db->prepare("
				INSERT INTO `CVSS_AccessComplexity` (`name`, `value`) VALUES
				('Undefined', -1),
				('High', 0.35),
				('Medium', 0.61),
				('Low', 0.71);

				INSERT INTO `CVSS_AccessVector` (`name`, `value`) VALUES
				('Undefined', -1),
				('Local', 0.395),
				('Adjacent Network', 0.646),
				('Network', 1);

				INSERT INTO `CVSS_Authentication` (`name`, `value`) VALUES
				('Undefined', -1),
				('None', 0.704),
				('Single Instance', 0.56),
				('Multiple Instances', 0.45);

				INSERT INTO `CVSS_AvailabilityRequirement` (`name`, `value`) VALUES
				('Undefined', -1),
				('Low', 0.5),
				('Medium', 1),
				('High', 1.51);

				INSERT INTO `CVSS_AvailImpact` (`name`, `value`) VALUES
				('Undefined', -1),
				('None', 0),
				('Partial', 0.275),
				('Complete', 0.66);

				INSERT INTO `CVSS_CollateralDamagePotential` (`name`, `value`) VALUES
				('Undefined', -1),
				('None', 0),
				('Low (light loss)', 0.1),
				('Low-Mdium', 0.3),
				('Medium-High', 0.4),
				('High', 0.5);

				INSERT INTO `CVSS_ConfidentialityRequirement` (`name`, `value`) VALUES
				('Undefined', -1),
				('Low', 0.5),
				('Medium', 1),
				('High', 1.51);

				INSERT INTO `CVSS_ConfImpact` (`name`, `value`) VALUES
				('Undefined', -1),
				('None', 0),
				('Partial', 0.275),
				('Complete', 0.66);

				INSERT INTO `CVSS_Exploitability` (`name`, `value`) VALUES
				('Undefined', -1),
				('Unproven that exploit exists', 0.85),
				('Proof of concept code', 0.9),
				('Functional exploit exists', 0.95),
				('Widespread', 1);

				INSERT INTO `CVSS_IntegImpact` (`name`, `value`) VALUES
				('Undefined', -1),
				('None', 0),
				('Partial', 0.275),
				('Complete', 0.66);

				INSERT INTO `CVSS_IntegrityRequirement` (`name`, `value`) VALUES
				('Undefined', -1),
				('Low', 0.5),
				('Medium', 1),
				('High', 1.51);

				INSERT INTO `CVSS_RemediationLevel` (`name`, `value`) VALUES
				('Undefined', -1),
				('Official fix', 0.87),
				('Temporary fix', 0.9),
				('Workaround', 0.95),
				('Unavailable', 1);

				INSERT INTO `CVSS_ReportConfidence` (`name`, `value`) VALUES
				('Undefined', -1),
				('Not confirmed', 0.9),
				('Uncorroborated', 0.95),
				('Confirmed', 1);

				INSERT INTO `CVSS_TargetDistribution` (`name`, `value`) VALUES
				('Undefined', -1),
				('None (0%)', 0),
				('Low (0-25%)', 0.25),
				('Medium (26-75%)', 0.75),
				('High (76-100%)', 1);
			");
			$stmt->execute();

                	// Create table for audit logs
			echo "Creating a new table for audit logging.<br />\n";
	                $stmt = $db->prepare("
				CREATE TABLE `audit_log` (
				`timestamp` TIMESTAMP NOT NULL ,
				`risk_id` INT( 11 ) NOT NULL ,
				`user_id` INT( 11 ) NOT NULL ,
				`message` TEXT NOT NULL
				) ENGINE = MYISAM ;
			");
			$stmt->execute();

			// Track the user ID for closures
			echo "Adding ability to track the user ID for risk closures.<br />\n";
			$stmt = $db->prepare("ALTER TABLE `closures` ADD `user_id` INT( 11 ) NOT NULL AFTER `risk_id`");
			$stmt->execute();

			// Don't need the risk lookup table anymore
			echo "Removing the risk lookup table as we don't need it anymore.<br />\n";
			$stmt = $db->prepare("DROP TABLE `risk_lookup`");
			$stmt->execute();

			// Get all risk ids, likelihoods, and impacts
			echo "Copying current likelihoods and impacts into new risk_scoring table.<br />\n";
			$stmt = $db->prepare("SELECT id, likelihood, impact FROM risks");
			$stmt->execute();
			$array = $stmt->fetchAll();

			foreach ($array as $risk)
			{
				$id = $risk['id'];
				$likelihood = $risk['likelihood'];
				$impact = $risk['impact'];
				$calculated_risk = calculate_risk($impact, $likelihood);
				echo "Copying risk ID " . $id . ".<br />\n";
				$stmt = $db->prepare("INSERT INTO `risk_scoring` (`id`, `scoring_method`, `calculated_risk`, `CLASSIC_likelihood`, `CLASSIC_impact`) VALUES (:id, 1, :calculated_risk, :likelihood, :impact)");
				$stmt->bindParam(":id", $id, PDO::PARAM_INT);
				$stmt->bindParam(":calculated_risk", $calculated_risk, PDO::PARAM_INT);
				$stmt->bindParam(":likelihood", $likelihood, PDO::PARAM_INT);
				$stmt->bindParam(":impact", $impact, PDO::PARAM_INT);
				$stmt->execute();
			}

			// Don't track likelihood and impact in the risks table
			echo "Removing likelihood and impact from the risks table.<br />\n";
			$stmt = $db->prepare("
				ALTER TABLE `risks` DROP `likelihood` ,
				DROP `impact` ;
			");
			$stmt->execute();

			// Create a new table to track project association
			echo "Creating a new table to track project associations.<br />\n";
			$stmt = $db->prepare("
				CREATE TABLE `projects` (
				`value` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`name` VARCHAR( 100 ) NOT NULL ,
				`order` INT NOT NULL DEFAULT '999999'
				) ENGINE = MYISAM ;
			");
			$stmt->execute();

			// Insert the default unassigned risks project
			echo "Adding the default unassigned risks project.<br />\n";
			$stmt = $db->prepare("INSERT INTO `projects` (`value`, `name`, `order`) VALUES ('0', 'Unassigned Risks', '0');");
			$stmt->execute();

			// Set the default project ID to 0
			echo "Setting the default project ID to 0.<br />\n";
			$stmt = $db->prepare("UPDATE `projects` SET `value` = '0' WHERE `projects`.`name` ='Unassigned Risks';");
			$stmt->execute();

			// Set the default project ID for all risks to 0
			echo "Setting the default project ID for all risks to 0.<br />\n";
			$stmt = $db->prepare("ALTER TABLE `risks` ADD `project_id` INT(11) NOT NULL DEFAULT '0'");
			$stmt->execute();

			// Add new permissions to users
			echo "Adding new permissions to users.<br />\n";
			$stmt = $db->prepare("
				ALTER TABLE `user` ADD `submit_risks` TINYINT( 1 ) NOT NULL DEFAULT '0',
				ADD `modify_risks` TINYINT( 1 ) NOT NULL DEFAULT '0',
				ADD `plan_mitigations` TINYINT( 1 ) NOT NULL DEFAULT '0'
			");
			$stmt->execute();

			// Alter admin user permissions to use tinyint instead of bool
			echo "Altering type for existing user permissions.<br />\n";
			$stmt = $db->prepare("ALTER TABLE `user` CHANGE `admin` `admin` TINYINT( 1 ) NOT NULL DEFAULT '0'");
			$stmt->execute();

			// Alter review_high user permission to use tinyint instead of bool
			echo "Altering type for review_high user permission.<br />\n";
			$stmt = $db->prepare("ALTER TABLE `user` CHANGE `review_high` `review_high` TINYINT( 1 ) NOT NULL DEFAULT '0'");
			$stmt->execute();

			// Alter review_medium user permission to use tinyint instead of bool
			echo "Altering type for review_medium user permission.<br />\n";
			$stmt = $db->prepare("ALTER TABLE `user` CHANGE `review_medium` `review_medium` TINYINT( 1 ) NOT NULL DEFAULT '0'");
			$stmt->execute();

			// Alter review_low user permission to use tinyint instead of bool
			echo "Altering type for review_low user permission.<br />\n";
			$stmt = $db->prepare("ALTER TABLE `user` CHANGE `review_low` `review_low` TINYINT( 1 ) NOT NULL DEFAULT '0'");
			$stmt->execute();

			// Add field to track teams that users are assigned to
			echo "Adding a new field to track teams that users are assigned to.<br />\n";
			$stmt = $db->prepare("ALTER TABLE `user` ADD `teams` VARCHAR( 200 ) NOT NULL DEFAULT 'none' AFTER `last_login`");
			$stmt->execute();

			// Add table to track user sessions
			echo "Adding a new table to track user sessions.<br />\n";
			$stmt = $db->prepare("
				CREATE TABLE `sessions` (
				id varchar(32) NOT NULL,
				access int(10) unsigned,
				data text,
				PRIMARY KEY (id));
			");
			$stmt->execute();

			// Update risk levels
			echo "Updating risk levels to align with CVSS scoring defaults.<br />\n";
			$stmt = $db->prepare("UPDATE `risk_levels` SET `value`='7' WHERE `name`='High'");
			$stmt->execute();
			$stmt = $db->prepare("UPDATE `risk_levels` SET `value`='4' WHERE `name`='Medium'");
			$stmt->execute();
			$stmt = $db->prepare("UPDATE `risk_levels` SET `value`='0' WHERE `name`='Low'");
			$stmt->execute();

			// Update admin with new permissions
			echo "Updating the admin user with new permissions.<br />\n";
			$stmt = $db->prepare("UPDATE user SET `teams`='all', `submit_risks`=1, `modify_risks`=1, `plan_mitigations`=1 WHERE username='admin'");
			$stmt->execute();

			/************************
		 	 * END DATABASE CHANGES *
		 	 ************************/

			// Update the database version information
			echo "Updating the database version information.<br />\n";
			$stmt = $db->prepare("UPDATE `settings` SET `value` = '" . $version_upgrading_to . "' WHERE `settings`.`name` = 'db_version' AND `settings`.`value` = '" . $version_to_upgrade . "' LIMIT 1 ;");
			$stmt->execute();

			// Disconnect from the database
			echo "Disconnecting from the SimpleRisk database.<br />\n";
        		db_close($db);

			echo "SimpleRisk database upgrade is complete.<br />\n";
		}
	}
?>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

</html>
