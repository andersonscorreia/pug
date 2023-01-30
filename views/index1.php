<?php
///Linkando com o arquivo de funções
require_once "functions.php";
?>

<!DOCTYPE html>
<html>
<body>

<?php 
///Iniciando uma sessão
session_start(); ?>
<?php
///Verificando se o usuario está logado
if (isset($_SESSION['ativa'])) { ?>
        <h3>"Logou com exito!"</h3>
<?php }
///Se não estiver logado será direcionado para o login
else {
      header ("location: login.php");
} ?>

<?php
///Iniciando a verificação de tipo de usuário logado
///De acordo com o usuario o sistema entregará funçoes diferentes
if (($_SESSION['tipo'])=='su') { ?>
        <hr>
        <?php echo "DOMÍNIOS CADASTRADOS:<br>";?>
        <?php echo listar_dominios($connect);?>
        
<?php }?>

<?php if (($_SESSION['tipo'])=='ad') { ?>
        <h4>###CODIGO PARA USUARIO ADMINISTRADOR###</h4><br>
<?php } ?>

<?php if (($_SESSION['tipo'])=='us') { ?>

        <h4>###CODIGO PARA USUARIO COMUM###</h4><br>
<?php } ?>
<hr>
<!–Link para usuário efetuar logout–>
<a href="logout.php">Logout</a>
