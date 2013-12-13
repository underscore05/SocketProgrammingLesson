package com.richardroque.sockets;

public interface ServerListener {
	public void onNewClient(ClientSocket clientSocket);
	public void onClientDisconnect(ClientSocket clientSocket);
}
