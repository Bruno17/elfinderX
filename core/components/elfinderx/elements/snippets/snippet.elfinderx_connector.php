<?php


/**
 * Simple function to demonstrate how to control file access using "accessControl" callback.
 * This method will disable accessing files/folders starting from  '.' (dot)
 *
 * @param  string  $attr  attribute name (read|write|locked|hidden)
 * @param  string  $path  file path relative to volume root directory started with directory separator
 * @return bool|null
 **/
function accessdemo($attr, $path, $data, $volume) {
    return strpos(basename($path), '.') === 0 // if file/folder begins with '.' (dot)
        ? !($attr == 'read' || $attr == 'write') // set read+write to false, other (locked+hidden) set to true
        : null; // else elFinder decide it itself
}


$base_path = $modx->getOption('base_path', null, MODX_BASE_PATH);
$base_url = $modx->getOption('base_url', null, MODX_BASE_URL);


$elfinder = $modx->getService('elfinderx', 'ElfinderX', $modx->getOption('elfinderx.core_path', null, $modx->getOption('core_path') . 'components/elfinderx/') . 'model/elfinderx/', $scriptProperties);
if (!($elfinder instanceof ElfinderX))
    return '';

$opts = array('debug' => true, 'roots' => array(array(
            'driver' => 'LocalFileSystem', // driver for accessing file system (REQUIRED)
            'path' => $modx->getOption('assets_path') . 'uploads/', // path to files (REQUIRED)
            'URL' => $modx->getOption('assets_url') . 'uploads/', // URL to files (REQUIRED)
            'accessControl' => 'accessdemo' // disable and hide dot starting files (OPTIONAL)
                )));

print_r($opts);

return $elfinder->runConnector($opts);


