<?php 
//index de redirection pour limiter l accés aux malveillants
require_once '../composants.autoload.php';
checkAccess(['Admin','Employee']);
