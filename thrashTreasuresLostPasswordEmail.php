<?php
$mensagem = "";
//verifica se o email foi inserido no input, caso tenha sido, abre a proxima pagina.
//esta pagina realmente não faz nada, mas é legal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputEmail = $_POST["inputEmail"];
    if ($inputEmail != "") {
        $link = "thrashTreasuresLostPasswordCode.php";
        header("Location: $link");
        exit();
    } else {
        $mensagem = "Digite um email valido";
    }
}
?>

<!--primeira pagina de recuperação de senha da Thrash Treasures-->
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--CSS-->
    <link rel="stylesheet" href="styleThrashTreasuresLogin.css">
    <title>Thrash Treasures - Recuperar Senha</title>
</head>

<body>
    <div id="loginPage">
        <!--a caixa de input-->
        <div class="loginBox">
            <!--lado esquerdo-->
            <div class="loginBoxLeft backgroundImagemB">
            </div>
            <!--lado direito-->
            <div class="loginBoxRight">
                <h2 id="lostPasswordTitle">Recuperar Senha</h2>
                <p class="lostPasswordTxt">Um codigo de verificação será enviado para você por Email</p>
                <p class="error">
                    <?php echo $mensagem; ?>
                </p>
                <!--input de email para recuperação de senha-->
                <form method="post" action="thrashTreasuresLostPasswordEmail.php">
                    <input type="email" placeholder="Email" name="inputEmail" required>
                    <input type="submit" value="Enviar Email">
                </form>
            </div>
        </div>
    </div>
</body>

</html>