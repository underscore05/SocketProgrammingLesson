<?php

$sockets = array();
$sockets[] = $serverSocket = socket_create_listen(8080);

$rawData = "<h1>Hello World....!</h1>";

$i= 0;
while(true) {
	$forReading = $sockets;
	$result = socket_select($forReading, $write=null, $except=null, 0);
	if(!$result && $result == 0) {
		continue;
	}
	if(in_array($serverSocket, $forReading)) {
		$sockets[] = $newSocket = socket_accept($serverSocket);
		$key = array_search($serverSocket, $forReading);
		unset($forReading[$key]);
	}
	foreach ($forReading as $clientSocket) {
		$data = socket_read($clientSocket, 1024);		
		if($data) {
			$headers = "HTTP/1.1 200\n".
						"Content-Type: text/html\n".
						"Content-Length: ".strlen($rawData)."\n".
						"";
			$header = "";
			$responseData = $headers."\n".$rawData;
			socket_write($clientSocket, $responseData);			
		}

		$key = array_search($clientSocket, $sockets);
		socket_close($clientSocket);
		unset($sockets[$key]);
		$i++;
	}
}

