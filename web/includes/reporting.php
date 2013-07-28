<?

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// Include required configuration files
require_once('functions.php');
require_once('HighchartsPHP/Highchart.php');

/****************************
 * FUNCTION: GET OPEN RISKS *
 ****************************/
function get_open_risks()
{
        // Open the database connection
        $db = db_open();

        // Query the database
        $stmt = $db->prepare("SELECT * FROM `risks` WHERE status != \"Closed\"");
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        // Close the database connection
        db_close($db);

        return count($array);
}

/******************************
 * FUNCTION: GET CLOSED RISKS *
 ******************************/
function get_closed_risks()
{
        // Open the database connection
        $db = db_open();

        // Query the database
        $stmt = $db->prepare("SELECT * FROM `risks` WHERE status = \"Closed\"");
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        // Close the database connection
        db_close($db);

        return count($array);
}

/**********************************
 * FUNCTION: OPEN RISK STATUS PIE *
 **********************************/
function open_risk_status_pie()
{
	$chart = new Highchart();

	$chart->chart->renderTo = "open_risk_status_pie";
	$chart->chart->plotBackgroundColor = null;
	$chart->chart->plotBorderWidth = null;
	$chart->chart->plotShadow = false;
	$chart->title->text = "Status";

	$chart->tooltip->formatter = new HighchartJsExpr("function() {
    	return '<b>'+ this.point.name +'</b>: '+ this.point.y; }");

	$chart->plotOptions->pie->allowPointSelect = 1;
	$chart->plotOptions->pie->cursor = "pointer";
	$chart->plotOptions->pie->dataLabels->enabled = false;
	$chart->plotOptions->pie->showInLegend = 1;
	$chart->credits->enabled = false;

        // Open the database connection
        $db = db_open();

        // Query the database
        $stmt = $db->prepare("SELECT status, COUNT(*) AS num FROM `risks` WHERE status != \"Closed\" GROUP BY status ORDER BY COUNT(*) DESC");
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        // Close the database connection
        db_close($db);

        // Create the data array
        foreach ($array as $row)
        {
                $data[] = array($row['status'], (int)$row['num']);
        }

	$chart->series[] = array('type' => "pie",
			'name' => "Status",
			'data' => $data);

    echo "<div id=\"open_risk_status_pie\"></div>\n";
    echo "<script type=\"text/javascript\">";
    echo $chart->render("open_risk_status_pie");
    echo "</script>\n";
}

/************************************
 * FUNCTION: CLOSED RISK REASON PIE *
 ************************************/
function closed_risk_reason_pie()
{
        $chart = new Highchart();

        $chart->chart->renderTo = "closed_risk_reason_pie";
        $chart->chart->plotBackgroundColor = null;
        $chart->chart->plotBorderWidth = null;
        $chart->chart->plotShadow = false;
        $chart->title->text = "Reasons";

        $chart->tooltip->formatter = new HighchartJsExpr("function() {
        return '<b>'+ this.point.name +'</b>: '+ this.point.y; }");

        $chart->plotOptions->pie->allowPointSelect = 1;
        $chart->plotOptions->pie->cursor = "pointer";
        $chart->plotOptions->pie->dataLabels->enabled = false;
        $chart->plotOptions->pie->showInLegend = 1;
        $chart->credits->enabled = false;

        // Open the database connection
        $db = db_open();

        // Query the database
	$stmt = $db->prepare("SELECT b.name, COUNT(*) AS num FROM `closures` a INNER JOIN `close_reason` b ON a.close_reason = b.value GROUP BY b.name ORDER BY COUNT(*) DESC");
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        // Close the database connection
        db_close($db);

        // Create the data array
        foreach ($array as $row)
        {
                $data[] = array($row['name'], (int)$row['num']);
        }

        $chart->series[] = array('type' => "pie",
                        'name' => "Status",
                        'data' => $data);

    echo "<div id=\"closed_risk_reason_pie\"></div>\n";
    echo "<script type=\"text/javascript\">";
    echo $chart->render("closed_risk_reason_pie");
    echo "</script>\n";
}

/************************************
 * FUNCTION: OPEN RISK LOCATION PIE *
 ************************************/
function open_risk_location_pie()
{
        $chart = new Highchart();

        $chart->chart->renderTo = "open_risk_location_pie";
        $chart->chart->plotBackgroundColor = null;
        $chart->chart->plotBorderWidth = null;
        $chart->chart->plotShadow = false;
        $chart->title->text = "Sites/Locations";

        $chart->tooltip->formatter = new HighchartJsExpr("function() {
        return '<b>'+ this.point.name +'</b>: '+ this.point.y; }");

        $chart->plotOptions->pie->allowPointSelect = 1;
        $chart->plotOptions->pie->cursor = "pointer";
        $chart->plotOptions->pie->dataLabels->enabled = false;
        $chart->plotOptions->pie->showInLegend = 1;
        $chart->credits->enabled = false;

        // Open the database connection
        $db = db_open();

        // Query the database
        $stmt = $db->prepare("SELECT b.name, COUNT(*) AS num FROM `risks` a INNER JOIN `location` b ON a.location = b.value GROUP BY b.name ORDER BY COUNT(*) DESC");
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        // Close the database connection
        db_close($db);

        // Create the data array
        foreach ($array as $row)
        {
                $data[] = array($row['name'], (int)$row['num']);
        }

        $chart->series[] = array('type' => "pie",
                        'name' => "Status",
                        'data' => $data);

    echo "<div id=\"open_risk_location_pie\"></div>\n";
    echo "<script type=\"text/javascript\">";
    echo $chart->render("open_risk_location_pie");
    echo "</script>\n";
}

/************************************
 * FUNCTION: OPEN RISK CATEGORY PIE *
 ************************************/
function open_risk_category_pie()
{
        $chart = new Highchart();

        $chart->chart->renderTo = "open_risk_category_pie";
        $chart->chart->plotBackgroundColor = null;
        $chart->chart->plotBorderWidth = null;
        $chart->chart->plotShadow = false;
        $chart->title->text = "Categories";

        $chart->tooltip->formatter = new HighchartJsExpr("function() {
        return '<b>'+ this.point.name +'</b>: '+ this.point.y; }");

        $chart->plotOptions->pie->allowPointSelect = 1;
        $chart->plotOptions->pie->cursor = "pointer";
        $chart->plotOptions->pie->dataLabels->enabled = false;
        $chart->plotOptions->pie->showInLegend = 1;
        $chart->credits->enabled = false;

        // Open the database connection
        $db = db_open();

        // Query the database
        $stmt = $db->prepare("SELECT b.name, COUNT(*) AS num FROM `risks` a INNER JOIN `category` b ON a.category = b.value GROUP BY b.name ORDER BY COUNT(*) DESC");
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        // Close the database connection
        db_close($db);

        // Create the data array
        foreach ($array as $row)
        {
                $data[] = array($row['name'], (int)$row['num']);
        }

        $chart->series[] = array('type' => "pie",
                        'name' => "Status",
                        'data' => $data);

    echo "<div id=\"open_risk_category_pie\"></div>\n";
    echo "<script type=\"text/javascript\">";
    echo $chart->render("open_risk_category_pie");
    echo "</script>\n";
}

/********************************
 * FUNCTION: OPEN RISK TEAM PIE *
 ********************************/
function open_risk_team_pie()
{
        $chart = new Highchart();

        $chart->chart->renderTo = "open_risk_team_pie";
        $chart->chart->plotBackgroundColor = null;
        $chart->chart->plotBorderWidth = null;
        $chart->chart->plotShadow = false;
        $chart->title->text = "Teams";

        $chart->tooltip->formatter = new HighchartJsExpr("function() {
        return '<b>'+ this.point.name +'</b>: '+ this.point.y; }");

        $chart->plotOptions->pie->allowPointSelect = 1;
        $chart->plotOptions->pie->cursor = "pointer";
        $chart->plotOptions->pie->dataLabels->enabled = false;
        $chart->plotOptions->pie->showInLegend = 1;
        $chart->credits->enabled = false;

        // Open the database connection
        $db = db_open();

        // Query the database
        $stmt = $db->prepare("SELECT b.name, COUNT(*) AS num FROM `risks` a INNER JOIN `team` b ON a.team = b.value GROUP BY b.name ORDER BY COUNT(*) DESC");
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        // Close the database connection
        db_close($db);

        // Create the data array
        foreach ($array as $row)
        {
                $data[] = array($row['name'], (int)$row['num']);
        }

        $chart->series[] = array('type' => "pie",
                        'name' => "Status",
                        'data' => $data);

    echo "<div id=\"open_risk_team_pie\"></div>\n";
    echo "<script type=\"text/javascript\">";
    echo $chart->render("open_risk_team_pie");
    echo "</script>\n";
}

/**************************************
 * FUNCTION: OPEN RISK TECHNOLOGY PIE *
 **************************************/
function open_risk_technology_pie()
{
        $chart = new Highchart();

        $chart->chart->renderTo = "open_risk_technology_pie";
        $chart->chart->plotBackgroundColor = null;
        $chart->chart->plotBorderWidth = null;
        $chart->chart->plotShadow = false;
        $chart->title->text = "Technologies";

        $chart->tooltip->formatter = new HighchartJsExpr("function() {
        return '<b>'+ this.point.name +'</b>: '+ this.point.y; }");

        $chart->plotOptions->pie->allowPointSelect = 1;
        $chart->plotOptions->pie->cursor = "pointer";
        $chart->plotOptions->pie->dataLabels->enabled = false;
        $chart->plotOptions->pie->showInLegend = 1;
        $chart->credits->enabled = false;

        // Open the database connection
        $db = db_open();

        // Query the database
        $stmt = $db->prepare("SELECT b.name, COUNT(*) AS num FROM `risks` a INNER JOIN `technology` b ON a.technology = b.value GROUP BY b.name ORDER BY COUNT(*) DESC");
        $stmt->execute();

        // Store the list in the array
        $array = $stmt->fetchAll();

        // Close the database connection
        db_close($db);

        // Create the data array
        foreach ($array as $row)
        {
                $data[] = array($row['name'], (int)$row['num']);
        }

        $chart->series[] = array('type' => "pie",
                        'name' => "Status",
                        'data' => $data);

    echo "<div id=\"open_risk_technology_pie\"></div>\n";
    echo "<script type=\"text/javascript\">";
    echo $chart->render("open_risk_technology_pie");
    echo "</script>\n";
}

?>
