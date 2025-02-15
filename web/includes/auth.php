<?php
// Check if the user is logged in and if the session hash matches
if (!isset($_SESSION['user']) || !isset($_SESSION['loginhash'])) {
    header('Location: /login.php');
    exit("Not signed in.");
}
