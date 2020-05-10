<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <link href="StyleSheet.css" rel="stylesheet">
 <link rel="stylesheet" href="mediaelementplayer.css">
	<title>AUDIO LOAD</title>
	
<?php	
	// Functions:
	
	// Distinguish between a get(sort), or a post that results in a 
	// registration, login, logout or submission.
	function whichAction() 
	{
		$result = isset($_SERVER['REQUEST_METHOD']) && 
			($_SERVER['REQUEST_METHOD'] == 'POST') ? 'post' : 'sort';
		
		// Check if it is a post method.
		if($result == 'post') 
		{
			// Check the post type.
			if(isset($_POST['postAction']))
			{
				$result = $_POST['postAction'];
			}
		}
		
		return $result;
	}
	
	// Checks if the user with the same session ID is already
	// logged in.
	function checkIfLoggedIn($db, $sessionID)
	{
		// The username of the user already logged in.
		$userName = "";
		
		// The query for selecting the actual user that is logged in and 
		// has this session.
		$query = "SELECT user_name FROM LoggedInUsersProject WHERE session_id = ?";
		$statementUserExistsGetPassword = $db->prepare($query);
		$statementUserExistsGetPassword->bind_param('s', $sessionID);
		
		$statementUserExistsGetPassword->execute();
		$statementUserExistsGetPassword->store_result();
		$statementUserExistsGetPassword->bind_result($userName);
		$statementUserExistsGetPassword->fetch();
		$statementUserExistsGetPassword->free_result();
		$statementUserExistsGetPassword->close();
		
		// If the username exists, return true.
		if($userName != "")
		{
			return true;
		}
		
		return false;
	}
	
	// Registers a new user into the registered users project 
	// table in the database.
	function registerUser($db)
	{
		// The user name of the new user.
		$userName = $_POST['userName'];
		
		// The password for the new user.
		$password = $_POST['password'];
		
		// The number of users registered with the user name. 
		// Should only be 0 or 1.
		$userCount = 0;
		
		// Check if the user name has already been taken.
		
		// The query for checking if the user name already exists.
		$query = "SELECT COUNT(*) FROM RegisteredUsersProject WHERE user_name = ?";
		$statementUserExistsGetPassword = $db->prepare($query);
		$statementUserExistsGetPassword->bind_param('s', $userName);
		$statementUserExistsGetPassword->execute();
		$statementUserExistsGetPassword->store_result();
		$statementUserExistsGetPassword->bind_result($userCount);
		$statementUserExistsGetPassword->fetch();
		$statementUserExistsGetPassword->free_result();
		$statementUserExistsGetPassword->close();
		
		// If the user count is not zero, then the user 
		// already exists and cannot be registered.
		// Otherwise register the user.
		if($userCount)
		{
			echo '<script type="text/javascript">alert("That user name has already been taken!");</script>';
		}
		else
		{
			// Add the new user to the database.
			
			//The query for inserting the registered user into the database.
			$insertquery = "INSERT INTO RegisteredUsersProject VALUES (?, ?)";
			$statement = $db->prepare($insertquery);
			$statement->bind_param('ss', $userName, $password);
			$statement->execute();
			$statement->close();
			
			echo '<script type="text/javascript">if(!alert("Registered! Please log in.")){window.location.href=\'http://ecs.fullerton.edu/~cs431s49/project\';}</script>';
		}
	}
	
	// Logs a user into the website, and places their logIn
	// information into the users logged in project table
	// in the database.
	function logIn($db, $sessionID)
	{
		// The user name of the new user.
		$userName = strip_tags($_POST['userName']);
		
		// The password for the new user.
		$password = strip_tags($_POST['password']);
		
		// The actual password of the user in the database.
		$actualPassword = "";
		
		// Add the new user to the database.
		
		// The query for selecting the actual password for the user.
		$query = "SELECT password FROM RegisteredUsersProject WHERE user_name = ?";
		$statementUserExistsGetPassword = $db->prepare($query);
		$statementUserExistsGetPassword->bind_param('s', $userName);
		$statementUserExistsGetPassword->execute();
		$statementUserExistsGetPassword->store_result();
		$statementUserExistsGetPassword->bind_result($actualPassword);
		$statementUserExistsGetPassword->fetch();
		$statementUserExistsGetPassword->free_result();
		$statementUserExistsGetPassword->close();
		
		if($actualPassword == "")
		{
			echo '<script type="text/javascript">alert("This user does not exist!");</script>';
		}
		else
		{
			// Check if the passwords match. If so, log in as the user.
			if($password == $actualPassword)
			{
				// Add the logged in user to the database.
			
				// The query for inserting the logged in user into the database.
				$insertquery = "INSERT INTO LoggedInUsersProject VALUES (?, ?)";
				$statement = $db->prepare($insertquery);
				$statement->bind_param('ss', $userName, $sessionID);
				$statement->execute();
				$statement->close();
				
				echo '<script type="text/javascript">alert("Welcome ' + $userName + '! You are now logged in!");</script>';
				
				return true;
			}
			else
			{
				echo '<script type="text/javascript">alert("The username or password was incorrect!");</script>';
			}
		}
		
		return false;
	}
	
	// Logs out a user, removing them from the logged in 
	// project table in the database.
	function logOut($db, $sessionID)
	{
		// The query for deleting the logged in user session from the database.
		$insertquery = "DELETE FROM LoggedInUsersProject WHERE session_id = ?";
		$statement = $db->prepare($insertquery);
		$statement->bind_param('s', $sessionID);
		$statement->execute();
		$statement->close();
		
		echo '<script type="text/javascript">alert("You are now logged out.");</script>';
	}
	
	// Submits an audio file to the audio file table in
	// the database. The new file should appear in and
	// audio player on the website.
	function submission($db, $user)
	{
		// This function assumes the user is already logged in 
		//(the submit button shouldnt show if they are not)
		
		// Only audio files are allowed on the site. These are all the audio extensions
		$allowedFileTypes = array('mp3', 'aac', 'aif', 'flac', 'iff', 'm4a', 'm4b', '.mid', 'midi', 'mpa', 'mpc', 'oga', 'ogg', 'ra', 'ram', 'snd', 'wav', 'wma');
		// Check the file in question
		$fileToCheck = $_FILES["userFile"]["name"];
		$extensionToCheck = pathinfo($fileToCheck, PATHINFO_EXTENSION);

		if ($_FILES['userFile']['error'] === UPLOAD_ERR_INI_SIZE) 
		{
			echo '<script type="text/javascript">if(!alert("Error: File too large (must be < 2MB).")){window.location.href=\'http://ecs.fullerton.edu/~cs431s49/project/submission.php\';}</script>';
		}
		// Tell the user that we only accept audio files
		elseif (!in_array($extensionToCheck, $allowedFileTypes)) {
			echo '<script type="text/javascript">if(!alert("Error: File not supported. (must be an audio file).")){window.location.href=\'http://ecs.fullerton.edu/~cs431s49/project/submission.php\';}</script>';
		}

		else
		{
			//the actual file name
			$fileName = basename($_FILES["userFile"]["name"]);
			
			// Get the values from the POST request for the file metadata.
			$name = $_POST['audioName'];// Name of file given by the user
			$genre = $_POST['genre'];
			$artist = $_POST['artistName'];
			$dt = \DateTime::createFromFormat('m/d/Y', $_POST['date']);	// Convert the date obtained into a suitable formatted string for db insert
			$date = $dt->format('Y-m-d');// Note: this relies on the user to input the correct date format
			
			// The query for inserting the file metadate into the  appropriate metadata table.
			$insertquery = "INSERT INTO AudioMetadataProject VALUES (?, ?, ?, ?, ?, ?)";
			$statement = $db->prepare($insertquery);
			$statement->bind_param('ssssss', $user, $name, $genre, $artist, $date, $fileName);
			$statement->execute();
			$statement->close();
			
			// if everything is ok, try to upload file and give user confirmation
			if (move_uploaded_file($_FILES["userFile"]["tmp_name"], "uploads/".$_FILES["userFile"]["name"])) 
			{
				echo '<script type="text/javascript">if(!alert("Your file ' . $fileName . ' was uploaded successfully!")){window.location.href=\'http://ecs.fullerton.edu/~cs431s49/project\';}</script>';
				
			} 
			else 
			{ 
				echo '<script type="text/javascript">alert("ERROR! ' . $fileName . ' was NOT uploaded!");</script>';
			}
		}
	}

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

	// Start of the program.
	
	// Starts or resumes a session.
	session_start();
	
	// The session ID for this user.
	$sessionID = session_id();

	// The sort type.
	$sortType = "none";

	// The action to perform as soon as this webpage
	// is opened.
	$action = whichAction();
	
	// The username, password, hostname and database name.
	$user = "cs431s49";
	$pass = "aPhuaQu7";
	$host = "mariadb";
	$db_name = "cs431s49";
	
	// A reference to the database.
	$db = new mysqli($host, $user, $pass, $db_name);
	
	// Checks if logged into the website as a user.
	$loggedIn = checkIfLoggedIn($db, $sessionID);

	// Perform the action based on the action's post
	switch($action)
	{
		case 'sort':
			$sortType = $_GET['sortType'];
			break;
		case 'registration':
			registerUser($db);
			break;
		case 'login':
			$loggedIn = logIn($db, $sessionID);
			break;
		case 'logout':
			logOut($db, $sessionID);
			$loggedIn = false;
			break;
		case 'submission':
			$user = getCurrentUsername($db, $sessionID);
			submission($db, $user);
			break;
		default:
			// Do nothing.
			break;
	}
	
	// Display the site's navbar
	if($loggedIn)
	{	
		$userName = getCurrentUsername($db, $sessionID);

		// Display a nav bar with the submit, logout button and site home button.
		echo '<ul>
				<li><input type="button" value="SUBMIT" class="siteThemeButtons" onclick="window.location.href=\'http://ecs.fullerton.edu/~cs431s49/project/submission.php\';"/></li>
					<form action="http://ecs.fullerton.edu/~cs431s49/project/index.php" method="post">
					<input type="hidden" name="postAction" value="logout">
				<li><input type="submit" class="siteThemeButtons" value="LOG OUT"/>
				</form></li>
				<li><p style="color: white; margin-right: 10px;"><i>' . $userName . '</i></p></li>
				<li style="float:left"><a href="#home">AUDIO LOAD</a></li>
			</ul>';
	}
	else
	{
		// Display a nav bar with the sign up, login button and site home button.
		echo '
		<ul>
			<li><input type="button" value="SIGN UP" class="siteThemeButtons" onclick="window.location.href=\'http://ecs.fullerton.edu/~cs431s49/project/registration.htm\';"/></li>
			<li><input type="button" value="LOG IN" class="siteThemeButtons" onclick="window.location.href=\'http://ecs.fullerton.edu/~cs431s49/project/login.htm\';"/></li>
			<li style="float:left"><a href="#home">AUDIO LOAD</a></li>
		</ul>';
	}

	//disconnect from database
	$db->close();
?>

</head>
<body>
	<div class="viewContent">
		<h2>Sort By Category:  
			<div class="dropdown">
				<input type="button" class="dropbtn" value="Select">
				<div class="dropdown-content">
					<form action="http://ecs.fullerton.edu/~cs431s49/project/index.php" method="get">
						<input type="hidden" name="sortType" value="name">
						<input type="submit" value="Name" style="font-size : 15px; width: 100%; height: auto;">
					</form>
					<form action="http://ecs.fullerton.edu/~cs431s49/project/index.php" method="get">
						<input type="hidden" name="sortType" value="date">
						<input type="submit" value="Date" style="font-size : 15px; width: 100%; height: auto;">
					</form>
					<form action="http://ecs.fullerton.edu/~cs431s49/project/index.php" method="get">
						<input type="hidden" name="sortType" value="location">
						<input type="submit" value="Artist" style="font-size : 15px; width: 100%; height: auto;">
					</form>
				</div>
			</div>
		</h2>
		<br>
			<?php
				// Sorts the image blocks that are gathered by a certain index.
				function sort_blocks($blocks, $byIndex)
				{
					// The sortable blocks array. It is a 2D array to 
					// store multiple blocks of the same values.
					$sortableBlocks = array();
					
					// The sorted blocks array.
					$sortedBlocks = array();
					
					// The index by which to sort by.
					$indexToSortBy = 0;
					
					// Select the sortable index.
					if ($byIndex === "name")
					{
						$indexToSortBy = 1;
					}
					else if ($byIndex === "date")
					{
						$indexToSortBy = 2;
					}
					else// This method sorts by artist
					{
						$indexToSortBy = 3;
					}
					
					// Create the sortable block array.
					foreach($blocks as $curBlock)
					{
						// Initialize the second dimension of the array if 
						// not done previously.
						if(!$sortableBlocks[$curBlock[$indexToSortBy]].is_array())
						{
							$sortableBlocks[$curBlock[$indexToSortBy]] = array();
						}
						
						array_push($sortableBlocks[$curBlock[$indexToSortBy]], $curBlock);
					}
					
					// Sort by the array's key. It is case insensitive.
					ksort($sortableBlocks, SORT_STRING | SORT_FLAG_CASE);
					
					// Send the sorted results to a normal array.
					foreach ($sortableBlocks as $curBlockArray)
					{
						foreach($curBlockArray as $curBlock)
						{
							array_push($sortedBlocks, $curBlock);
						}
					}
					
					return $sortedBlocks;
				}
			
				//define the upload dir for photos
				$directory = '/mnt/useraccounts/titan0/cs431s/cs431s49/homepage/project/uploads';
				if (!is_dir($directory)) 
				{
					exit('Invalid diretory path');
				}
				
				// The audio blocks that will be displayed on the webpage.
				$audioBlocks = array();
				
				//put each photo in /uploads into an array named $file
				foreach (scandir($directory) as $file) 
				{
					if ($file !== '.' && $file !== '..') 
					{
						//parse the database and store each record into temporary arrays [for comparison]	
						$db = new mysqli($host, $user, $pass, $db_name);
						
						//look for a file that matches the filename in the uploads folder, then store that data from the record into a temp array for comparison operations
						$retrievequery = "SELECT * FROM AudioMetadataProject WHERE file_url = ?";
						$statement = $db->prepare($retrievequery);
						$statement->bind_param('s', $file);
						$statement->execute();
						$statement->store_result();
						
						//place the record data into a temp array
						$separatedDataArray = array();
						
						$statement->bind_result($separatedDataArray[0], $separatedDataArray[1],$separatedDataArray[2],$separatedDataArray[3],$separatedDataArray[4],$separatedDataArray[5]);
						
						$statement->fetch();
						
						//disconnect from database
						$statement->free_result();
						$db->close();
						
						//if the current audio filename matches stored filename record, pair the audio with that record of data
						if($separatedDataArray[5] == $file)
						{
							array_push($audioBlocks, $separatedDataArray);
						}
					}
				}
				
				// The sorted blocks.
				$sortedBlocks = array();
				
				// Sort the array based on the sort type.
				if ($sortType !== "none")
				{
					$sortedBlocks = sort_blocks($audioBlocks, $sortType);
				}
				else
				{
					$sortedBlocks = $audioBlocks;
				}
				
				// Display the audio blocks to the webpage.
				foreach ($sortedBlocks as $curBlock)
				{
					echo '
					<div class="siteThemeButtons">
						<table>
							<tr>
								<td>
									<p style="color: white">
										TITLE: <b>'.$curBlock[1].'</b> &ensp; ARTIST: '.$curBlock[3].'
											<br />
										GENRE: '.$curBlock[2].' &ensp; DATE: '.$curBlock[4].'
									</p>
								</td>
							</tr>
							<tr>
								<td>
									<div class="media-wrapper">
										<audio id="player2" preload="none" controls style="max-width:100%;">
											<source src="http://ecs.fullerton.edu/~cs431s49/project/uploads/'.$curBlock[5].'" type="audio/mp3">
										</audio>
									</div>
								</td>
							</tr>
							<tr>
								<td>
									<p style="color: white">UPLOADED BY: '.$curBlock[0].'</p>
								</td>
							</tr>
						</table>
					</div>';
				}
			?>
		</div>
	</body>
</html>