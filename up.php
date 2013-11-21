<?php

header("Content-Type: application/json");

require_once dirname(__FILE__).'/config.php';

$dirname = filter_input(INPUT_POST, 'dirname');
$res['dirname'] = $dirname;

$outputLines = shell_exec('cd '.$baseDir.'/'.$dirname.'; '.
                          'svn up --username '.$svnUsername.' --password '.$svnPassword.' --non-interactive '.
                                 '--force --accept mine-conflict 2>&1;');

$res['status'] = 'ok';
$res['output'] = $outputLines;

echo json_encode($res);

