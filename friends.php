<?php
session_start();
mysql_connect("localhost", "root", "");
mysql_select_db("www");
//START OF LOGIN
if(!isset($_SESSION["logged"])) {
if(isset($_POST["username"])) {
$query = mysql_query("SELECT id FROM members WHERE username = '" . $_POST["username"] . "'");
if(mysql_num_rows($query) > 0) {
$row = mysql_fetch_array($query);
$_SESSION["logged"] = $row["id"];
header("Location: " . $_SERVER["PHP_SELF"]);
}
} else {
echo("<form method=\"POST\">
<input type=\"text\" name=\"username\" value=\"Type username here\">
<input type=\"submit\" name=\"submit\">
</form>");
}
} else {
//END OF LOGIN
//START OF ADD FRIEND
if(isset($_GET["add"])) {
$query = mysql_query("SELECT id FROM members WHERE id = '" . $_GET["add"] . "'");
if(mysql_num_rows($query) > 0) {
$_query = mysql_query("SELECT * FROM friend_requests WHERE sender = '" . $_SESSION["logged"] . "' AND recipient = '" . $_GET["add"] . "'");
if(mysql_num_rows($_query) == 0) {
mysql_query("INSERT INTO friend_requests SET sender = '" . $_SESSION["logged"] . "', recipient = '" . $_GET["add"] . "'");
}
}
}
//END OF ADD FRIEND
//START OF ACCEPT FRIEND
if(isset($_GET["accept"])) {
$query = mysql_query("SELECT * FROM friend_requests WHERE sender = '" . $_GET["accept"] . "' AND recipient = '" . $_SESSION["logged"] . "'");
if(mysql_num_rows($query) > 0) {
$_query = mysql_query("SELECT * FROM members WHERE id = '" . $_GET["accept"] . "'");
$_row = mysql_fetch_array($_query);
$friends = unserialize($_row["friends"]);
$friends[] = $_SESSION["logged"];
mysql_query("UPDATE members SET friends = '" . serialize($friends) . "' WHERE id = '" . $_GET["accept"] . "'");
$_query = mysql_query("SELECT * FROM members WHERE id = '" . $_SESSION["logged"] . "'");
$_row = mysql_fetch_array($_query);
$friends = unserialize($_row["friends"]);
$friends[] = $_GET["accept"];
mysql_query("UPDATE members SET friends = '" . serialize($friends) . "' WHERE id = '" . $_SESSION["logged"] . "'");
}
mysql_query("DELETE FROM friend_requests WHERE sender = '" . $_GET["accept"] . "' AND recipient = '" . $_SESSION["logged"] . "'");
}
//END OF ACCEPT FRIEND
//START OF SHOW FRIEND REQUESTS
$query = mysql_query("SELECT * FROM friend_requests WHERE recipient = '" . $_SESSION["logged"] . "'");
if(mysql_num_rows($query) > 0) {
while($row = mysql_fetch_array($query)) {
$_query = mysql_query("SELECT * FROM members WHERE id = '" . $row["sender"] . "'");
while($_row = mysql_fetch_array($_query)) {
echo $_row["username"] . " wants to be your friend. <a href=\"" . $_SERVER["PHP_SELF"] . "?accept=" . $_row["id"] . "\">Accept?</a>";
}
}
}
//END OF SHOW FRIEND REQUESTS
//START OF MEMBERLIST
echo "<h2>Member List:</h2>";
$query = mysql_query("SELECT * FROM members WHERE id != '" . $_SESSION["logged"] . "'");
while($row = mysql_fetch_array($query)) {
$alreadyFriend = false;
$friends = unserialize($row["friends"]);
if(isset($friends[0])) {
foreach($friends as $friend) {
if($friend == $_SESSION["logged"]) $alreadyFriend = true;
}
}
echo $row["username"];
$_query = mysql_query("SELECT * FROM friend_requests WHERE sender = '" . $_SESSION["logged"] . "' AND recipient = '" . $row["id"] . "'");
if(mysql_num_rows($_query) > 0) {
echo " - Friendship requested.";
} elseif($alreadyFriend == false) {
echo " - <a href=\"" . $_SERVER["PHP_SELF"] . "?add=" . $row["id"] . "\">Add as friend</a>";
} else {
echo " - Already friends.";
}
echo "";
}
//END OF MEMBERLIST
//START OF FRIENDLIST
echo "<h2>Friend List:</h2>";
$query = mysql_query("SELECT friends FROM members WHERE id = '" . $_SESSION["logged"] . "'");
while($row = mysql_fetch_array($query)) {
$friends = unserialize($row["friends"]);
if(isset($friends[0])) {
foreach($friends as $friend) {
$_query = mysql_query("SELECT username FROM members WHERE id = '" . $friend . "'");
$_row = mysql_fetch_array($_query);
echo $_row["username"] . "";
}
}
}
//END OF FRIENDLIST
}
?>