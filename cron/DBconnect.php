<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * Project name : Centreon Syslog
 * Module name: Centreon-Syslog-Server
 * 
 * SVN : $URL:$
 * SVN : $Id:$
 * 
 */
 
 	require_once "DB.php";
 	
 	/*
 	 * Import database connection information
 	 */
 	if (file_exists(@DIR@))
		include(@DIR@);
	else
		exit();
 	
	global $pear_syslogDB, $syslogOpt;

	/*
	 * Make PEAR connection object
	 */
	function getSyslogParameters() {
		global $pear_syslogDB, $syslogOpt;

		$syslogDB = array(
		    'phptype'  => 'mysql',
		    'username' => $syslogOpt["syslog_server_db_user"],
		    'password' => $syslogOpt["syslog_server_db_password"],
		    'hostspec' => $syslogOpt["syslog_server"],
		    'database' => $syslogOpt["syslog_db_name"],
		);

		$options = array(
		    'debug'       => 2,
		    'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
		);

		$pear_syslogDB =& DB::connect($syslogDB, $options);

		if (PEAR::isError($pear_syslogDB))
		    die($pear_syslogDB->getMessage());

		$pear_syslogDB->setFetchMode(DB_FETCHMODE_ASSOC);

		return $pear_syslogDB;
	}
?>