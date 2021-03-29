<?php
require_once dirname(__FILE__).'/init.php';


class Objects {
	private $system;
	private $cpu;
	private $ram;
	private $network; 
	private $drives;
	private $mounted;

	private $linfo = "";
	private $parser = "";

	private $emergency;

	function __construct($sys = true, $processor = true, $memory = true, $net = true, $hd = true, $partitions = true, $rescue = false) {
		$this->system = $sys;
		$this->cpu = $processor;
		$this->ram = $memory;
		$this->network = $net;
		$this->drives = $hd;
		$this->mounted = $partitions;

		$this->emergency = $rescue;

		$this->linfo = new Linfo;
		$this->linfo->scan();

		$this->parser = $this->linfo->getInfo();

		
	}

	function showSystem() {
		if ($this->system == true) {
			echo "<table>";
			echo "<tr><td class='parameter' colspan='2'>SYSTEM:</td></tr>";
			echo "<tr><td>OS:</td><td>" . $this->parser["OS"] . "</td></tr>";
			// echo "<tr><td>Distribution:</td><td>$parser[Distro]</td></tr>";
			echo "<tr><td>Kernel:</td><td>" . $this->parser["Kernel"] . "</td></tr>";
			echo "<tr><td>Hostname:</td><td>" . $this->parser["HostName"] . "</td></tr>";
			echo "<tr><td>Architecture:</td><td>" . $this->parser["CPUArchitecture"] . "</td></tr>";
			echo "<tr><td>Porcesses:</td><td>" . $this->parser["processStats"]["proc_total"] . "</td></tr>";
			echo "<tr><td>Threads:</td><td>" . $this->parser["processStats"]["threads"] . "</td></tr>";
			echo "<tr><td>Load:</td><td>" . $this->parser["Load"] . "</td></tr>";
			// echo "<tr><td>CPU Usage:</td><td>" . $parser["cpuUsage"] . "</td></tr>";
			echo "<tr><td>Uptime:</td><td>" . $this->parser["UpTime"]["text"] . "</td></tr>";
			echo "<tr><td>Booted:</td><td>" . date('d/m/Y H:i:s', $this->parser["UpTime"]["bootedTimestamp"]) . "</td></tr>";
			echo "</table>";
			// echo '<pre>' . var_export($parser["UpTime"], true) . '</pre>';
		}

	}

	function showCPU() {
		if ($this->cpu == true) {
			echo "<table>";
			echo "<tr><td class='parameter' colspan='2'>CPU:</td></tr>";
			echo "<tr><td>CPU INFO:</td><td>" . $this->parser["CPU"]["0"]["Model"] . "</td></tr>";
			foreach ($this->parser["CPU"] as $key => $value) {
				// echo $key . " " . $value;
				echo "<tr>";
				echo "<td>Core $key</td>";
				echo "<td>";
				foreach ($value as $key2 => $value2) {
					if ($key2 == "MHz") {
						echo $value2 . " MHz  -- ";
					}
					if ($key2 == "usage_percentage") {
						echo "Usage Percentage" . " " . $value2 . " ";
					}
					// } else {
					// echo $key2 . " " . $value2 . " ";
					// }
				}
				echo "</td>";
				echo "</tr>";
			}
			echo "</table>";
		}
	}

	function showRAM() {
		if ($this->ram == true) {
			echo "<table>";
			echo "<tr><td class='parameter' colspan='2'>RAM:</td></tr>";
			foreach ($this->parser["RAM"] as $key => $value) {
				if ($value == "Physical") {
					echo "<tr><td>$key</td><td>$value</td></tr>";
				} else {
					echo '<tr><td>' . $key . '</td><td class="bytes" data-bytes="' . $value . '">' . $value . '</td></tr>';
				}
			}
			echo "</table>";
		}
	}

	function showNetwork() {
		if ($this->network == true) {
			echo "<table>";
			echo "<tr><td class='parameter'>Network Devices:</td><td class='parameter'>recieved</td><td class='parameter'>sent</td><td class='parameter'>state</td></tr>";
			foreach ($this->parser["Network Devices"] as $key => $value) {
				echo "<tr>";
				echo "<td>$key</td>";
				foreach ($value as $key2 => $value2) {
					if($key2=="recieved" || $key2=="sent") {
						//echo "<td class='parameter'>$key2</td>";
						// echo "<td class='bytes'>";
						foreach ($value2 as $key3 => $value3) {
							if ($key3 == "bytes")
								echo "<td class='bytes' data-bytes='" . $value3 . "'>" . $value3 . "</td>";
						}
						// echo "</td>";
					}
					else if($key2=="type") {
						//does nothing
					} else {
						if($value2 == "Media disconnected") {
							echo "<td>Not connected</td>";
						} else {				
							echo "<td>$value2</td>";
						}
					}
				}
				echo "</tr>";
			}
			echo "</table>";
		}
	}

	function showDrives() {
		if ($this->drives == true) {
			echo "<table>";
			echo "<tr><td class='parameter' colspan='9'>Drives:</td></tr>";
			foreach ($this->parser["HD"] as $key => $value) {
				echo "<tr>";
				echo "<td>Drive $key</td>";
				foreach ($value as $key2 => $value2) {
					if($key2 == 'device' || $key2 == 'vendor') {
						//does nothing
					} else if($key2 == 'partitions') {			
						foreach ($value2 as $key3 => $value3) {
							echo "<tr>";
							echo "<td>Partition $key3</td>";
							echo '<td colspan="8" class="bytes" data-bytes="' . $value3["size"] . '">' . $value3["size"] . '</td>';
							echo "</tr>";
						}			
					} else if($key2 == 'reads' || $key2 == 'writes') {
						//does nothing
					} else if($key2 == 'size') {
						echo "<td class='bytes' data-bytes='" . $value2 . "'>$value2</td>";
					} else {
						echo "<td class='parameter'>$key2</td>";
						echo "<td>$value2</td>";
					}		
				}
				echo "</tr>";
			}
			echo "</table>";
		}
	}

	function showMounted() {
		if ($this->mounted == true) {
			echo "<table>";
			echo "<tr><td class='parameter' colspan='7'>Mounted Drives</td></tr>";
			echo "<tr id='devtypestyle'><td>Type</td><td>Mount Point</td><td>File-system</td><td>Size</td><td>Used</td><td>Free</td></tr>";
			foreach ($this->parser["Mounts"] as $key => $value) {	
				echo "<tr><td id='devtype'>" . $value["devtype"] . "</td><td>" . $value["mount"] . "</td><td>" . $value["type"] . '</td><td class="bytes" data-bytes="' . $value["size"] . '">' . $value["size"] . '</td><td class="bytes" data-bytes="' . $value["used"] . '">' . $value["used"] . '</td><td class="bytes" data-bytes="' . $value["free"] . '">' . $value["free"] . "</td></tr>";
			}
			echo "</table>";
		}
	}

	function emergencyDisplay() {
		if ($this->emergency) {
			echo '<pre>' . var_export($this->parser["CPU"], true) . '</pre>';
			echo '<pre>' . var_export($this->parser["RAM"], true) . '</pre>';
			echo '<pre>' . var_export($this->parser["Network Devices"], true) . '</pre>';
			echo '<pre>' . var_export($this->parser["HD"], true) . '</pre>';
			echo '<pre>' . var_export($this->parser["Mounts"], true) . '</pre>';
		}
	}

	function getParser(){
		return $this->parser;
	}

}
?>