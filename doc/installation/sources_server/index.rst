======================
Centreon Syslog Server
======================

The centreon Syslog Server allows to store syslog event into MySQL database.

It can be install on the central server or as a distant collector.

Extract sources
===============

Copy the tarball into '/tmp' directory and run the following commands::

  $ tar xzf centreon-syslog-server-1.2.3.tar.gz
  $ cd centreon-syslog-server-1.2.3
  $ dos2unix install.sh libinstall/*

Install module
==============

Start installation using following command::

  $ bash install.sh -i
  
  ###############################################################################
  #                                                                             #
  #           http://forge.centreon.com/projects/show/centreon-syslog           #
  #                          Thanks for using Centreon                          #
  #                                                                             #
  #                                    v1.2.3                                   #
  #                                                                             #
  ###############################################################################
  ------------------------------------------------------------------------
          Checking all needed binaries
  ------------------------------------------------------------------------
  rm                                                         OK
  cp                                                         OK
  mv                                                         OK
  /bin/chmod                                                 OK
  /bin/chown                                                 OK
  echo                                                       OK
  more                                                       OK
  mkdir                                                      OK
  find                                                       OK
  /bin/grep                                                  OK
  /bin/cat                                                   OK
  /bin/sed                                                   OK
  groupadd                                                   OK
  useradd                                                    OK
  
  You will now read Centreon Syslog module Licence.
          Press enter to continue.

Press ENTER key and accept GPL v2 licence::

  Do you accept GPL license ?
  [y/n], default to [n]:
  > y

For the server module to function properly, a user is created. 
This user will be the user specified the cron jobs. 
The installation script will offer to create this user if it is not detected. 
The installation is aborted if the user is not created::

  ------------------------------------------------------------------------
          Checking syslog group and user
  ------------------------------------------------------------------------
  Cannot find user: syslog                                   FAIL
  
  Do you want to create this user
  [y/n], default to [n]:
  > y
  
  Create user: syslog                                        OK

Notice: No password has been defined for the 'syslog' user. To work correctly, 
the Centreon Syslog Frontend module needs to connect to syslog server via SSH. 
This is true even if the client and the server are the same machine. 
That is why we must set a SHELL password for the new 'syslog' user.


The installation script checks prerequires::

  ------------------------------------------------------------------------
          Checking binaries and processus
  ------------------------------------------------------------------------
  Mysql is running:                                          OK
  PHP version: 5.1.6                                         OK
  Pear-DB version: 1.7.14                                    OK
  PHP MySQL module:                                          OK

Define prefix directory for installation::

  ------------------------------------------------------------------------
          Get directories for installation
  ------------------------------------------------------------------------
  
  Where do you want to install files ?
  default to [/usr/bin/syslog]
  > /usr/local/centreon-syslog
  
  Do you want me to create this directory ? [/usr/local/centreon-syslog]
  [y/n], default to [n]:
  > y

Define location of logs files::

  Where would you like to store your logs ?
  default to [/usr/local/centreon-syslog/logs]
  >
  
  Do you want me to create this directory ? [/usr/local/centreon-syslog/logs]
  [y/n], default to [n]:
  > y

Define location of configuration files::

  Where would you like to store configuration ?
  default to [/usr/local/centreon-syslog/etc]
  >
  
  Do you want me to create this directory ? [/usr/local/centreon-syslog/etc]
  [y/n], default to [n]:
  > y

If you don't have MySQL root password press ENTER key::

  ------------------------------------------------------------------------
          Create syslog Database
  ------------------------------------------------------------------------
  What is password for root user on MySQL ?
  >
  
Define name of Centreon Syslog Server database::
  
  What is the database name to record syslog message ? default to [syslog]
  > centreon_syslog
  
  Do you want me to create this database ? [centreon_syslog]
  [y/n], default to [n]:
  > y
  
  Creating database centreon_syslog:                         OK

Define size of 'program' field for 'logs' table (30)::

  Which must be the size of the field "program", default to [15]:
  > 30

Press ENTER key to create first 'logs' table::

  Do you want me to create this table "logs" in "centreon_syslog" database ?
  [y/n], default to [n]:
  > y
  
  Creating table logs:                                       OK

Create local MySQL account for the module::

  Creation of local db user for cron
  
  Do you want me to create user 'syslogadmin'@'localhost' ?
  [y/n], default to [n]:
  > y
  
  Create user 'syslogadmin'@'localhost':                     OK

Add password for local MySQL account::

  Do you want to add password for this user: 'syslogadmin'@'localhost'
  [y/n], default to [y]:
  > y
  
  Enter password for dbuser
  > syslogpasswd
  
  Retype password for dbuser
  > syslogpasswd
  
  Add password for user 'syslogadmin'@'localhost':           OK

Create distant MySQL account for Centreon main server::

  Creation of distant db user for cron
  
  What is IP address of Centreon server ?
  > 10.30.2.164
  
  Do you want me to create user 'syslogadmin'@'10.30.2.164' ?
  [y/n], default to [n]:
  > y
  
  Create user 'syslogadmin'@'10.30.2.164':                   OK
  
  Do you want to add password for this user: 'syslogadmin'@'10.30.2.164'
  [y/n], default to [y]:
  > y
  
  Enter password for dbuser
  > syslogpasswd
  
  Retype password for dbuser
  > syslogpasswd
  
  Add password for user 'syslogadmin'@'10.30.2.164':         OK

.. note:: if the database is on the same server tha n Centreon main server, you must specify the external IP address of the server.

The installation script install the Centreon Syslog Server module on your plateform::

  ------------------------------------------------------------------------
          Install Syslog Cron
  ------------------------------------------------------------------------
  Generation of the new Syslog cron:                         OK
  Change of the macros in the files:                         OK
  Application of the rights on the files:                    OK
  Change of the owners on the files:                         OK
  Removal of the old Syslog cron:                            OK
  Copy php cron files:                                       OK
  Copy cron in cron.d directory:                             OK
  Erase temporay installation directory:                     OK
  
  ------------------------------------------------------------------------
          Create log rotation file
  ------------------------------------------------------------------------
  
  Create log rotate file: /etc/logrotate.d//centreon-syslog  OK
  
  ------------------------------------------------------------------------
          Create syslog configuration files
  ------------------------------------------------------------------------
  
  Create syslog configuration file: syslog_conf.pm           OK
  Create php syslog configuration file: /usr/local/centreon-sOKlog/etc/syslog.conf.php
  Set rigths on : /usr/local/centreon-syslog/etc/syslog.conf.OKp
  
  ------------------------------------------------------------------------
          Update database
  ------------------------------------------------------------------------
  
  No update available:                                       PASSED
  
  ------------------------------------------------------------------------
          End of installation
  ------------------------------------------------------------------------
  
  Installation is complete !                                 OK
  
  
  ###############################################################################
  #                                                                             #
  #      Report bugs at                                                         #
  #           http://forge.centreon.com/projects/centreon-syslog/issues/new     #
  #                                                                             #
  ###############################################################################

Your module is installed

.. note:: the installer creates a "syslog_conf.php" file in the "etc" directory. This file will be used for updates without asking any questions.

To configure the module, see :ref:`configuration-label`
