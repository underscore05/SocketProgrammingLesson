package com.richardroque.sockets;

import java.io.IOException;
import java.net.ServerSocket;
import java.util.ArrayList;
import java.util.Iterator;

public class Server extends Thread {
	private ServerSocket socket;
	private ArrayList<ClientSocket> sockets = new ArrayList<>();
	public ArrayList<ServerListener> listeners = new ArrayList<>();
	
	public Iterator<ClientSocket> getClientSockets() {
		return sockets.iterator();
	}
	
	public Server(int port) {
		try {
			socket = new ServerSocket(port, 10);
		} catch (IOException e) {			
			e.printStackTrace();
		}				
	}	
	public void run() {
		while(true) {
			try {
				final ClientSocket client = new ClientSocket(socket.accept());
				sockets.add(client);
				client.listeners.add(new SocketListener() {
					@Override
					public void onNewData(Object data) {}					
					@Override
					public void onDisconnect() {
						sockets.remove(client);
						for(ServerListener listener: listeners) {
							listener.onClientDisconnect(client);
						}
					}
				});
				for(ServerListener listener: listeners) {
					listener.onNewClient(client);
				}
			} catch (IOException e) {
				e.printStackTrace();
			}
		}
	}
}