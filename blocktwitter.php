<?php
/**
 * Prestashop Block Twitter Module
 * @author : Kurniawan <iam@iwanwan.me>
 * @since : 2012-08-05
 **/

if (!defined('_PS_VERSION_'))
	exit;

class BlockTwitter extends Module{

	public function __construct(){

		$this->name = 'blocktwitter';
		$this->tab = 'front_office_features';
		$this->version = 0.1;
		$this->author = 'Kurniawan <iam@iwanwan.me>';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Twitter block');
		$this->description = $this->l('Adds a block that displays your Twitter Account.');
		$this->confirmUninstall = $this->l('Are you sure you want to uninstall block twitter from your page.?');

	}

	public function install(){

		if ( !parent::install() OR !$this->registerHook('leftColumn')
			|| !Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'configuration` SET `id_configuration` = null, `name` = \'PS_BT_WIDTH\', `value` = \'195\', `date_add` = NOW() ;')
			|| !Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'configuration` SET `id_configuration` = null, `name` = \'PS_BT_HEIGHT\', `value` = \'300\', `date_add` = NOW() ;')
			|| !Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'configuration` SET `id_configuration` = null, `name` = \'PS_BT_SHELL_BG\', `value` = \'#333333\', `date_add` = NOW() ;')
			|| !Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'configuration` SET `id_configuration` = null, `name` = \'PS_BT_SHELL_COLOR\', `value` = \'#ffffff\', `date_add` = NOW() ;')
			|| !Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'configuration` SET `id_configuration` = null, `name` = \'PS_BT_TWEET_BG\', `value` = \'#d0d3d8\', `date_add` = NOW() ;')
			|| !Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'configuration` SET `id_configuration` = null, `name` = \'PS_BT_TWEET_COLOR\', `value` = \'#333333\', `date_add` = NOW() ;')
			|| !Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'configuration` SET `id_configuration` = null, `name` = \'PS_BT_TWEET_LINK\', `value` = \'#007941\', `date_add` = NOW() ;')
			|| !Db::getInstance()->Execute('INSERT IGNORE INTO `'._DB_PREFIX_.'configuration` SET `id_configuration` = null, `name` = \'PS_BT_USERNAME\', `value` = \'iwanwan\', `date_add` = NOW() ;')
		){
			return false;
		}
		return true;
	}

	public function uninstall(){
		if (!parent::uninstall()
			|| !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `name` = \'PS_BT_WIDTH\'')
			|| !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `name` = \'PS_BT_HEIGHT\'')
			|| !Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'configuration` WHERE `name` = \'PS_BT_SHELL_BG\'')
			|| !Db::getInstance()->Execute('DELETE FROM  `'._DB_PREFIX_.'configuration` WHERE `name` = \'PS_BT_SHELL_COLOR\'')
			|| !Db::getInstance()->Execute('DELETE FROM  `'._DB_PREFIX_.'configuration` WHERE `name` = \'PS_BT_TWEET_BG\'')
			|| !Db::getInstance()->Execute('DELETE FROM  `'._DB_PREFIX_.'configuration` WHERE `name` = \'PS_BT_TWEET_COLOR\'')
			|| !Db::getInstance()->Execute('DELETE FROM  `'._DB_PREFIX_.'configuration` WHERE `name` = \'PS_BT_TWEET_LINK\'')
			|| !Db::getInstance()->Execute('DELETE FROM  `'._DB_PREFIX_.'configuration` WHERE `name` = \'PS_BT_USERNAME\'')
		){
			return false;
		}
		return true;
	}

	public function hookLeftColumn( $params ){
		global $smarty;
		$config = $this->getConfig();
		$smarty->assign(array(
			'config' => $config
		));
		if (!$config)
			return false;
		return $this->display( __FILE__, 'blocktwitter.tpl' );
	}

	public function hookRightColumn( $params ){
		return $this->hookLeftColumn( $params );
	}

	/**
	 * {{{ models
	 **/
	private function saveConfig($k,$v){
		$sql = '
			UPDATE '._DB_PREFIX_.'configuration
			SET
				`name`     = \''.pSQL($k).'\',
				`value`    = \''.pSQL($v).'\',
				`date_upd` = NOW()
			WHERE
				`name` = \''.pSQL($k).'\'
		';
		return (Db::getInstance()->Execute($sql));
	}
	private function getConfig() {
		$datas = array();
		/* Get  Config */
		$sql = 'SELECT a.* FROM '._DB_PREFIX_.'configuration AS a
			WHERE `name` LIKE "PS_BT_%"
			ORDER BY a.id_configuration DESC';
		if (!$datas = Db::getInstance()->ExecuteS($sql)){
			return false;
		}
		$ret = array();
		foreach($datas AS $data){
			$ret[$data['name']] = $data['value'];
		}
		return $ret;
	}
	/**
	 * }}}
	 **/


	/**
	 * {{{ Controlller
	**/
	public function getContent(){

		global $currentIndex, $cookie;

		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if(isset($_GET['act']))  $act = $_GET['act'];
		if(isset($_POST['act'])) $act = $_POST['act'];

		if (isset($act)) $cb = 'on_'.strtolower( $act );
		if (!method_exists($this, $cb)) $cb = 'on_bt_cfg';
		if (!method_exists($this, $cb)) return;

		$this->$cb();
		return $this->_html;
	}

	private function on_bt_cfg(){

		global $currentIndex, $cookie, $adminObj;
		$languages = Language::getLanguages();

		$val = $this->getConfig();
		if( Tools::isSubmit('submitSaveConfig') ){
			if (
				empty($_POST['PS_BT_USERNAME'])
				|| empty($_POST['PS_BT_WIDTH'])
				|| empty($_POST['PS_BT_HEIGHT'])
				|| empty($_POST['PS_BT_SHELL_BG'])
				|| empty($_POST['PS_BT_SHELL_COLOR'])
				|| empty($_POST['PS_BT_TWEET_BG'])
				|| empty($_POST['PS_BT_TWEET_COLOR'])
				|| empty($_POST['PS_BT_TWEET_LINK'])
			)
				$this->_html .= $this->displayError($this->l('You must fill in mandatory fields'));
			else
				if (
					$this->saveConfig('PS_BT_USERNAME', $_POST['PS_BT_USERNAME'])
					&& $this->saveConfig('PS_BT_WIDTH', $_POST['PS_BT_WIDTH'])
					&& $this->saveConfig('PS_BT_HEIGHT', $_POST['PS_BT_HEIGHT'])
					&& $this->saveConfig('PS_BT_SHELL_BG', $_POST['PS_BT_SHELL_BG'])
					&& $this->saveConfig('PS_BT_SHELL_COLOR', $_POST['PS_BT_SHELL_COLOR'])
					&& $this->saveConfig('PS_BT_TWEET_BG', $_POST['PS_BT_TWEET_BG'])
					&& $this->saveConfig('PS_BT_TWEET_COLOR', $_POST['PS_BT_TWEET_COLOR'])
					&& $this->saveConfig('PS_BT_TWEET_LINK', $_POST['PS_BT_TWEET_LINK'])
				){
					$this->_html .= $this->displayConfirmation($this->l('The Configuration has been updated.'));
					unset($_POST);
				}else{
					$this->_html .= $this->displayError($this->l('An error occurred during update, please try again.'));
				}

			$val = $this->getConfig();
		}

		$this->_html .= '<link rel="stylesheet" media="screen" type="text/css" href="'.$this->_path.'js/colorpicker/css/colorpicker.css" />';
		$this->_html .= '<script type="text/javascript" src="'.$this->_path.'js/colorpicker/js/colorpicker.js"></script>';

		$this->_html .= '
		<fieldset>
			<div style="width : 50%; float: left;">
				<form method="post" action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'">
					<label>Twitter Username</label>
					<div class="margin-form">
						<input type="text" name="PS_BT_USERNAME" id="PS_BT_USERNAME" value="'.(( isset($val['PS_BT_USERNAME'])) ? $val['PS_BT_USERNAME'] : '').'" />
						<sup> *</sup>
					</div>
					<label>Block width</label>
					<div class="margin-form">
						<input type="text" name="PS_BT_WIDTH" id="PS_BT_WIDTH" value="'.(( isset($val['PS_BT_WIDTH'])) ? $val['PS_BT_WIDTH'] : '').'" />
						<sup> *</sup>
					</div>
					<label>Block Height</label>
					<div class="margin-form">
						<input type="text" name="PS_BT_HEIGHT" id="PS_BT_HEIGHT" value="'.(( isset($val['PS_BT_HEIGHT'])) ? $val['PS_BT_HEIGHT'] : '').'" />
						<sup> *</sup>
					</div>

					<label>Shell Background</label>
					<div class="margin-form">
						<div style="float:left">
							<input type="text" name="PS_BT_SHELL_BG" id="PS_BT_SHELL_BG" value="'.(( isset($val['PS_BT_SHELL_BG'])) ? $val['PS_BT_SHELL_BG'] : '').'" />
							<sup> *</sup>
						</div>
						' . $this->colorpicker(array('divId' => 'csShellBG', 'fieldId' => 'PS_BT_SHELL_BG', 'fieldVal' => $val['PS_BT_SHELL_BG'] )) . '
					</div>

					<label>Shell Color</label>
					<div class="margin-form">
						<div style="float:left">
							<input type="text" name="PS_BT_SHELL_COLOR" id="PS_BT_SHELL_COLOR" value="'.(( isset($val['PS_BT_SHELL_COLOR'])) ? $val['PS_BT_SHELL_COLOR'] : '').'" />
							<sup> *</sup>
						</div>
						' . $this->colorpicker(array('divId' => 'csShellColor', 'fieldId' => 'PS_BT_SHELL_COLOR', 'fieldVal' => $val['PS_BT_SHELL_COLOR'] )) . '
					</div>

					<label>Tweet Background</label>
					<div class="margin-form">
						<div style="float:left">
							<input type="text" name="PS_BT_TWEET_BG" id="PS_BT_TWEET_BG" value="'.(( isset($val['PS_BT_TWEET_BG'])) ? $val['PS_BT_TWEET_BG'] : '').'" />
							<sup> *</sup>
						</div>
						' . $this->colorpicker(array('divId' => 'csTweetBg', 'fieldId' => 'PS_BT_TWEET_BG', 'fieldVal' => $val['PS_BT_TWEET_BG'] )) . '
					</div>

					<label>Tweet Color</label>
					<div class="margin-form">
						<div style="float:left">
							<input type="text" name="PS_BT_TWEET_COLOR" id="PS_BT_TWEET_COLOR" value="'.(( isset($val['PS_BT_TWEET_COLOR'])) ? $val['PS_BT_TWEET_COLOR'] : '').'" />
							<sup> *</sup>
						</div>
						' . $this->colorpicker(array('divId' => 'csTweetColor', 'fieldId' => 'PS_BT_TWEET_COLOR', 'fieldVal' => $val['PS_BT_TWEET_COLOR'] )) . '
					</div>

					<label>Tweet Link</label>
					<div class="margin-form">
						<div style="float:left">
							<input type="text" name="PS_BT_TWEET_LINK" id="PS_BT_TWEET_LINK" value="'.(( isset($val['PS_BT_TWEET_LINK'])) ? $val['PS_BT_TWEET_LINK'] : '').'" />
							<sup> *</sup>
						</div>
						' . $this->colorpicker(array('divId' => 'csTweeLink', 'fieldId' => 'PS_BT_TWEET_LINK', 'fieldVal' => $val['PS_BT_TWEET_LINK'] )) . '
					</div>

					<div class="margin-form">
						<input type="hidden" name="act" id="act" value="configure" />
						<input type="submit" class="button" name="submitSaveConfig" value="'.$this->l('Save Configuration').'" />
					</div>
				</form>
			</div>
			<div>
				<div style=\'margin-left: auto; margin-right: auto;width: ' . $val['PS_BT_WIDTH'] . 'px;\'>
					<script charset="utf-8" src="http://widgets.twimg.com/j/2/widget.js"></script>
					<script>
					var tw = ' . $val['PS_BT_WIDTH'] . ' - 10;
					new TWTR.Widget({
					  version: 2,
					  type: \'profile\',
					  rpp: 10,
					  interval: 30000,
					  width: tw,
					  height: ' . $val['PS_BT_HEIGHT'] . ',
					  theme: {
						shell: {
						  background: \'' . $val['PS_BT_SHELL_BG'] . '\',
						  color: \'' . $val['PS_BT_SHELL_COLOR'] . '\'
						},
						tweets: {
						  background: \' ' . $val['PS_BT_TWEET_BG'] .'\',
						  color: \'' . $val['PS_BT_TWEET_COLOR'] . '\',
						  links: \'' . $val['PS_BT_TWEET_LINK'] . '\'
						}
					  },
					  features: {
						scrollbar: false,
						loop: false,
						live: true,
						behavior: \'default\'
					  }
					}).render().setUser(\' ' . $val['PS_BT_USERNAME'] .'\').start();
					</script>
				</div>
			</div>
		</fieldset>
		';

	}

	/**
	 * Generate colorpicker
	 * @param array(
		'divId'
		'fieldId'
		'fieldVal'
	 )
	 **/
	private function colorpicker($params){

		$ret = '
		<div id="' . $params['divId'] . '" style="width: 20px; height: 20px;margin-left: 150px;">
			<div style="width: 18px; height: 18px; border:1px solid #000; background-color: '.(( isset( $params['fieldVal'] )) ? $params['fieldVal'] : '').'"></div>
		</div>
		<script type="text/javascript">
		$(\'#' . $params['divId'] . '\').ColorPicker({
			color: \''.( isset( $params['fieldVal'] ) ? $params['fieldVal'] : '').'\',
			onShow: function (colpkr) {
				$(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				$(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				$(\'#' . $params['divId'] . ' div\').css(\'backgroundColor\', \'#\' + hex);
				$(\'#' . $params['fieldId'] . '\').val(\'#\' + hex);
			}
		});
		</script>
		';

		return $ret;

	}
	/**
	 * }}}
	**/

}