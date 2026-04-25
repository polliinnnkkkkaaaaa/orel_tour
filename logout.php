<?php
require_once 'includes/functions.php';
session_destroy();
session_start();
set_flash('success', 'Вы вышли из аккаунта.');
redirect('login.php');
