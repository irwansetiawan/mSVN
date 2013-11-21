<?php

header("Content-Type: application/json");

require_once dirname(__FILE__).'/config.php';

$dirname = filter_input(INPUT_POST, 'dirname');
$filename = filter_input(INPUT_POST, 'filename');
$res['dirname'] = $dirname;
$res['filename'] = $filename;

$outputLines = shell_exec('cd '.$baseDir.'/'.$dirname.'; '.
                          'svn revert --username '.$svnUsername.' --password '.$svnPassword.' --non-interactive '.$filename.';');

$res['status'] = 'ok';
$res['output'] = $outputLines;

echo json_encode($res);

