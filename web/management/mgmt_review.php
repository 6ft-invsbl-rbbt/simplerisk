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

	// Default is not approved
	$approved = false;

        // Check if access is authorized
        if ($_SESSION["access"] != "granted")
        {
                header("Location: /");
                exit(0);
        }

        // Check if a risk ID was sent
        if (isset($_GET['id']) || isset($_POST['id']))
        {
                if (isset($_GET['id']))
                {
                        $id = htmlentities($_GET['id']);
                }
                else if (isset($_POST['id']))
                {
                        $id = htmlentities($_POST['id']);
                }

                // Get the details of the risk
                $risk = get_risk_by_id($id);

                $subject = htmlentities($risk[0]['subject']);
		$calculated_risk = $risk[0]['calculated_risk'];
		$risk_level = get_risk_level_name($calculated_risk);

		// If the risk level is high and they have permission
		if (($risk_level == "High") && ($_SESSION['review_high'] == 1))
		{
			// Review is approved
			$approved = true;
		}
		// If the risk level is medium and they have permission
		else if (($risk_level == "Medium") && ($_SESSION['review_medium'] == 1))
		{
                        // Review is approved
                        $approved = true;
		}
		// If the risk level is low and they have permission
		else if (($risk_level == "Low") && ($_SESSION['review_low'] == 1))
		{
                        // Review is approved
                        $approved = true;
		}
        }

	// If they are not approved to review the risk
	if (!($approved))
	{
		// There is an alert
		$alert = true;
		$alert_message = "You do not have permission to review " . $risk_level . " level risks.  Any reviews that you attempt to submit will not be recorded.  Please contact an administrator if you feel that you have reached this message in error.";
	}

        // Check if a new risk mitigation was submitted
        if (isset($_POST['submit']))
        {
		// If they are approved to review the risk
		if ($approved)
		{
                	$status = "Mgmt Reviewed";
                	$review = (int)addslashes($_POST['review']);
			$next_step = (int)addslashes($_POST['next_step']);
                	$reviewer = $_SESSION['uid'];
                	$comments = addslashes($_POST['comments']);

                	// Submit review
                	submit_management_review($id, $status, $review, $next_step, $reviewer, $comments);

                	// Audit log
                	$risk_id = $id;
                	$message = "A management review was submitted for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                	write_log($risk_id, $_SESSION['uid'], $message);

			// If the reviewer rejected the risk
			if ($review == 2)
			{
                		$status = "Closed";
                		$close_reason = "The risk was rejected by the reviewer.";
                		$note = "Risk was closed automatically when the reviewer rejected the risk.";

                		// Close the risk
                		close_risk($risk_id, $_SESSION['uid'], $status, $close_reason, $note);

                		// Audit log
                		$message = "Risk ID \"" . $risk_id . "\" automatically closed when username \"" . $_SESSION['user'] . "\" rejected the risk.";
                		write_log($risk_id, $_SESSION['uid'], $message);
			}

			// Redirect to plan mitigations page
			header('Location: /management/management_review.php?reviewed=true'); 
		}
		// They do not have permissions to review the risk
		else
		{
                	// There is an alert
                	$alert = true;
                	$alert_message = "You do not have permission to review " . $risk_level . " level risks.  The review that you attempted to submit was not recorded.  Please contact an administrator if you feel that you have reached this message in error.";
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
    <? if ($alert) echo "<script>alert(\"" . $alert_message . "\");</script>"; ?>
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
              <li class="active">
                <a href="/management/index.php">Risk Management</a> 
              </li>
              <li>
                <a href="/reports/index.php">Reporting</a> 
              </li>
<?
if ($_SESSION["admin"] == "1")
{
          echo "<li>\n";
          echo "<a href=\"/admin/index.php\">Configure</a>\n";
          echo "</li>\n";
}
          echo "</ul>\n";
          echo "</div>\n";

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
              <a href="/management/index.php">I. Submit Your Risks</a> 
            </li>
            <li>
              <a href="/management/plan_mitigations.php">II. Plan Your Mitigations</a> 
            </li>
            <li class="active">
              <a href="/management/management_review.php">III. Perform Management Reviews</a> 
            </li>
            <li>
              <a href="/management/prioritize_planning.php">IV. Prioritize for Project Planning</a> 
            </li>
            <li>
              <a href="/management/review_risks.php">V. Review Risks Regularly</a>
            </li>
          </ul>
        </div>
        <div class="span9">
          <div class="row-fluid">
            <div class="span12">
              <form name="submit_management_review" method="post" action="">
                <h4>Submit Management Review</h4>
                <h4>Risk ID: <? echo $id ?></h4>
                <h4>Subject: <? echo $subject ?></h4>
                Review: <? create_dropdown("review"); ?><br />
		Next Step: <? create_dropdown("next_step"); ?><br />
                <label>Comments</label>
                <textarea name="comments" cols="50" rows="3" id="comments"></textarea>
                <div class="form-actions">
                  <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                  <input class="btn" value="Reset" type="reset">
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

</html>
