# DEPRECATED

***NOTE***<br />
***THIS PLUGIN DOES NOT WORK IN RC 1.4.x AND ITS DEVELOPMENT IS DICONTINUED***<br />
***all functionalities are now integrated into the new plugin [roundcube-toolbox](https://github.com/gianlucagiacometti/roundcube-toolbox)***<br />

AUTHORS

Gianluca Giacometti (php@gianlucagiacometti.it)
Philipp Kapfer (https://github.com/Crazyphil)



CONTRIBUTORS

Bob Hutchinson (https://github.com/bobhutch)



VERSION

1.3.0



RELEASE DATE

16-06-2017



INSTALL

Requirements :
- jQuery UI.

To install this plugin, copy all files into /plugin/purge folder and
add it to the plugin array in config/config.inc.php :

// List of active plugins (in plugins/ directory)
$rcmail_config['plugins'] = array('purge');



CONFIGURATION

Edit the plugin configuration file 'config.inc.php' and choose the
appropriate options:

$rcmail_config['purge_driver'] = 'sql';

    so far only sql is available

$rcmail_config['purge_visible'] = boolean;

    indicates if users can change purge values in their settings

$rcmail_config['purge_sql_dsn'] = value;

    example value: 'pgsql://username:password@host/database'
    example value: 'mysql://username:password@host/database'

$rcmail_config['purge_sql_write'] = query;

    the query depends upon your postfixadmin database structure
    placeholders %goto and %address must be kept unchanged
    default query: 'UPDATE mailbox SET purge_trash = %purgetrash, purge_junk = %purgejunk WHERE username = %username'
    example query: 'UPDATE mailboxes SET purge_trash = %purgetrash, purge_junk = %purgejunk WHERE username = %username'

$rcmail_config['purge_sql_read'] = query;

    the query depends upon your postfixadmin database structure
    placeholder %address must be kept unchanged
    default query: 'SELECT * FROM mailbox WHERE username = %username'
    example query: 'SELECT * FROM mailboxes WHERE username = %username'

$rcmail_config['purge_sql_read_domain'] = query;

    the query depends upon your postfixadmin database structure
    default query: 'SELECT purge_trash, purge_junk FROM domain WHERE domain = %domain'

$rcmail_config['purge_script_query'] = query;

    the query depends upon your postfixadmin database structure
    default query: 'SELECT domain, local_part, purge_trash, purge_junk FROM mailbox'

$rcmail_config['purge_maildir_path'] = path;

    path to postfix maildir location
    default path: '/web/vmail'
    example path: '/var/www/vmail'



CONFIGURE PURGE FOLDERS SCRIPT

Set purgefolders.php script executable 

    chmod 0755 /path/to/roundcube/plugins/purge/purgefolders.php

Create a cron job to execute it everyday
```
  ---------------------------------------------------------------------------------------------------------------
  # At 4:01am every night, purge old messages in Trash and Junk folders.
  01 04 * * * root php5 /path/to/roundcube/plugins/purge/purgefolders.php 2>> /var/log/roundcube/purgefolders.err
  ---------------------------------------------------------------------------------------------------------------
```
For Debian/Ubuntu systems just run (in Ubuntu prepend 'sudo' to all commands):
```
    echo -e "#\!/bin/sh\n\n/path/to/roundcube/plugins/purge/purgefolders.php 2>> /var/log/roundcube/purgefolders.err" > /etc/cron.daily/purge-trash-junk-folders
    sed -i -e 's/\\!/!/' /etc/cron.daily/purge-trash-junk-folders
    chmod 0755 /etc/cron.daily/purge-trash-junk-folders
```
or
```
    echo -e "# At 4:01am every night, purge old messages in Trash and Junk folders.\n01 04 * * * root /path/to/roundcube/plugins/purge/purgefolders.php 2>> /var/log/roundcube/purgefolders.err" > /etc/cron.d/purge-trash-junk-folders
```


LICENCE

Licensed under GNU GPL2 licence.



NOTE

The code is based on Vacation plugin (rc-vacation) by Boris HUISGEN et al. (https://github.com/bhuisgen/rc-vacation).
Thank you Boris et al.
