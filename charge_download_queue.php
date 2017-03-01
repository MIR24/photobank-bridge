<?php
require 'db.php';
require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('main');

//Operation limit, mostly for debug purposes. Set to 0 at production run.
$limit = 5;
$minImageWidth = 800;
$minImageHeight = 800;
$urlTemplate = "http://stc01.mir24.tv/media/images/original/original";

$log->pushHandler(new StreamHandler('main.log', Logger::DEBUG));
$log->debug('Sync started');

$sql = "SELECT * FROM photobank JOIN image ON image_id=image.id WHERE showinbank = 1 AND image.width > $minImageWidth AND image.height > $minImageHeight";
if ($limit) $sql.= ($limit)?" LIMIT ".$limit:"";
$log->debug($sql);

$source = getSource($sql);
foreach($source as $one)
{
  $fileNameTemplate = $one['image_id'].".jpg";
  $fileUrl = $urlTemplate . $fileNameTemplate;

  //Check if file is synced already
  $sql = "SELECT count(*) synced FROM file WHERE originalFilename  =\"original".$fileNameTemplate."\"";
  $log->debug("Looking for file", ['SQL' => $sql]);

  $isSynced = getSource($sql)->fetch();
  $log->debug('Is synced:', $isSynced);

  if($isSynced["synced"]) {
    $log->warning("Synced already, skip and continue",["file"=>$fileUrl]);
    continue;
  }

  //Check if queued already
  $sql = "SELECT count(*) queued FROM remote_url_download_queue WHERE url =\"$fileUrl\"";
  $log->debug("Looking for queued", ['SQL' => $sql]);

  $isQueued = getSource($sql)->fetch();
  $log->debug('Is queued:', $isQueued);

  if($isQueued["queued"]) {
    $log->warning("Queued already, skip and continue",["file"=>$fileUrl]);
    continue;
  }

  $log->debug("Going to queue", ['URL' => $fileUrl]);

  $sql = "INSERT INTO `remote_url_download_queue` (user_id, url, file_server_id, job_status, folder_id, created, started, finished, download_percent) VALUES (1, '$fileUrl', 1, 'pending', 1, NOW(), '0000-00-00 00:00:00', '0000-00-00 00:00:00',0)";
  $log->debug("Push to queue", ['SQL' => $sql]);

  $stmt = $dbh->prepare($sql);
  $res = $stmt->execute();
  $log->debug("Result", ["res"=>$res]);
  if(!$res) $log->error("Failed to queue");
}
