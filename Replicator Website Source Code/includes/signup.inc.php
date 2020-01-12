<?php
if(isset($_POST['signup-submit'])){
  include_once 'dbh.inc.php';

  $first = $_POST["first"];
  $last = $_POST["last"];
  $email = $_POST["email"];
  $pwd = $_POST["pwd"];

  if(empty($first) || empty($last) || empty($email) || empty($pwd)){
    header("Location: ../signup.php?error=emptyFields");
    exit();
  }else {
    if((!preg_match("/^[a-zA-Z]*$/", $first)) || !preg_match("/^[a-zA-Z]*$/", $last)){
      header("Location: ../signup.php?error=InvalidFirstameOrLastame");
      exit();
    }else {
      if(strlen($pwd) < 8){
        header("Location: ../signup.php?error=PasswordMusteAtLeast8Characters");
        exit();
      }else {
        if(!preg_match("/^[a-zA-Z0-9!@#$%^&]*$/", $pwd)){
          header("Location: ../signup.php?error=InvalidPassword");
          exit();
        }else {
          $checkUserTakenSQl = "SELECT * FROM users WHERE Email='$email'";
          $userTakenResult = sqlsrv_query($conn, $checkUserTakenSQl);
          $resultCheck = sqlsrv_num_rows($userTakenResult);
          if($resultCheck > 0){
            header("Location: ../signup.php?userTaken");
            exit();
          }else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO Users (FirstName , LastName , Email, Password) VALUES ('$first' , '$last', '$email' , '$hashedPassword');";
            session_start();
            $_SESSION['email'] = $email;
            $_SESSION['first'] = $first;
            $_SESSION['last'] = $last;
            $getResults= sqlsrv_query($conn, $sql);
            header("Location: ../index.php?signupsuccess");
            exit();
          }
        }
      }
    }
  }
}else {
  header("Location: ../signup.php?illegalEntry");
  exit();
}
?>
