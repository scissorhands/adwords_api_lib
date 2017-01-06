<?php 
require __DIR__.'/tests/Test.php';
$test = new Test();
$campaigns = $test->get_campaigns(9527366725);
print_r($campaigns);
?>