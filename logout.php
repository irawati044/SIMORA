<?php
require_once "config/auth.php";

session_unset();
session_destroy();

header("Location: " . BASE_URL . "/login.php");
exit;