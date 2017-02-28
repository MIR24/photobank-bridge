<?php

$dsn = 'mysql:dbname=photobank;host=172.17.0.2;charset=utf8';
$user = '';
$password = '';

try {
    $dbh = new PDO($dsn, $user, $password);
    echo "Connected to source!\n";
} catch (PDOException $e) {
    echo 'DB connection failed' . $e->getMessage();
}

function getSource($sql)
{
  global $dbh;
  $res = $dbh->query($sql, PDO::FETCH_ASSOC);

  return $res;
}
