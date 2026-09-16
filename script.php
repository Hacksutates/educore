<?php
session_start();
include 'connection.php';

if (isset($_POST['signup'])) {

$fullname = $_POST['username'];
$login = $_POST['login'];
$password = $_POST['pass'];
$role     = $_POST['role'];
$confirm_password = $_POST['confirm_pass'];

$_SESSION['old_fullname'] = $fullname;
$_SESSION['old_login']    = $login;
$_SESSION['old_password'] = $password;
$_SESSION['old_role']     = $role;

$arr = str_split($login);
$hasError = false;
$hasErrorAuto = false;

$_SESSION['loginError'] = '';
$_SESSION['passwordError'] = '';
$_SESSION['generalError'] = '';
$_SESSION['autoError'] = '';

if ($fullname == '' || $login == '' || $password == '' || $confirm_password == '' || $role == '') {
    $_SESSION['generalError'] = "All fields must be filled";
    $hasError = true;
}

$hasAtSymbol = false;
for ($i = 0; $i < count($arr); $i++) {
    if ($arr[$i] == "@") {
        $hasAtSymbol = true;
        break;
    }
}

if (!$hasAtSymbol) {
    $_SESSION['loginError'] = "Login must contain @ symbol";
    $hasError = true;
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
    header("Location: registration.php");
    exit;
}
if ($hasError) {
  $_SESSION['old_fullname'] = $fullname;
  $_SESSION['old_login']    = $login;
  $_SESSION['old_password'] = $password;
  $_SESSION['old_role']     = $role;
    header("Location: registration.php");
    exit;
}

if (!$hasError) {

    if ($role == 'student') {
        $query1 = mysqli_query(
            $connect,
            "INSERT INTO students (StudentName, Login_Student, Password_Student)
             VALUES ('$fullname', '$login', '$password')"
        );

        if (!$query1) {
            die("Database error: " . mysqli_error($connect));
        }

        $_SESSION['name'] = $login;
        $_SESSION['StudentID'] = mysqli_insert_id($connect);
        $_SESSION['role'] = 'student';

        $_SESSION['success'] = "Registration successful";
        header("Location: schedulestudent.php");
        exit;

    } elseif ($role == 'teacher') {

        $query2 = mysqli_query(
            $connect,
            "INSERT INTO teachers (TeacherName, Login_Teacher, Password_Teacher)
             VALUES ('$fullname', '$login', '$password')"
        );

        if (!$query2) {
            die("Database error: " . mysqli_error($connect));
        }

        $_SESSION['name'] = $login;
        $_SESSION['TeacherID'] = mysqli_insert_id($connect);
        $_SESSION['role'] = 'teacher';

        $_SESSION['success'] = "Registration successful";
        header("Location: scheduleteacher.php");
        exit;
    }
}
}

if (isset($_POST['signin'])) {

    $fullname = $_POST['username'];
    $login = $_POST['login'];
    $password = $_POST['pass'];
    $confirm_password = $_POST['confirm_pass'];
    $role = $_POST['role'];

    $arr = str_split($login);

    $hasError = false;
    $hasErrorAuto = false;

    $_SESSION['old_fullname'] = $fullname;
    $_SESSION['old_login']    = $login;
    $_SESSION['old_password'] = $password;
    $_SESSION['old_role']     = $role;

    $_SESSION['name'] = $_POST['login'];

    // 🔹 Проверка пустых полей
    if ($login == '' || $password == '' || $confirm_password == '' || $role == '') {
        $_SESSION['generalError'] = "All fields must be filled";
        header("Location: registration.php");
        exit;
    }

    // 🔹 Проверка @
    if (strpos($login, '@') === false) {
        $_SESSION['loginError'] = "Login must contain @ symbol";
        $hasError = true;
    }

    //
    if ($password !== $confirm_password) {
        $_SESSION['passwordError'] = "Passwords do not match";
        $hasError = true;
    }

    //
    if ($password && strlen($password) < 8) {
        $_SESSION['passwordError'] = "Password must be at least 8 characters";
        $hasError = true;
    }

    // 
    if ($hasError) {
        header("Location: registration.php");
        exit;
    }

    // 🔹 STUDENT LOGIN
    if ($role == 'student') {

        $query2 = mysqli_query($connect,
            "SELECT Password_Student, StudentID
             FROM students
             WHERE Login_Student = '$login'"
        );

        while ($data = mysqli_fetch_assoc($query2)) {

            if ($data && $data['Password_Student'] == $password) {

                $_SESSION['name'] = $login;
                $_SESSION['StudentID'] = $data['StudentID'];
                $_SESSION['role'] = 'student';

                header("Location: schedulestudent.php");
                exit;
            }
        }

        // если не нашли
        $_SESSION['autoError'] = "Wrong login or password";
        header("Location: registration.php");
        exit;
    }

    // 🔹 TEACHER LOGIN
    if ($role == 'teacher') {

        $query2 = mysqli_query($connect,
            "SELECT Password_Teacher, TeacherID
             FROM teachers
             WHERE Login_Teacher = '$login'"
        );

        while ($data = mysqli_fetch_assoc($query2)) {

            if ($data && $data['Password_Teacher'] == $password) {

                $_SESSION['name'] = $login;
                $_SESSION['TeacherID'] = $data['TeacherID'];
                $_SESSION['role'] = 'teacher';

                header("Location: scheduleteacher.php");
                exit;
            }
        }

        $_SESSION['autoError'] = "Wrong login or password";
        header("Location: registration.php");
        exit;
    }
}




if (isset($_POST['bio'])) {
   header("Location: bioauth.html");
 }

?>
