<?php

/*

 +-----------------------------------------------------------------------+
 | PostfixAdmin Purge Plugin for RoundCube                               |
 | Version: 0.7.2                                                        |
 | Author: Gianluca Giacometti <php@gianlucagiacometti.it>               |
 | Copyright (C) 2012 Gianluca Giacometti                                |
 | License: GNU General Public License                                   |
 +-----------------------------------------------------------------------+

 code structure based on:

 +-----------------------------------------------------------------------+
 | Vacation Module for RoundCube                                         |
 | Copyright (C) 2009 Boris HUISGEN <bhuisgen@hbis.fr>                   |
 | Licensed under the GNU GPL                                            |
 +-----------------------------------------------------------------------+

*/

define('PLUGIN_SUCCESS', 0);
define('PLUGIN_ERROR_CONNECT', 1);
define('PLUGIN_ERROR_PROCESS', 2);

class purge extends rcube_plugin {

	public $task = 'settings';
	private $rc;
	private $obj;

	public function init() {

		$rcmail = rcmail::get_instance();
		$this->rc = &$rcmail;
		$this->add_texts('localization/', true);

		$this->rc->output->add_label('purge');
		$this->register_action('plugin.purge', array($this, 'purge_init'));
		$this->register_action('plugin.purge-save', array($this, 'purge_save'));
		$this->include_script('purge.js');

		$this->load_config();
		$this->require_plugin('jqueryui');

		require_once ($this->home . '/lib/rcube_purge.php');
		$this->obj = new rcube_purge();

		}

	public function purge_init() {
		$this->read_data();
		$this->register_handler('plugin.body', array($this, 'purge_form'));
		$this->rc->output->set_pagetitle($this->gettext('purge'));
		$this->rc->output->send('plugin');
		}

	public function purge_save() {
		$this->write_data();
		$this->register_handler('plugin.body', array($this, 'purge_form'));
		$this->rc->output->set_pagetitle($this->gettext('purge'));
		rcmail_overwrite_action('plugin.purge');
		$this->rc->output->send('plugin');
		}

	public function purge_form() {

		$table = new html_table(array('cols' => 2));

		$field_id = 'purgetrash';
		$select_purgetrash = new html_select(array('name' => "_purgetrash[]", 'onchange' => JS_OBJECT_NAME . '.purge_purgetrash_select(this)'));
		$select_purgetrash->add(Q($this->gettext('purgealways')), 0);
		$select_purgetrash->add(Q("1 " . $this->gettext('purgeday')), 1);
		$select_purgetrash->add(Q("3 " . $this->gettext('purgedays')), 3);
		$select_purgetrash->add(Q("7 " . $this->gettext('purgedays')), 7);
		$select_purgetrash->add(Q("15 " . $this->gettext('purgedays')), 15);
		$select_purgetrash->add(Q("30 " . $this->gettext('purgedays')), 30);
		$select_purgetrash->add(Q("45 " . $this->gettext('purgedays')), 45);
		$select_purgetrash->add(Q("60 " . $this->gettext('purgedays')), 60);
		$select_purgetrash->add(Q("90 " . $this->gettext('purgedays')), 90);
		$select_purgetrash->add(Q("120 " . $this->gettext('purgedays')), 120);
		$select_purgetrash->add(Q("150 " . $this->gettext('purgedays')), 150);
		$select_purgetrash->add(Q("180 " . $this->gettext('purgedays')), 180);
		$select_purgetrash->add(Q("270 " . $this->gettext('purgedays')), 270);
		$select_purgetrash->add(Q("360 " . $this->gettext('purgedays')), 360);
		$table->add('title', html::label($field_id, Q($this->gettext('purgetrashfolder'))));
		$table->add(null, $select_purgetrash->show(intval($this->obj->get_purgetrash())));

		$field_id = 'purgejunk';
		$select_purgejunk = new html_select(array('name' => "_purgejunk[]", 'onchange' => JS_OBJECT_NAME . '.purge_purgejunk_select(this)'));
		$select_purgejunk->add(Q($this->gettext('purgealways')), 0);
		$select_purgejunk->add(Q("1 " . $this->gettext('purgeday')), 1);
		$select_purgejunk->add(Q("3 " . $this->gettext('purgedays')), 3);
		$select_purgejunk->add(Q("7 " . $this->gettext('purgedays')), 7);
		$select_purgejunk->add(Q("15 " . $this->gettext('purgedays')), 15);
		$select_purgejunk->add(Q("30 " . $this->gettext('purgedays')), 30);
		$select_purgejunk->add(Q("45 " . $this->gettext('purgedays')), 45);
		$select_purgejunk->add(Q("60 " . $this->gettext('purgedays')), 60);
		$select_purgejunk->add(Q("90 " . $this->gettext('purgedays')), 90);
		$select_purgejunk->add(Q("120 " . $this->gettext('purgedays')), 120);
		$select_purgejunk->add(Q("150 " . $this->gettext('purgedays')), 150);
		$select_purgejunk->add(Q("180 " . $this->gettext('purgedays')), 180);
		$select_purgejunk->add(Q("270 " . $this->gettext('purgedays')), 270);
		$select_purgejunk->add(Q("360 " . $this->gettext('purgedays')), 360);
		$table->add('title', html::label($field_id, Q($this->gettext('purgejunkfolder'))));
		$table->add(null, $select_purgejunk->show(intval($this->obj->get_purgejunk())));

		$out = html::div(array('class' => "box"), html::div(array('id' => "purge-title", 'class' => 'boxtitle'), $this->gettext('purge')) . html::div(array('class' => "boxcontent"), $table->show() . html::p(null, $this->rc->output->button(array('command' => 'plugin.purge-save', 'type' => 'input', 'class' => 'button mainaction', 'label' => 'save')))));

		$this->rc->output->add_gui_object('purgeform', 'purge-form');

		return $this->rc->output->form_tag(array('id' => 'purge-form', 'name' => 'purge-form', 'method' => 'post', 'action' => './?_task=settings&_action=plugin.purge-save'), $out);

		}

	public function read_data() {

		$driver = $this->home . '/lib/drivers/' . $this->rc->config->get('purge_driver', 'sql').'.php';

		if (!is_readable($driver)) {
			raise_error(array('code' => 600, 'type' => 'php', 'file' => __FILE__, 'message' => "purge plugin: unable to open driver file $driver"), true, false);
			return $this->gettext('purgeinternalerror');
			}

		require_once($driver);

		if (!function_exists('purge_folder_read')) {
			raise_error(array('code' => 600, 'type' => 'php', 'file' => __FILE__, 'message' => "purge plugin: function purge_folder_read not found in driver $driver"), true, false);
			return $this->gettext('purgeinternalerror');
			}

		$data = array();
		$data['username'] = $this->obj->username;

		$ret = purge_folder_read($data);
		switch ($ret) {
			case PLUGIN_ERROR_CONNECT:
				$this->rc->output->command('display_message', $this->gettext('purgedriverconnecterror'), 'error');
				return FALSE;
				break;
			case PLUGIN_ERROR_PROCESS:
				$this->rc->output->command('display_message', $this->gettext('purgedriverprocesserror'), 'error');
				return FALSE;
				break;
			case PLUGIN_SUCCESS:
			default:
				break;
			}

		$this->obj->set_purgetrash($data['purge_trash']);
		$this->obj->set_purgejunk($data['purge_junk']);

		return TRUE;

		}

	public function write_data() {

		$purgetrash = get_input_value('_purgetrash', RCUBE_INPUT_POST);
		$purgejunk = get_input_value('_purgejunk', RCUBE_INPUT_POST);

		if (($purgetrash[0] < 0) || ($purgetrash[0] > 365)) { $purgetrash[0] = 0; }
		if (($purgejunk[0] < 0) || ($purgejunk[0] > 365)) { $purgejunk[0] = 0; }

		$this->obj->set_purgetrash($purgetrash[0]);
		$this->obj->set_purgejunk($purgejunk[0]);

		$driver = $this->home . '/lib/drivers/' . $this->rc->config->get('purge_driver', 'sql').'.php';

		if (!is_readable($driver)) {
			raise_error(array('code' => 600, 'type' => 'php', 'file' => __FILE__, 'message' => "purge plugin: unable to open driver file $driver"), true, false);
			return $this->gettext('purgeinternalerror');
			}

		require_once($driver);

		if (!function_exists('purge_folder_write')) {
			raise_error(array('code' => 600, 'type' => 'php', 'file' => __FILE__, 'message' => "purge plugin: function purge_folder_write not found in driver $driver"), true, false);
			return $this->gettext('purgeinternalerror');
			}

		$data = array();
		$data['username'] = $this->obj->username;
		$data['purge_trash'] = $this->obj->get_purgetrash();
		$data['purge_junk'] = $this->obj->get_purgejunk();

		$ret = purge_folder_write ($data);
		switch ($ret) {
			case PLUGIN_ERROR_CONNECT:
					$this->rc->output->command('display_message', $this->gettext('purgedriverconnecterror'), 'error');
					return FALSE;
					break;
			case PLUGIN_ERROR_PROCESS:
					$this->rc->output->command('display_message', $this->gettext('purgedriverprocesserror'), 'error');
					return FALSE;
					break;
			case PLUGIN_SUCCESS:
			default:
					$this->rc->output->command('display_message', $this->gettext('purgesuccessfullysaved'), 'confirmation');
					break;
			}

		$this->read_data();

		return TRUE;

		}

	}

?>
