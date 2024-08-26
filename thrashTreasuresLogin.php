<?php
try {
    //iniciando sessão
    session_start();
    //pdo
    $pdo = new PDO('pgsql:host=localhost;dbname=thrashTreasures', "postgres", "210606");

    $mensagem = "";
    $usuarioExiste = false;

    //verifica se usuario/adm está logado, caso esteja, encerra a sessão para que uma nova possa ser iniciada
    if (isset($_SESSION["usuarioId"]) || isset($_SESSION["admID"])) {
        session_unset();
        session_destroy();
    }

    //verifica se ouve uma troca de senha, caso tenha ocorrido, utiliza o mesmo usuario durante o login
    if (isset($_SESSION["usuarioEmail"])) {
        $usuario = $_SESSION["usuarioEmail"];
        $usuarioExiste = true;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST["submitBtn"])) {
            if (isset($_POST["inputUsuario"])) {
                $usuario = $_POST["inputUsuario"];

                //caso o usuario tenha clickado em "esqueci minha senha"
                if ($_POST["submitBtn"] == "Esqueci minha senha") {
                    $_SESSION["lostPasswordUser"] = $_POST["inputUsuario"];

                    $link = "thrashTreasuresLostPasswordEmail.php";
                    header("Location: $link");
                    exit();

                    // caso o usuario tenha logado normalmente
                } else {
                    if (isset($_POST["senha"])) {
                        $senha = sha1($_POST["senha"]);

                        //verifica se um cliente
                        $stmt = $pdo->prepare("SELECT * FROM clientes
                            WHERE nome=? OR email=?
                            LIMIT 1;");
                        $stmt->execute([$usuario, $usuario]);

                        if ($row = $stmt->fetch()) {
                            $usuarioExiste = true;

                            //verifica a senha
                            if ($senha == $row["senha"]) {
                                $_SESSION["usuarioId"] = $row["id"];

                                $link = "index.php";
                                header("Location: $link");
                                exit();
                            } else {
                                $mensagem = "Senha incorreta";
                            }
                        } else {
                            //verifica se é um adm
                            $stmt = $pdo->prepare("SELECT * FROM administrador
                            WHERE nome=? OR email=?
                            LIMIT 1;");
                            $stmt->execute([$usuario, $usuario]);

                            if ($row = $stmt->fetch()) {
                                $_SESSION["admID"] = $row["id"];

                                $link = "index.php";
                                header("Location: $link");
                                exit();
                            } else {
                                $mensagem = "Digite um Usuário ou um Email válido";
                            }
                        }
                    } else {
                        $mensagem = "Digite a senha";
                    }
                }
            } else {
                $mensagem = "Digite um Usuário ou um Email válido";
            }
        }
    }
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>


<!--pagina de login da Thrash Treasures-->
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--CSS-->
    <link rel="stylesheet" href="styleThrashTreasuresLogin.css">
    <title>Thrash Treasures - Entrar</title>
</head>

<body>
    <div id="loginPage">
        <!--a caixa de input-->
        <div class="loginBox">
            <!--lado esquerdo-->
            <div class="loginBoxLeft backgroundImagemA">
            </div>
            <!--lado direito-->
            <div class="loginBoxRight">
                <h2 id="loginTitle">Entrar</h2>
                <!--mensagem de erro-->
                <p class="error">
                    <?php echo $mensagem; ?>
                </p>
                <!--formulario de login-->
                <form method="post" action="thrashTreasuresLogin.php">
                    <input type="text" placeholder="Nome de Usuário ou Email" name="inputUsuario" value="<?php if ($usuarioExiste) {
                        echo $usuario;
                    } else {
                        echo "";
                    } ?>" required>
                    <div class="senhaView">
                        <input type="password" placeholder="Senha" name="senha">
                        <input type="button" id="senhaBtn" onclick="mostraSenha(this);">
                    </div>
                    <!--link da pagina de recuperação de senha-->
                    <input type="submit" name="submitBtn" value="Esqueci minha senha" id="lostPasswordBtn">
                    <input type="submit" name="submitBtn" value="Entrar">
                </form>
                <!--link da pagina de criação conta-->
                <p id="createContaBtn">Ainda não tem uma conta? <b
                        onclick=" window.location.href = 'thrashTreasuresCreateConta.php'">Criar conta</b></p>
            </div>
        </div>
    </div>

    <script src="scriptThrashTreasuresLogin.js"></script>
</body>

</html>