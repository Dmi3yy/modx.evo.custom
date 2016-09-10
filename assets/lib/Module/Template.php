<?php namespace Module;

abstract class Template{
    protected $_modx = null;
    protected $_tplFolder = null;
	protected $_publicFolder;
    const TPL_EXT = 'html';

    public $vars = array(
        'modx_lang_attribute',
        'modx_textdir',
        'manager_theme',
        'modx_manager_charset',
        '_lang',
        '_style',
        'e',
        'SystemAlertMsgQueque',
        'incPath',
        'content'
    );
    protected static $_ajax = false;

    public function __construct(\DocumentParser $modx, $ajax = false, $tplFolder = null){
        $this->_modx = $modx;
        self::$_ajax = (boolean) $ajax;
        $this->loadVars();
        if(is_null($tplFolder)){
		  $tplFolder = dirname(dirname(__FILE__));
        }
        $FS = \Helpers\FS::getInstance();
        $tplFolder = $FS->relativePath($tplFolder);

		$this->_publicFolder = "/".$tplFolder."/public/";
        $this->_tplFolder = MODX_BASE_PATH.$tplFolder."/template/";

        if(!defined('MODX_MAIN_URL')){
            define('MODX_MAIN_URL', MODX_SITE_URL);
        }
    }
    public static function isAjax(){
        return self::$_ajax;
    }
	public function publicFolder(){
		return $this->_publicFolder;
	}
	public function src($path){
		return rtrim(MODX_MAIN_URL, '/').$this->publicFolder().ltrim($path, '/');
	}

    public function showHeader(){
        return $this->_getMainTpl('header.inc.php');
    }
    protected function _getMainTpl($name){
        $content = '';
        if( ! self::isAjax()){

            ob_start();
            extract($this->vars);
            if(file_exists($incPath . $name)){
                include($incPath . $name);
                $content = ob_get_contents();
            }
            ob_end_clean();
        }
        return $content;
    }
    public function loadVars(){
        $vars = array();
        foreach($this->vars as $item){
            global $$item;
            $vars[$item] = $$item;
        }
        $this->vars = $vars;
        $this->vars['tplClass'] = $this;
        $this->vars['modx'] = $this->_modx;
    }
    public function showFooter(){
        return $this->_getMainTpl('footer.inc.php');
    }
    public function showBody($TplName, array $tplParams = array()){
        ob_start();
        if(file_exists($this->_tplFolder.$TplName.".".self::TPL_EXT)){
            extract($this->vars);
            include($this->_tplFolder.$TplName.".".self::TPL_EXT);
        }
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    public static function getParam($key, array $param = array(), $default = null){
        return isset($param[$key]) ? $param[$key] : $default;
    }
    public function makeUrl($action, array $data = array(), $module = null, $full = false){
        $action = is_scalar($action) ? $action : '';
        $content = self::getParam('content', $this->vars, array());
        $data = array_merge(
            array(
                'mode' => Helper::getMode()
            ),
            $data,
            array(
                'a' => 112,
                'action' => $action,
                'id' => empty($module) ? self::getParam('id', $content, 0) : (int)$module
            )
        );
		$out = implode("?", array($this->_modx->getManagerPath(), http_build_query($data)));
		if ($full) {
			$out = $this->_modx->getConfig('site_url') . ltrim($out, '/');
		}
        return $out;
    }
    public static function showLog(){
        return self::isAjax() ? 'log' : 'main';
    }
    abstract public function Lists();
}