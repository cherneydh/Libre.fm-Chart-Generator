<?php
include('/home/daniel/Desktop/MySQL/signin.php');
include('image_resize.php');

# I have no idea what I am doing
$dest = imagecreatefrompng('template.png');
imagealphablending($dest, false);
imagesavealpha($dest, true);

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
#$url = "http://libre.fm/2.0/?method=user.getrecenttracks&user=" . $user . "&page=" . $page . "&limit=100";
$xml = simplexml_load_file("sample.xml");
if (!$xml) {
	echo '<h2>Error trying to retrieve that user\'s data!</h2><br>';
	exit;
}

echo "<h1>" . $user . "'s Top " . $count  .  " Albums for the Past Week </h1>";

foreach($xml->children() as $childs) {
	foreach($childs as $child) {
		$entry = "INSERT INTO albums VALUES (\"" . $child->artist . "\", \"" . $child->album . "\")";
		mysqli_query( $conn, $entry) or die(mysqli_error($conn));	
	}
}

$albums = array();
$artists = array();

$sql = mysqli_query( $conn, "SELECT album, artist FROM albums GROUP BY album, artist ORDER BY COUNT(*) DESC LIMIT " . $count) or die(mysqli_error($conn));

while($row = mysqli_fetch_assoc($sql)){
	$j = 0;
	foreach($row as $key => $val){
		if($j % 2 == 0){
			array_push($albums, $val);
		}else{
			array_push($artists, $val);
		}
		$j++;
	}
}

$albumKeys = array_keys($albums);

$artistKeys = array_keys($artists);

for($i = 0; $i < 9; $i++){
echo $albums[$albumKeys[$i]] . $artists[$artistKeys[$i]] . "<br>";
}
############################ INITIALIZE VARIABLES TO DO IMAGE PROCESSING ###########################
$resized_src = 'resize.jpeg';
$x = 0;
$y = 0;
$z = 0;

while($y<1200){
	$x = 0;
	while($x<1200){
		#$release = simplexml_load_file("https://musicbrainz.org/ws/2/release/?query=release:" . $albums[$albumKeys[$z]]);	
		
		$release = simplexml_load_file("release.xml");
		
		$url = "http://coverartarchive.org/release/a171fb49-0fc1-494d-993b-a8940fef90a7/front";
		$img = 'temp.jpeg';
		file_put_contents($img, file_get_contents($url));

		smart_resize_image('temp.jpeg', null, 400, 400, false, $resized_src, false, false, 100);
		$src = imagecreatefromjpeg('resize.jpeg');

		imagecopymerge($dest, $src, $x, $y, 0, 0, 400, 400, 100);
		$x = $x + 400;
		$z++;
	}
	$y = $y + 400;
}

############################### PRINT OUT THE FINAL CHART ###########################################
#ob_start();
#imagepng($dest);
#printf('<img src="data:image/png;base64,%s"/>', base64_encode(ob_get_clean()));


############################## DESTROY EXCESS IMAGES ###############################################
#imagedestroy($dest);
#imagedestroy($src);

#echo "<img><img>";


#coverartarchive.org/release/MBID/front
#https://musicbrainz.org/ws/2/release/?query=release:radio%20amor

$conn->close();
?>
