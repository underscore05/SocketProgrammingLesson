package com.richardroque.sockets;

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintStream;
import java.net.Socket;
import java.util.ArrayList;

public class ClientSocket {
	public ArrayList<SocketListener> listeners = new ArrayList<SocketListener>();
	public PrintStream writer;
	public BufferedReader reader;
	public Socket socket;
	
	public ClientSocket(Socket socket) throws IOException {
		this.socket = socket;
		reader = new BufferedReader(new InputStreamReader(socket.getInputStream()));
		writer = new PrintStream(socket.getOutputStream());	
		new Thread() {
			public void run() {
				try {
					String data;						
					while((data=reader.readLine())!=null) {						
						for(SocketListener listener: listeners) {
							listener.onNewData(data);
						}
					}					
					for(SocketListener listener: listeners) {
						listener.onDisconnect();
					}					
				} catch (IOException e) {
					for(SocketListener listener: listeners) {
						listener.onDisconnect();
					}
					//e.printStackTrace();
				}
			}
		}.start();		
	}
}