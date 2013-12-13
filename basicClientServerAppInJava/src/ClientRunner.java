import java.awt.BorderLayout;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.io.IOException;
import java.net.Socket;
import javax.swing.JFrame;
import javax.swing.JScrollPane;
import javax.swing.JTextArea;
import javax.swing.JTextField;

import com.richardroque.sockets.ClientSocket;
import com.richardroque.sockets.SocketListener;

public class ClientRunner {

	public static void main(String[] args) {
		String host = "localhost";
		int port = 9000;
				
		try {
			JFrame jf = new JFrame();
			jf.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
			
			final JTextArea messages = new JTextArea();
			final JTextField message = new JTextField();
			
			message.setFocusable(true);
			messages.setEditable(false);			
			
			jf.add(new JScrollPane(messages), BorderLayout.CENTER);
			jf.add(message, BorderLayout.SOUTH);
			
			final ClientSocket clientSocket = new ClientSocket(new Socket(host, port));						
			clientSocket.listeners.add(new SocketListener() {
				@Override
				public void onNewData(Object data) {
					String msg = (String) data;
					messages.setText(messages.getText() + msg.trim() + "\n" );
				}				
				@Override
				public void onDisconnect() {}
			});
			
			message.addActionListener(new ActionListener() {				
				public void actionPerformed(ActionEvent e) {
					clientSocket.writer.println(message.getText());
					message.setText("");
				}
			});
			
		
			jf.setVisible(true);
			jf.setSize(200, 150);
			
		} catch (IOException e) {
			System.out.println(e.getMessage());
			e.printStackTrace();
		}
	}
}
