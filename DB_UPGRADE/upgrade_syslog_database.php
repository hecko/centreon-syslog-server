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
 
 	global $pear_syslogDB, $table_cache_tmp, $logsTables, $columnName;

	$table_cache_tmp = "temp";

	/*
	 * Make PEAR-DB object to access on syslog database
	 */
	function makePearDB() {
		global $pear_syslogDB, $syslogOpt, $columnName;

		print "Try to connect to syslog database at".date("Y-m-d H:i:s")."\n";

		$syslogDB = array(
		    'phptype'  => 'mysql',
		    'username' => "syslog_server_db_user",
		    'password' => "syslog_server_db_password",
		    'hostspec' => "syslog_server",
		    'database' => "syslog_db",
		);
		
		$columnName = "Tables_in_".$syslogDB['database'];

		$options = array(
		    'debug'       => 2,
		    'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
		);

		$pear_syslogDB =& DB::connect($syslogDB, $options);

		if (PEAR::isError($pear_syslogDB)) {
			 die($pear_syslogDB->getMessage()."\n");
		} else {
			print "Connection successfull\n";
		}		  

		$pear_syslogDB->setFetchMode(DB_FETCHMODE_ASSOC);

		return $pear_syslogDB;
	}
	
	/*
	 * Get name of logs tables
	 */
	function getLogsTables() {
		global $pear_syslogDB, $logsTables, $columnName;

		print "Get names of all logs table\n";

		$res =& $pear_syslogDB->query("SHOW TABLES;");
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}

		if ($res->numRows()){
			while($row = $res->fetchRow()) {
				if ((strstr($row[$columnName], "logs")) 
					&& (strcmp($row[$columnName], "logs") != 0)
					&& (strcmp($row[$columnName], "all_logs") != 0)) {
					$logsTables[$row[$columnName]] = $row[$columnName];
				}
			}
		}
		return $logsTables;
	}
	
	/*
	 * Get name of cache tables
	 */
	function getCacheTables() {
		global $pear_syslogDB, $syslogOpt, $cacheTables, $columnName;

		$res =& $pear_syslogDB->query("SHOW TABLES;");
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}

		if ($res->numRows()){
			while($row = $res->fetchRow()) {
				if (strstr($row[$columnName], "cache") 
					&& (strcmp($row[$columnName], "all_cache") != 0)) {
					$cacheTables[$row[$columnName]] = $row[$columnName];
				}
			}
		}
		return $cacheTables;
	}
	
	/*
	 * Create temporary table
	 */
	function createTemporaryTable() {
		global $table_cache_tmp, $pear_syslogDB;
		
		$query = "CREATE TABLE IF NOT EXISTS `".$table_cache_tmp."` (";
		$query = $query." `type` enum('HOST','FACILITY','PROGRAM','PRIORITY', 'TAG') collate utf8_unicode_ci default NULL,";
		$query = $query." `value` varchar(50) collate utf8_unicode_ci default NULL";
		$query = $query." ) ENGINE=MEMORY DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

		$pear_syslogDB->query($query);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}
	}
	
	/*
	 * For each logs table create cache table associated
	 */
	function createCacheTable() {
		global $pear_syslogDB, $logsTables, $table_cache_tmp;
		
		if (count($logsTables) != 0) {
			foreach ($logsTables as $logTable) {
				$cacheTables = getCacheTables();
				$actualCache = substr($logTable,4); // Get datetime forma YYYYMMdd
				$actualCache = "cache".$actualCache;
				
				 if (!isset($cacheTables[$actualCache])) {
					print "Create table ".$actualCache." at ".date("Y-m-d H:i:s")."\n";
					
					createTemporaryTable();
					
					// Get cache information	
					$query_host = "SELECT distinct(host) as host FROM ".$logTable.";";
					$query_tag = "SELECT distinct(tag) as tag FROM ".$logTable.";";
					$query_facility = "SELECT distinct(facility) as facility FROM ".$logTable.";";
					$query_priority = "SELECT distinct(priority) as priority FROM ".$logTable.";";
					$query_program = "SELECT distinct(program) as program FROM ".$logTable.";";
					
					## HOST
					$res =& $pear_syslogDB->query($query_host);
					if (PEAR::isError($pear_syslogDB)) {
						print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
					}
			
					if ($res->numRows()){
						while($row = $res->fetchRow()) {
							$pear_syslogDB->query("INSERT INTO ".$table_cache_tmp." (type, value) VALUES('HOST', \"".$row["host"]."\")");
							if (PEAR::isError($pear_syslogDB)) {
								print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
							}
						}
					}
					
					## TAG
					$res =& $pear_syslogDB->query($query_tag);
					if (PEAR::isError($pear_syslogDB)) {
						print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
					}
			
					if ($res->numRows()){
						while($row = $res->fetchRow()) {
							$pear_syslogDB->query("INSERT INTO ".$table_cache_tmp." (type, value) VALUES('TAG', \"".$row["tag"]."\")");
							if (PEAR::isError($pear_syslogDB)) {
								print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
							}
						}
					}
					
					## FACILITY
					$res =& $pear_syslogDB->query($query_facility);
					if (PEAR::isError($pear_syslogDB)) {
						print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
					}
			
					if ($res->numRows()){
						while($row = $res->fetchRow()) {
							$pear_syslogDB->query("INSERT INTO ".$table_cache_tmp." (type, value) VALUES('FACILITY', \"".$row["facility"]."\")");
							if (PEAR::isError($pear_syslogDB)) {
								print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
							}
						}
					}
					
					## PRIORITY
					$res =& $pear_syslogDB->query($query_priority);
					if (PEAR::isError($pear_syslogDB)) {
						print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
					}
			
					if ($res->numRows()){
						while($row = $res->fetchRow()) {
							$pear_syslogDB->query("INSERT INTO ".$table_cache_tmp." (type, value) VALUES('PRIORITY', \"".$row["priority"]."\")");
							if (PEAR::isError($pear_syslogDB)) {
								print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
							}
						}
					}
					
					## PROGRAM
					$res =& $pear_syslogDB->query($query_program);
					if (PEAR::isError($pear_syslogDB)) {
						print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
					}
			
					if ($res->numRows()){
						while($row = $res->fetchRow()) {
							$pear_syslogDB->query("INSERT INTO ".$table_cache_tmp." (type, value) VALUES('PROGRAM', \"".$row["program"]."\")");
							if (PEAR::isError($pear_syslogDB)) {
								print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
							}
						}
					}
					
					// Create cache table
					$query = "CREATE TABLE ".$actualCache." AS SELECT * FROM ".$table_cache_tmp.";";
					$pear_syslogDB->query($query);
					if (PEAR::isError($pear_syslogDB)) {
						print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
					} else {
						print "Table ".$actualCache." created\n";
					}
					
					// Drop temporay cache table
					$query = "DROP TABLE ".$table_cache_tmp;
					$pear_syslogDB->query($query);
					if (PEAR::isError($pear_syslogDB)) {
						print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
					}
				}
			}
		}
	}
 
 	/*
 	 * Create cache merge table
 	 */
 	function mergeAllCache() {
		global $pear_syslogDB, $syslogOpt;
  
		$pear_syslogDB->query("DROP TABLE IF EXISTS `all_cache`;");
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}
		else {
			print "Drop table all_cache\n";
		}
  
		$mergeTable = "CREATE TABLE `all_cache` (";
		$mergeTable = $mergeTable . "`type` enum('HOST','FACILITY','PROGRAM','PRIORITY', 'TAG') collate utf8_unicode_ci default NULL,";
		$mergeTable = $mergeTable . "`value` varchar(50) collate utf8_unicode_ci default NULL";
		$mergeTable = $mergeTable . ") ENGINE=MRG_MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci UNION=(";
  
		$CacheTables = getCacheTables();
  
		foreach ($CacheTables as $CacheTable) {
			if (strcmp($CacheTable, "cache") > 0) {
				$mergeTable = $mergeTable."`".$CacheTable."`, ";
			}
		}
		$mergeTable = substr($mergeTable,0,strlen($mergeTable)-2);

		$mergeTable = $mergeTable.");";
  
		$pear_syslogDB->query($mergeTable);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}
		else {
			print "Table all_cache created\n";
		}
  
		$pear_syslogDB->query("FLUSH TABLES");
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}
	}
 
 	/*
	 * Main program
	 */
 	print "BEGIN UPGRADE FOR SYSLOG DATABASE AT ".date("Y-m-d H:i:s")."\n";
 	$pear_syslogDB = makePearDB();
 	$logsTables = getLogsTables();
 	$cacheTables= getCacheTables();
 	createCacheTable();
 	mergeAllCache();
 	print "FINISH UPGRADE FOR SYSLOG DATABASE AT ".date("Y-m-d H:i:s")."\n";
 ?>