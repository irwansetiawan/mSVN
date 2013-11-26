<?php

header("Content-Type: application/json");

require_once dirname(__FILE__).'/init.php';
require_once dirname(__FILE__).'/config.php';

$svnUrl = filter_input(INPUT_POST, 'svnurl');
$res['svnurl'] = $svnUrl;

$projectName = $branchName = NULL;

// check if svn url format is correct
if (preg_match('/\/([^\/]*)\/trunk$/', $svnUrl, $matches)) { // is trunk copy
	$projectName = $matches[1];
	$branchName = 'trunk';
} else if (preg_match('/\/([^\/]*)\/branches\/([^\/]*)\/?$/', $svnUrl, $matches)) { // is branch copy
	$projectName = $matches[1];
	$branchName = $matches[2];
} else {
	$res['status'] = 'error';
	$res['error'] = 'Invalid SVN URL';
}

if (!empty($projectName) && !empty($branchName)) {
	$dirname = strtolower($projectName.'-'.$branchName);
	$res['dirname'] = $dirname;

	$outputLines = shell_exec('cd '.$baseDir.'; '.
	                          'svn checkout --username '.$svnUsername.' --password '.$svnPassword.' --non-interactive '.
	                                        $svnUrl.' '.$dirname.' 2>&1;');

	shell_exec('cd '.$baseDir.'; '.
	           'touch '.$dirname.'/'.$projectInitFile);

	$res['status'] = 'ok';
	$res['output'] = $outputLines;
}

echo json_encode($res);

