#!/bin/bash
#
# Copyright 2005-2013 MERETHIS
# Centreon is developped by : Julien Mathis and Romain Le Merlus under
# GPL Licence 2.0.
# 
# This program is free software; you can redistribute it and/or modify it under 
# the terms of the GNU General Public License as published by the Free Software 
# Foundation ; either version 2 of the License.
# 
# This program is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
# PARTICULAR PURPOSE. See the GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License along with 
# this program; if not, see <http://www.gnu.org/licenses>.
# 
# Linking this program statically or dynamically with other modules is making a 
# combined work based on this program. Thus, the terms and conditions of the GNU 
# General Public License cover the whole combination.
# 
# As a special exception, the copyright holders of this program give MERETHIS 
# permission to link this program with independent modules to produce an executable, 
# regardless of the license terms of these independent modules, and to copy and 
# distribute the resulting executable under terms of MERETHIS choice, provided that 
# MERETHIS also meet, for each linked independent module, the terms  and conditions 
# of the license of that module. An independent module is a module which is not 
# derived from this program. If you modify this program, you may extend this 
# exception to your version of the program, but you are not obliged to do so. If you
# do not wish to do so, delete this exception statement from your version.
# 
#  For more information : contact@centreon.com
# 
#  Project name : Centreon Syslog
#  Module name: Centreon-Syslog-Server
#
# SVN : $URL$
# SVN : $Id$
# 

line="------------------------------------------------------------------------"
NAME="centreon-syslog-server"
VERSION="1.2.4"
MODULE=$NAME.$VERSION
LOG_VERSION="Centreon $MODULE installation"
FILE_CONF="syslog_conf.pm"
PHP_FILE_CONF="syslog.conf.php"
SYSLOG_USER="syslog"
SYSLOG_GROUP="syslog"
SYSLOGADMIN_PASSWD=""
DEFAULT_DIR_CONF="/etc/syslog-server/"
CRON_NAME="centreon-syslog"
SYSLOG_DIR_BIN="/usr/bin/syslog"
ROOT_PASSWORD=""
IP_CENTREON=""
APACHE_USER="www-data"
SYSLOG_PROGRAM_FIELD_SIZE=30

#---
## {Print help and usage}
##
## @Stdout Usage and Help program
#----
function usage() {
	local program=$0
	echo -e "Usage: $program"
	echo -e "  -i\tInteractive installation"
	echo -e "  -u\tupgrade Syslog server with old parameters describe in $FILE_CONF"
	line="------------------------------------------------------------------------"
}

### Main

# define where is sources 
BASE_DIR=$(dirname $0)
## set directory
BASE_DIR=$( cd $BASE_DIR; pwd )
export BASE_DIR
if [ -z "${BASE_DIR#/}" ] ; then
	echo -e "I think it is not right to have Centreon source on slash"
	exit 1
fi

INSTALL_DIR="$BASE_DIR/libinstall"
export INSTALL_DIR

## load all functions used in this script
. $INSTALL_DIR/functions

## Define a default log file
#LOG_FILE=${LOG_FILE:=log\/install_centreon.log}
LOG_FILE="$PWD/install.log"

## Valid if you are root 
USERID=`id -u`
if [ "$USERID" != "0" ]; then
    echo -e "You must exec with root user"
    exit 1
fi

## Vars install
_tmp_install_opts="0"
silent_install="0"
user_conf=""

## Getopts
while getopts "iu:h" Options
do
	case ${Options} in
		i )	_tmp_install_opts="1"
			silent_install="0"
			;;
		u )	_tmp_install_opts="1"
			silent_install="1"
			user_conf="${OPTARG%/}"
			;;
		\?|h)	usage ; 
			exit 0 
			;;
		* )	usage ; 
			exit 1 
			;;
	esac
done

## Control if an option are defined
if [ $_tmp_install_opts -eq 0 ] ; then
	usage
	exit 1
fi

## Export variable for all programs
export silent_install user_install_vars cinstall_opts inst_upgrade_dir

LOG_DIR=$BASE_DIR

# init LOG_FILE
# backup old log file...
[ ! -d "$LOG_DIR" ] && mkdir -p "$LOG_DIR"
if [ -e "$LOG_FILE" ] ; then
	mv "$LOG_FILE" "$LOG_FILE.`date +%Y%m%d-%H%M%S`"
fi
# Clean (and create) my log file
${CAT} << __EOL__ > "$LOG_FILE"
__EOL__

# Init GREP,CAT,SED,CHMOD,CHOWN variables
define_specific_binary_vars

${CAT} << __EOT__
###############################################################################
#                                                                             #
#           http://forge.centreon.com/projects/show/centreon-syslog           #
#                          Thanks for using Centreon                          #
#                                                                             #
#                                    v$VERSION                                   #
#                                                                             #
###############################################################################
__EOT__


## Test all binaries
BINARIES="rm cp mv ${CHMOD} ${CHOWN} echo more mkdir find ${GREP} ${CAT} ${SED} groupadd useradd"

echo "$line"
echo -e "\tChecking all needed binaries"
echo "$line"

binary_fail="0"
# For the moment, I check if all binary exists in path.
# After, I must look a solution to use complet path by binary
for binary in $BINARIES; do
	if [ ! -e ${binary} ] ; then 
		pathfind "$binary"
		if [ "$?" -eq 0 ] ; then
			echo_success "${binary}" "$ok"
		else 
			echo_failure "${binary}" "$fail"
			log "ERR" "\$binary not found in \$PATH"
			binary_fail=1
		fi
	else
		echo_success "${binary}" "$ok"
	fi
done

# Script stop if one binary wasn't found
if [ "$binary_fail" -eq 1 ] ; then
	echo_info "Please check fail binary and retry"
	exit 1
fi

# Interactive installation 
if [ "$silent_install" -eq 0 ] ; then
	APACHE_USER=$(${CAT} /etc/passwd | ${GREP} Apache | cut -d":" -f1)
	echo -e "\nYou will now read Centreon Syslog module Licence.\\n\\tPress enter to continue."
	read 
	tput clear 
	more "$BASE_DIR/LICENSE"

	yes_no_default "Do you accept GPL license ?"
	if [ "$?" -ne 0 ] ; then 
		echo_info "You do not agree to GPL license ? Okay... have a nice day."
		exit 1
	else
		log "INFO" "You accepted GPL license"
	fi

	echo ""
	echo "$line"
	echo -e "\tChecking syslog group and user"
	echo "$line"
	check_user_syslog;
	
	echo ""
	echo "$line"
	echo -e "\tChecking binaries and processus"
	echo "$line"
	check_mysqlrun;
	check_phpversion;
	check_phpMySQL;
	
	get_dir;
	createDB;
	copy_cron;
	
	create_log_file;
	
	create_conf_files;
	check_update_database;
fi

# Silent install
if [ "$silent_install" -eq 1 ] ; then
	DEFAULT_DIR_CONF=$user_conf


	echo ""
	echo "$line"
	echo -e "\tChecking syslog server configuration directory"
	echo "$line"
	check_file_conf;
	get_vars;
	set_macro;
	copy_cron;
	create_log_file;
	create_conf_files;
		
	check_update_database;
fi

echo ""
echo "$line"
echo -e "\tEnd of installation"
echo "$line"
echo -e ""
echo_success "Installation is complete !" "$ok"
echo -e ""

${CAT} << __EOT__

###############################################################################
#                                                                             #
#      Report bugs at                                                         #
#           http://forge.centreon.com/projects/centreon-syslog/issues/new     #
#                                                                             #
###############################################################################
__EOT__

exit 0
