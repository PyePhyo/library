<?php
// start the session
session_start();
session_destroy();
header('Location:index.php');
include './templates/header.php';