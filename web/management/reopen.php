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

		// Reopen the risk
		reopen_risk($id);

                // Audit log
                $risk_id = $id;
                $message = "Risk ID \"" . $risk_id . "\" was reopened by username \"" . $_SESSION['user'] . "\".";
                write_log($risk_id, $_SESSION['uid'], $message);

		// Redirect to view the risk
		header('Location: /management/view.php?id='.$id.'&reopened=true');
        }
	else header('Location: /reports/closed.php');
?>
