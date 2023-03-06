<?php

// Definir o identificador da fila de mensagens
define('MSG_QUEUE', 1234);

// Criar uma nova fila de mensagens, se não existir
if (!msg_queue_exists(MSG_QUEUE)) {
    msg_remove_queue(msg_get_queue(MSG_QUEUE, 0666 | IPC_CREAT));
}

// Loop infinito
while (true) {
    // Verificar se há novas mensagens na fila a cada 5 segundos
    $msg_type = null;
    $max_msg_size = 1024;
    $flags = 0;
    $message = null;
    $error_code = null;
    $result = msg_receive(MSG_QUEUE, 1, $msg_type, $max_msg_size, $message, false, $flags, $error_code);

    // Se uma nova mensagem foi recebida, tratar a mensagem
    if ($result) {
        // Obter a mensagem recebida
        $mensagem = $message;

        // Conectar-se ao banco de dados
        $host = "localhost";
        $username = "usuario";
        $password = "senha";
        $database = "nome_do_banco";
        $mysqli = new mysqli($host, $username, $password, $database);

        // Verificar se a conexão foi bem-sucedida
        if ($mysqli->connect_errno) {
            // Se houve um erro de conexão, enviar uma mensagem de falha
            $error_message = "Falha ao conectar-se ao banco de dados: " . $mysqli->connect_error;
            $response = array(
                'success' => false,
                'message' => $error_message
            );
        } else {
            // Se a conexão foi bem-sucedida, inserir a mensagem no banco de dados
            $sql = "INSERT INTO mensagens (mensagem) VALUES ('$mensagem')";
            if ($mysqli->query($sql)) {
                // Se a inserção foi bem-sucedida, enviar uma mensagem de sucesso
                $response = array(
                    'success' => true,
                    'message' => "Mensagem inserida com sucesso"
                );
            } else {
                // Se a inserção falhou, enviar uma mensagem de falha
                $error_message = "Falha ao inserir a mensagem no banco de dados: " . $mysqli->error;
                $response = array(
                    'success' => false,
                    'message' => $error_message
                );
            }

            // Fechar a conexão com o banco de dados
            $mysqli->close();
        }

        // Enviar a resposta de volta para o script que enviou a mensagem
        $response_message = json_encode($response);
        msg_send($msg_type, $MSG_QUEUE, $response_message);
    }

    // Aguardar por 5 segundos antes de verificar novamente a fila de mensagens
    sleep(5);
}
