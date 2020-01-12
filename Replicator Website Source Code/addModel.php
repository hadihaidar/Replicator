<?php
if (isset($_POST['submit'])){
    $name1 = $_POST['name'];
    $name2 = $_POST['name'];

    $file = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));
    //dynamic name
    $nameError = "";
    $fileError = "";
    if (!preg_match("/^(?!\s*$).+/", $name1)){
      $nameError = "Name can not be empty";
    }
    elseif (!preg_match("/^[a-zA-Z ]*$/",$name1)){
      $nameError = "Only characters and white spaces are allowed";
    }
    else {
      $arrayOfAllNames = array();
      $url1 = "https://replicatorservices.azurewebsites.net/api/TableToJson?code=rMntaUlGc23ph9o2Mc67TScAUZQNMUV8Xac//lbjY74dqh47X2l8zQ==";
      $contents1 = file_get_contents($url1);
      $result1 = "";
      if ($contents1!=false){
        $array2 = json_decode($contents1,true);
        $title = "<h5>Similarities Found</h5><table><tr><th>Name</th></tr>";
        $found = 0;
        foreach($array2 as $key) {
           $naming = $key["name"];
           $gcode = $key["gcode"];
           $naminglwr = strtolower($naming);
           $name1lwr = strtolower($name1);

           if (preg_match("/$naminglwr/", $name1lwr)){
             $result1 = $result1."<tr><td>$naming</td></tr>";
             $found = 1;
           }

        }
        $result1 = $result1."</table>";
        // $result1 = $result1."<h6>If you would like to proceed by adding the model please click the button below</h6>";


        if ($found ==1){
          $nameError = "We found similar items in our models";
          $result1 = $result1."</br><p><b>If you would like to proceed by adding the model please click the button below</b></p>
          <form method='POST' action='/includes/upload.php'>
            <input type='hidden' name='name1' value='$name1'>

            <input type='submit' name='button1' value='Proceed'></form>
            <a href='ourModels.php'>Or you can check all the models available</a>";
        }
        else{
          $nameList = explode(' ', $name1);
          $name = implode("_", $nameList);
          $remote_file = $name.'.'.$fileActualExt;
          if ($fileActualExt!=='gcode' && $fileActualExt!=='g') {
           $fileError = "File must be of type: g or gcode";
          }

          else {
            $ftp_server = "waws-prod-bn1-031.ftp.azurewebsites.windows.net";
            $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
            $ftp_username= 'username';
            $ftp_userpass="password";
            $login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);
            ftp_chdir($ftp_conn, '/site/wwwroot/tempFiles/');
            ftp_pasv($ftp_conn, true);
            //var_dump($_FILES);
            $ftpPutError = 0;
            if (ftp_put($ftp_conn, $remote_file, $file, FTP_BINARY))
              {
              $ftpPutError = 1;
              }
            if ($ftpPutError==1){
              include 'addingFunctions.php';
              //postToBlob($remote_file);
              //deleteFileFromFTP($ftp_conn, $remote_file);
              if ($contents === 'added') {
                echo "<script type='text/javascript'>alert('File Succesfully Uploaded');window.location='/../ourModels.php#ourModelsTable';</script>";
              }
            }
            ftp_close($ftp_conn); //closing
          }
        }


      }

      }




}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>The Replicator - Add Model</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Amatic+SC:400,700|Work+Sans:300,400,700" rel="stylesheet">
    <link rel="stylesheet" href="fonts/icomoon/style.css">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet" href="css/jquery-ui.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/bootstrap-datepicker.css">
    <link rel="stylesheet" href="css/animate.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mediaelement@4.2.7/build/mediaelementplayer.min.css">

    <link rel="stylesheet" href="fonts/flaticon/font/flaticon.css">

    <link rel="stylesheet" href="css/aos.css">

    <link rel="stylesheet" href="css/style.css">

  </head>
  <body>

  <!-- <div class="site-wrap" style="background-color:#F8F8F8;"> -->

    <div class="site-mobile-menu">
      <div class="site-mobile-menu-header">
        <div class="site-mobile-menu-close mt-3">
          <span class="icon-close2 js-menu-toggle"></span>
        </div>
      </div>
      <div class="site-mobile-menu-body"></div>
    </div> <!-- .site-mobile-menu -->


    <div class="site-navbar-wrap js-site-navbar bg-white">

      <div class="container">
        <div class="site-navbar bg-light">
          <div class="py-1">
            <div class="row align-items-center">
              <div class="col-2">
                <h2 class="mb-0 site-logo"><a href="index.php" style="color:black;">Replicator</a></h2>
                <!--<div class="col-md-10 text-center">
                  <h1 class="mb-4">Adding A New Model</h1>
                </div>-->

              </div>
              <div class="col-10">
                <nav class="site-navigation text-right" role="navigation">
                  <div class="container">
                    <!-- d-lg-none -->
                    <div class="d-inline-block  ml-md-0 mr-auto py-3"><a href="#" class="site-menu-toggle js-menu-toggle"><span class="icon-menu h3" style="color:black;"></span></a></div>
                     <!-- d-lg-block -->
                     <ul class="site-menu js-clone-nav d-none">
                       <li>
                         <a href="index.php">Home</a>
                       </li>
                       <li><a href="ourModels.php">Available Models</a></li>
                       <li><a href="addModel.php">Add Models</a></li>



                        <li><a href="Alexa.php">Communicate With Alexa!</a></li>

                     </ul>
                  </div>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>





    <!-- </div> -->

    <div class="site-section bg-light">
      <div class="container">
        <div class="row">

          <div class="col-md-12 col-lg-8 mb-5">

          <form action="addModel.php" class="p-5 bg-white" method="POST" enctype="multipart/form-data"> <!-- XSS(Cross Site Scripting) attacks HAHA-->
            <h1 class="mb-4">Adding A New Model</h1>

            <div class="row form-group">
              <div class="col-md-12">
                <label class="font-weight-bold">Name of the Item</label>
                <p id='nameError' style='color:red;'><?php echo $nameError;?></p>
                <input type="name" id="name" name ="name" class="form-control" placeholder="Name"  value= "<?php if(!empty($name2)){ echo $name2;} ?>">

              </div>
            </div>

            <div class="row form-group">
              <div class="col-md-12">
                <label class="font-weight-bold" for="email">GCode/G File</label>
              </div>
            </div>

          <!-- <form action="includes/upload.php" method="POST" enctype="multipart/form-data"> -->
              <div class="row form-group">
                <div class="col-md-12">
                    <input type = "file" name = "file"/>
                    <button type="submit" name="submit">Upload</button>
                </div>
                <p id='fileError' style='color:red;'><?php echo $fileError;?></p>
              </div>
          </form>
          </div>


          <div class="wrap-table100" id="ourModelsTable">
            <div class="table100">
              <?php if ($found==1){
                echo $title.$result1;
              }?>

            </div>
          </div>


        </div>
      </div>
    </div>





      <script src="js/jquery-3.3.1.min.js"></script>
      <script src="js/jquery-migrate-3.0.1.min.js"></script>
      <script src="js/jquery-ui.js"></script>
      <script src="js/popper.min.js"></script>
      <script src="js/bootstrap.min.js"></script>
      <script src="js/owl.carousel.min.js"></script>
      <script src="js/jquery.stellar.min.js"></script>
      <script src="js/jquery.countdown.min.js"></script>
      <script src="js/jquery.magnific-popup.min.js"></script>
      <script src="js/bootstrap-datepicker.min.js"></script>
      <script src="js/aos.js"></script>


      <script src="js/mediaelement-and-player.min.js"></script>

      <script src="js/main.js"></script>


      <script>
          document.addEventListener('DOMContentLoaded', function() {
                    var mediaElements = document.querySelectorAll('video, audio'), total = mediaElements.length;

                    for (var i = 0; i < total; i++) {
                        new MediaElementPlayer(mediaElements[i], {
                            pluginPath: 'https://cdn.jsdelivr.net/npm/mediaelement@4.2.7/build/',
                            shimScriptAccess: 'always',
                            success: function () {
                                var target = document.body.querySelectorAll('.player'), targetTotal = target.length;
                                for (var j = 0; j < targetTotal; j++) {
                                    target[j].style.visibility = 'visible';
                                }
                      }
                    });
                    }
                });

        </script>

      </body>
    </html>
