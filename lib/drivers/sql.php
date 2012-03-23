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
 | lib/drivers/sql.php                                                   |
 | Copyright (C) 2009 Boris HUISGEN <bhuisgen@hbis.fr>                   |
 | Licensed under the GNU GPL                                            |
 +-----------------------------------------------------------------------+

*/

/*
 * Read driver function.
 * @params: array $data the array of data to get and set.
 * @return: integer the status code.
 */
function purge_folder_read(array &$data) {

	$rcmail = rcmail::get_instance();

	if ($dsn = $rcmail->config->get('purge_sql_dsn')) {
		if (is_array($dsn) && empty($dsn['new_link'])) {
			$dsn['new_link'] = true;
			}
		else if (!is_array($dsn) && !preg_match('/\?new_link=true/', $dsn)) {
			$dsn .= '?new_link=true';
			}
		$db = new rcube_mdb2($dsn, '', FALSE);
		$db->set_debug((bool)$rcmail->config->get('sql_debug'));
		$db->db_connect('w');
		}
	else {
		$db = $rcmail->get_dbh();
		}

	if ($err = $db->is_error()) {
		return PLUGIN_ERROR_CONNECT;
		}

	$search = array(
			'%username'
			);
	$replace = array(
			$db->quote($data['username'])
			);
	$query = str_replace($search, $replace, $rcmail->config->get('purge_sql_read'));

	$sql_result = $db->query($query);
	if ($err = $db->is_error()) {
		return PLUGIN_ERROR_PROCESS;
		}

	$sql_arr = $db->fetch_assoc($sql_result);

	if (isset($sql_arr['purge_trash'])) { $data['purge_trash'] = $sql_arr['purge_trash']; }
	else { $data['purge_trash'] = 0; }

	if (isset($sql_arr['purge_junk'])) { $data['purge_junk'] = $sql_arr['purge_junk']; }
	else { $data['purge_junk'] = 0; }

	return PLUGIN_SUCCESS;

	}

/*
 * Write driver function.
 * @params: array $data the array of data to get and set.
 * @return: integer the status code.
 */
function purge_folder_write(array &$data) {

	$rcmail = rcmail::get_instance();

	if ($dsn = $rcmail->config->get('purge_sql_dsn')) {
		if (is_array($dsn) && empty($dsn['new_link'])) {
			$dsn['new_link'] = true;
			}
		else if (!is_array($dsn) && !preg_match('/\?new_link=true/', $dsn)) {
			$dsn .= '?new_link=true';
			}
		$db = new rcube_mdb2($dsn, '', FALSE);
		$db->set_debug((bool)$rcmail->config->get('sql_debug'));
		$db->db_connect('w');
		}
	else {
		$db = $rcmail->get_dbh();
		}

	if ($err = $db->is_error()) {
		return PLUGIN_ERROR_CONNECT;
		}

	$search = array(
			'%username',
			'%purgetrash',
			'%purgejunk'
			);
	$replace = array(
			$db->quote($data['username']),
			 $db->quote($data['purge_trash']),
			 $db->quote($data['purge_junk'])
			);
	$query = str_replace($search, $replace, $rcmail->config->get('purge_sql_write'));

	$sql_result = $db->query($query);
	if ($err = $db->is_error()) {
		return PLUGIN_ERROR_PROCESS;
		}

	return PLUGIN_SUCCESS;

	}

?>
