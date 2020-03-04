<?php
session_start();
// $_SESSION['user_id'] = '';
unset($_SESSION['user_id']);
unset($_SESSION['username']);
session_destroy();
echo "<script>window.location='login.php'</script>";
?>