<?php
$mensagem = "";

//verifica se o codigo esta correto (o codigo sempre é "123456")
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputA = $_POST["inputA"];
    $inputB = $_POST["inputB"];
    $inputC = $_POST["inputC"];
    $inputD = $_POST["inputD"];
    $inputE = $_POST["inputE"];
    $inputF = $_POST["inputF"];

    $codigo = $inputA . $inputB . $inputC . $inputD . $inputE . $inputF;

    if ($inputA == "" || $inputB == "" || $inputC == "" || $inputD == "" || $inputE == "" || $inputF == "") {
        $mensagem = "O código deve ser preenchido";
    } else if ($codigo != "123456") {
        $mensagem = "Código incorreto";
    } else {
        $link = "thrashTreasuresLostPasswordNew.php";
        header("Location: $link");
        exit();
    }
}
?>
<!--segunda pagina de recuperação de senha da Thrash Treasures-->
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
            <div class="loginBoxLeft backgroundImagemA">
            </div>
            <!--lado direito-->
            <div class="loginBoxRight">
                <h2 id="lostPasswordTitle">Recuperar Senha</h2>
                <p class="lostPasswordTxt">Digite o código de 6 digitos</p>
                <!--mensagem de erro-->
                <p class="error">
                    <?php echo $mensagem; ?>
                </p>
                <!--os inputs do 'codigo de verificação'-->
                <form method="post" action="thrashTreasuresLostPasswordCode.php">
                    <div id="codeInput">
                        <input type="text" maxlength="1" name="inputA" id="inputA" oninput="nextInput(this, 'inputB');"
                            required>
                        <input type="text" maxlength="1" name="inputB" id="inputB" oninput="nextInput(this, 'inputC');"
                            required>
                        <input type="text" maxlength="1" name="inputC" id="inputC" oninput="nextInput(this, 'inputD');"
                            required>
                        <input type="text" maxlength="1" name="inputD" id="inputD" oninput="nextInput(this, 'inputE');"
                            required>
                        <input type="text" maxlength="1" name="inputE" id="inputE" oninput="nextInput(this, 'inputF');"
                            required>
                        <input type="text" maxlength="1" name="inputF" id="inputF" oninput="nextInput(this, 'inputF');"
                            required>
                    </div>

                    <button type="submit">Enviar</button>
                </form>
                <!--botão 're-enviar Email' (não existe email)-->
                <p id="reEnviarBtn">Não recebeu o Email? <b>Re-enviar</b></p>
            </div>
        </div>
    </div>

    <script>
        //function para passar ao proximo input depois que o input anterior é utilizado
        function nextInput(input, nextInputId) {
            if (input.value.length > 0) {
                document.getElementById(nextInputId).focus();
            }
        }
    </script>
</body>

</html>