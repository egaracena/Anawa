<?php
session_start();
session_destroy();
header("Location: /Anawa/index.php");
exit();
?>