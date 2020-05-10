<!DOCTYPE html>
<html>
<head>
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link href="StyleSheet.css" rel="stylesheet">
	<title>AUDIO LOAD</title>
	
<?php
	// Starts or resumes a session.
	session_start();
	
	// The session ID for this user.
	$sessionID = session_id();
	
	// The username, password, hostname and database name.
	$user = "cs431s49";
	$pass = "aPhuaQu7";
	$host = "mariadb";
	$db_name = "cs431s49";
	
	// A reference to the database.
	$db = new mysqli($host, $user, $pass, $db_name);
	
	
	// Function to retrieve the username of the current session id
	function getCurrentUsername($db, $sessionID){
		// The username of the user already logged in.
		$userName = "";
		
		// The query for selecting the actual user that is logged in and 
		// has this session.
		$query = "SELECT user_name FROM LoggedInUsersProject WHERE session_id = ?";
		$statementUsername = $db->prepare($query);
		$statementUsername->bind_param('s', $sessionID);
		
		$statementUsername->execute();
		$statementUsername->store_result();
		$statementUsername->bind_result($userName);
		$statementUsername->fetch();
		$statementUsername->free_result();
		$statementUsername->close();
		
		// If the username exists, return true.
		if($userName != "")
		{
			return $userName;
		}
		
		return "USERNAME ERROR(404)";
	}

	$userName = getCurrentUsername($db, $sessionID);

	echo '<ul>
			<li><p style="color: white; margin-right: 20px;"><i>' . $userName . '</i></p></li>
			<li style="float:left"><a href="http://ecs.fullerton.edu/~cs431s49/project/index.php">AUDIO LOAD</a></li>
		</ul>';
		
?>
	
	
	
</head>
<body>
<form action="http://ecs.fullerton.edu/~cs431s49/project/index.php" method="post" enctype="multipart/form-data">
<div class="viewContent">
	<div>
		<h2>AUDIO SUBMISSION</h2>
		
			<h3>AUDIO FILE:
			<input type="file" accept=".mp3,audio/*" name="userFile" id="userFile"/>
			<input type="hidden" name="postAction" value="submission">
			</h3>
			
			<table>
				<tr>
					<th><h3>NAME:</h3></th>
					<th><input type="text" name="audioName" id="audioName"/></th>
				</tr>
				<tr>
					<th><h3>GENRE:</h3></th>
					<th><input type="text" name="genre" id="genre"/></th>
				</tr>
				<tr>
					<th><h3>ARTIST:</h3></th>
					<th><input type="text" name="artistName" id="artistName"/></th>
				</tr>
				<tr>
					<th><h3>DATE :</h3><h3>(dd/mm/yyyy)</h3></th>
					<th><input type="text" name="date" id="date" size="10"/></th>
				</tr>
				<tr>
					<td><input type="submit" class="siteThemeButtons" name="submit" value="SUBMIT"/></td>
				</tr>
			</table>
		
	</div>
</div>
</form>
</body>
</html>