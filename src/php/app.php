<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>System Info</title>
	<link rel="stylesheet" href="style.css">
	<link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
	<link rel="icon" href="favicon.png" type="image/x-con">
</head>
<body>
<p>
	Select size
	<select onchange="changedSelect()">
	  <option value="GiB">GiB</option>
	  <option value="MiB">MiB</option>
	  <option value="kiB">kiB</option>
	  <option value="B">B</option>
	</select>
</p>
<label><input type="checkbox" class="refresh">Disable autorefresh</label>
</br>
<label><input type="checkbox" class="date">Disable showing date of warning</label>
<?php

require('./sqling.php');
require('./objects.php');
// emergency way to show info about devices
$emergency = false;

$sqling = new Sqling;
$sqling->connect();
$sqling->getHighestCPUUsage();
$sqling->generateCPUWarning();
$sqling->generateRAMWarning();

// Load libs
// require_once dirname(__FILE__).'/init.php';

// Load settings and language
// $linfo = new Linfo;

// Run through /proc or wherever and build our list of settings
// $linfo->scan();
// $anotherParser = $linfo->getParser();

// $names = ["OS", "Kernel", "AccessedIP", "Distro", "RAM", "HD", "Mounts", "Load", "HostName", "UpTime", "CPU", "Model", "CPUArchitecutre", "Network Devices", "Devices", "Temps", "Battery", "Raid", "Wifi", "SoundCards", "processStats", "services", "numLoggedIn", "virtualization", "cpuUsage", "phpVersion", "webService", "contains"];
// $parser = $linfo->getInfo();

$objects = new Objects();

// Table for system specs
$objects->showSystem();

echo "<br/>";

$objects->showCPU();

echo "<br/>";

$objects->showRAM();

echo "<br/>";

$objects->showNetwork();

echo "<br/>";

$objects->showDrives();

// echo '<pre>' . var_export($parser["Mounts"], true) . '</pre>';
echo "<br/>";

// Table for Mounted Drives
$objects->showMounted();


$sqling->createSQL($objects->getParser());
$sqling->send();
$sqling->closeConnection();

$objects->emergencyDisplay();

?>
<script src="../js/main.js"></script>
<script src="../js/bubbles.js"></script>
</body>
</html>
