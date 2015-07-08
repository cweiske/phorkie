<?php
/**
 * Check if all is setup correctly
 */
namespace phorkie;
header('HTTP/1.0 500 Internal Server Error');

$reqWritePermissions = false;
require_once 'www-header.php';

if (!$GLOBALS['phorkie']['cfg']['setupcheck']) {
    header('HTTP/1.0 403 Forbidden');
    header('Content-type: text/plain');
    echo "Setup check is disabled\n";
    exit(1);
}

$messages = SetupCheck::run();
$errors = 0;
foreach ($messages as $arMessage) {
    list($type, $message) = $arMessage;
    $type == 'error' && ++$errors;
}
if ($errors == 0) {
    header('HTTP/1.0 200 OK');
}
header('Content-type: text/html');

if ($errors == 0) {
    $messages[] =  array('ok', 'All fine');
}

$out = <<<HTM
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
 <head>
  <title>phorkie setup check</title>
  <meta charset="utf-8" />
  <link rel="stylesheet" href="css/bootstrap.min.css"/>
  <link rel="stylesheet" href="css/font-awesome.css"/>
  <link rel="stylesheet" href="css/phorkie.css"/>
  <link rel="icon" href="favicon.ico"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <style type="text/css">
    /**/
    li:before {
        text-align: center;
        display: inline-block;
        width: 1em;
        padding: 0 0.5ex;
        margin-right: 0.5ex;
    }
    li.list-group-item-success:before {
        content: '✔';
        color: green;
    }
    li.list-group-item-danger:before {
        content: "✘";
        color: white;
        background-color: red;
    }
    li.list-group-item-info:before {
        content: "i";
        font-weight: bold;
        color: blue;
    }
/**/
  </style>
 </head>
 <body>
  <div class="container">
   <div class="row">
    <div class="span12">

     <div class="page-header">
      <h1>phorkie setup check</h1>
     </div>
     <h3>Check results</h3>

     <ul class="list-group">
HTM;
$stateMap = array(
    'ok'    => 'success',
    'info'  => 'info',
    'error' => 'danger'
);
foreach ($messages as $arMessage) {
    list($type, $message) = $arMessage;
    $out .= '<li class="list-group-item list-group-item-'
        . $stateMap[$type] . '">';
    $out .= htmlspecialchars($message);
    $out .= '</li>' . "\n";
}
$out .= <<<HTM
     </ul>
HTM;

if (array_sum($GLOBALS['phorkie']['cfgfiles']) == 0) {
    //no config file loaded
    reset($GLOBALS['phorkie']['cfgfiles']);
    list($cfgFilePath, ) = each($GLOBALS['phorkie']['cfgfiles']);

    $cfgFilePath = Tools::foldPath($cfgFilePath);
    $cfgFileTemplate = htmlspecialchars(
        file_get_contents(__DIR__ . '/../data/config.php.dist')
    );
    $cfgFileLines = count(explode("\n", $cfgFileTemplate));

    $out .= <<<HTM
     <h3 id="configfile">Configuration file</h3>
     <p>
      Phorkie did not find a configuration file.
      Please create one at
     </p>
     <pre>$cfgFilePath</pre>
     <p>
      from the following template:
     </p>
     <textarea style="width:99%; background-color: #F5F5F5" rows="$cfgFileLines">$cfgFileTemplate</textarea>
     <p>
      Remove the leading <tt>//</tt> from a line if you want to adjust it.
     </p>
HTM;
}

$out .= <<<HTM
     <p style="margin-top: 4ex">
      <a href="./"><i class="icon-arrow-left"></i> back</a> to the index
     </p>
    </div>
   </div>
  </div>

  <div class="container footer">
   <a href="//sf.net/p/phorkie/">phorkie</a>,
   the self-hosted, git-based pastebin software is available under the
   <a href="http://www.gnu.org/licenses/agpl-3.0.html">
    <abbr title="GNU Affero General Public License">AGPL</abbr></a>.
  </div>

 </body>
</html>
HTM;
echo $out;
?>
