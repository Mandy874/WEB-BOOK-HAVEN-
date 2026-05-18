<?php
require_once 'config.php';

// Destroy session and redirect to home
session_destroy();
redirect('index.php');