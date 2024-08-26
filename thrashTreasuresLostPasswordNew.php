<?php
try {
    //inicia sessão
    session_start();
    //pdo
    $pdo = new PDO('pgsql:host=localhost;dbname=thrashTreasures', "postgres", "210606");

    //verifica se o usuario foi "guardado"
    if (isset($_SESSION["lostPasswordUser"])) {
        $lostPasswordUser = $_SESSION["lostPasswordUser"];
    } else {
        $mensagem = "ususario não encotrado";
    }

    $mensagem = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST["senhaA"]) && isset($_POST["senhaB"])) {
            //codifica as senhas
            $senhaA = sha1($_POST["senhaA"]);
            $senhaB = sha1($_POST["senhaB"]);

            //procura pelo usuario
            $stmt = $pdo->prepare("SELECT * FROM clientes
        WHERE email=? OR nome=?;");
            $stmt->execute([$lostPasswordUser, $lostPasswordUser]);

            if ($row = $stmt->fetch()) {
                $clienteId = $row["id"];
                $clienteEmail = $row["email"];
                //verifica se as senhas coencidem
                if ($senhaA == $senhaB) {
                    //atualiza a senha
                    $sql = "UPDATE clientes SET senha=? WHERE id=?;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$senhaA, $clienteId]);

                    $_SESSION["usuarioId"] = $clienteId;
                    $_SESSION["usuarioEmail"] = $clienteEmail;

                    //envia para a proxima pagina
                    $link = "thrashTreasuresLostPasswordMsg.html";
                    header("Location: $link");
                    exit();

                } else {
                    $mensagem = "As senhas não coencidem";
                }
            } else {
                $mensagem = "Este usuário não existe";
            }
        } else {
            $mensagem = "Digite a senha";
        }
    }

} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!--terceira pagina de recuperação de senha da Thrash Treasures-->
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
                <p class="lostPasswordTxt">Crie uma nova senha</p>
                <!--mensagem de erro-->
                <p class="error">
                    <?php echo $mensagem; ?>
                </p>
                <!--os inputs da nova senha-->
                <form method="post" action="thrashTreasuresLostPasswordNew.php">
                    <div class="senhaView">
                        <input type="password" id="senhaA" name="senhaA" placeholder="Digite a senha" required>
                        <input type="button" id="senhaBtnA" onclick="mostraSenha(this);">
                    </div>
                    <div class="senhaView">
                        <input type="password" id="senhaB" name="senhaB" placeholder="Confirmar senha" required>
                        <input type="button" id="senhaBtnB" onclick="mostraSenha(this);">
                    </div>
                    <input type="submit" value="Trocar Senha">
                </form>
            </div>
        </div>
    </div>

    <script src="scriptThrashTreasuresLogin.js"></script>
</body>

</html>