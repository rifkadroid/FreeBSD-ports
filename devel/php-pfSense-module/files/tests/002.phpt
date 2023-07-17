--TEST--
test1() Basic test
--EXTENSIONS--
kontrol
--FILE--
<?php
$ret = test1();

var_dump($ret);
?>
--EXPECT--
The extension kontrol is loaded and working!
NULL
