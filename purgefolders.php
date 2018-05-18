#!/usr/bin/php

<?php

/*

 +-----------------------------------------------------------------------+
 | PostfixAdmin Purge Plugin for RoundCube                               |
 | Version: 1.1.1                                                        |
 | Authors:                                                              |
 |          Gianluca Giacometti <php@gianlucagiacometti.it>              |
 |          Philipp Kapfer (https://github.com/Crazyphil)                |
 | Contributors:                                                         |
 |          Bob Hutchinson (https://github.com/bobhutch)                 |
 | Copyright (C) 2015 Gianluca Giacometti - Philipp Kapfer               |
 | License: GNU General Public License                                   |
 +-----------------------------------------------------------------------+

*/

define('INSTALL_PATH', __DIR__ . '/../../');
require_once INSTALL_PATH . 'program/include/clisetup.php';

$rcmail = rcmail::get_instance();
$rcmail->plugins->load_plugin('purge', true);
if ($rcmail->config->get('purge_debug')) {
	$rcmail->write_log('purgefolders', 'Purge operation started');
	}

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
	if ($rcmail->config->get('purge_debug')) {
		$rcmail->write_log('purgefolders', 'Error connecting to database: ' . $err);
		}
	exit($err . "\n");
	}

$sql_result = $db->query($rcmail->config->get('purge_script_query'));
if ($err = $db->is_error()) {
	if ($rcmail->config->get('purge_debug')) {
		$rcmail->write_log('purgefolders', 'Error reading mailboxes from database: ' . $err);
		}
	exit($err . "\n");
	}

$mailboxes = $sql_result->fetchAll();
for ( $i = 0 ; $i < count($mailboxes) ; $i++) {
	$purge_trash = $mailboxes[$i]['purge_trash'];
	$purge_junk = $mailboxes[$i]['purge_junk'];
	if (($purge_trash === null || $purge_junk === null) && $rcmail->config->get('purge_sql_read_domain') !== null) {
		$search = array(
				'%domain'
				);
		$replace = array(
				$db->quote($mailboxes[$i]['domain'])
				);
		$query = str_replace($search, $replace, $rcmail->config->get('purge_sql_read_domain'));
		$sql_result = $db->query($query);
		if ($err = $db->is_error()) {
			if ($rcmail->config->get('purge_debug')) {
				$rcmail->write_log('purgefolders', 'Error reading domain quota for ' . $mailboxes[$i]['domain'] . ': ' . $err);
				}
			exit($err . "\n");
		    }
		$sql_arr = $db->fetch_assoc($sql_result);

		if ($purge_trash === null) {
			$purge_trash = $sql_arr['purge_trash'];
			}
		if ($purge_junk === null) {
			$purge_junk = $sql_arr['purge_junk'];
			}
		}
	$purge_trash = intval($purge_trash);
	$purge_junk = intval($purge_junk);

	$tcount = 0;
	$tlast = $purge_trash > 0 ? ($purge_trash == 1 ? " 1 day" : " " . strval($purge_trash) . " days") : "ever";
	if ($rcmail->config->get('purge_debug')) {
		$rcmail->write_log('purgefolders', "User " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain'] . " keeps messages in Trash folder for" . $tlast);
		}
	if ($purge_trash > 0) {
		$trash = $rcmail->config->get('purge_maildir_path') . "/" . $mailboxes[$i]['domain'] . "/" . $mailboxes[$i]['local_part'] . "/Maildir/.Trash/cur";
		$files = array_diff(scandir($trash), array('..', '.'));
		if (!empty($files)) {
			foreach ($files as $filename) {
				if (file_exists($trash . "/" . $filename) && ((time() - filemtime($trash . "/" . $filename)) > ($purge_trash * 60 * 60 * 24))) {
					if ($rcmail->config->get('purge_debug')) {
						$rcmail->write_log('purgefolders', "Purging file " . $filename . " (dated " . date('Y-m-d H:i:s', filemtime($trash . "/" . $filename)) . ") from Trash folder of user " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain']);
						}
					unlink($trash . "/" . $filename);
					$tcount++;
					}
				}
			}
		}
	if ($rcmail->config->get('purge_debug')) {
		$rcmail->write_log('purgefolders', $tcount . " messages purged from Trash folder of user " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain']);
		}
	$jcount = 0;
	$jlast = $purge_junk > 0 ? ($purge_junk == 1 ? " 1 day" : " " . strval($purge_junk) . " days") : "ever";
	if ($rcmail->config->get('purge_debug')) {
		$rcmail->write_log('purgefolders', "User " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain'] . " keeps messages in Junk folder for" . $jlast);
		}
	if (intval($mailboxes[$i]['purge_junk']) > 0) {
		$junk = $rcmail->config->get('purge_maildir_path') . "/" . $mailboxes[$i]['domain'] . "/" . $mailboxes[$i]['local_part'] . "/Maildir/.Junk/cur";
		$files = array_diff(scandir($junk), array('..', '.'));
		if (!empty($files)) {
			foreach ($files as $filename) {
				if (file_exists($junk . "/" . $filename) && ((time() - filemtime($junk . "/" . $filename)) > ($purge_junk * 60 * 60 * 24))) {
					if ($rcmail->config->get('purge_debug')) {
						$rcmail->write_log('purgefolders', "Purging file " . $filename . " (dated " . date('Y-m-d H:i:s', filemtime($trash . "/" . $filename)) . ") from Junk folder of user " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain']);
						}
					unlink($junk . "/" . $filename);
					$jcount++;
					}
				}
			}
		}
	if ($rcmail->config->get('purge_debug')) {
		$rcmail->write_log('purgefolders', $jcount . " messages purged from Junk folder of user " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain']);
		}
	}

if ($rcmail->config->get('purge_debug')) {
	$rcmail->write_log('purgefolders', "Purge operation ended");
	}

?>
