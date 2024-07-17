<?php
// connection.php

function getServerList($conn) {
    $serverList = array_keys($conn->servers);
    return $serverList;
}

function connectToDatabase($serverName = null) {
    
   $servers = [
   
   // Fill in the login information for each server
   
		'server1' => [
        'hostname' => 'ptero01.adminom.hu',
        'port' => '3306',
        'username' => 'u6453_Gruq03AkBm',
        'password' => 'II@6VK.Ec==akL+=fDX8D3yC',
        'database' => 's6453_ADMINS',
        'prefix' => ''
    ],     
	// Add other servers here.



];

    // If serverName is not provided, set it to the first server in the list.
    if ($serverName === null) {
        $serverName = key($servers);
    }

    // Check if serverName is valid.
    if (isset($servers[$serverName])) {
        $serverInfo = $servers[$serverName];
    } else {
        die("Invalid server selection");
    }
   $serverInfo['prefix'] = $servers[$serverName]['prefix'];

    try {
        
// Add the port to the connection string.
        $conn = new PDO("mysql:host={$serverInfo['hostname']};port={$serverInfo['port']};dbname={$serverInfo['database']};charset=utf8mb4", $serverInfo['username'], $serverInfo['password']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Add the variable $servers to the connection result.
 $conn->servers = $servers;
        $conn->defaultServer = key($servers); // Add the key of the first server to the connection result.

        return $conn;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}


?>