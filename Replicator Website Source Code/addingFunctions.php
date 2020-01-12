<?php
require_once "includes/vendor/autoload.php";
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;
$conString ="connectionstring" ;
$blobRestProxy = ServicesBuilder::getInstance()->createBlobService($conString);
//Adding file to blob Azure Storage
try {
  $container = "3d-models";
  $contentB = file_get_contents($file);
  $blobRestProxy->createBlockBlob($container, $remote_file, $contentB);

} catch(ServiceException $e){
  $code = $e->getCode();
  $error_message = $e->getMessage();
  echo $code.": ".$error_message."<br />";
}

//Deleting file from the FTP server
$ftpDeleteError = 0;
if (ftp_delete($ftp_conn, $remote_file)) {
 $ftpDeleteError = 1;
}

//Adding To Table
$blob_list = $blobRestProxy->listBlobs("3d-models");
$blobs = $blob_list->getBlobs();
$blobURL = '';
foreach($blobs as $blob)
{
  if ($blob->getName() === $remote_file){
    $blobURL = $blob->getUrl();
  }
}
$name2 = implode("%20", $nameList);
$url = 'https://replicatorservices.azurewebsites.net/api/postToTable?name='.$name2.'&url='.$blobURL.'&code=rMntaUlGc23ph9o2Mc67TScAUZQNMUV8Xac//lbjY74dqh47X2l8zQ==';
$contents = file_get_contents($url);

?>
