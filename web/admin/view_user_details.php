<?
        /* This Source Code Form is subject to the terms of the Mozilla Public
         * License, v. 2.0. If a copy of the MPL was not distributed with this
         * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

        // Include required functions file
        require_once('../includes/functions.php');
        require_once('../includes/authenticate.php');

        // Session handler is database
        session_set_save_handler('db_open', 'db_close', '_read', '_write', '_destroy', '_clean');

        // Start the session
        session_start('SimpleRisk');

        // Check for session timeout or renegotiation
        session_check();

	// Default is no alert
	$alert = false;

        // Check if access is authorized
        if ($_SESSION["admin"] != "1")
        {
                header("Location: /");
                exit(0);
        }

	// Get the user ID
        $user_id = (int)$_POST['user'];

	// If the user has been updated
	if (isset($_POST['update_user']))
	{
		// Verify the user ID value is an integer
		if (is_int($user_id))
		{
                	// There is an alert message
                	$alert = true;

			// Get the submitted values
			$name = $_POST['name'];
			$email = $_POST['email'];
			$teams = $_POST['team'];
	                $admin = isset($_POST['admin']) ? '1' : '0';
        	        $submit_risks = isset($_POST['submit_risks']) ? '1' : '0';
                	$modify_risks = isset($_POST['modify_risks']) ? '1' : '0';
                	$plan_mitigations = isset($_POST['plan_mitigations']) ? '1' : '0';
                	$review_high = isset($_POST['review_high']) ? '1' : '0';
                	$review_medium = isset($_POST['review_medium']) ? '1' : '0';
                	$review_low = isset($_POST['review_low']) ? '1' : '0';

                        // Create a boolean for all
                        $all = false;

                        // Create a boolean for none
                        $none = false;

                        // Create the team value
                        foreach ($teams as $value)
                        {
                                // If the selected value is all
                                if ($value == "all") $all = true;

                                // If the selected value is none
                                if ($value == "none") $none = true;

                                $team .= ":";
                                $team .= $value;
                                $team .= ":";
                        }

                        // If all was selected then assign all teams
                        if ($all) $team = "all";

                        // If none was selected then assign no teams
                        if ($none) $team = "none";

			// Update the user
			update_user($user_id, $name, $email, $team, $admin, $review_high, $review_medium, $review_low, $submit_risks, $modify_risks, $plan_mitigations);

                        // Audit log
                        $risk_id = 1000;
                        $message = "An existing user was modified by the \"" . $_SESSION['user'] . "\" user.";
                        write_log($risk_id, $_SESSION['uid'], $message);

                        $alert_message = "The user was updated successfully.";
		}
	}

        // Verify value is an integer
        if (is_int($user_id))
        {
                // Get the users information
                $user_info = get_user_by_id($user_id);
                $username = $user_info['username'];
                $name = $user_info['name'];
                $email = $user_info['email'];
                $last_login = $user_info['last_login'];
		$teams = $user_info['teams'];
                $admin = $user_info['admin'];
                $review_high = $user_info['review_high'];
                $review_medium = $user_info['review_medium'];
                $review_low = $user_info['review_low'];
                $submit_risks = $user_info['submit_risks'];
                $modify_risks = $user_info['modify_risks'];
                $plan_mitigations = $user_info['plan_mitigations'];
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
                <a href="/index.php">Home</a> 
              </li>
              <li>
                <a href="/management/index.php">Risk Management</a> 
              </li>
              <li>
                <a href="/reports/index.php">Reporting</a> 
              </li>
              <li class="active">
                <a href="/admin/index.php">Configure</a>
              </li>
            </ul>
          </div>
<?
if ($_SESSION["access"] == "granted")
{
          echo "<div class=\"btn-group pull-right\">\n";
          echo "<a class=\"btn dropdown-toggle\" data-toggle=\"dropdown\" href=\"#\">".$_SESSION['name']."<span class=\"caret\"></span></a>\n";
          echo "<ul class=\"dropdown-menu\">\n";
          echo "<li>\n";
          echo "<a href=\"/account/profile.php\">My Profile</a>\n";
          echo "</li>\n";
          echo "<li>\n";
          echo "<a href=\"/logout.php\">Logout</a>\n";
          echo "</li>\n";
          echo "</ul>\n";
          echo "</div>\n";
}
?>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <ul class="nav  nav-pills nav-stacked">
            <li>
              <a href="/admin/index.php">Configure Risk Formula</a> 
            </li>
            <li>
              <a href="/admin/review_settings.php">Configure Review Settings</a>
            </li>
            <li>
              <a href="/admin/add_remove_values.php">Add and Remove Values</a> 
            </li>
            <li class="active">
              <a href="/admin/user_management.php">User Management</a> 
            </li>
            <li>
              <a href="/admin/custom_names.php">Redefine Naming Conventions</a> 
            </li>
            <li>
              <a href="/admin/audit_trail.php">Audit Trail</a>
            </li>
            <li>
              <a href="/admin/extras.php">Extras</a>
            </li>
            <li>
              <a href="/admin/announcements.php">Announcements</a>
            </li>
            <li>
              <a href="/admin/about.php">About</a>        
            </li>
          </ul>
        </div>
        <div class="span9">
          <div class="row-fluid">
            <div class="span12">
              <div class="hero-unit">
                <form name="update_user" method="post" action="">
                <p>
                <h4>Update an Existing User:</h4>
                <input name="user" type="hidden" value="<? echo $user_id; ?>" />
                Full Name: <input name="name" type="text" maxlength="50" size="20" value="<? echo htmlentities($name); ?>" /><br />
                E-mail Address: <input name="email" type="text" maxlength="200" size="20" value="<? echo htmlentities($email); ?>" /><br />
                Username: <input name="username" type="text" maxlength="20" size="20" disabled="disabled" value="<? echo htmlentities($username); ?>" /><br />
		Last Login: <input name="last_login" type="text" maxlength="20" size="20" disabled="disabled" value="<? echo $last_login; ?>" /><br />
                <h6><u>Team(s)</u></h6>
                <? create_multiple_dropdown("team", $teams); ?>
                <h6><u>User Responsibilities</u></h6>
                <ul>
                  <li><input name="submit_risks" type="checkbox"<? if ($submit_risks) echo " checked" ?> />&nbsp;Able to Submit New Risks</li>
                  <li><input name="modify_risks" type="checkbox"<? if ($modify_risks) echo " checked" ?> />&nbsp;Able to Modify Existing Risks</li>
                  <li><input name="plan_mitigations" type="checkbox"<? if ($plan_mitigations) echo " checked" ?> />&nbsp;Able to Plan Mitigations</li>
                  <li><input name="review_low" type="checkbox"<? if ($review_low) echo " checked" ?> />&nbsp;Able to Review Low Risks</li>
                  <li><input name="review_medium" type="checkbox"<? if ($review_medium) echo " checked" ?> />&nbsp;Able to Review Medium Risks</li>
                  <li><input name="review_high" type="checkbox"<? if ($review_high) echo " checked" ?> />&nbsp;Able to Review High Risks</li>
                  <li><input name="admin" type="checkbox"<? if ($admin) echo " checked" ?> />&nbsp;Allow Access to &quot;Configure&quot; Menu</li>
                </ul>
                <input type="submit" value="Update" name="update_user" /><br />
                </p>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

</html>
