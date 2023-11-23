<!-- Test Oracle file for UBC CPSC304
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  Modified by Jason Hall (23-09-20)
  This file shows the very basics of how to execute PHP commands on Oracle.
  Specifically, it will drop a table, create a table, insert values update
  values, and then query for values
  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up All OCI commands are
  commands to the Oracle libraries. To get the file to work, you must place it
  somewhere where your Apache server can run it, and you must rename it to have
  a ".php" extension. You must also change the username and password on the
  oci_connect below to be your ORACLE username and password
-->

<?php
// The preceding tag tells the web server to parse the following text as PHP
// rather than HTML (the default)

// The following 3 lines allow PHP errors to be displayed along with the page
// content. Delete or comment out this block when it's no longer needed.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set some parameters

// Database access configuration
$config["dbuser"] = "ora_nelsonl1";			// change "cwl" to your own CWL
$config["dbpassword"] = "a32900045";		// change to 'a' + your student number
$config["dbserver"] = "dbhost.students.cs.ubc.ca:1522/stu";
$db_conn = null;	// login credentials are used in connectToDB()


// ADDED THIS : initializes db_conn
connectToDB();

$success = true;	// keep track of errors so page redirects only if there are no errors

$show_debug_alert_messages = False; // show which methods are being triggered (see debugAlertMessage())

// The next tag tells the web server to stop parsing the text as PHP. Use the
// pair of tags wherever the content switches to PHP


// // ADDED THIS : Check if buttons have been clicked
if (isset($_POST['resetTablesRequest'])) {
    // The reset button was clicked, call the handleResetRequest function
    handleResetRequest();
	handleDisplayRequest();
	echo "Reset/Initialized Tables!";
} elseif (isset($_POST['insertQueryRequest'])) {
	// The insert button was clicked, call the handleResetRequest function
	handleInsertRequest();
	echo "Inserted values into table!";
} elseif (isset($_POST['updateQueryRequest'])) {
	// The update button was clicked, call the handleUpdateRequest function
    handleUpdateRequest();
	echo "Updated query!";
} elseif (isset($_POST['countTupleRequest'])) {
	// The count button was clicked, call the handleCountRequest function
	handleCountRequest();
	echo "Counted tuples!!";
} elseif (isset($_POST['displayTuplesRequest'])) {
	// The display button was clicked, call the handleDisplayRequest function
	handleDisplayRequest();
	echo "Displaying tuples!!";
}


?>

<html>

<head>
	<title>CPSC 304 - PC Parts Database Project</title>
</head>

<body>
	<h2>Reset</h2>
	<p>To reset the tables to the original values, please click the "Reset" button below. If this is the first time you're running this page, please click "Reset" to initialize the tables</p>

	<form method="POST" action="template.php">
		<!-- "action" specifies the file or page that will receive the form data for processing. As with this example, it can be this same file. -->
		<input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
		<p><input type="submit" value="Reset" name="reset"></p>
	</form>

	<hr />

	<h2>Insert Values into DemoTable</h2>
	<form method="POST" action="template.php">
		<input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
		Number: <input type="text" name="insNo"> <br /><br />
		Name: <input type="text" name="insName"> <br /><br />

		<input type="submit" value="Insert" name="insertSubmit"></p>
	</form>

	<hr />

	<h2>Update Name in DemoTable</h2>
	<p>This will change all the names that are currently the old name to the new name in the table. The values are case sensitive and if you enter in the wrong case, the update statement will not do anything.</p>

	<form method="POST" action="template.php">
		<input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
		Old Name: <input type="text" name="oldName"> <br /><br />
		New Name: <input type="text" name="newName"> <br /><br />

		<input type="submit" value="Update" name="updateSubmit"></p>
	</form>

	<hr />

	<h2>Count the Tuples in DemoTable</h2>
	<form method="GET" action="template.php">
		<input type="hidden" id="countTupleRequest" name="countTupleRequest">
		<input type="submit" name="countTuples"></p>
	</form>

	<hr />

	<h2>Display Tuples in DemoTable</h2>
	<form method="GET" action="template.php">
		<input type="hidden" id="displayTuplesRequest" name="displayTuplesRequest">
		<input type="submit" name="displayTuples"></p>
	</form>


	<?php
	// The following code will be parsed as PHP

	function debugAlertMessage($message)
	{
		global $show_debug_alert_messages;

		if ($show_debug_alert_messages) {
			echo "<script type='text/javascript'>alert('" . $message . "');</script>";
		}
	}

	function executePlainSQL($cmdstr)
	{ //takes a plain (no bound variables) SQL command and executes it
		//echo "<br>running ".$cmdstr."<br>";
		global $db_conn, $success;

		$statement = oci_parse($db_conn, $cmdstr);
		//There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn); // For oci_parse errors pass the connection handle
			echo htmlentities($e['message']);
			$success = False;
		}

		$r = oci_execute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = oci_error($statement); // For oci_execute errors pass the statementhandle
			echo htmlentities($e['message']);
			$success = False;
		}

		return $statement;
	}

	function executeBoundSQL($cmdstr, $list)
	{
		/* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

		global $db_conn, $success;
		$statement = oci_parse($db_conn, $cmdstr);

		if (!$statement) {
			echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($db_conn);
			echo htmlentities($e['message']);
			$success = False;
		}

		foreach ($list as $tuple) {
			foreach ($tuple as $bind => $val) {
				//echo $val;
				//echo "<br>".$bind."<br>";
				oci_bind_by_name($statement, $bind, $val);
				unset($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
			}

			$r = oci_execute($statement, OCI_DEFAULT);
			if (!$r) {
				echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
				$e = OCI_Error($statement); // For oci_execute errors, pass the statementhandle
				echo htmlentities($e['message']);
				echo "<br>";
				$success = False;
			}
		}
	}

	function printResult($result)
	{ //prints results from a select statement
		echo "<br>Retrieved data from table demoTable:<br>";
		echo "<table>";
		echo "<tr><th>ID</th><th>Name</th></tr>";

		while ($row = OCI_Fetch_Array($result, OCI_ASSOC)) {
			echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]"
		}

		echo "</table>";
	}

	function connectToDB()
	{
		global $db_conn;
		global $config;

		// Your username is ora_(CWL_ID) and the password is a(student number). For example,
		// ora_platypus is the username and a12345678 is the password.
		// $db_conn = oci_connect("ora_cwl", "a12345678", "dbhost.students.cs.ubc.ca:1522/stu");
		$db_conn = oci_connect($config["dbuser"], $config["dbpassword"], $config["dbserver"]);

		if ($db_conn) {
			debugAlertMessage("Database is Connected");
			return true;
		} else {
			debugAlertMessage("Cannot connect to Database");
			$e = OCI_Error(); // For oci_connect errors pass no handle
			echo htmlentities($e['message']);
			return false;
		}
	}

	function disconnectFromDB()
	{
		global $db_conn;

		debugAlertMessage("Disconnect from Database");
		oci_close($db_conn);
	}

	function handleUpdateRequest()
	{
		global $db_conn;

		$old_name = $_POST['oldName'];
		$new_name = $_POST['newName'];

		// you need the wrap the old name and new name values with single quotations
		executePlainSQL("UPDATE demoTable SET name='" . $new_name . "' WHERE name='" . $old_name . "'");
		oci_commit($db_conn);
	}

	function handleResetRequest()
	{
		global $db_conn;
		// Drop old table
		executePlainSQL("DROP TABLE demoTable cascade constraints");

		// Create new table
		echo "<br> creating new table <br>";
		executePlainSQL("CREATE TABLE demoTable (id int PRIMARY KEY, name VARCHAR(30))");
		oci_commit($db_conn);
	}

	// function handleInsertRequest()
	// {
	// 	global $db_conn;

	// 	//Getting the values from user and insert data into the table
	// 	$tuple = array(
	// 		":bind1" => $_POST['insNo'],
	// 		":bind2" => $_POST['insName']
	// 	);

	// 	$alltuples = array(
	// 		$tuple
	// 	);

	// 	executeBoundSQL("insert into demoTable (id, name) values (:bind1, :bind2)", $alltuples);
	// 	oci_commit($db_conn);
	// }

	function insertTuple($insertStatement, $values)
	{
		global $db_conn;

		// Prepare the SQL statement
		$statement = oci_parse($db_conn, $insertStatement);

		if (!$statement) {
			$e = oci_error($db_conn);
			echo "Error preparing statement: " . htmlentities($e['message']);
			return false;
		}

		// Bind parameters
		foreach ($values as $bind => &$val) {
			oci_bind_by_name($statement, $bind, $val);
		}

		// Execute the statement
		$result = oci_execute($statement, OCI_COMMIT_ON_SUCCESS);

		if (!$result) {
			$e = oci_error($statement);
			echo "Error executing statement: " . htmlentities($e['message']);
			return false;
		}

		// Commit the transaction
		oci_commit($db_conn);

		return true;
	}

	function handleInsertRequest()
	{
		global $db_conn;

		//Getting the values from user and insert data into the table
		$values = array(
			":bind1" => $_POST['insNo'],
			":bind2" => $_POST['insName']
		);

		$insertStatement = "INSERT INTO your_table (column1, column2) VALUES (:bind1, :bind2)";
		// Call the function
		$result = insertTuple($db_conn, $insertStatement, $values);

		if ($result) {
			echo "Tuple inserted successfully!";
		} else {
			echo "Failed to insert tuple.";
		}
		oci_commit($db_conn);
	}

	function handleDisplayRequest()
	{
		global $db_conn;
		$result = executePlainSQL("SELECT * FROM demoTable");
		printResult($result);
	}

	// HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handlePOSTRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('resetTablesRequest', $_POST)) {
				handleResetRequest();
			} else if (array_key_exists('updateQueryRequest', $_POST)) {
				handleUpdateRequest();
			} else if (array_key_exists('insertQueryRequest', $_POST)) {
				handleInsertRequest();
			}

			disconnectFromDB();
		}
	}

	// HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
	function handleGETRequest()
	{
		if (connectToDB()) {
			if (array_key_exists('countTuples', $_GET)) {
				handleCountRequest();
			} elseif (array_key_exists('displayTuples', $_GET)) {
				handleDisplayRequest();
			}

			disconnectFromDB();
		}
	}

	if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
		handlePOSTRequest();
	} else if (isset($_GET['countTupleRequest']) || isset($_GET['displayTuplesRequest'])) {
		handleGETRequest();
	}

	// End PHP parsing and send the rest of the HTML content
	?>
</body>

</html>