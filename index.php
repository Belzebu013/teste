<?php

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém a mensagem a partir do formulário
    $mensagem = $_POST['mensagem'];

    // Cria uma mensagem para ser enviada
    $msg = [
        'tipo' => 'mensagem',
        'dados' => [
            'mensagem' => $mensagem
        ]
    ];

    // Envia a mensagem para o daemon
    if (msg_send($queue, 1, $msg, false, false, $error)) {
        // Aguarda a resposta do daemon
        if (msg_receive($queue, 2, $tipo_resposta, 1024, $resposta, false, 0, $error)) {
            echo "Resultado: $resposta\n";
        } else {
            echo "Erro ao receber resposta: $error\n";
        }
    } else {
        echo "Erro ao enviar mensagem: $error\n";
    }
}
?>

<form method="POST">
    <label for="mensagem">Mensagem:</label>
    <input type="text" name="mensagem">
    <button type="submit">Enviar</button>
</form>
