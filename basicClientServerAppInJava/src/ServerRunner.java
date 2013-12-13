import java.util.Iterator;
import com.richardroque.sockets.ClientSocket;
import com.richardroque.sockets.Server;
import com.richardroque.sockets.ServerListener;
import com.richardroque.sockets.SocketListener;

public class ServerRunner {

	public static void main(String[] args) {
		final Server server = new Server(9000);
		server.start();
		server.listeners.add(new ServerListener() {
			public void onNewClient(final ClientSocket clientSocket) {
				clientSocket.writer.println("Welcome!");
				clientSocket.listeners.add(new SocketListener() {
					public void onNewData(Object data) {						
						Iterator<ClientSocket> clientSocketIterator = server.getClientSockets();
						while(clientSocketIterator.hasNext()) {
							ClientSocket otherClientSocket =  (ClientSocket) clientSocketIterator.next();
							if(clientSocket!=otherClientSocket) {
								otherClientSocket.writer.println(data);
							}							
						}
					}
					public void onDisconnect() {}	
				});
			}

			public void onClientDisconnect(ClientSocket clientSocket) {
				Iterator<ClientSocket> clientSocketIterator = server.getClientSockets();
				while(clientSocketIterator.hasNext()) {
					ClientSocket otherClientSocket =  (ClientSocket) clientSocketIterator.next();
					otherClientSocket.writer.println("Other client disconnected");
				}
			}
		});
	}
}



