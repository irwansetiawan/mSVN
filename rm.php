<?php

header("Content-Type: application/json");

require_once dirname(__FILE__).'/config.php';

$dirname = filter_input(INPUT_POST, 'dirname');
$res['dirname'] = $dirname;

$dirToRemove = $baseDir.'/'.$dirname;
if (file_exists($dirToRemove) && is_dir($dirToRemove)) {
    deleteDirectory($dirToRemove);
    $res['status'] = 'ok';
} else {
    $res['status'] = 'error';
    $res['error'] = $dirname.' could not be found';
}

echo json_encode($res);



function deleteDirectory($dirpath) {
    global $ignoredFoldersAndFiles;
    if (is_dir($dirpath)) {
        $contents = scandir($dirpath);
        foreach($contents as $content) {
            $childpath = $dirpath.'/'.$content;
            if (!in_array($content, $ignoredFoldersAndFiles)) {
                $deleted = is_dir($childpath) ? deleteDirectory($childpath) : deleteFile($childpath);
            }
        }
        rmdir($dirpath);
        return true;
    } else {
        return false;
    }
}

function deleteFile($filepath) {
    if (is_file($filepath)) {
        unlink($filepath);
        return true;
    } else {
        return false;
    }
}