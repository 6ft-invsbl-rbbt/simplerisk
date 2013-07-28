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

        // Check if a risk ID was sent
        if (isset($_GET['id']))
        {
                $id = htmlentities($_GET['id']);

                // Get the details of the risk
                $risk = get_risk_by_id($id);

                $status = htmlentities($risk[0]['status']);
                $subject = htmlentities($risk[0]['subject']);
		$location = htmlentities($risk[0]['location']);
                $category = htmlentities($risk[0]['category']);
                $team = htmlentities($risk[0]['team']);
                $technology = htmlentities($risk[0]['technology']);
                $owner = htmlentities($risk[0]['owner']);
                $manager = htmlentities($risk[0]['manager']);
                $assessment = htmlentities($risk[0]['assessment']);
                $notes = htmlentities($risk[0]['notes']);
		$submission_date = htmlentities($risk[0]['submission_date']);
		$mitigation_id = htmlentities($risk[0]['mitigation_id']);
		$mgmt_review = htmlentities($risk[0]['mgmt_review']);
		$calculated_risk = $risk[0]['calculated_risk'];

		$scoring_method = $risk[0]['scoring_method'];
		$CLASSIC_likelihood = $risk[0]['CLASSIC_likelihood'];
		$CLASSIC_impact = $risk[0]['CLASSIC_impact'];
		$CVSS_AccessVector = $risk[0]['CVSS_AccessVector'];
		$CVSS_AccessComplexity = $risk[0]['CVSS_AccessComplexity'];
		$CVSS_Authentication = $risk[0]['CVSS_Authentication'];
		$CVSS_ConfImpact = $risk[0]['CVSS_ConfImpact'];
		$CVSS_IntegImpact = $risk[0]['CVSS_IntegImpact'];
		$CVSS_AvailImpact = $risk[0]['CVSS_AvailImpact'];
		$CVSS_Exploitability = $risk[0]['CVSS_Exploitability'];
		$CVSS_RemediationLevel = $risk[0]['CVSS_RemediationLevel'];
		$CVSS_ReportConfidence = $risk[0]['CVSS_ReportConfidence'];
		$CVSS_CollateralDamagePotential = $risk[0]['CVSS_CollateralDamagePotential'];
		$CVSS_TargetDistribution = $risk[0]['CVSS_TargetDistribution'];
		$CVSS_ConfidentialityRequirement = $risk[0]['CVSS_ConfidentialityRequirement'];
		$CVSS_IntegrityRequirement = $risk[0]['CVSS_IntegrityRequirement'];
		$CVSS_AvailabilityRequirement = $risk[0]['CVSS_AvailabilityRequirement'];

                if ($submission_date == "")
                {
                        $submission_date = "N/A";
                }
                else $submission_date = date('Y-m-d g:i A T', strtotime($submission_date));

		// Get the mitigation for the risk
		$mitigation = get_mitigation_by_id($id);

		$mitigation_date = htmlentities($mitigation[0]['submission_date']);
		$planning_strategy = htmlentities($mitigation[0]['planning_strategy']);
		$mitigation_effort = htmlentities($mitigation[0]['mitigation_effort']);
		$current_solution = htmlentities($mitigation[0]['current_solution']);
		$security_requirements = htmlentities($mitigation[0]['security_requirements']);
		$security_recommendations = htmlentities($mitigation[0]['security_recommendations']);

		if ($mitigation_date == "")
		{
			$mitigation_date = "N/A";
		}
		else $mitigation_date = date('Y-m-d g:i A T', strtotime($mitigation_date));

		// Get the management reviews for the risk
		$mgmt_reviews = get_review_by_id($id);

		$review_date = htmlentities($mgmt_reviews[0]['submission_date']);
		$review = htmlentities($mgmt_reviews[0]['review']);
		$next_step = htmlentities($mgmt_reviews[0]['next_step']);
		$reviewer = htmlentities($mgmt_reviews[0]['reviewer']);
		$comments = htmlentities($mgmt_reviews[0]['comments']);

                if ($review_date == "")
                {
                        $review_date = "N/A";
                }
                else $review_date = date('Y-m-d g:i A T', strtotime($review_date));
        }

	// If the risk details were updated
        if (isset($_POST['update_details']))
        {
		// If the user has permission to modify risks
		if ($_SESSION["modify_risks"] == 1)
		{
                	$subject = addslashes($_POST['subject']);
			$location = (int)$_POST['location'];
                	$CLASSIC_likelihood = (int)$_POST['likelihood'];
                	$CLASSIC_impact =(int) $_POST['impact'];
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
                	$category = (int)$_POST['category'];
                	$team = (int)$_POST['team'];
                	$technology = (int)$_POST['technology'];
                	$owner = (int)$_POST['owner'];
                	$manager = (int)$_POST['manager'];
                	$assessment = addslashes($_POST['assessment']);
                	$notes = addslashes($_POST['notes']);

			// Update risk
			update_risk($id, $subject, $location, $category, $team, $technology, $owner, $manager, $assessment, $notes);

			// Update the risk score
			$calculated_risk = update_risk_scoring($id, $scoring_method, $CLASSIC_likelihood, $CLASSIC_impact, $CVSS_AccessVector, $CVSS_AccessComplexity, $CVSS_Authentication, $CVSS_ConfImpact, $CVSS_IntegImpact, $CVSS_AvailImpact, $CVSS_Exploitability, $CVSS_RemediationLevel, $CVSS_ReportConfidence, $CVSS_CollateralDamagePotential, $CVSS_TargetDistribution, $CVSS_ConfidentialityRequirement, $CVSS_IntegrityRequirement, $CVSS_AvailabilityRequirement);

                	// Audit log
                	$risk_id = $id;
                	$message = "Risk details were updated for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                	write_log($risk_id, $_SESSION['uid'], $message);

			$alert = true;
			$alert_message = "The risk has been successfully modified.";
		}
		// Otherwise, the user did not have permission to modify risks
		else
		{
			$alert = true;
                	$alert_message = "You do not have permission to modify risks.  Your attempt to modify the details of this risk was not recorded.  Please contact an Administrator if you feel that you have reached this message in error.";
		}
        }

	// If the user has selected to edit the risk details and does not have permission
	if ((isset($_POST['edit_details'])) && ($_SESSION['modify_risks'] != 1))
	{
        	$alert = true;
                $alert_message = "You do not have permission to modify risks.  Any risks that you attempt to modify will not be recorded.  Please contact an Administrator if you feel that you have reached this message in error.";
	}

	// Check if a mitigation was updated
	if (isset($_POST['update_mitigation']))
	{
                $planning_strategy = (int)$_POST['planning_strategy'];
		$mitigation_effort = (int)$_POST['mitigation_effort'];
                $current_solution = addslashes($_POST['current_solution']);
                $security_requirements = addslashes($_POST['security_requirements']);
                $security_recommendations = addslashes($_POST['security_recommendations']);

		// If we don't yet have a mitigation
		if ($mitigation_id == 0)
		{
	                $status = "Mitigation Planned";

                	// Submit mitigation
                	submit_mitigation($id, $status, $planning_strategy, $mitigation_effort, $current_solution, $security_requirements, $security_recommendations);
		}
		else
		{
			// Update mitigation
			update_mitigation($id, $planning_strategy, $mitigation_effort, $current_solution, $security_requirements, $security_recommendations);
		}

                // Audit log
                $risk_id = $id;
                $message = "Risk mitigation details were updated for risk ID \"" . $risk_id . "\" by username \"" . $_SESSION['user'] . "\".";
                write_log($risk_id, $_SESSION['uid'], $message);

		$alert = true;
		$alert_message = "The risk mitigation has been successfully modified.";
	}

	// If comment is passed via GET
	if (isset($_GET['comment']))
	{
		// If it's true
		if ($_GET['comment'] == true)
		{
			$alert = true;
			$alert_message = "Your comment has been successfully added to the risk.";
		}
	}

        // If closed is passed via GET
        if (isset($_GET['closed']))
        {       
                // If it's true
                if ($_GET['closed'] == true)
                {
                        $alert = true;
                        $alert_message = "Your risk has now been marked as closed.";
                }
        }

        // If reopened is passed via GET
        if (isset($_GET['reopened']))
        {       
                // If it's true
                if ($_GET['reopened'] == true)
                {       
                        $alert = true; 
                        $alert_message = "Your risk has now been reopened.";      
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
    <link rel="stylesheet" href="/css/divshot-util.css">
    <link rel="stylesheet" href="/css/divshot-canvas.css">
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
            <li>
              <a href="/management/management_review.php">III. Perform Management Reviews</a> 
            </li>
            <li>
              <a href="/management/prioritize_planning.php">IV. Prioritize for Project Planning</a> 
            </li>
            <li class="active">
              <a href="/management/review_risks.php">V. Review Risks Regularly</a>
            </li>
          </ul>
        </div>
        <div class="span9">
          <div class="row-fluid">
            <div class="well">
              <div class="btn-group pull-right">
                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">Action<span class="caret"></span></a>
                <ul class="dropdown-menu">
             	<? 
			// If the risk is closed, offer to reopen
                    	if ($status == "Closed")
                    	{
                      		echo "<li><a href=\"/management/reopen.php?id=".$id."\">Reopen Risk</a></li>\n";
			}
			// Otherwise, offer to close
			else
			{
				echo "<li><a href=\"/management/close.php?id=".$id."\">Close Risk</a></li>\n";
			}

			// If the risk is unmitigated, offer mitigation option
			if ($mitigation_id == 0)
			{
				echo "<li><a href=\"/management/mitigate.php?id=".$id."\">Plan a Mitigation</a></li>\n";
			}
		?>
                  <li><a href="/management/mgmt_review.php?id=<? echo $id; ?>">Perform a Review</a></li>
                  <li><a href="/management/comment.php?id=<? echo $id; ?>">Add a Comment</a></li>
                </ul>
              </div>
              <h4>Risk ID: <? echo $id ?></h4>
              <h4>Calculated Risk: <? echo $calculated_risk . " (". get_risk_level_name($calculated_risk) . ")"; ?></h4>
              <h4>Status: <? echo $status ?></h4>
            </div>
          </div>
          <div class="row-fluid">
            <form name="submit_risk" method="post" action="">
            <div class="span4">
              <div class="well">
                <h4>Details</h4>
<?
// If the user has selected to edit the risk
if (isset($_POST['edit_details']))
{
        echo "Submission Date: \n";
        echo "<input type=\"text\" name=\"submission_date\" id=\"submission_date\" size=\"50\" value=\"" . htmlentities($submission_date) . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
        echo "Subject: <input type=\"text\" name=\"subject\" id=\"subject\" size=\"50\" value=\"" . htmlentities($subject) . "\" />\n";
	echo "<br />\n";
	echo "Site/Location: \n";
	create_dropdown("location", $location);
	echo "<br />\n";
	echo "Category: \n";
        create_dropdown("category", $category);
	echo "<br />\n";
	echo "Team: \n";
        create_dropdown("team", $team);
	echo "<br />\n";
	echo "Technology: \n";
        create_dropdown("technology", $technology);
	echo "<br />\n";
	echo "Owner: \n";
        create_dropdown("user", $owner, "owner");
	echo "<br />\n";
	echo "Owner&#39;s Manager: \n";
        create_dropdown("user", $manager, "manager");
	echo "<br />\n";
        // If this is CLASSIC risk scoring
        if ($scoring_method == 1)
        {
        	echo "Current Likelihood: \n";
        	create_dropdown("likelihood", $CLASSIC_likelihood, NULL, false);
        	echo "<br />\n";
        	echo "Current Impact: \n";
        	create_dropdown("impact", $CLASSIC_impact, NULL, false);
        	echo "<br />\n";
	}
        // If this is CVSS risk scoring
        else if ($scoring_method == "2")
        {
                echo "Attack Vector: \n";
		create_dropdown("CVSS_AccessVector", $CVSS_AccessVector, NULL, false);
                echo "<br />\n";
                echo "Attack Complexity: \n";
		create_dropdown("CVSS_AccessComplexity", $CVSS_AccessComplexity, NULL, false);
                echo "<br />\n";
                echo "Authentication: \n";
		create_dropdown("CVSS_Authentication", $CVSS_Authentication, NULL, false);
                echo "<br />\n";
                echo "Confidentiality Impact: \n";
		create_dropdown("CVSS_ConfImpact", $CVSS_ConfImpact, NULL, false);
                echo "<br />\n";
                echo "Integrity Impact: \n";
		create_dropdown("CVSS_IntegImpact", $CVSS_IntegImpact, NULL, false);
                echo "<br />\n";
                echo "Availability Impact: \n";
		create_dropdown("CVSS_AvailImpact", $CVSS_AvailImpact, NULL, false);
                echo "<br />\n";
                echo "Exploitability: \n";
		create_dropdown("CVSS_Exploitability", $CVSS_Exploitability, NULL, false);
                echo "<br />\n";
                echo "Remediation Level: \n";
		create_dropdown("CVSS_RemediationLevel", $CVSS_RemediationLevel, NULL, false);
                echo "<br />\n";
                echo "Report Confidence: \n";
		create_dropdown("CVSS_ReportConfidence", $CVSS_ReportConfidence, NULL, false);
                echo "<br />\n";
                echo "Collateral Damage Potential: \n";
		create_dropdown("CVSS_CollateralDamagePotential", $CVSS_CollateralDamagePotential, NULL, false);
                echo "<br />\n";
                echo "Target Distribution: \n";
		create_dropdown("CVSS_TargetDistribution", $CVSS_TargetDistribution, NULL, false);
                echo "<br />\n";
                echo "Confidentiality Requirement: \n";
		create_dropdown("CVSS_ConfidentialityRequirement", $CVSS_ConfidentialityRequirement, NULL, false);
                echo "<br />\n";
                echo "Integrity Requirement: \n";
		create_dropdown("CVSS_IntegrityRequirement", $CVSS_IntegrityRequirement, NULL, false);
                echo "<br />\n";
                echo "Availability Requirement: \n";
		create_dropdown("CVSS_AvailabilityRequirement", $CVSS_AvailabilityRequirement, NULL, false);
                echo "<br />\n";
        }

        echo "<label>Risk Assessment</label>\n";
        echo "<textarea name=\"assessment\" cols=\"50\" rows=\"3\" id=\"assessment\">" . htmlentities(stripslashes($assessment)) . "</textarea>\n";
        echo "<label>Additional Notes</label>\n";
        echo "<textarea name=\"notes\" cols=\"50\" rows=\"3\" id=\"notes\">" . htmlentities(stripslashes($notes)) . "</textarea>\n";
        echo "<div class=\"form-actions\">\n";
        echo "<button type=\"submit\" name=\"update_details\" class=\"btn btn-primary\">Update</button>\n";
        echo "</div>\n";
}
// Otherwise we are just viewing the risk
else
{
        echo "Submission Date: \n";
        echo "<input type=\"text\" name=\"submission_date\" id=\"submission_date\" size=\"50\" value=\"" . $submission_date . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
        echo "Subject: \n";
        echo "<input type=\"text\" name=\"subject\" id=\"subject\" size=\"50\" value=\"" . htmlentities($subject) . "\" disabled=\"disabled\" />\n";
	echo "<br />\n";
        echo "Site/Location: \n";
        echo "<input type=\"text\" name=\"location\" id=\"location\" size=\"50\" value=\"" . get_name_by_value("location", $location) . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
	echo "Category: \n";
        echo "<input type=\"text\" name=\"category\" id=\"category\" size=\"50\" value=\"" . get_name_by_value("category", $category) . "\" disabled=\"disabled\" />\n";
	echo "<br />\n";
	echo "Team: \n";
        echo "<input type=\"text\" name=\"team\" id=\"team\" size=\"50\" value=\"" . get_name_by_value("team", $team) . "\" disabled=\"disabled\" />\n";
	echo "<br />\n";
	echo "Technology: \n";
        echo "<input type=\"text\" name=\"technology\" id=\"technology\" size=\"50\" value=\"" . get_name_by_value("technology", $technology) . "\" disabled=\"disabled\" />\n";
	echo "<br />\n";
	echo "Owner: \n";
        echo "<input type=\"text\" name=\"owner\" id=\"owner\" size=\"50\" value=\"" . get_name_by_value("user", $owner) . "\" disabled=\"disabled\" />\n";
	echo "<br />\n";
	echo "Owner&#39;s Manager: \n";
        echo "<input type=\"text\" name=\"manager\" id=\"manager\" size=\"50\" value=\"" . get_name_by_value("user", $manager) . "\" disabled=\"disabled\" />\n";
	echo "<br />\n";

	// If this is CLASSIC risk scoring
	if ($scoring_method == 1)
	{
        	echo "Current Likelihood: \n";
        	echo "<input type=\"text\" name=\"likelihood\" id=\"likelihood\" size=\"50\" value=\"" . get_name_by_value("likelihood", $CLASSIC_likelihood) . "\" disabled=\"disabled\" />\n";
        	echo "<br />\n";
        	echo "Current Impact: \n";
        	echo "<input type=\"text\" name=\"impact\" id=\"impact\" size=\"50\" value=\"" . get_name_by_value("impact", $CLASSIC_impact) . "\" disabled=\"disabled\" />\n";
        	echo "<br />\n";
	}
	// If this is CVSS risk scoring
	else if ($scoring_method == "2")
	{
		echo "Attack Vector: \n";
		echo "<input type=\"text\" name=\"AccessVectorVar\" id=\"AccessVectorVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_AccessVector", $CVSS_AccessVector) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Attack Complexity: \n";
                echo "<input type=\"text\" name=\"AccessComplexityVar\" id=\"AccessComplexityVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_AccessComplexity", $CVSS_AccessComplexity) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Authentication: \n";
                echo "<input type=\"text\" name=\"AuthenticationVar\" id=\"AuthenticationVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_Authentication", $CVSS_Authentication) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Confidentiality Impact: \n";
                echo "<input type=\"text\" name=\"ConfImpactVar\" id=\"ConfImpactVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_ConfImpact", $CVSS_ConfImpact) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Integrity Impact: \n";
                echo "<input type=\"text\" name=\"IntegImpactVar\" id=\"IntegImpactVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_IntegImpact", $CVSS_IntegImpact) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Availability Impact: \n";
                echo "<input type=\"text\" name=\"AvailImpactVar\" id=\"AvailImpactVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_AvailImpact", $CVSS_AvailImpact) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Exploitability: \n";
                echo "<input type=\"text\" name=\"ExploitabilityVar\" id=\"ExploitabilityVar\" size=\"50\" value=\"" . get_name_by_value("CVSS_Exploitability", $CVSS_Exploitability) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Remediation Level: \n";
                echo "<input type=\"text\" name=\"RemediationLevelVar\" id=\"RemediationLevelVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_RemediationLevel", $CVSS_RemediationLevel) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Report Confidence: \n";
                echo "<input type=\"text\" name=\"ReportConfidenceVar\" id=\"ReportConfidenceVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_ReportConfidence", $CVSS_ReportConfidence) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Collateral Damage Potential: \n";
                echo "<input type=\"text\" name=\"CollateralDamagePotentialVar\" id=\"CollateralDamagePotentialVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_CollateralDamagePotential", $CVSS_CollateralDamagePotential) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Target Distribution: \n";
                echo "<input type=\"text\" name=\"TargetDistributionVar\" id=\"TargetDistributionVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_TargetDistribution", $CVSS_TargetDistribution) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Confidentiality Requirement: \n";
                echo "<input type=\"text\" name=\"ConfidentialityRequirementVar\" id=\"ConfidentialityRequirementVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_ConfidentialityRequirement", $CVSS_ConfidentialityRequirement) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Integrity Requirement: \n";
                echo "<input type=\"text\" name=\"IntegrityRequirementVar\" id=\"IntegrityRequirementVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_IntegrityRequirement", $CVSS_IntegrityRequirement) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
                echo "Availability Requirement: \n";
                echo "<input type=\"text\" name=\"AvailabilityRequirementVar\" id=\"AvailabilityRequirementVar\" size=\"50\" value=\"" . get_name_by_float("CVSS_AvailabilityRequirement", $CVSS_AvailabilityRequirement) . "\" disabled=\"disabled\" />\n";
                echo "<br />\n";
	}

        echo "<label>Risk Assessment</label>\n";
        echo "<textarea name=\"assessment\" cols=\"50\" rows=\"3\" id=\"assessment\" disabled=\"disabled\">" . htmlentities(stripslashes($assessment)) . "</textarea>\n";
        echo "<label>Additional Notes</label>\n";
        echo "<textarea name=\"notes\" cols=\"50\" rows=\"3\" id=\"notes\" disabled=\"disabled\">" . htmlentities(stripslashes($notes)) . "</textarea>\n";
        echo "<div class=\"form-actions\">\n";
	echo "<button type=\"submit\" name=\"edit_details\" class=\"btn btn-primary\">Edit Details</button>\n";
        echo "</div>\n";
}
?>
              </div>
            </div>
            <div class="span4">
              <div class="well">
                <h4>Mitigation</h4>
<?
// If the user has selected to edit the mitigation
if (isset($_POST['edit_mitigation']))
{ 
        echo "Mitigation Date: \n";
        echo "<input type=\"text\" name=\"mitigation_date\" id=\"mitigation_date\" size=\"50\" value=\"" . $mitigation_date . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
        echo "Planning Strategy: \n";
	create_dropdown("planning_strategy", $planning_strategy);
	echo "<br />\n";
        echo "Mitigation Effort: \n";
        create_dropdown("mitigation_effort", $mitigation_effort);
        echo "<br />\n";
        echo "<label>Current Solution</label>\n";
        echo "<textarea name=\"current_solution\" cols=\"50\" rows=\"3\" id=\"current_solution\">" . htmlentities(stripslashes($current_solution)) . "</textarea>\n";
        echo "<label>Security Requirements</label>\n";
        echo "<textarea name=\"security_requirements\" cols=\"50\" rows=\"3\" id=\"security_requirements\">" . htmlentities(stripslashes($security_requirements)) . "</textarea>\n";
        echo "<label>Security Recommendations</label>\n";
        echo "<textarea name=\"security_recommendations\" cols=\"50\" rows=\"3\" id=\"security_recommendations\">" . htmlentities(stripslashes($security_recommendations)) . "</textarea>\n";
        echo "<div class=\"form-actions\">\n";
        echo "<button type=\"submit\" name=\"update_mitigation\" class=\"btn btn-primary\">Update</button>\n";
        echo "</div>\n";
}
// Otherwise we are just viewing the mitigation
else
{
        echo "Mitigation Date: \n";
        echo "<input type=\"text\" name=\"mitigation_date\" id=\"mitigation_date\" size=\"50\" value=\"" . $mitigation_date . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
        echo "Planning Strategy: \n";
        echo "<input type=\"text\" name=\"planning_strategy\" id=\"planning_strategy\" size=\"50\" value=\"" . get_name_by_value("planning_strategy", $planning_strategy) . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
        echo "Mitigation Effort: \n";
        echo "<input type=\"text\" name=\"mitigation_effort\" id=\"mitigation_effort\" size=\"50\" value=\"" . get_name_by_value("mitigation_effort", $mitigation_effort) . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
	echo "<label>Current Solution</label>\n";
        echo "<textarea name=\"current_solution\" cols=\"50\" rows=\"3\" id=\"current_solution\" disabled=\"disabled\">" . htmlentities(stripslashes($current_solution)) . "</textarea>\n";
	echo "<label>Security Requirements</label>\n";
        echo "<textarea name=\"security_requirements\" cols=\"50\" rows=\"3\" id=\"security_requirements\" disabled=\"disabled\">" . htmlentities(stripslashes($security_requirements)) . "</textarea>\n";
	echo "<label>Security Recommendations</label>\n";
        echo "<textarea name=\"security_recommendations\" cols=\"50\" rows=\"3\" id=\"security_recommendations\" disabled=\"disabled\">" . htmlentities(stripslashes($security_recommendations)) . "</textarea>\n";
        echo "<div class=\"form-actions\">\n";
        echo "<button type=\"submit\" name=\"edit_mitigation\" class=\"btn btn-primary\">Edit Mitigation</button>\n";
        echo "</div>\n";
}
?>
              </div>
            </div>
            <div class="span4">
              <div class="well">
                <h4>Last Review</h4>
<?
        echo "Review Date: \n";
        echo "<input type=\"text\" name=\"review_date\" id=\"review_date\" size=\"50\" value=\"" . $review_date . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
        echo "Reviewer: \n";
        echo "<input type=\"text\" name=\"reviewer\" id=\"reviewer\" size=\"50\" value=\"" . get_name_by_value("user", $reviewer) . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
        echo "Review: \n";
        echo "<input type=\"text\" name=\"review\" id=\"review\" size=\"50\" value=\"" . get_name_by_value("review", $review) . "\" disabled=\"disabled\" />\n";
	echo "<br />\n";
        echo "Next Step: \n";
	echo "<input type=\"text\" name=\"next_step\" id=\"next_step\" size=\"50\" value=\"" . get_name_by_value("next_step", $next_step) . "\" disabled=\"disabled\" />\n";
        echo "<br />\n";
        echo "<label>Comments</label>\n";
        echo "<textarea name=\"comments\" cols=\"50\" rows=\"3\" id=\"comments\" disabled=\"disabled\">" . $comments . "</textarea>\n";
	echo "<p><a href=\"/management/reviews.php?id=".$id."\">View All Reviews</a></p>";
?>
              </div>
            </div>
            </form>
          </div>
          <div class="row-fluid">
            <div class="well">
              <h4>Comments</h4>
              <? get_comments($id); ?>
            </div>
          </div>
          <div class="row-fluid">
            <div class="well">
              <h4>Audit Trail</h4>
              <? get_audit_trail($id); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

</html>
