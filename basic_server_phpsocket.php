<?php

$port = $argv[1] ? intVal($argv[1]) : 8080;
$sockets = array();
$sockets[] = $serverSocket = @socket_create_listen($port);
echo "Server listening to port:{$port}\r\n";
if(socket_last_error()) {
	echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
	exit;
}

while(true) {
	$socketsToRead = $sockets;
	$readCount = socket_select($socketsToRead, $write=null, $except=null, 0);	

	if($readCount > 0) {
		foreach($socketsToRead as $socket) {
			if(in_array($serverSocket, $socketsToRead)) {
				$sockets[] = $newClientSocket = socket_accept($socket);
				$key = array_search($socket, $socketsToRead);
				unset($socketsToRead[$key]);
				socket_write($newClientSocket, chr(0));
			} else {
				$data = @socket_read($socket, 1024, PHP_NORMAL_READ);
				if(!$data) {
					$key = array_search($socket, $sockets);
					unset($sockets[$key]);
				} else {
					foreach ($sockets as $clientSocket) {
						if($clientSocket!=$serverSocket && $clientSocket!=$socket) {						
							socket_write($clientSocket, $data);
						}
					}
				}
			}
		}
	}
}
