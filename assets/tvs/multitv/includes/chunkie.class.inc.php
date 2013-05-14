<?php
####
#
#	Name: Chunkie
#	Version: 1.0
#	Author: Armand "bS" Pondman (apondman@zerobarrier.nl)
#	Date: Oct 8, 2006 00:00 CET
#
####

if (!class_exists('evoChunkie')) {

	class evoChunkie {

		private $template;
		private $phx;
		private $phxreq;
		private $phxerror;
		private $check;

		public function evoChunkie($template = '', $templates = array()) {
			global $modx;

			if (!class_exists("PHxParser")) {
				include_once(strtr(realpath(dirname(__FILE__)) . "/phx.parser.class.inc.php", '\\', '/'));
			}
			if (!isset($modx->evoChunkieCache)) {
				$modx->evoChunkieCache = $templates;
			} elseif (count($templates)) {
				$modx->evoChunkieCache = array_merge($modx->evoChunkieCache, $templates);
			}
			$this->template = $this->getTemplate($template);
			$this->phx = new PHxParser();
			$this->phxreq = "2.0.0";
			$this->phxerror = '<div style="border: 1px solid red;font-weight: bold;margin: 10px;padding: 5px;">';
			$this->phxerror .= 'Error! This MODx installation is running an older version of the PHx plugin.<br /><br />';
			$this->phxerror .= 'Please update PHx to version ' . $this->phxreq . ' or higher.<br />OR - Disable the PHx plugin in the MODx Manager. (Manage Resources -> Plugins)';
			$this->phxerror .= '</div>';
			$this->check = ($this->phx->version < $this->phxreq) ? 0 : 1;
		}

		public function CreateVars($value = '', $key = '', $path = '') {
			$keypath = !empty($path) ? $path . "." . $key : $key;
			if (is_array($value)) {
				foreach ($value as $subkey => $subval) {
					$this->CreateVars($subval, $subkey, $keypath);
				}
			} else {
				$this->phx->setPHxVariable($keypath, $value);
			}
		}

		public function AddVar($name, $value) {
			if ($this->check)
				$this->CreateVars($value, $name);
		}

		public function Render() {
			if (!$this->check) {
				$template = $this->phxerror;
			} else {
				$template = $this->phx->Parse($this->template);
			}
			return $template;
		}

		public function getTemplate($tpl) {
			// by Mark Kaplan
			global $modx;
			$template = "";
			if (isset($modx->evoChunkieCache[$tpl])) {
				$template = $modx->evoChunkieCache[$tpl];
			} else {
				if ($modx->getChunk($tpl) != "") {
					$template = $modx->getChunk($tpl);
				} else if (substr($tpl, 0, 6) == "@FILE:") {
					$template = file_get_contents(MODX_BASE_PATH . trim(substr($tpl, 6)));
				} else if (substr($tpl, 0, 6) == "@CODE:") {
					$template = trim(substr($tpl, 6));
				} else if (substr($tpl, 0, 5) == "@FILE") {
					$template = file_get_contents(MODX_BASE_PATH . trim(substr($tpl, 5)));
				} else if (substr($tpl, 0, 5) == "@CODE") {
					$template = trim(substr($tpl, 5));
				} else {
					$template = FALSE;
				}
				$modx->evoChunkieCache[$tpl] = $template;
			}
			$this->template = $template;
			return $template;
		}

	}

}
?>
