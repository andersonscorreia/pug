<?php
///Local para inserir os dados para conexao com o banco de dados
$host = "192.168.102.100";
$db_user = "container57";
$db_pass = "1F(044480)";
$db_name = "ASA57";

///Criando uma variavel para conexao com o banco de dados
$conn = mysqli_connect( $host, $db_user, $db_pass, $db_name );

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}
echo "Conexão estabelecida com sucesso";

$conn->close();


?>
