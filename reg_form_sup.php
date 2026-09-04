<?php
session_start();
$loginError    = $_SESSION['loginError']    ?? '';
$passwordError = $_SESSION['passwordError'] ?? '';
$generalError  = $_SESSION['generalError']  ?? '';
$autoError     = $_SESSION['autoError']     ?? '';

$fullname = $_SESSION['old_fullname'] ?? '';
$login    = $_SESSION['old_login'] ?? '';
$password = $_SESSION['old_password'] ?? '';
$confirm_password = $_SESSION['old_confirm_pass'] ?? '';
$role     = $_SESSION['old_role'] ?? '';

unset(
    $_SESSION['loginError'],
    $_SESSION['passwordError'],
    $_SESSION['generalError'],
    $_SESSION['autoError'],
    $_SESSION['old_fullname'],
    $_SESSION['old_login'],
    $_SESSION['old_password'],
    $_SESSION['old_confirm_pass'],
    $_SESSION['old_role']
);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style3.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registration</title>
  </head>
<style>
.error {
  color: #d64545;
  font-size: 13px;
  margin-top: 10px;
}
</style>
<body>

  <div class="page">
    <div class="container">

<div id = "block">
      <h2>registration page for supervisors</h2>
      <h1>EduCore</h1>
      <h1>Management</h1>
      <h1>System</h1>

      <p>We love questions and feedback – and we're always happy to help!</p>
      <p>Here are some ways to contact us.</p>

      <div class="contacts">

        <div class="contacts">
          <div class="contact-card">
            <div class="icon">📧</div>
            <div class="text">
              <span class="label">Email:</span>
              <span class="value">educore@nis.kz</span>
            </div>
          </div>

          <div class="contact-card">
            <div class="icon">📞</div>
            <div class="text">
              <span class="label">Phone:</span>
              <span class="value">(123) 123-3213-23</span>
            </div>
          </div>
        </div>
      </div>

      </div>

   <div class="form-box">
    <form method = "POST" action = "script1.php">
      <label>Full Name</label><br>
      <input type = "text" name = "username" placeholder = "Your name" value = "<?= $fullname ?>" ><br>
    <label>Login</label><br>
    <input type = "text" name = "login" placeholder = "Name123@kst.nis.edu.kz" value = "<?= $login ?>" ><br>
    <?php if ($loginError): ?>
    <p class="error"><?= $loginError ?></p>
    <?php endif; ?>
    <label>Password</label><br>
    <input type = "password" name = "pass" placeholder = "Password must be at least 8 characters long" value = "<?= $password ?>" ><br>
    <label>Confirm Password</label><br>
    <input type="password" name="confirm_pass" placeholder = "Enter your password again" value = "<?= $confirm_password ?>"><br>
    <?php if ($passwordError): ?>
    <p class="error"><?= $passwordError ?></p>
    <?php endif; ?>
   <?php if ($generalError): ?>
   <p class="error"><?= $generalError ?></p>
   <?php endif; ?>
   <?php if ($autoError): ?>
   <p class="error"><?= $autoError ?></p>
   <?php endif; ?>

    <div class = "buttons">
    <input type = "submit" name = "signup" value = "Sign Up">
    <input type = "submit" name = "signin" value = "Log In">
   </div>
 </form>
</div>
</div>
</div>
  </body>
</html>
