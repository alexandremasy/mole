<?php 
	
	require('../libs/Mole.php');

	$root = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR .'sample';
	$flag = dirname($root).DIRECTORY_SEPARATOR.'build/build.flag';

	$demo = new Mole( 'statics/libs/demo.min.js', $root, null, true );
	$demo->add('statics/libs/demo/hello.js');
	$demo->add('statics/libs/demo');
	$demo->write( Mole::CLOSURE, $flag );
	echo $demo->build( Mole::HTML );
?>