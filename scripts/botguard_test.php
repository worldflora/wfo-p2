<?php

require_once('../config.php');
require_once('../includes/BotGuard.php');

$base = 'http://localhost:1965/';
//$base = 'https://wfo-test.rbge.info/';

BotGuard::runTest($base);
