package com.richardroque.sockets;

public interface SocketListener {
	public void onNewData(Object data);	
	public void onDisconnect();	
}