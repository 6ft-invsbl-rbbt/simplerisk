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
            <li>
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
            <li class="active">
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
                <h4>SimpleRisk Announcements</h4>
                <p>
                  <? echo get_announcements(); ?>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </body>

</html>
