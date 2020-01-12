<?php

if(isset($_POST['login-submit'])){
  require 'dbh.inc.php';
  $email = $_POST['email']);
  $password = $_POST['pwd'];
  if(empty($email)  || empty($password)){
    header("Location: ../login.php?error=emptyFields");
    exit();
  }else {
    $sql = "SELECT * FROM Users WHERE Email='$email'";
    $result = sqlsrv_query($conn , $sql);
    $resultCheck = sqlsrv_num_rows($result);
    if ($resultCheck < 1){
      header("Location: ../login.php?login=NoSuchUser");
      exit();
    }else{
      //checking password
      $hash = sqlsrv_query($conn, "SELECT Password FROM Users WHERE Email='$email'");
      if (password_verify($password, $hash[0])) {
        // Success!
        header("Location: ../index.php?loginsuccess");
        exit();
      }
      else {
        // Invalid credentials
        header("Location: ../login.php?login=NoSuchUser");
        exit();
      }
      
      header("Location: ../login.php?login=userExists");
      exit();
    }
  }
}else {
  header("Location: ../login.php?illegalEntry");
  exit();
}

?>
