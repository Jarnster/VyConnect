<?php
require 'config.php';
session_start();

// Check if the user is logged in and if the session hash matches
if (!isset($_SESSION['user']) || !isset($_SESSION['loginhash'])) {
    header('Location: login.php');
    exit("Not signed in.");
}

// Use password_verify to check if the session hash matches the stored password hash
if (!password_verify($ADMIN_PWD, $_SESSION['loginhash'])) {
    // Destroy session if hash does not match
    unset($_SESSION['user']);
    unset($_SESSION['loginhash']);
    session_destroy();
    header('Location: login.php');
    exit("You need to login again.");
}
?>
<title>VyConnect - Local UI</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
<link rel="stylesheet" href="assets/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<?php
if (isset($_SESSION['theme'])) {
    if ($_SESSION['theme'] == 'dark') {
        echo '<link rel="stylesheet" href="assets/themes/' . $_SESSION['theme'] . '.css">';
    }
}
?>