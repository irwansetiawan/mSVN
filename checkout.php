<?php

header("Content-Type: application/json");

require_once dirname(__FILE__).'/config.php';

$svnUrl = filter_input(INPUT_POST, 'svnurl');
$res['svnurl'] = $svnUrl;

$dirname = strtolower(basename($svnUrl));
$res['dirname'] = $dirname;

$outputLines = shell_exec('cd '.$baseDir.'; '.
                          'svn checkout --username '.$svnUsername.' --password '.$svnPassword.' --non-interactive '.
                                        $svnUrl.' '.$dirname.' 2>&1;');

$res['status'] = 'ok';
$res['output'] = $outputLines;


echo json_encode($res);

