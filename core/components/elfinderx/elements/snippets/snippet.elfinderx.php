<?php


$base_path = $modx->getOption('base_path', null, MODX_BASE_PATH);
$base_url = $modx->getOption('base_url', null, MODX_BASE_URL);

$elfinder = $modx->getService('elfinderx', 'ElfinderX', $modx->getOption('elfinderx.core_path', null, $modx->getOption('core_path') . 'components/elfinderx/') . 'model/elfinderx/', $scriptProperties);
if (!($elfinder instanceof ElfinderX))
    return '';


return $elfinder->run();


