<?php
define('FM_EMBED', true);
define('FM_SELF_URL', $_SERVER['PHP_SELF']);
require 'error_404.php';//problem is here LINE 279
//$data= file_get_contents($tpl_file);
//$html_encoded = htmlentities($data);
//echo $html_encoded;