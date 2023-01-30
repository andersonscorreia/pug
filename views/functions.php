<?php
///Local para inserir os dados para conexao com o banco de dados
$host = "192.168.102.100";
$db_user = "container71";
$db_pass = "1F(995709)";
$db_name = "ASA71";

///Criando uma variavel para conexao com o banco de dados
$connect = mysqli_connect( $host, $db_user, $db_pass, $db_name );

///Funcao para receber dados e enviar para o banco de dados
function login($connect){
        ///Aqui usamos uma serie de filtros para evitar conexoes desnecessarias com o BD,
        ///Como por exemplo, o usuario não pode deixar campos vazios e deve digitar email valido
        if (isset($_POST['acessar']) AND !empty($_POST['email']) AND !empty($_POST['senha'])) {
                ///Filtro para email
                $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
                ///Recebendo a senha
                $senha = ($_POST['senha']);
                ///enviando os dados ao BD e recebendo resposta
                $query = "SELECT * FROM ftpusers WHERE email = '$email' AND senha = '$senha' ";
                $executar = mysqli_query ($connect, $query);
                $return = mysqli_fetch_assoc($executar);
                ///Verificando resposta do BD
                if (!empty($return['email'])) {
//                      ///Se usuario e senha OK inicia-se a sessao
                        session_start();
                        ///Pedindo nome associado no BD
                        $_SESSION['nome'] = $return['nome'];
                        ///Pedindo o tipo de usuario (importante devido niveis de usuarios diferentes)
                        $_SESSION['tipo'] = $return['tipo'];
                        ///Criando a variavel "ativa" e setando para que a index.php seja exibida
                        $_SESSION['ativa'] = TRUE;
                        ///Direcionando para a pagina index.php
                        header("location: index.php");
                }
                else {
                        echo "Usuário e senha não encontrado";
                }

        }
}
///Uma função para matar a sessao e direcionar para a pagina de login
function logout(){

        session_start();
        session_unset();
        session_destroy();
        header("location: login.php");
}
///Funcao para listar e deletar dominios listados
function listar_dominios($connect)
{

	$query = "SELECT domain FROM domains";
	$resultado = mysqli_query($connect, $query);

	echo "<form method='post' action='deletar_dominio.php'>";
	while ($row_domains = mysqli_fetch_assoc($resultado)) {
		$linha = $row_domains['domain'];
		echo "<input type='checkbox' name='subject[]' id='domains' value='$linha'>
	<label for='domain'>$linha</label><br>";
	}
	echo "<input type='submit' value='EXCLUIR'><br>";

}
?>
