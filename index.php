<?php

$configFile = dirname(__FILE__).'/config.php';

if (!file_exists($configFile)) {
    die('Unable to find config.php! Please copy from config.php-example');
}

require_once dirname(__FILE__).'/init.php';
require_once $configFile;

$contents = scandir($baseDir);

$homeDirs = array();
foreach($contents as $content) {
    if (!in_array($content, $ignoredFoldersAndFiles) && is_dir($baseDir.'/'.$content) 
        && file_exists($baseDir.'/'.$content.'/'.$projectInitFile)) {
        $homeDirs[] = array(
            'path' => $baseDir.'/'.$content,
            'dir_name' => $content
        );
    }
}

foreach($homeDirs as $idx => $homeDir) {
    $outputLines = array();
    exec('cd '.$homeDir['path'].'; svn info;', $outputLines);
    foreach($outputLines as $output) {
        if (preg_match('/^URL\:\s?(.*)$/i', $output, $matches)) {
            $svnUrl = $matches[1];
            $homeDirs[$idx]['svn_url'] = $svnUrl;
            $branchName = 'trunk';
            if (preg_match('#/branches/([^\/]*)#i', $svnUrl, $matches)) {
                $branchName = $matches[1];
            }
            $homeDirs[$idx]['branch_name'] = $branchName;
            $homeDirs[$idx]['site_url'] = strtolower(sprintf("http://%s.$mainHost", $branchName));
        }
        else if (preg_match('/^Revision\:\s?(\d+)$/i', $output, $matches)) {
            $revision = $matches[1];
            $homeDirs[$idx]['rev'] = $revision;
        }
    }
    $outputLines = array();
    exec('cd '.$homeDir['path'].'; svn stat;', $outputLines);
    foreach($outputLines as $idx => $output) {
        if (preg_match('#^\?\s+'.$projectInitFile.'$#', $output) {
            unset($outputLines[$idx]);
        }
    }
    $homeDirs[$idx]['changes'] = $outputLines;
}


?>
<!doctype html>
<head>
    <title>SVN</title>
    <meta charset="utf-8" />
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>
    <style>
        #container { margin-top: 20px; }
        #svn-checkout { margin-bottom: 40px; }
        body #checkout-modal.modal { width: 800px; margin-left: -400px; }
        #checking-out { font-size: 1.2em; }
        ul#branches { list-style: none; padding-left: 0; margin: 0; }
        ul#branches li.branch { margin: 10px 0 20px; padding: 10px 20px; border-top: 1px solid #dddddd;
            background: #f6f6f6; position: relative; }
        ul#branches li.branch:hover { background: #ffffbb; }
        ul#branches li.branch h3 { margin: 0; }
        ul#branches li.branch .branch-commands { display: none; position: absolute; right: 15px; top: 10px; }
        ul#branches li.branch:hover .branch-commands { display: block; }
        ul#branches li.branch > a { }
        .small { font-size: 12px; }
        .mini { font-size: 10px; }
    </style>

    <script>
        $(document).ready(function() {
            $('#checkout-btn').click(function(e) {
                e.preventDefault();
                $('#checkout-modal').modal().css({
                    width: 'auto',
                    'margin-left': function () {
                        return -($(this).width() / 2);
                    }
                });
            }) ;
            $('.svnup-btn').click(function(e) {
                e.preventDefault();
                var dirname = $(this).closest('.branch').data('dirname');
                $.post('/up.php', 'dirname='+dirname, function(res) {
                    if (res.status == 'error') {
                        alert(res.error);
                    } else {
                        window.location.reload();
                    }
                }, 'json');
            });
            $('.trash-btn').click(function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to remove this branch?')) {
                    var dirname = $(this).closest('.branch').data('dirname');
                    $.post('/rm.php', 'dirname='+dirname, function(res) {
                        if (res.status == 'error') {
                            alert(res.error);
                        } else {
                            $('#branch-'+dirname).remove();
                        }
                    }, 'json');
                }
            });
            $('.svnrevert-btn').click(function(e) {
                e.preventDefault();
                var dirname = $(this).closest('.branch').data('dirname');
                var filename = $(this).closest('.file').data('filename');
                $.post('/revert.php', 'dirname='+dirname+'&filename='+filename, function(res) {
                    if (res.status == 'error') {
                        alert(res.error);
                    } else {
                        window.location.reload();
                    }
                }, 'json');
            });
            $('.svndiff-btn').click(function(e) {
                e.preventDefault();
                var dirname = $(this).closest('.branch').data('dirname');
                var filename = $(this).closest('.file').data('filename');
                $.post('/diff.php', 'dirname='+dirname+'&filename='+filename, function(res) {
                    if (res.status == 'error') {
                        alert(res.error);
                    } else {
                        $('#diff-modal').find('h3').html('SVN Diff: '+filename);
                        $('#diff-modal').find('pre').html(res.diff);
                        $('#diff-modal').modal().css({
                            width: 'auto',
                            'margin-left': function () {
                                return -($(this).width() / 2);
                            }
                        });
                    }
                }, 'json');
            });
        });

        function svnCheckout(form) {
            var svnUrl = form.svnurl.value;
            $('#checking-out').show();
            $.post('/checkout.php', 'svnurl='+svnUrl, function(res) {
                if (res.status == 'error') {
                    alert(res.error);
                } else {
                    window.location.reload();
                }
                $('#checking-out').hide();
            }, 'json');
            $('#checkout-modal').modal('hide');
        }
    </script>
</head>

<body>

<!--
<?php print_r($homeDirs); ?>
-->

    <div id="checkout-modal" class="modal hide fade">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>SVN Checkout</h3>
        </div>
        <div class="modal-body">
            <form id="svncheckout-form" method="POST" action="" class="form-horizontal" onsubmit="svnCheckout(this); return false;">
                <div class="control-group">
                    <label class="control-label" for="svnurl">SVN URL</label>
                    <div class="controls">
                        <input type="text" id="svnurl" class="input-xxlarge" name="svnurl" placeholder="http://" value="<?php echo $baseRepoUri ?>">
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <a href="#" class="btn" class="close" data-dismiss="modal">Close</a>
            <a href="#" class="btn btn-primary" onclick="$('#svncheckout-form').submit(); return false;">SVN Checkout</a>
        </div>
    </div>

    <div id="diff-modal" class="modal hide fade">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3>SVN Diff</h3>
        </div>
        <div class="modal-body">
            <pre>
            </pre>
        </div>
        <div class="modal-footer">
            <a href="#" class="btn" class="close" data-dismiss="modal">Close</a>
        </div>
    </div>

    <div id="container" class="container">
        <div class="row-fluid">
            <div class="span12">

                <div id="svn-checkout" class="text-right">
                    <span id="checking-out" class="hide">
                        <i class="icon-spinner icon-spin icon-large"></i>
                        Please wait... Checking out...
                    </span>
                    <button id="checkout-btn" class="btn btn-primary btn-large">
                        <i class="icon-download-alt"></i> &nbsp;SVN Checkout
                    </button>
                </div>

                <ul id="branches">

                    <?php foreach($homeDirs as $homeDir) { ?>
                    <li id="branch-<?php echo $homeDir['dir_name'] ?>" class="branch" data-dirname="<?php echo $homeDir['dir_name'] ?>">

                        <h4>
                            <span class="badge badge-warning"><?php echo $homeDir['rev'] ?></span> &nbsp;
                            <a href="<?php echo $homeDir['site_url'] ?>">
                                <?php echo $homeDir['branch_name'] ?></a>
                            <span class="small">
                                <?php echo strtolower($homeDir['branch_name']) == strtolower($homeDir['dir_name']) ? '' 
                                                : ('('.$homeDir['dir_name'].')') ?>
                            </span>
                        </h4>

                        <?php if (!empty($homeDir['changes'])) {  ?>
                        Local Changes:<br>
                        <ul class="changes">
                            <?php foreach($homeDir['changes'] as $change) { ?>
                            <li class="file" data-filename="<?php if (preg_match('/([^\s]+)$/i', $change, $matches)) { echo $matches[1]; } ?>">
                                <?php echo $change ?> &nbsp;
                                <a href="#" class="svndiff-btn"><i class="icon-file-text-alt"></i></a> &nbsp;
                                <a href="#" class="svnrevert-btn"><i class="icon-undo"></i></a>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>

                        <div class="branch-commands">
                            <h4>
                                <a href="#" class="svnup-btn" title="SVN up"><i class="icon-upload-alt"></i></a> &nbsp;
                                <a href="#" class="trash-btn" title="Remove branch"><i class="icon-trash"></i></a>
                            </h4>
                        </div>

                    </li>
                    <?php } ?>

                </ul>

            </div> <!-- .span12 -->
        </div> <!-- .row-fluid -->
    </div> <!-- .container -->

</body>
</html>