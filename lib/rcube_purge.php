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

class rcube_purge {

	public $username = '';
	public $purgetrash = 0;
	public $purgejunk = 0;

	public function __construct() {
		$this->init();
		}

	private function init() {
		$this->username = rcmail::get_instance()->user->get_username();
		}

	// Gets the username.
	public function get_username() {
		return $this->username;
		}

	// Gets the days-alive for trash.
	public function get_purgetrash() {
		return $this->purgetrash;
		}

	// Gets the days-alive for junk.
	public function get_purgejunk() {
		return $this->purgejunk;
		}

	// Sets the days-alive for trash.
	public function set_purgetrash($purgetrash) {
		$this->purgetrash = $purgetrash;
		}

	// Sets the days-alive for junk.
	public function set_purgejunk($purgejunk) {
		$this->purgejunk = $purgejunk;
		}

	}
