<?php
# I have no idea what I am doing
$user = $_POST["user"];
$url = "http://libre.fm/2.0/?method=user.getrecenttracks&user=" . $user . "&page=1&limit=100";
$xml = simplexml_load_file($url);
print_r($xml);
?>
