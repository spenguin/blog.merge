<?php
// settings
$publicDir = ABSPATH;
$tmpDir = BLOGUPLOADDIR.'/tmpdata';
if (!is_dir(BLOGUPLOADDIR)) mkdir(BLOGUPLOADDIR);
if (!is_dir($tmpDir)) mkdir($tmpDir);

$baseUri='/filepress';
// Create the root node
$root = new Sabre_DAV_FS_Directory($publicDir);

// The rootnode needs in turn to be passed to the server class
$server = new Sabre_DAV_Server($root);

if (isset($baseUri))
    $server->setBaseUri($baseUri);

// Support for LOCK and UNLOCK 
$lockBackend = new Sabre_DAV_Locks_Backend_File($tmpDir . '/locksdb');
$lockPlugin = new Sabre_DAV_Locks_Plugin($lockBackend);
$server->addPlugin($lockPlugin);

// Support for html frontend
$browser = new Sabre_DAV_Browser_Plugin();
$server->addPlugin($browser);

// Authentication backend
$authBackend = new Sabre_DAV_Auth_Backend_Wordpress();
$auth = new Sabre_DAV_Auth_Plugin($authBackend,'SabreDAV');
$server->addPlugin($auth);

// Temporary file filter
$tempFF = new Sabre_DAV_TemporaryFileFilterPlugin($tmpDir);
$server->addPlugin($tempFF);

// And off we go!
$server->exec();

