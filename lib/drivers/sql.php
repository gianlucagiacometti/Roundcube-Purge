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
		$db = rcube_db::factory($dsn, '', false);
		$db->set_debug((bool)$rcmail->config->get('sql_debug'));
		$db->db_connect('w');
		}
	else {
		$db = $rcmail->get_dbh();
		}

	if ($err = $db->is_error()) {
		return PLUGIN_ERROR_CONNECT;
		}

	$sql_arr = purge_get_values_for_user($db, $data['username']);
	if ($sql_arr === null) {
		return PLUGIN_ERROR_PROCESS;
		}

	$result = purge_set_data($sql_arr, $data, $db, 'purge_trash');
	if ($result !== PLUGIN_SUCCESS) {
		return $result;
	}
	$result = purge_set_data($sql_arr, $data, $db, 'purge_junk');
	return $result;

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
		$db = rcube_db::factory($dsn, '', false);
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

	$db->query($query);
	if ($err = $db->is_error()) {
		return PLUGIN_ERROR_PROCESS;
		}

	return PLUGIN_SUCCESS;

	}

function purge_get_values_for_user($db, $username) {
	$rcmail = rcmail::get_instance();
	return purge_query($db, '%username', $username, $rcmail->config->get('purge_sql_read'));
	}

function purge_get_values_for_domain($db, $domain) {
	$rcmail = rcmail::get_instance();
	return purge_query($db, '%domain', $domain, $rcmail->config->get('purge_sql_read_domain'));
	}

function purge_set_data(&$sql_arr, &$data, $db, $value) {
	$rcmail = rcmail::get_instance();

	if (isset($sql_arr[$value]) && !empty($sql_arr[$value])) { $data[$value] = $sql_arr[$value]; }
	else if ($data['domain'] !== null && $rcmail->config->get('purge_sql_read_domain') !== null) {
		$domain_arr = purge_get_values_for_domain($db, $data['domain']);
		if ($domain_arr === null) {
			return PLUGIN_ERROR_PROCESS;
			}

		$data[$value] = $domain_arr[$value];
		}
	else { $data[$value] = 0; }
	return PLUGIN_SUCCESS;
	}

function purge_query($db, $placeholder, $value, $query) {
	$rcmail = rcmail::get_instance();

	$query = str_replace($placeholder, $db->quote($value), $query);
	$sql_result = $db->query($query);
	if ($err = $db->is_error()) {
		return null;
		}

	$sql_arr = $db->fetch_assoc($sql_result);
	return $sql_arr;
	}

?>
