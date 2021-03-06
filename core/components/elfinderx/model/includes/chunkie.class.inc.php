<?php
/*
 * Name: revoChunkie
 * Original name: Chunkie
 * Version: 1.0
 * Author: Armand "bS" Pondman (apondman@zerobarrier.nl)
 * Date: Oct 8, 2006 00:00 CET
 * Modified for Revolution by Thomas Jakobi (thomas.jakobi@partout.info)
 */

if (!class_exists('revoChunkie')) {

	class revoChunkie {

		private $template;
		private $basepath;
		private $placeholders;
		private $depth;
		private $maxdepth;

		function revoChunkie($template = '', $basepath = '') {
			global $modx;

			$this->template = $this->getTemplate($template);
			$this->depth = 0;
			$this->maxdepth = 4;
			$this->basepath = ($basepath == '') ? $modx->getOption('core_path') : $modx->getOption('base_path') . $basepath;
		}

		function setBasepath($basepath) {
			global $modx;

			$this->basepath = $basepath;
		}

		function CreateVars($value = '', $key = '', $path = '') {
			$this->depth++;
			if ($this->depth > $this->maxdepth) {
				return;
			}
			$keypath = !empty($path) ? $path . "." . $key : $key;

			if (is_array($value)) {
				foreach ($value as $subkey => $subval) {
					$this->CreateVars($subval, $subkey, $keypath);
					$this->depth--;
				}
			} else {
				$this->placeholders[$keypath] = $value;
			}
		}

		function AddVar($name, $value) {
			$this->placeholders[$name] = $value;
		}

		function Render() {
			global $modx;

			$template = $this->template;
			$chunk = $modx->newObject('modChunk');
			$chunk->setCacheable(false);
			$template = $chunk->process($this->placeholders, $template);
			unset($chunk);
			return $template;
		}

		function getTemplate($tpl) {
			global $modx;

			$template = "";

			if (substr($tpl, 0, 6) == "@FILE ") {
				$filename = substr($tpl, 6);
				if (!isset($modx->chunkieCache['@FILE'])) {
					$modx->chunkieCache['@FILE'] = array();
				}
				if (!array_key_exists($filename, $modx->chunkieCache['@FILE'])) {
					if (file_exists($this->basepath . $filename)) {
						$template = file_get_contents($this->basepath . $filename);
					}
					$modx->chunkieCache['@FILE'][$filename] = $template;
				} else {
					$template = $modx->chunkieCache['@FILE'][$filename];
				}
			} elseif (substr($tpl, 0, 8) == "@INLINE ") {
				$template = substr($tpl, 8);
			} else {
				if (substr($tpl, 0, 7) == "@CHUNK ") {
					$chunkname = substr($tpl, 7);
				} else {
					$chunkname = $tpl;
				}
				if (!isset($modx->chunkieCache['@CHUNK'])) {
					$modx->chunkieCache['@CHUNK'] = array();
				}
				if (!array_key_exists($chunkname, $modx->chunkieCache['@CHUNK'])) {
					$chunk = $modx->getObject('modChunk', array('name' => $chunkname));
					if ($chunk) {
						$modx->chunkieCache['@CHUNK'][$chunkname] = $chunk->getContent();
					} else {
						$modx->chunkieCache['@CHUNK'][$chunkname] = FALSE;
					}
				}
				$template = $modx->chunkieCache['@CHUNK'][$chunkname];
			}

			$this->template = $template;
			return $this->template;
		}

	}

}
?>
