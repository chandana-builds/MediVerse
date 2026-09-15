<?php
session_start();

// Destroy session
session_unset();
session_destroy();

// Show message + redirect
echo "<script>
alert('Logged out successfully');
window.location.href='index.php';
</script>";
exit();
?>