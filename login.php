<?php require_once "functions.php" ; 
if (isset($_POST['acessar'])) {
login($connect);
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Formulário de acesso ao site</title>
</head>
<body>
	<form action="" method="post">
		<fieldset>
			<legend>Painel de login</legend>
			<input type="email" name="email" placeholder="Informe seu email" required>
			<input type="password" name="senha" placeholder="Insira sua senha" required>
			<input type="submit" name="acessar" value="Acessar">
		</fieldset>
	</form>
</body>
</html>