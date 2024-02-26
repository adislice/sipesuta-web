<?php 

session_start();
session_unset();
session_destroy();
if(isset($_COOKIE['id'])){
    unset($_COOKIE['id']);
    setcookie("id", "", time()-3600);
}
if (isset($_COOKIE['azhdaha'])) {
    unset($_COOKIE['azhdaha']);
    setcookie("azhdaha", "", time()-3600);
}
header('location: index.php');
exit();
?>