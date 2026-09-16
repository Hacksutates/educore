<?php
session_start();
include 'connection.php';
if (isset($_POST['signup'])) {

$fullname = $_POST['username'];
$login = $_POST['login'];
$password = $_POST['pass'];
$confirm_password = $_POST['confirm_pass'];

$_SESSION['old_fullname'] = $fullname;
$_SESSION['old_login']    = $login;
$_SESSION['old_password'] = $password;


$arr = str_split($login);
$hasError = false;
$hasErrorAuto = false;

    $_SESSION['loginError'] = '';
    $_SESSION['passwordError'] = '';
    $_SESSION['generalError'] = '';
    $_SESSION['autoError'] = '';

    if ($fullname == '' || $login == '' || $password == '' || $confirm_password == '' ) {
        $_SESSION['generalError'] = "All fields must be filled";
        $hasError = true;
    }


for ($i = 0; $i < count($arr); $i++) {
    if ($arr[$i] == "@") {
      $hasError = false;
      }
      if (strpos($login, '@') === false) {
        $_SESSION['loginError'] = "Login must contain @ symbol";
        $hasError = true;
    }
  }
  if ($password && strlen($password) < 8) {
        $_SESSION['passwordError'] = "Password must be at least 8 characters";
        $hasError = true;
    }
    if ($password !== $confirm_password) {
           $_SESSION['passwordError'] = "Passwords do not match";
           $hasError = true;
       }

    if ($hasError) {
      $_SESSION['old_fullname'] = $fullname;
      $_SESSION['old_login']    = $login;
      $_SESSION['old_password'] = $password;

        header("Location: reg_form_sup.php");
        exit;
    }

if (!$hasError) {
$query1 = mysqli_query(
    $connect,
    "INSERT INTO supervisors (supervisor_name, supervisor_login, supervisor_password)
     VALUES ('$fullname', '$login', '$password')"
     
);
    if (!$query1) {
           die("Database error: " . mysqli_error($connect));
       }

$_SESSION['name'] = $login;
$_SESSION['supervisor_id'] = mysqli_insert_id($connect);
$_SESSION['success'] = "Registration successful";
header("Location: attendancemanagement.php");
exit;
}

    }
      if (isset($_POST['signin'])) {

      $fullname = $_POST['username'];
      $login = $_POST['login'];
      $password = $_POST['pass'];
      $confirm_password = $_POST['confirm_pass'];
      $arr = str_split($login);

      $_SESSION['old_fullname'] = $fullname;
      $_SESSION['old_login']    = $login;
      $_SESSION['old_password'] = $password;

 $_SESSION['name'] = $_POST['login'];

 if ($fullname == '' || $login == '' || $password == '' || $confirm_password == '') {
    $_SESSION['generalError'] = "All fields must be filled";
    header("Location: reg_form_sup.php");
    exit;
}

for ($i = 0; $i < count($arr); $i++) {
  if (strpos($login, '@') === false) {
    $_SESSION['loginError'] = "Login must contain @ symbol";
    $hasError = true;
}
  }
  if ($password && strlen($password) < 8) {
        $_SESSION['passwordError'] = "Password must be at least 8 characters";
        $hasError = true;
    }
    if ($password !== $confirm_password) {
           $_SESSION['passwordError'] = "Passwords do not match";
           $hasError = true;
       }
    if ($hasError) {
      $_SESSION['old_fullname'] = $fullname;
      $_SESSION['old_login']    = $login;
      $_SESSION['old_password'] = $password;

        header("Location: reg_form_sup.php");
        exit;
    }
if (!$hasError) {
 $query2 = mysqli_query(
     $connect,
     "SELECT supervisor_login, supervisor_id, supervisor_password
      FROM supervisors
      WHERE supervisor_login = '$login'"
 );

    $data = mysqli_fetch_assoc($query2);

      if ($data && $data['supervisor_password'] == $password) {

          $_SESSION['name'] = $login;
          $_SESSION['supervisor_id'] = $data['supervisor_id'];
          header("Location: attendancemanagement.php");
          exit;
        }
       else {
      $_SESSION['autoError'] = "Wrong login or password";
      $_SESSION['old_fullname'] = $fullname;
      $_SESSION['old_login']    = $login;
      $_SESSION['old_password'] = $password;

      header("Location: reg_form_sup.php");
      exit;

           }
         }
       }

?>
