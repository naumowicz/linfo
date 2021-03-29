<?php

class Sqling {
	private $servername = "localhost";
	private $username = "root";
	private $password = "";
	private $dbname = "linfoapp";
	
	private $databaseConnection = NULL;
	
	private $sqlSystem = '';
	private $sqlCPU = '';
	private $sqlRAM = '';
	private $netDevicesDataSQL = array();

	private $sqlGetCPUUsage = '';

	function connect() {
		$this->databaseConnection = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
		if ($this->databaseConnection->connect_error) {
			die("Connection failed: " . $this->databaseConnection->connect_error);
		}
	}
	
	function createSQL($parser) {
		$this->sqlSystem = 'INSERT INTO `system` (`Hostname`, `Processes`, `Threads`, `SystemLoad`, `Uptime`, `SystemDate`) VALUES ("' . $parser['HostName'] . '","' . $parser['processStats']['proc_total'] . '","' . $parser['processStats']['threads'] . '","' . $parser['Load'] . '","' . $parser['UpTime']['text'] . '", NOW())';
	
	
		$MHzSum = 0;
		$usageSum = 0;
		for ($i=0; $i < count($parser["CPU"]); $i++) { 
			$MHzSum += $parser["CPU"][$i]["MHz"];
			$usageSum += $parser["CPU"][$i]["usage_percentage"];
		}
		$avgMHz = $MHzSum / count($parser["CPU"]);
		$avgUsage = $usageSum / count($parser["CPU"]);
		$this->sqlCPU = 'INSERT INTO `cpuinfo` (`Model`, `MHz`, `UsagePercentage`, `CPUDate`) VALUES ("' . $parser["CPU"][0]["Model"] . '","' . $avgMHz . '","' . $avgUsage . '", NOW())';
		
	
		$this->sqlRAM = "INSERT INTO `ram` (`Total`, `Free`, `RAMDate`) VALUES (" . $parser["RAM"]["total"] . "," . $parser["RAM"]["free"] . ", NOW())";
		
		
		foreach ($parser["Network Devices"] as $key => $value) {
			$a = array($key, $value["recieved"]["bytes"], $value["sent"]["bytes"], $value["state"], $value["type"]);
			$netSQL = 'INSERT INTO `networkdevices` (`Name`, `ReceivedBytes`, `SentBytes`, `Status`, `DeviceType`, `NetworkDate`) VALUES ("' . $a[0] . '","' . $a[1] . '","' . $a[2] . '","' . $a[3] . '","' . $a[4] . '", NOW())';
		
			array_push($this->netDevicesDataSQL, $netSQL);
		}		
	}
	
	function send() {
		$this->databaseConnection->query($this->sqlSystem);
		$this->databaseConnection->query($this->sqlCPU);
		$this->databaseConnection->query($this->sqlRAM);
		foreach ($this->netDevicesDataSQL as $key => $value) {
			$this->databaseConnection->query($value);
		}
	}
	
	function closeConnection() {
		$this->databaseConnection->close();
	}

	function getHighestCPUUsage() {
		$this->getHighestCPUUsage = 'SELECT * FROM `cpuinfo` ORDER BY `UsagePercentage` DESC LIMIT 1';
		$dataFromDatabase = $this->databaseConnection->query($this->getHighestCPUUsage);
		if ($dataFromDatabase->num_rows > 0) {
			// output data of each row
			while($row = $dataFromDatabase->fetch_assoc()) {
				echo "<p>Highest CPU Usage noticed at: " . $row["CPUDate"] . " with value " . $row["UsagePercentage"] . "%</p>";
			}
		} 
	} 

	function generateCPUWarning() {
		$JSON = file_get_contents("config.json");
		$array = json_decode($JSON, true);

		$sqlCPUWarning = "SELECT * FROM cpuinfo WHERE (UsagePercentage >= " . $array["CPUwarning"] . ") AND (CPUDate > DATE_SUB(NOW(), INTERVAL 1 DAY))";
		$sqlCPUMax = "SELECT * FROM cpuinfo WHERE (UsagePercentage >= " . $array["CPUmax"] . ") AND (CPUDate > DATE_SUB(NOW(), INTERVAL 1 DAY))";

		$dataFromDatabase = $this->databaseConnection->query($sqlCPUWarning);
		$dataFromDatabase2 = $this->databaseConnection->query($sqlCPUMax);

		if ($dataFromDatabase->num_rows > 0) {
			if ($dataFromDatabase->num_rows == 1) {
				echo "<p><span class='red'>CPU warning value exceeded " . $dataFromDatabase->num_rows . " time!</span>";
			} else {
				echo "<p><span class='red'>CPU warning value exceeded " . $dataFromDatabase->num_rows . " times!</span>";
			}

			if($array["ShowErrorDate"]) {
				echo "<ul>";
				while($row = $dataFromDatabase->fetch_assoc()) {
					echo "<li>" . $row['CPUDate'] . "</li>";
				}
				echo "</ul>";
				echo "</p>";
			}
		}

		if ($dataFromDatabase2->num_rows > 0) {
			if ($dataFromDatabase2->num_rows == 1) {
				echo "<p><span class='pulsate'>CPU was overloaded " . $dataFromDatabase2->num_rows . " time!</span></p>";
			} else {
				echo "<p><span class='pulsate'>CPU was overloaded " . $dataFromDatabase2->num_rows . " times!</span></p>";
			}

			if($array["ShowErrorDate"]) {
				echo "<ul>";
				while($row = $dataFromDatabase2->fetch_assoc()) {
					echo "<li>" . $row['CPUDate'] . "</li>";
				}
				echo "</ul>";
				echo "</p>";
			}
		}
	}

	function generateRAMWarning() {
		$JSON = file_get_contents("config.json");
		$array = json_decode($JSON, true);
		
		$sqlRAMWarning = "SELECT * FROM `ram` WHERE Free / Total < " . $array["RAMwarning"] . "AND (RAMDate > DATE_SUB(NOW(), INTERVAL 1 DAY))";
		$sqlRAMMax = "SELECT * FROM `ram` WHERE Free / Total < " . $array["RAMmax"] . "AND (RAMDate > DATE_SUB(NOW(), INTERVAL 1 DAY))";

		$dataFromDatabase = $this->databaseConnection->query($sqlRAMWarning);
		$dataFromDatabase2 = $this->databaseConnection->query($sqlRAMMax);

		if ($dataFromDatabase->num_rows > 0) {
			if ($dataFromDatabase->num_rows == 1) {
				echo "<p><span class='red'>RAM warning value exceeded " . $dataFromDatabase->num_rows . " time!</span>";
			} else {
				echo "<p><span class='red'>RAM warning value exceeded " . $dataFromDatabase->num_rows . " times!</span>";
			}

			if($array["ShowErrorDate"]) {
				echo "<ul>";
				while($row = $dataFromDatabase->fetch_assoc()) {
					echo "<li>" . $row['RAMDate'] . "</li>";
				}
				echo "</ul>";
			}	
			echo "</p>";
		}

		if ($dataFromDatabase2->num_rows > 0) {
			if ($dataFromDatabase2->num_rows == 1) {
				echo "<p><span class='pulsate'>RAM was overloaded " . $dataFromDatabase2->num_rows . " time!</span></p>";
			} else {
				echo "<p><span class='pulsate'>RAM was overloaded " . $dataFromDatabase2->num_rows . " times!</span></p>";
			}

			if($array["ShowErrorDate"]) {
				echo "<ul>";
				while($row = $dataFromDatabase2->fetch_assoc()) {
					echo "<li>" . $row['RAMDate'] . "</li>";
				}
				echo "</ul>";
			}			
			echo "</p>";
		}

	}
}
?>