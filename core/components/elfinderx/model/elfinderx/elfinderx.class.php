<?php

/*
* Yass
* 
* Copyright 2012 by Thomas Jakobi <thomas.jakobi@partout.info>
* 
* Yass is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the Free
* Software Foundation; either version 2 of the License, or (at your option) any
* later version.
*
* Yass is distributed in the hope that it will be useful, but WITHOUT 
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
* FOR A PARTICULAR PURPOSE. See the GNU General Public License for more 
* details.
*
* You should have received a copy of the GNU General Public License along with
* Yass; if not, write to the Free Software Foundation, Inc., 
* 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*
* @package yass
* 
* Yass modX service class.
*/


if (!class_exists('ElfinderX')) {

    class ElfinderX {

        // public
        public $output = array();
        // private
        private $modx;

        function __construct(modX & $modx, array $config = array()) {
            $this->modx = &$modx;

            /* allows you to set paths in different environments
            * this allows for easier SVN management of files
            */
            $corePath = $this->modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/elfinderx/');
            $assetsPath = $this->modx->getOption('migx.assets_path', null, $modx->getOption('assets_path') . 'components/elfinderx/');
            $assetsUrl = $this->modx->getOption('migx.assets_url', null, $modx->getOption('assets_url') . 'components/elfinderx/');

            $defaultconfig['scriptProperties'] = $config;

            $defaultconfig['debugUser'] = '';
            $defaultconfig['corePath'] = $corePath;
            $defaultconfig['modelPath'] = $corePath . 'model/';
            $defaultconfig['processorsPath'] = $corePath . 'processors/';
            $defaultconfig['templatesPath'] = $corePath . 'templates/';
            $defaultconfig['controllersPath'] = $corePath . 'controllers/';
            $defaultconfig['chunksPath'] = $corePath . 'elements/chunks/';
            $defaultconfig['snippetsPath'] = $corePath . 'elements/snippets/';
            $defaultconfig['elPath'] = $corePath . 'model/includes/elfinder/php/';
            $defaultconfig['chunkiePath'] = $corePath . 'model/includes/';
            $defaultconfig['auto_create_tables'] = true;
            $defaultconfig['baseUrl'] = $assetsUrl;
            $defaultconfig['cssUrl'] = $assetsUrl . 'css/';
            $defaultconfig['jsUrl'] = $assetsUrl . 'js/';
            $defaultconfig['jsPath'] = $assetsPath . 'js/';
            $defaultconfig['connectorUrl'] = $assetsUrl . 'connector.php';
            $defaultconfig['request'] = $_REQUEST;
            $defaultconfig['mode'] = 'output';
            $defaultconfig['defaultroot'] = '{"base_url":"' . $this->modx->getOption('assets_url') . '","base_path":"' . $this->modx->getOption('assets_path') .
                '","rootpath":"","hideurl":"","driver":"LocalFileSystem","accessControl":"accessdemo"}';

            $this->config = array_merge($defaultconfig, $config);

            if (!class_exists('revoChunkie')) {
                include $this->config['chunkiePath'] . 'chunkie.class.inc.php';
            }


        }

        function regScripts() {

            $config = array();
            $script = $this->modx->getOption('scriptTpl', $this->config, '');
            $defaultScript = '@FILE ' . $this->config['chunksPath'] . 'script.elfinder.init.html';

            if (!empty($script)) {
                $scriptTpl = file_exists($scriptTpl) ? '@FILE ' . $script : '';
                $scriptTpl = empty($scriptTpl) && file_exists($this->config['chunksPath'] . $script) ? '@FILE ' . $this->config['chunksPath'] . $script : $scriptTpl;
                $scriptTpl = empty($scriptTpl) && file_exists($this->config['chunksPath'] . 'script.elfinder.init.' . $script . '.html') ? '@FILE ' . $this->config['chunksPath'] . 'script.elfinder.init.' . $script .
                    '.html' : $scriptTpl;
                $scriptTpl = empty($scriptTpl) ? $script : $scriptTpl;
            } else {
                $scriptTpl = $defaultScript;
            }


            /*
            <link rel="stylesheet" type="text/css" media="screen" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css">
            <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
            <script type="text/javascript" src="http://code.jquery.com/ui/1.9.1/jquery-ui.js"></script>
            */

            $this->modx->regClientCSS('assets/components/elfinderx/css/elfinder.min.css');
            $this->modx->regClientCSS('assets/components/elfinderx/css/theme.css');
            $this->modx->regClientCSS('http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css');

            $this->modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js');
            $this->modx->regClientStartupScript('http://code.jquery.com/ui/1.9.1/jquery-ui.js');

            //$this->modx->regClientScript('assets/components/elfinderx/js/elfinder.js');
            $this->modx->regClientScript('assets/components/elfinderx/js/elfinder.min.js');
            $this->modx->regClientScript('assets/components/elfinderx/js/i18n/elfinder.de.js');

            $parser = new revoChunkie($scriptTpl);
            $parser->createVars($this->config);
            $script = $parser->Render();

            $this->modx->regClientScript($script);


        }

        function createOutput() {
            $this->regScripts();
            return '<div id="elfinder"></div>';
        }

        function initElfinder() {
            if (isset($this->modx->elfinder)) return;
            
            $roots = $this->modx->getOption('roots', $this->config, array(array()));
            $roots = $this->modx->fromJson($this->config['roots']);
            $defaultroot = $this->modx->fromJson($this->config['defaultroot']);
            $tmproots = array();
            $pathcollection = array();
            $tmbPath = '.tmb/';

            foreach ($roots as $root) {
                foreach ($defaultroot as $key => $value) {
                    $root[$key] = array_key_exists($key, $root) ? $root[$key] : $value;
                }
                $root['path'] = $root['base_path'] . $root['rootpath']; // path to files (REQUIRED)
                //collect pathes
                $pathcollection[] = $root['path'];

                if (empty($root['hideurl'])) {
                    $root['URL'] = $root['base_url'] . $root['rootpath']; // URL to files (REQUIRED)
                }
                if (isset($root['thumbPath'])) {
                    $root['tmbURL'] = $this->modx->getOption('base_url') . $root['thumbPath']; // URL to files (REQUIRED)
                    $root['tmbPath'] = $this->modx->getOption('base_path') . $root['thumbPath']; // URL to files (REQUIRED)
                }


                unset($root['hideurl'], $root['base_path'], $root['base_url'], $root['rootpath'], $root['thumbPath']);
                $tmproots[] = $root;
            }
            $roots = $tmproots;
            unset($tmproots);

            error_reporting(0); // Set E_ALL for debuging

            include_once $this->config['elPath'] . 'elFinderConnector.class.php';
            include_once $this->config['elPath'] . 'elFinder.class.php';
            include_once $this->config['elPath'] . 'elFinderVolumeDriver.class.php';
            include_once $this->config['elPath'] . 'elFinderVolumeLocalFileSystem.class.php';
            // Required for MySQL storage connector
            // include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php';
            // Required for FTP connector support
            // include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php';

            $opts = $this->config[$scriptProperties];
            $opts['roots'] = $roots;

            // run elFinder
            $this->modx->elfinder = new elFinder($opts);
        }

        function runConnector() {
            
            $this->initElfinder();
            $connector = new elFinderConnector($this->modx->elfinder);
            $connector->run();

        }

        /**
         * Return file real path
         *
         * @param  string  $hash  file hash
         * @return string
         * @author Dmitry (dio) Levashov
         **/
        public function realpath($hash) {
            $this->initElfinder();
            return $this->modx->elfinder->realpath($hash);
        }

        function run() {

            switch ($this->config['mode']) {
                case 'output':
                    return $this->createOutput();
                    break;
            }


        }

    }


}

?>