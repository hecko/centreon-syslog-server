<?php
/*
 * Copyright 2005-2012 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

	include 'DBconnect.php';

	/*
	 * Define global variables
	 */
	$table_cache_tmp = "temp";
	$table_cache = "cache";
	$merge_cache = "all_cache";

	/*
	 * Create temporary cache table
	 */
	function createTemporaryTable() {
		global $table_cache_tmp, $pear_syslogDB;
		
		$query = "CREATE TABLE IF NOT EXISTS `".$table_cache_tmp."` (";
		$query = $query." `type` enum('HOST','FACILITY','PROGRAM','PRIORITY', 'TAG') collate utf8_unicode_ci default NULL,";
		$query = $query." `value` varchar(50) collate utf8_unicode_ci default NULL";
		$query = $query." ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

		$pear_syslogDB->query($query);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage();
		}
	}
	
	/*
	 * Check if logs table exist
	 */
	function checkLogTable() {
		global $pear_syslogDB, $syslogOpt;

        $query = "SHOW TABLES LIKE 'logs';";

        $res =& $pear_syslogDB->query($query);
        if (PEAR::isError($pear_syslogDB)) {
                print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
        }

        $row = $res->fetchRow();
        if ($row["Tables_in_".$syslogOpt["syslog_db_name"]." (logs)"] == "logs") {
          return 0;
        } else {
          return 1;
        }
	}
	
	/*
	 * Get cache (keys and values) and insert it into temporary cache table
	 */
	function insertCache() {
		global $table_cache_tmp, $pear_syslogDB, $syslogOpt;
		
		$query_host = "SELECT distinct(host) as host FROM logs";
		$query_tag = "SELECT distinct(tag) as tag FROM logs";
		$query_facility = "SELECT distinct(facility) as facility FROM logs";
		$query_priority = "SELECT distinct(priority) as priority FROM logs";
		$query_program = "SELECT distinct(program) as program FROM logs";
		
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
	}
	
	/*
	 * Create new cache table from temporary cache table
	 */
	function createCacheTable() {
		global $table_cache, $table_cache_tmp, $merge_cache, $pear_syslogDB, $syslogOpt;

		$query = "DROP TABLE IF EXISTS ".$merge_cache.";";
		$pear_syslogDB->query($query);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}

		$query = "DROP TABLE IF EXISTS ".$table_cache.";";
		$pear_syslogDB->query($query);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}
		
		$query = "CREATE TABLE ".$table_cache." AS SELECT * FROM ".$table_cache_tmp.";";
		$pear_syslogDB->query($query);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}
		
		$pear_syslogDB->query("FLUSH TABLES");
	  	if (PEAR::isError($pear_syslogDB)) {
	   		print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
	  	}
		
		$query = "DROP TABLE ".$table_cache_tmp;
		$pear_syslogDB->query($query);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}
	}

	/*
	 * Get names of cache tables
	 */
	function getCacheTables() {
	  	global $pear_syslogDB, $syslogOpt, $cacheTables;
	
	  	$res =& $pear_syslogDB->query("SHOW TABLES;");
	  	if (PEAR::isError($pear_syslogDB)) {
	    	print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
	  	}
	
	  	if ($res->numRows()){
	    	while($row = $res->fetchRow()) {
	      		if (strstr($row["Tables_in_".$syslogOpt["syslog_db_name"]], "cache")) {
					$cacheTables[$row["Tables_in_".$syslogOpt["syslog_db_name"]]] = $row["Tables_in_".$syslogOpt["syslog_db_name"]];
	      		}
	    	}
	  	}
	  	return $cacheTables;
	}

	/*
	 * Rebuild merge table "all_cache"
	 */
	function mergeAllCache() {
	  	global $pear_syslogDB, $merge_cache, $table_cache;
	
	  	$query = "DROP TABLE IF EXISTS ".$merge_cache.";";
	  	$pear_syslogDB->query($query);
	  	if (PEAR::isError($pear_syslogDB)) {
	    	print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
	  	}
	  
	  	$mergeTable = "CREATE TABLE $merge_cache (";
	  	$mergeTable = $mergeTable . "`type` enum('HOST','FACILITY','PROGRAM','PRIORITY', 'TAG') collate utf8_unicode_ci default NULL,";
	  	$mergeTable = $mergeTable . "`value` varchar(50) collate utf8_unicode_ci default NULL";
	  	$mergeTable = $mergeTable . ") ENGINE=MRG_MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci UNION=(";
	
	  	$CacheTables = getCacheTables();
	
	  	foreach ($CacheTables as $CacheTable) {
	   		if (strcmp($CacheTable, "cache") > 0) {
	      		$mergeTable = $mergeTable."`".$CacheTable."`, ";
	    	}
	  	}
	
	  	$mergeTable = $mergeTable." ".$table_cache." );";
	
	  	$pear_syslogDB->query($mergeTable);
	  	if (PEAR::isError($pear_syslogDB)) {
	    	print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
	  	}
	
	  	$pear_syslogDB->query("FLUSH TABLES");
	  	if (PEAR::isError($pear_syslogDB)) {
	   		print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
	  	}
	}

	/*
	 * Check if process is running
	 * @processName name of process
	 */
	function controlProcess($processName)
	{
		global $pear_syslogDB;
		
		$query = "SELECT `status` FROM `instance` WHERE `name` = \"".$processName."\";";
		
		$res =& $pear_syslogDB->query($query);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage();
		}
		
		$row = $res->fetchRow();
		if ($row["status"] == "0") {
			$query = "UPDATE `instance` SET `status` = '1' WHERE CONVERT( `instance`.`name` USING utf8 ) = 'reloadCache';";
			
			$pear_syslogDB->query($query);
			if (PEAR::isError($pear_syslogDB)) {
				print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
			}
			return 0;
		} else {
			if (preg_match("/reloadCache/", $processName)) {			
				$numberOfProcess = exec("ps -ef | grep \"".$processName.".php\" | wc -l | bc");
				
				if ($numberOfProcess == 0) {return 0;} # No process but 1 into centeon_syslog.instance
				
				$numberOfRetry = 0;
				while($numberOfRetry < 3) {
					sleep(10);
					$numberOfRetry++;
				}
				
				$numberOfProcess = exec("ps -ef | grep \"".$processName.".php\" | wc -l | bc");
				if ($numberOfProcess == 0) {
					return 0; # Process disappear
				} else {
					exec("ps -ef | grep \"".$processName.".php\" | grep -v \"grep\" | awk {'print $2'} | xargs -exec kill"); # kill all reloadCache.php process
				}
				
				$numberOfProcess = exec("ps -ef | grep \"".$processName.".php\" | wc -l | bc");
				if ($numberOfProcess == 0) { return 0;} # All process killed
			}
			return 1;
		}
	}

	/*
	 * Free process in database
	 */
	function freeProcess() {
		global $pear_syslogDB, $syslogOpt;
		
		$query = "UPDATE `instance` SET `status` = '0' WHERE CONVERT( `instance`.`name` USING utf8 ) = 'reloadCache';";
		
		$pear_syslogDB->query($query);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}
	}

	/*
	 * Create table logs
	 */
	function createTableLogs() {
		global $pear_syslogDB, $syslogOpt;
		
		$newTable = "CREATE TABLE IF NOT EXISTS `".$syslogOpt["syslog_db_name"]."`.`logs` (";
		$newTable = $newTable." host varchar(128) default NULL,";
		$newTable = $newTable." facility varchar(10) default NULL,";
		$newTable = $newTable." priority varchar(10) default NULL,";
		$newTable = $newTable." level varchar(10) default NULL,";
		$newTable = $newTable." tag varchar(10) default NULL,";
		$newTable = $newTable." datetime datetime default NULL,";
		$newTable = $newTable." program varchar(@SYSLOG_PROGRAM_FIELD_SIZE@) default NULL,";
		$newTable = $newTable." msg text,";
		$newTable = $newTable." seq bigint(20) unsigned NOT NULL auto_increment,";
		$newTable = $newTable." counter int(11) NOT NULL default '1',";
		$newTable = $newTable." fo datetime default NULL,";
		$newTable = $newTable." lo datetime default NULL,";
		$newTable = $newTable." PRIMARY KEY  (seq),";
		$newTable = $newTable." KEY datetime (datetime),";
		$newTable = $newTable." KEY priority (priority),";
		$newTable = $newTable." KEY facility (facility),";
		$newTable = $newTable." KEY program (program),";
		$newTable = $newTable." KEY host (host),";
		$newTable = $newTable." KEY host_datetime (host,datetime)";
		$newTable = $newTable." ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		
		$pear_syslogDB->query($newTable);
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage()."\n";
		}
		else {
			print "CREATE TABLE logs\n";
		}
		
		$pear_syslogDB->query("FLUSH TABLES");
		if (PEAR::isError($pear_syslogDB)) {
			print "Mysql Error : ".$pear_syslogDB->getMessage();
		}
	}
	
	/*
	 * Main program
	 */
	 
	 $pear_syslogDB = getSyslogParameters();
	 // Control if "tableLogRotate" is not running else wait 10 seconds
	while (true) {
		if (controlProcess("reloadCache") == 1) {exit;}
		if (controlProcess("tableLogRotate") == 1) {sleep(10);}
		else {break;}
		
	}
	
	print "BEGIN RELOAD CACHE AT ".date("Y-m-d H:i:s")."\n";
	
	if (checkLogTable() == 0) {
		createTemporaryTable();
		insertCache();
		createCacheTable();
		mergeAllCache();
	} else {
		createTableLogs();
	}
	freeProcess();
	print "END OF RELOAD CACHE AT ".date("Y-m-d H:i:s")."\n";
	print "\n";
?>
