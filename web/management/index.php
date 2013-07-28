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
        if ($_SESSION["access"] != "granted")
        {
                header("Location: /");
                exit(0);
        }

	// Check if the user has access to submit risks
	if ($_SESSION["submit_risks"] != 1)
	{
		$submit_risks = false;
		$alert = true;
		$alert_message = "You do not have permission to submit new risks.  Any risks that you attempt to submit will not be recorded.  Please contact an Administrator if you feel that you have reached this message in error.";
	}
	else $submit_risks = true;

        // Check if a new risk was submitted and the user has permissions to submit new risks
        if ((isset($_POST['submit'])) && $submit_risks)
        {
                $status = "New";
                $subject = addslashes($_POST['subject']);
		$location = addslashes($_POST['location']);
                $category = (int)$_POST['category'];
                $team = (int)$_POST['team'];
                $technology = (int)$_POST['technology'];
                $owner = (int)$_POST['owner'];
                $manager = (int)$_POST['manager'];
                $assessment = addslashes($_POST['assessment']);
                $notes = addslashes($_POST['notes']);

		// Risk scoring method
		// 1 = Classic
		// 2 = CVSS
		$scoring_method = (int)$_POST['scoring_method'];

		// Classic Risk Scoring Inputs
		$likelihood = (int)$_POST['likelihood'];
                $impact =(int) $_POST['impact'];

		// CVSS Risk Scoring Inputs
		$CVSS_AccessVector = (float)$_POST['CVSS_AccessVector'];
		$CVSS_AccessComplexity = (float)$_POST['CVSS_AccessComplexity'];
		$CVSS_Authentication = (float)$_POST['CVSS_Authentication'];
		$CVSS_ConfImpact = (float)$_POST['CVSS_ConfImpact'];
		$CVSS_IntegImpact = (float)$_POST['CVSS_IntegImpact'];
		$CVSS_AvailImpact = (float)$_POST['CVSS_AvailImpact'];
		$CVSS_Exploitability = (float)$_POST['CVSS_Exploitability'];
		$CVSS_RemediationLevel = (float)$_POST['CVSS_RemediationLevel'];
		$CVSS_ReportConfidence = (float)$_POST['CVSS_ReportConfidence'];
		$CVSS_CollateralDamagePotential = (float)$_POST['CVSS_CollateralDamagePotential'];
		$CVSS_TargetDistribution = (float)$_POST['CVSS_TargetDistribution'];
		$CVSS_ConfidentialityRequirement = (float)$_POST['CVSS_ConfidentialityRequirement'];
		$CVSS_IntegrityRequirement = (float)$_POST['CVSS_IntegrityRequirement'];
		$CVSS_AvailabilityRequirement = (float)$_POST['CVSS_AvailabilityRequirement'];

                // Submit risk and get back the id
                $last_insert_id = submit_risk($status, $subject, $location, $category, $team, $technology, $owner, $manager, $assessment, $notes);

		// Submit risk scoring
		submit_risk_scoring($last_insert_id, $scoring_method, $likelihood, $impact, $CVSS_AccessVector, $CVSS_AccessComplexity, $CVSS_Authentication, $CVSS_ConfImpact, $CVSS_IntegImpact, $CVSS_AvailImpact, $CVSS_Exploitability, $CVSS_RemediationLevel, $CVSS_ReportConfidence, $CVSS_CollateralDamagePotential, $CVSS_TargetDistribution, $CVSS_ConfidentialityRequirement, $CVSS_IntegrityRequirement, $CVSS_AvailabilityRequirement);

		// Audit log
		$risk_id = $last_insert_id + 1000;
		$message = "A new risk ID \"" . $risk_id . "\" was submitted by username \"" . $_SESSION['user'] . "\".";
		write_log($risk_id, $_SESSION['uid'], $message);

		// There is an alert message
		$alert = true;
		$alert_message = "Risk submitted successfully!";
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
    <script type="text/javascript">
      function popuponclick()
      {
        my_window = window.open('/management/cvss_rating.php','popupwindow','width=680,height=545,menu=0,status=0');
      }

      function closepopup()
      {
        if(false == my_window.closed)
        {
          my_window.close ();
        }
        else
        {
          alert('Window already closed!');
        }
      }

      function handleSelection(choice) {
        if (choice=="1") {
	  document.getElementById("classic").style.display = "";
          document.getElementById("cvss").style.display = "none";
	}
        if (choice=="2") {
          document.getElementById("cvss").style.display = "";
          document.getElementById("classic").style.display = "none";
	}
      }
    </script>
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
            <li class="active">
              <a href="/management/index.php">I. Submit Your Risks</a> 
            </li>
            <li>
              <a href="/management/plan_mitigations.php">II. Plan Your Mitigations</a> 
            </li>
            <li>
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
              <div class="hero-unit">
                <h4>Document a New Risk</h4>
                <p>Use this form in order to document a new risk for consideration in the Risk Management Process.</p>
                <form name="submit_risk" method="post" action="">
                Subject: <input maxlength="100" name="subject" id="subject" class="input-medium" type="text"><br />
                Site/Location: <? create_dropdown("location"); ?><br />
		Category: <? create_dropdown("category"); ?><br />
		Team: <? create_dropdown("team"); ?><br />
		Technology: <? create_dropdown("technology"); ?><br />
		Owner: <? create_dropdown("user", NULL, "owner"); ?><br />
		Owner&#39;s Manager: <? create_dropdown("user", NULL, "manager"); ?><br />
		Risk Scoring Method: <select name="scoring_method" id="select" onChange="handleSelection(value)">
		<option selected value="1">Classic</option>
		<option value="2">CVSS</option>
		</select>
		<div id="classic">
                Current Likelihood: <? create_dropdown("likelihood"); ?><br />
                Current Impact: <? create_dropdown("impact"); ?><br />
		</div>
		<div id="cvss" style="display: none;">
		<p><input type="button" name="cvssSubmit" id="cvssSubmit" value="Score Using CVSS" onclick="javascript: popuponclick();" /></p>
                <input type="hidden" name="CVSS_AccessVector" id="CVSS_AccessVector" value="1.0" />
                <input type="hidden" name="CVSS_AccessComplexity" id="CVSS_AccessComplexity" value="0.71" />
                <input type="hidden" name="CVSS_Authentication" id="CVSS_Authentication" value="0.704" />
                <input type="hidden" name="CVSS_ConfImpact" id="CVSS_ConfImpact" value="0.66" />
                <input type="hidden" name="CVSS_IntegImpact" id="CVSS_IntegImpact" value="0.66" />
                <input type="hidden" name="CVSS_AvailImpact" id="CVSS_AvailImpact" value="0.66" />
                <input type="hidden" name="CVSS_Exploitability" id="CVSS_Exploitability" value="-1" />
                <input type="hidden" name="CVSS_RemediationLevel" id="CVSS_RemediationLevel" value="-1" />
                <input type="hidden" name="CVSS_ReportConfidence" id="CVSS_ReportConfidence" value="-1" />
                <input type="hidden" name="CVSS_CollateralDamagePotential" id="CVSS_CollateralDamagePotential" value="-1" />
                <input type="hidden" name="CVSS_TargetDistribution" id="CVSS_TargetDistribution" value="-1" />
                <input type="hidden" name="CVSS_ConfidentialityRequirement" id="CVSS_ConfidentialityRequirement" value="-1" />
                <input type="hidden" name="CVSS_IntegrityRequirement" id="CVSS_IntegrityRequirement" value="-1" />
                <input type="hidden" name="CVSS_AvailabilityRequirement" id="CVSS_AvailabilityRequirement" value="-1" />
		</div>
		<label>Risk Assessment</label>
		<textarea name="assessment" cols="50" rows="3" id="assessment"></textarea>
		<label>Additional Notes</label>
		<textarea name="notes" cols="50" rows="3" id="notes"></textarea>
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
    </div>
  </body>

</html>
