======================
Centreon Syslog Server
======================

The centreon Syslog Server allows to store syslog event into MySQL database.

It can be install on the central server or as a distant collector.

Run the following command as privileged user::

   $ yum install centreon-syslog-server

YUM suggests then installing the latest version of the package::

  =========================================================================================================
   Package                       Arch          Version          Repository                            Size
  =========================================================================================================
  Installing:
   centreon-syslog-server        noarch        1.2.3-1          ces-standard                          14 k
  
  Transaction Summary
  =========================================================================================================
  Install       1 Package(s)
  Upgrade       0 Package(s)
  
  Total download size: 14 k
  Is this ok [y/N]: y

Enter 'y' and press ENTER key to install package on your server.


YUM downloads the package and installs the latter::

  Installed:
    centreon-syslog-server.noarch 0:1.2.3-1

  Complete!

The package centreon-syslog-server is now installed on your server.


If you have install the package centreon-syslog-server in a distant poller it is necessary to create a MySQL account.
Indeed the Centreon server must be able to reach the database MySQL of your poller.
Run the following commands on your poller::

  $ mysql -u root -p
  mysql> GRANT SELECT ON centreon_syslog.* TO centreon@'<IP>' IDENTIFIED BY '<PASSWORD>';
  mysql> FLUSH PRIVILEGES;
  mysql> quit;

Notice: replace <IP> address by the Centreon main server IP address and <PASSWORD> by your password.

To configure the module, see :ref:`configuration-label`
