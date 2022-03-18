<?php

$dbconn = pg_pconnect("host=$pg_host port=$pg_port dbname=$pg_dbname user=$pg_dbuser password=$pg_dbpassword") or die("Could not connect");
if ($debug) {
	echo "host=$pg_host, port=$pg_port, dbname=$pg_dbname, user=$pg_dbuser, password=$pg_dbpassword<br>";
	$stat = pg_connection_status($dbconn);
	if ($stat === PGSQL_CONNECTION_OK) {
		echo 'Connection status ok';
	} else {
		echo 'Connection status bad';
	}    
}

function  sess_open() {
  return true;
}
function  sess_close() {
  return true;
}
function  sess_read($id) {
  global  $Rediska;
  return  $Rediska->on(‘common’)->get(“session_”  .  $id);
}
function  sess_write($id,  $data) {
  global  $Rediska;
  $Rediska->on(‘common’)->set(“session_”  .  $id,  $data);
  $Rediska->on(‘common’)->expire(“session_”  .  $id,  86400);
}
function  sess_destroy($id) {
  global  $Rediska;
  $Rediska->on(‘common’)->delete(“session_”  .  $id);
}
function  sess_clean($max) {
  return true;
}

function run_query($dbconn, $query) {
	if ($debug) {
		echo "$query<br>";
	}
	$result = pg_query($dbconn, $query);
	if ($result == False and $debug) {
		echo "Query failed<br>";
	}
	return $result;
}

//database functions
function get_article_list($dbconn){
	$query= 
		"SELECT 
		articles.created_on as date,
		articles.aid as aid,
		articles.title as title,
		authors.username as author,
		articles.stub as stub
		FROM
		articles
		INNER JOIN
		authors ON articles.author=authors.id
		ORDER BY
		date DESC";
return run_query($dbconn, $query);
}

function get_article($dbconn, $aid) {
	$query= 
		"SELECT 
		articles.created_on as date,
		articles.aid as aid,
		articles.title as title,
		authors.username as author,
		articles.stub as stub,
		articles.content as content
		FROM 
		articles
		INNER JOIN
		authors ON articles.author=authors.id
		WHERE
		aid='".$aid."'
		LIMIT 1";
return run_query($dbconn, $query);
}

function delete_article($dbconn, $aid) {
	$query= "DELETE FROM articles WHERE aid='".$aid."'";
	return run_query($dbconn, $query);
}

function add_article($dbconn, $title, $content, $author) {
	$stub = substr($content, 0, 30);
	$aid = str_replace(" ", "-", strtolower($title));
	$query="
		INSERT INTO
		articles
		(aid, title, author, stub, content) 
		VALUES
		('$aid', '$title', $author, '$stub', '$content')";
	return run_query($dbconn, $query);
}

function update_article($dbconn, $title, $content, $aid) {
	$query=
		"UPDATE articles
		SET 
		title='$title',
		content='$content'
		WHERE
		aid='$aid'";
	return run_query($dbconn, $query);
}

function authenticate_user($dbconn, $username, $password) {
	$stmt = $dbconn->prepare("SELECT
		authors.id as id,
		authors.username as username,
		authors.password as password,
		authors.role as role
		FROM authors WHERE
		username=? AND password=?
		LIMIT 1");
	$user = $_POST['username'];
	$pass = $_POST['password'];	
	
	$stmt->bind_param("ss", $user, $pass);
	$stmt->execute();
	$res = $stmt->get_result();
	
	return $res;
}
?>
