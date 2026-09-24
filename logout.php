<?php
require_once __DIR__ . '/includes/auth.php';

logoutCustomer();
redirectTo('login.php?logged_out=1');
