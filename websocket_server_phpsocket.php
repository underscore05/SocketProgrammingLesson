<?php

$port = intVal($argv[1] ? $argv[1] : 8080);
$sockets = array();
$sockets[] = $serverSocket = socket_create_listen($port);
echo "Server Listening to port:{$port}";
while(true) {
	$socketsToRead = $sockets;
	if(socket_select($socketsToRead, $write=null, $exception=null, 0) > 0 ) {
		if(in_array($serverSocket, $socketsToRead)) {
			$sockets[] = $newClientSocket = socket_accept($serverSocket);			
			$key = array_search($serverSocket, $socketsToRead);
			unset($socketsToRead[$key]);
			performHandshake($newClientSocket);
		}
		foreach ($socketsToRead as $socket) {
			$data = readSocket($socket);			
			if(!$data) {
				$key = array_search($socket, $sockets);
				unset($sockets[$key]);
			} else if(trim($data)!="") {
				$data = mask(unmask($data));				
				foreach($sockets as $clientSocket) {
					if($clientSocket!=$serverSocket) {
						socket_write($clientSocket, $data);
					}
				}
			}
		}
	}
}


//Required functions
//Generate Handshake headers
//Parse websocket sent headers
//Generate handshake key
//Parse incoming data
//Encode outgoing data

function readSocket($socket) {
	return @socket_read($socket, 2048, MSG_WAITALL);
}

function performHandshake($socket) {
	$data = readSocket($socket);
	$headers = parseHeaders($data);
	$key = generateHandshakeKey($headers["Sec-WebSocket-Key"]);
	$response = "HTTP/1.1 101 Switching Protocols\r\n".
				"Upgrade: websocket\r\n".
				"Connection: Upgrade\r\n".
				"Sec-WebSocket-Accept: $key\r\n\r\n";
	socket_write($socket, $response);
}

function parseHeaders($data) {
	$headers = array();
	if(preg_match_all("/(.*): (.*)\r\n/", $data, $matches)){
		for($i=0, $max=count($matches[1]); $i<$max;  $i++) {
			$headerName = $matches[1][$i];
			$headers[$headerName] = $matches[2][$i];
		}				
	}
	return $headers;
}

function generateHandshakeKey($key) {
	$guid = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
	return base64_encode(sha1($key.$guid, true));
}

function unmask($data) {
	$check = (0x1 << 7) | 0x8;
	$length = ord($data[1]) & 127;
	if($length == 126) {
		$masks = substr($data, 4, 4);
		$data = substr($data, 8);
	} elseif($length == 127) {
		$masks = substr($data, 10, 4);
		$data = substr($data, 14);
	} else {
		$masks = substr($data, 2, 4);
		$data = substr($data, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;                             
}

function mask($text) {
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}