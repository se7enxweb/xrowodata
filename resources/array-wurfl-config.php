<?php

/*
 * Example of WURFL PHP API Array-based Configuration
 */

$configuration = array(
	// WURFL File Configuration
	'wurfl' => array(
		'main-file' => 'wurfl.zip',
		'patches' => array("web_browsers_patch.xml"),
	),
	// Persistence (Long-Term Storage) Configuration
	'persistence' => array(
		'provider' => 'file',
		'dir' => eZSys::cacheDirectory() . '/wurfl/persistence',
	),
	// Cache (Short-Term Storage) Configuration
	'cache' => array(
		'provider' => 'file',
	    'dir' => eZSys::cacheDirectory() . '/wurfl/cache'
	),
);