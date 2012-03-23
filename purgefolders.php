#!/usr/bin/php5

<?php

/*

 +-----------------------------------------------------------------------+
 | PostfixAdmin Purge Plugin for RoundCube                               |
 | Version: 0.7.2                                                        |
 | Author: Gianluca Giacometti <php@gianlucagiacometti.it>               |
 | Copyright (C) 2012 Gianluca Giacometti                                |
 | License: GNU General Public License                                   |
 +-----------------------------------------------------------------------+

*/

require_once 'PEAR.php';
require_once 'MDB2.php';
require_once("config.inc.php");

$log_file = "/var/log/roundcube/purgefolders.log";

$log = fopen($log_file, "a");
fwrite($log, date('Y-m-d H:i:s') . " - Purge operation started.\n");

$options = array(
	'persistent' => true
	);

$db = MDB2::connect($rcmail_config['purge_sql_dsn'], $options);
if (PEAR::isError($db)) {
	exit($db->getMessage());
	}

$sql_result = $db->query($rcmail_config['purge_script_query']);
if (PEAR::isError($sql_result)) {
	exit($db->getMessage());
	}

$mailboxes = $sql_result->fetchAll(MDB2_FETCHMODE_ASSOC);

for ( $i = 0 ; $i < count($mailboxes) ; $i++) {
	$tcount = 0;
	$tlast = intval($mailboxes[$i]['purge_trash']) > 0 ? (intval($mailboxes[$i]['purge_trash']) == 1 ? " 1 day" : " " . strval($mailboxes[$i]['purge_trash']) . " days") : "ever";
	fwrite($log, date('Y-m-d H:i:s') . "     User " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain'] . " keeps messages in Trash folder for" . $tlast . ".\n");
	if (intval($mailboxes[$i]['purge_trash']) > 0) {
		$trash = $rcmail_config['purge_maildir_path'] . "/" . $mailboxes[$i]['domain'] . "/" . $mailboxes[$i]['local_part'] . "/Maildir/.Trash/cur";
		$files = array_diff(scandir($trash), array('..', '.'));
		if (!empty($files)) {
			foreach ($files as $filename) {
				if ((time() - filemtime($trash . "/" . $filename)) > ($mailboxes[$i]['purge_trash'] * 60 * 60 * 24)) {
					fwrite($log, date('Y-m-d H:i:s') . "     Purging file " . $filename . " (dated " . date('Y-m-d H:i:s', filemtime($trash . "/" . $filename)) . ") from Trash folder of user " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain'] . ".\n");
					unlink($trash . "/" . $filename);
					$tcount++;
					}
				}
			}
		}
	fwrite($log, date('Y-m-d H:i:s') . "       " . $tcount . " messages purged from Trash folder of user " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain'] . ".\n");
	$jcount = 0;
	$jlast = intval($mailboxes[$i]['purge_junk']) > 0 ? (intval($mailboxes[$i]['purge_junk']) == 1 ? " 1 day" : " " . strval($mailboxes[$i]['purge_junk']) . " days") : "ever";
	fwrite($log, date('Y-m-d H:i:s') . "     User " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain'] . " keeps messages in Junk folder for" . $jlast . ".\n");
	if (intval($mailboxes[$i]['purge_junk']) > 0) {
		$junk = $rcmail_config['purge_maildir_path'] . "/" . $mailboxes[$i]['domain'] . "/" . $mailboxes[$i]['local_part'] . "/Maildir/.Junk/cur";
		$files = array_diff(scandir($junk), array('..', '.'));
		if (!empty($files)) {
			foreach ($files as $filename) {
				if ((time() - filemtime($junk . "/" . $filename)) > ($mailboxes[$i]['purge_junk'] * 60 * 60 * 24)) {
					fwrite($log, date('Y-m-d H:i:s') . "     Purging file " . $filename . " (dated " . date('Y-m-d H:i:s', filemtime($trash . "/" . $filename)) . ") from Junk folder of user " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain'] . ".\n");
					unlink($junk . "/" . $filename);
					$jcount++;
					}
				}
			}
		}
	fwrite($log, date('Y-m-d H:i:s') . "       " . $jcount . " messages purged from Junk folder of user " .  $mailboxes[$i]['local_part'] . "@" . $mailboxes[$i]['domain'] . ".\n");
	}

fwrite($log, date('Y-m-d H:i:s') . " - Purge operation ended.\n\n");
fclose($log);

$sql_result->free();
$db->disconnect();

?>
