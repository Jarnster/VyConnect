<?php
session_start();
require 'includes/config.php';

// If the user is already logged in, redirect to the dashboard
if (isset($_SESSION['user']) && $_SESSION['user'] === true) {
  header('Location: index.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Check if the provided password matches the hashed password
  if (password_verify($_POST['pwd'], $PWD_HASH)) {
    $_SESSION['user'] = true;
    if (htmlspecialchars($_POST['dark_theme']) == 1) {
      $_SESSION['theme'] = "dark"; // Dark theme
    } else {
      // $_SESSION['theme'] = "light"; // Default theme
    }
    $_SESSION['loginhash'] = $PWD_HASH; // Store the already hashed password in the session
    $_SESSION['pendingChanges'] = false; // Reset pending changes state
    $_SESSION['routerIndex'] = 0; // Reset current router index
    echo "<p style='color:green;'>Password is correct. Redirecting...</p>";
    header('Location: index.php');
    exit;
  } else {
    $error_message = "Incorrect password.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login to VyConnect</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    body {
      font-family: Helvetica, sans-serif;
      background-color: rgba(32, 127, 235, 0.75);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .login-container {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 20px;
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    h1 {
      color: rgba(32, 127, 235, 0.75);
      margin-bottom: 20px;
    }

    .input-field {
      width: 95%;
      padding: 14px;
      text-align: center;
      font-size: 18px;
      border-radius: 6px;
      border: 1px solid #ddd;
      background-color: #f9f9f9;
    }

    .submit-btn {
      width: 100%;
      padding: 14px;
      background-color: rgba(32, 127, 235, 0.75);
      font-size: 18px;
      border: none;
      border-radius: 6px;
      color: #fff;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .submit-btn:hover {
      background-color: rgba(32, 127, 235, 0.75);
    }

    .error-message {
      color: rgba(32, 127, 235, 0.75);
      font-size: 16px;
      margin-top: 10px;
    }

    .icon {
      font-size: 24px;
      color: rgba(32, 127, 235, 0.75);
      margin-bottom: 10px;
    }
  </style>
</head>

<body>

  <div class="login-container">
    <div class="icon">
      <i class="fas fa-lock"></i>
    </div>

    <h2><i class="fa fa-ethernet"></i> VyConnect</h2>
    <h1>Controller UI</h1>

    <?php if (isset($error_message)) : ?>
      <div class="error-message"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="post">
      <input type="password" name="pwd" class="input-field" placeholder="Enter password" required>
      <hr>
      <input type="checkbox" name="dark_theme" value="1" id="dark_theme"><i class="fa fa-moon"></i> Use dark theme
      <hr>
      <button type="submit" class="submit-btn"><i class="fas fa-sign-in-alt"></i> Submit</button>
    </form>
  </div>

</body>

</html>