<?php
$dbh = new PDO('mysql:host=localhost;port=3306;dbname=infuse_test_banner', 'root', 'password');
if (!$dbh) {
    die("error connect to database");
}
$ip = getIp();
$agent = $_SERVER["HTTP_USER_AGENT"];
$url = $_SERVER['HTTP_REFERER'];

$stmt = $dbh->prepare("SELECT views_count FROM log WHERE ip_address=:ip AND user_agent=:agent AND page_url=:url");
$stmt->bindParam(':ip',$ip);
$stmt->bindParam(':agent',$agent);
$stmt->bindParam(':url',$url);
$stmt->execute();
$count = $stmt->rowCount();

$date = date ("Y-m-d H:i:s",time());
if ($count==0) {
    $stmt = $dbh->prepare("INSERT INTO log (ip_address, user_agent, page_url, view_date, views_count) VALUES(:ip,:agent,:url,:view_date,:v_count)");
    $count++;
    $stmt->bindParam(':ip',$ip);
    $stmt->bindParam(':agent',$agent);
    $stmt->bindParam(':url',$url);
    $stmt->bindParam(':view_date',$date);
    $stmt->bindParam(':v_count',$count);
    $stmt->execute();
} else {
    $row = $stmt->fetch();
    $viewsCount = $row["views_count"];
    $viewsCount++;
    $stmt = $dbh->prepare("UPDATE log SET view_date=:view_date,views_count=:v_count 
        WHERE ip_address=:ip AND user_agent=:agent AND page_url=:url");
    $stmt->bindParam(':ip',$ip);
    $stmt->bindParam(':agent',$agent);
    $stmt->bindParam(':url',$url);
    $stmt->bindParam(':view_date',$date);
    $stmt->bindParam(':v_count',$viewsCount);
    $stmt->execute();
}
$content = file_get_contents("logo.svg");

header("Content-type: image/svg+xml");
header("Content-Length: " . strlen($content));

echo $content;

function getIp() {
    $keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ex = explode(',', $_SERVER[$key]);
            $ip = trim(end($ex));
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
}