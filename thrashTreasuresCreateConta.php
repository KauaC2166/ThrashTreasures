<?php
try {
    //inicia sessão
    session_start();
    //pdo
    $pdo = new PDO('pgsql:host=localhost;dbname=thrashTreasures', "postgres", "210606");

    $mensagem = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        //verifica se todos os campos foram preenchidos
        if (isset($_POST["userName"]) && isset($_POST["userBirthday"]) && isset($_POST["userEmail"]) && isset($_POST["userPasswordA"]) && isset($_POST["userPasswordB"])) {
            $userName = $_POST["userName"];
            $userBirthday = $_POST["userBirthday"];
            $userBirthdayObj = new DateTime($userBirthday);
            $userEmail = $_POST["userEmail"];
            $userPasswordA = sha1($_POST["userPasswordA"]);
            $userPasswordB = sha1($_POST["userPasswordB"]);

            //verifica se o cliente é maior de idade
            $dataAtual = new DateTime();
            $diferencaIdade = $dataAtual->diff($userBirthdayObj)->y;

            if ($diferencaIdade >= 18) {
                if (isset($_POST["termosCheckbox"])) {

                    //verifica se o email já foi cadastrado
                    $stmt = $pdo->prepare("SELECT * FROM clientes
                WHERE email=?;");
                    $stmt->execute([$userEmail]);

                    if ($row = $stmt->fetch()) {
                        $mensagem = "Este email já foi cadastrado";
                    } else {
                        if ($userPasswordA == $userPasswordB) {
                            //insere o cliente no bd
                            $sql = "INSERT INTO clientes (nome, email, senha, data_nascimento) VALUES (?,?,?,?);";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$userName, $userEmail, $userPasswordA, $userBirthday]);

                            $stmt = $pdo->prepare("SELECT * FROM clientes
                             WHERE email=?
                             LIMIT 1;");
                            $stmt->execute([$userEmail]);

                            if ($row = $stmt->fetch()) {
                                $userId = $row["id"];
                                $_SESSION["usuarioId"] = $userId;

                                //insere a foto de perfil na pasta uploads
                                if (isset($_FILES["userPerfilImage"])) {
                                    $target_dir = "uploads/";
                                    $target_file = $target_dir . "usuario_foto_perfil_" . basename($_FILES["userPerfilImage"]["name"]);
                                    $uploadOk = 1;
                                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                                    if (isset($_POST["submit"])) {
                                        if (!empty($_FILES["userPerfilImage"]["tmp_name"])) {
                                            $check = getimagesize($_FILES["userPerfilImage"]["tmp_name"]);
                                            if ($check !== false) {
                                                $uploadOk = 1;
                                            } else {
                                                $uploadOk = 0;
                                            }
                                        } else {
                                            $uploadOk = 0;
                                        }
                                    }

                                    if ($_FILES["userPerfilImage"]["size"] > 500000) {
                                        $uploadOk = 0;
                                    }

                                    if (
                                        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                                        && $imageFileType != "gif"
                                    ) {
                                        $uploadOk = 0;
                                    }

                                    if ($uploadOk == 0) {

                                    } else if (file_exists($target_file)) {
                                        $sql = "UPDATE clientes SET foto_perfil=? WHERE id=?";
                                        $stmt = $pdo->prepare($sql);
                                        $stmt->execute([$target_file, $userId]);

                                    } else {
                                        if (move_uploaded_file($_FILES["userPerfilImage"]["tmp_name"], $target_file)) {
                                            $sql = "UPDATE clientes SET foto_perfil=? WHERE id=?";
                                            $stmt = $pdo->prepare($sql);
                                            $stmt->execute([$target_file, $userId]);

                                        }
                                    }
                                }

                                //envia o usuario para a pagina inical
                                $link = "index.php";
                                header("Location: $link");
                                exit();
                            }
                        } else {
                            $mensagem = "As senhas não coencidem";
                        }
                    }
                } else {
                    $mensagem = "Aceite os Termos de Serviços";
                }
            } else {
                $mensagem = "A criação de conta não é permitida para menores de 18 anos.";
            }
        } else {
            $mensagem = "Todos os campos devem ser preenchidos";
        }
    }

} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!--pagina de criação de conta da Thrash Treasures-->
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--CSS-->
    <link rel="stylesheet" href="styleThrashTreasuresLogin.css">
    <script src="https://kit.fontawesome.com/a1f82f34b1.js" crossorigin="anonymous"></script>
    <title>Thrash Treasures - Criar Conta</title>
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
                <h2 id="createContaTitle">Criar conta</h2>
                <!--mensagem de erro-->
                <p class="error" id="erroMsg">
                    <?php echo $mensagem; ?>
                </p>
                <!--formulario de criação de conta-->
                <form method="post" action="thrashTreasuresCreateConta.php" enctype="multipart/form-data">

                    <!--input da imagem do perfil-->
                    <label for="userPerfilImageInput" id="imageContainer">
                        <i class="fa-solid fa-user fa-xl" id="perfilIcon" title="Escolher foto de perfil"></i>
                        <img src="#" alt="Perfil" id="perfilImage" title="Escolher foto de perfil">
                    </label>
                    <input type="file" name="userPerfilImage" id="userPerfilImageInput" onchange="mostraImagem(this);">
                    <input type="text" name="userName" placeholder="Nome" value="<?php if (isset($userName)) {
                        echo $userName;
                    } else {
                        echo "";
                    } ?>" required>
                    <input type="text" name="userBirthday" placeholder="Data de nascimento" onfocus="(this.type='date')"
                        onblur="voltaInputData();" value="<?php if (isset($userBirthday)) {
                            echo $userBirthday;
                        } else {
                            echo "";
                        } ?>" required>
                    <input type="email" name="userEmail" id="emailInput" placeholder="Email" value="<?php if (isset($userEmail)) {
                        echo $userEmail;
                    } else {
                        echo "";
                    } ?>" required>
                    <div class="senhaView">
                        <input type="password" name="userPasswordA" placeholder="Senha" required>
                        <input type="button" onclick="mostraSenha(this);">
                    </div>
                    <div class="senhaView">
                        <input type="password" name="userPasswordB" placeholder="Confirmar senha" required>
                        <input type="button" onclick="mostraSenha(this);">
                    </div>
                    <!--checkbox de 'termos de serviço'-->
                    <div id="checkboxBox">
                        <input type="checkbox" name="termosCheckbox" id="termosCheckbox" required>
                        <!--os termos de serviço (o arquivo realmente existe, é um lorem ipsum gigante)-->
                        <label for="termosCheckbox">Li e concordo com os <b
                                onclick="window.open('termosServico.html', '_blank');">Termos de Serviço</b></label>
                    </div>
                    <input type="submit" name="submit" value="Criar Conta">
                </form>
            </div>
        </div>
    </div>

    <script src="scriptThrashTreasuresLogin.js"></script>
</body>

</html>