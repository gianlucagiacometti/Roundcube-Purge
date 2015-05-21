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

 code structure based on:

 +-----------------------------------------------------------------------+
 | Vacation Module for RoundCube                                         |
 | Copyright (C) 2009 Boris HUISGEN <bhuisgen@hbis.fr>                   |
 | Licensed under the GNU GPL                                            |
 +-----------------------------------------------------------------------+

*/

if (window.rcmail) {

	rcmail.addEventListener('init', function(evt) {

		var tab = $('<span>').attr('id', 'settingstabpluginpurge').addClass('tablink');

		var button = $('<a>').attr('href', rcmail.env.comm_path + '&_action=plugin.purge').html(rcmail.gettext('purge', 'purge')).appendTo(tab);

		button.bind('click', function(e) {
			return rcmail.command('plugin.purge', this);
			});

		rcmail.add_element(tab, 'tabs');

		rcmail.register_command('plugin.purge', function() {
			rcmail.goto_url('plugin.purge')
			}, true);

		rcmail.register_command('plugin.purge-save', function() {
			rcmail.gui_objects.purgeform.submit();
			}, true);

		})

	}
