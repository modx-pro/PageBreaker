<?php
/**
 * The base class for PageBreaker.
 */

class PageBreaker {
	/* @var modX $modx */
	public $modx;


	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = array()) {
		$this->modx =& $modx;

		$this->config = array_merge(array(
			'assetsUrl' => $this->modx->getOption('pagebreaker_assets_url', $config, $this->modx->getOption('assets_url') . 'components/pagebreaker/'),
			'frontend_js' => $this->modx->getOption('pagebreaker_frontend_js'),
			'frontend_css' => $this->modx->getOption('pagebreaker_frontend_css')
		), $config);

		$this->modx->lexicon->load('pagebreaker:default');
	}


	/**
	 * Initializes AjaxForm into different contexts.
	 *
	 * @param array $scriptProperties array with additional parameters
	 *
	 * @return boolean
	 */
	public function initialize($scriptProperties = array()) {
		$this->config = array_merge($this->config, $scriptProperties);
		if (!defined('MODX_API_MODE') || !MODX_API_MODE) {
			if ($css = trim($this->config['frontend_css'])) {
				if (preg_match('/\.css/i', $css)) {
					$this->modx->regClientCSS(str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $css));
				}
			}

			if ($this->modx->getOption('pagebreaker_ajax', null, false, true)) {
				$config_js = preg_replace(array('/^\n/', '/\t{5}/'), '', '
					pbConfig = {
						selector: "'.$this->modx->getOption('pagebreaker_ajax_selector').'"
						,prefix: "'.$this->modx->getOption('pagebreaker_page_var', null, 'p', true).'"
						,extension: "'.$this->modx->getObject('modContentType', array('mime_type' => 'text/html'))->file_extensions.'"
					};
				');
				if (file_put_contents($this->config['assetsPath'] . 'js/config.js', $config_js)) {
					$this->modx->regClientStartupScript($this->config['assetsUrl'] . 'js/config.js');
				}
				else {
					$this->modx->regClientStartupScript("<script type=\"text/javascript\">\n".$config_js."\n</script>", true);
				}

				if ($js = trim($this->config['frontend_js'])) {
					if (preg_match('/\.js/i', $js)) {
						$this->modx->regClientScript(preg_replace(array('/^\n/', '/\t{7}/'), '', '
							<script type="text/javascript">
								if(typeof jQuery == "undefined") {
									document.write("<script src=\"'.$this->config['assetsUrl'].'js/lib/jquery.min.js\" type=\"text/javascript\"><\/script>");
								}
							</script>
						'), true);
						$this->modx->regClientScript(str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $js));
					}
				}
			}
		}
		return true;
	}

}