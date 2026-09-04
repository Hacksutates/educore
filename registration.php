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
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style3.css">

<title>Registration</title>

<style>
.error {
  color: #d64545;
  font-size: 13px;
  margin-top: 6px;
}
</style>

</head>

<body>

<div class="page">
  <div class="container">

    <!-- LEFT -->
    <div id="block">
      <h2>Registration</h2>

      <h1>EduCore</h1>
      <h1>Management</h1>
      <h1>System</h1>

      <p>We love questions and feedback – and we're always happy to help!</p>

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

    <!-- RIGHT -->
    <div class="form-box">

<form method="POST" action="script.php">

<label>Full Name</label>
<input type="text" name="username" placeholder="Your name" value="<?= $fullname ?>">

<label>Choose Your Role</label>
<div class="roles">
  <label><input type="radio" name="role" value="student"> Student</label>
  <label><input type="radio" name="role" value="teacher"> Teacher</label>
</div>

<label>Login</label>
<input type="text" name="login" placeholder="Name123@kst.nis.edu.kz" value="<?= $login ?>">

<?php if ($loginError): ?>
<p class="error"><?= $loginError ?></p>
<?php endif; ?>

<label>Password</label>
<input type="password" name="pass" placeholder="Password must be at least 8 characters long">

<label>Confirm Password</label>
<input type="password" name="confirm_pass" placeholder="Enter your password again">

<?php if ($passwordError): ?>
<p class="error"><?= $passwordError ?></p>
<?php endif; ?>

<?php if ($generalError): ?>
<p class="error"><?= $generalError ?></p>
<?php endif; ?>

<?php if ($autoError): ?>
<p class="error"><?= $autoError ?></p>
<?php endif; ?>

<div class="buttons">
  <input type="submit" name="signup" value="Sign Up">
  <input type="submit" name="signin" value="Log In">
  <input type="submit" name="bio" value="Biometric Authentication">
</div>

</form>

    </div>
  </div>
</div>

</body>
</html>
