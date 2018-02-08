<?php
include('/home/daniel/Desktop/MySQL/signin.php');

# I have no idea what I am doing
$size = $_POST['size'];

if($size == "0")
{
	$count = 9;
}

if($size == "1")
{
	$count = 16;
}

if($size == "2")
{
	$count = 25;
}

#Create Temporary MySQL Table
$entry = "DROP TEMPORARY TABLE IF EXISTS albums";
mysqli_query ( $conn, $entry ) or die(mysqli_error($conn));

$entry = "CREATE TEMPORARY TABLE albums
		(
			artist VARCHAR(100) NOT NULL,
			album VARCHAR(100) NOT NULL
		) ";
mysqli_query ( $conn, $entry ) or die(mysqli_error($conn));

#Set Page Number
$page = 1;

#Get username for user
$user = $_POST["user"];

#Get XML for past 100 results
$url = "http://libre.fm/2.0/?method=user.getrecenttracks&user=" . $user . "&page=" . $page . "&limit=100";
$xml = simplexml_load_file($url);
if (!$xml) {
	echo '<h2>Error trying to retrieve that user\'s data!</h2><br>';
	exit;
}

echo "<h1>" . $user . "'s Top " . $count  .  " Albums for the Past Week </h1>";

$artist = $xml->xpath('/lfm/recenttracks/track/artist');
$album = $xml->xpath('/lfm/recenttracks/track/album');

while(list( , $node1) = each($artist)) {
	while(list( , $node2) = each($album)) {
		$entry = "INSERT INTO albums VALUES (\"" . $node1 . "\", \"" . $node2 . "\")";
		mysqli_query( $conn, $entry) or die(mysqli_error($conn)); 
	}
}

$sql = mysqli_query( $conn, "SELECT album,COUNT(*) as count FROM albums GROUP BY album ORDER BY count DESC LIMIT " . $count) or die(mysqli_error($conn));

while($row = mysqli_fetch_assoc($sql)){
	foreach($row as $key => $val){
		echo $key . ": " . $val . "<br>";
	}
}

$conn->close();
?>
