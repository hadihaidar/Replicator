<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Add Model</title>
</head>
<body>
  <p>TEST1</p>
  <?php
  require_once "includes/vendor/autoload.php";
  use WindowsAzure\Common\ServicesBuilder;
  use WindowsAzure\Common\ServiceException;
  $conString ="connectionstring" ;
  $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($conString);
  try {
    // List blobs.
    $blob_list = $blobRestProxy->listBlobs("3d-models");
    $blobs = $blob_list->getBlobs();

    foreach($blobs as $blob)
    {
      echo $blob->getName().": ".$blob->getUrl()."<br />";
    }
  } catch(ServiceException $e){
    $code = $e->getCode();
    $error_message = $e->getMessage();
    echo $code.": ".$error_message."<br />";
  }
    ?>
    <p>TEST2</p>
    <form method="post" enctype="multipart/form-data")
        <input type = "file" name="myfile"/>
        <button name="btn">Upload</button>
    </form>
</body>
</html>
