<?php
try {
    //inicia sessão
    session_start();
    //pdo
    $pdo = new PDO('pgsql:host=localhost;dbname=thrashTreasures', "postgres", "210606");

    if (isset($_SESSION["admID"])) {
        $admId = $_SESSION["admID"];

        $stmt = $pdo->prepare("SELECT administrador.nome AS nome, administrador.foto_perfil AS foto_perfil,
        cargos.permissao_cadastro_produtos AS perm_cad_product, cargos.permissao_cadastro_func AS perm_cad_func,
        cargos.permissao_historico_vendas AS perm_hist_vendas, cargos.permissao_visualizacao_clientes AS perm_visual_client FROM administrador
        INNER JOIN cargos ON administrador.id_cargo = cargos.id
        WHERE administrador.id=?
        LIMIT 1;");
        $stmt->execute([$admId]);

        if ($row = $stmt->fetch()) {
            $admName = $row["nome"];
            $admFoto = $row["foto_perfil"];
            $permCadProduct = $row["perm_cad_product"];
            $permCadFunc = $row["perm_cad_func"];
            $permHistVendas = $row["perm_hist_vendas"];
            $permVisualClient = $row["perm_visual_client"];
        }
    }

    //arreys para a barra de pesquisa
    $bandaSearchList = array();
    $categoriaSearchList = array();
    $generoSearchList = array();

    $stmt = $pdo->query("SELECT * FROM bandas;");
    while ($row = $stmt->fetch()) {
        $bandaSearchList[$row["id"]] = $row["nome"];
    }

    $stmt = $pdo->query("SELECT * FROM categorias;");
    while ($row = $stmt->fetch()) {
        $categoriaSearchList[$row["id"]] = $row["nome"];
    }

    $stmt = $pdo->query("SELECT * FROM genero;");
    while ($row = $stmt->fetch()) {
        $generoSearchList[$row["id"]] = $row["nome"];
    }

    if (isset($_POST["funcRadio"])) {
        $funcRadio = $_POST["funcRadio"];
    } else {
        $funcRadio = "";
    }

    if (isset($_POST["funcId"]) && $_POST["funcId"] !== "") {
        $funcId = intval($_POST["funcId"]);
    } else {
        $funcId = null;
    }

    if (isset($_POST["funcSelectFeito"]) && $_POST["funcSelectFeito"] !== "") {
        $selectFeito = boolval($_POST["funcSelectFeito"]);
    } else {
        $selectFeito = false;
    }

    if (isset($_POST["funcRadioInsertChecked"]) && isset($_POST["funcRadioUpdateChecked"])) {
        $radioInsertChecked = $_POST["funcRadioInsertChecked"];
        $radioUpdateChecked = $_POST["funcRadioUpdateChecked"];
    } else {
        $radioInsertChecked = "checked";
        $radioUpdateChecked = "";
    }

    if (isset($_POST["deleteCheckbox"]) && $_POST["deleteCheckbox"] !== "") {
        $deleteCheckbox = boolval($_POST["deleteCheckbox"]);
    } else {
        $deleteCheckbox = false;
    }

    if (isset($_POST["funcName"]) && $_POST["funcName"] !== "") {
        $funcName = $_POST["funcName"];
    } else {
        $funcName = "";
    }

    if (isset($_POST["funcEmail"]) && $_POST["funcEmail"] !== "") {
        $funcEmail = $_POST["funcEmail"];
    } else {
        $funcEmail = "";
    }

    if (isset($_POST["funcCargo"]) && $_POST["funcCargo"] !== "") {
        $funcCargo = intval($_POST["funcCargo"]);
    } else {
        $funcCargo = null;
    }

    if (isset($_POST["funcSenhaA"]) && $_POST["funcSenhaA"] !== "") {
        $funcSenhaA = sha1($_POST["funcSenhaA"]);
    } else {
        $funcSenhaA = "";
    }

    if (isset($_POST["funcSenhaB"]) && $_POST["funcSenhaB"] !== "") {
        $funcSenhaB = sha1($_POST["funcSenhaB"]);
    } else {
        $funcSenhaB = "";
    }

    if (isset($_GET["funcList"]) && $_GET["funcList"] !== "") {
        $funcList = $_GET["funcList"];
    } else {
        $funcList = "";
    }

    $mensagem = "";

    if ($funcRadio == 'insert') {
        if (strlen($funcName) > 100) {
            $mensagem = "O nome é muito longo";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else if (strlen($funcEmail) > 40) {
            $mensagem = "O email é muito longa";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else if (strlen($funcSenhaA) > 40 || strlen($funcSenhaB) > 40) {
            $mensagem = "A senha é muito longa";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else if ($funcSenhaA != $funcSenhaB) {
            $mensagem = "As senhas não coencidem";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else {
            $sql = "INSERT INTO administrador (nome, senha, email, id_cargo) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$funcName, $funcSenhaA, $funcEmail, $funcCargo]);

            $stmt = $pdo->prepare("SELECT administrador.id AS id FROM administrador
            WHERE nome = ?
            LIMIT 1;");
            $stmt->execute([$funcName]);

            if ($row = $stmt->fetch()) {
                $funcId = $row["id"];

                if (isset($_FILES["funcPerfilImage"])) {
                    $target_dir = "uploads/";
                    $target_file = $target_dir . "func_foto_perfil_" . basename($_FILES["funcPerfilImage"]["name"]);
                    $uploadOk = 1;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    // Check if image file is a actual image or fake image
                    if (isset($_POST["submit"])) {
                        if (!empty($_FILES["funcPerfilImage"]["tmp_name"])) {
                            $check = getimagesize($_FILES["funcPerfilImage"]["tmp_name"]);
                            if ($check !== false) {
                                $uploadOk = 1;
                            } else {
                                $mensagem .= "File is not an image.";
                                $uploadOk = 0;
                            }
                        } else {
                            $mensagem .= "Invalid file path.";
                            $uploadOk = 0;
                        }
                    }

                    // Check file size
                    if ($_FILES["funcPerfilImage"]["size"] > 500000) {
                        $mensagem .= "Sorry, your file is too large.";
                        $uploadOk = 0;
                    }

                    // Allow certain file formats
                    if (
                        $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                        && $imageFileType != "gif"
                    ) {
                        $mensagem .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                        $uploadOk = 0;
                    }

                    // Check if $uploadOk is set to 0 by an error
                    if ($uploadOk == 0) {
                        $mensagem .= "Sorry, your file was not uploaded.";

                    } else if (file_exists($target_file)) {
                        $sql = "UPDATE administrador SET foto_perfil=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$target_file, $funcId]);

                        $mensagem = "Insert feito";

                    } else {
                        if (move_uploaded_file($_FILES["funcPerfilImage"]["tmp_name"], $target_file)) {
                            $sql = "UPDATE administrador SET foto_perfil=? WHERE id=?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$target_file, $funcId]);

                            $mensagem = "Insert feito";

                        }
                    }
                }
            }

            $mensagem = "Insert feito";

            $deleteCheckbox = false;
            $funcId = null;
            $funcName = "";
            $funcEmail = "";
            $funcSenhaA = "";
            $funcSenhaB = "";
            $funcCargo = null;

            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";
        }

    } else if ($funcList != "" && !$selectFeito) {
        $stmt = $pdo->prepare("SELECT administrador.id AS id, nome, email, id_cargo, foto_perfil FROM administrador WHERE administrador.nome = ?
        LIMIT 1;");
        $stmt->execute([$funcList]);

        if ($row = $stmt->fetch()) {
            $funcId = $row["id"];
            $funcName = $row["nome"];
            $funcEmail = $row["email"];
            $funcCargo = $row["id_cargo"];
            $funcFotoPerfil = $row["foto_perfil"];
        }

        $mensagem = "Produto encontrado com sucesso!";

        $selectFeito = true;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

    } else if ($funcRadio == 'update' && !$selectFeito && !empty($funcName)) {
        $stmt = $pdo->prepare("SELECT administrador.id AS id, nome, email, id_cargo, foto_perfil FROM administrador WHERE administrador.nome LIKE ?
        LIMIT 1;");
        $stmt->execute(["%$funcName%"]);

        if ($row = $stmt->fetch()) {
            $funcId = $row["id"];
            $funcName = $row["nome"];
            $funcEmail = $row["email"];
            $funcCargo = $row["id_cargo"];
            $funcFotoPerfil = $row["foto_perfil"];

            $mensagem = "Produto encontrado com sucesso!";

            $selectFeito = true;
        } else {
            $mensagem = "Não foi possível encontrar este produto";

            $selectFeito = false;
        }

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

    } else if ($funcRadio == 'update' && $selectFeito && !$deleteCheckbox) {
        if (strlen($funcName) > 100) {
            $mensagem = "O nome é muito longo";

            $selectFeito = true;
            $deleteCheckbox = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

        } else if (strlen($funcEmail) > 40) {
            $mensagem = "O email é muito longa";

            $selectFeito = true;
            $deleteCheckbox = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

        } else {
            $sql = "UPDATE administrador SET nome=?, email=?, id_cargo=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$funcName, $funcEmail, $funcCargo, $funcId]);
            echo "Atualização realizada com sucesso.";

            if (isset($_FILES["funcPerfilImage"])) {
                $target_dir = "uploads/";
                $target_file = $target_dir . "func_foto_perfil_" . basename($_FILES["funcPerfilImage"]["name"]);
                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                // Check if image file is a actual image or fake image
                if (isset($_POST["submit"])) {
                    if (!empty($_FILES["funcPerfilImage"]["tmp_name"])) {
                        $check = getimagesize($_FILES["funcPerfilImage"]["tmp_name"]);
                        if ($check !== false) {
                            $uploadOk = 1;
                        } else {
                            $mensagem .= "File is not an image.";
                            $uploadOk = 0;
                        }
                    } else {
                        $mensagem .= "Invalid file path.";
                        $uploadOk = 0;
                    }
                }

                // Check file size
                if ($_FILES["funcPerfilImage"]["size"] > 500000) {
                    $mensagem .= "Sorry, your file is too large.";
                    $uploadOk = 0;
                }

                // Allow certain file formats
                if (
                    $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
                    && $imageFileType != "gif"
                ) {
                    $mensagem .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                    $uploadOk = 0;
                }

                // Check if $uploadOk is set to 0 by an error
                if ($uploadOk == 0) {
                    $mensagem .= "Sorry, your file was not uploaded.";

                } else if (file_exists($target_file)) {
                    $sql = "UPDATE administrador SET foto_perfil=? WHERE id=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$target_file, $funcId]);

                    $mensagem = "Insert feito";

                } else {
                    if (move_uploaded_file($_FILES["funcPerfilImage"]["tmp_name"], $target_file)) {
                        $sql = "UPDATE administrador SET foto_perfil=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$target_file, $funcId]);

                        $mensagem = "Insert feito";

                    }
                }
            }

            $selectFeito = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

            $mensagem = "update feito";

            $deleteCheckbox = false;
            $funcId = null;
            $funcName = "";
            $funcEmail = "";
            $funcSenhaA = "";
            $funcSenhaB = "";
            $funcCargo = null;
        }

    } else if ($funcRadio == 'update' && $selectFeito && $deleteCheckbox) {
        $sql = "DELETE FROM administrador WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$funcId]);

        $selectFeito = false;
        $deleteCheckbox = false;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

        $mensagem = "produto deletado";

        $deleteCheckbox = false;
        $funcId = null;
        $funcName = "";
        $funcEmail = "";
        $funcSenhaA = "";
        $funcSenhaB = "";
        $funcCargo = null;
    }

} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!--pagina de input de funcionarios da Thrash Treasures-->
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--CSS-->
    <link rel="stylesheet" href="styleThrashTreasuresInputPage.css">
    <!--Charts.js-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js">
    </script>
    <title>Thrash Treasures - Funcionarios</title>
    <link rel="icon" type="images/favicon" href="favicon.ico">
    <script src="https://kit.fontawesome.com/a1f82f34b1.js" crossorigin="anonymous"></script>
</head>

<body>
    <!--///////////////////////////////////////   Menu Superior  //////////////////////////////////-->
    <div id="barraCima">
        <div id="menuSuperior">

            <!--  ///////////// Logo1  /////////////-->
            <div><img id="logoImg" src="log.png" alt="Logotipo" onclick="window.location.href='index.php'">
            </div>
            <!--  ////////////////  Logo1  //////////////////-->

            <!-- ///////////////////  Barra de Pesquisa  //////////////////////-->
            <div id="barraDiv">
                <form action="paginaPesquisa.php" method="post" id="searchBarForm">
                    <input type="search" id="barraDePesquisa" placeholder="Encontre seu álbum favorito"
                        name="searchInput" onkeyup="searchBar();" onfocus="mostraSearchList();">
                    <i id="lupa" class="fa-solid fa-magnifying-glass fa-sm" style="color: #968700;"></i>
                    <ul id="barraPesquisaList">
                        <?php
                        foreach ($bandaSearchList as $id => $nome) {
                            echo "<li><button type=\"submit\" name=\"searchBandaObj\" value=\"" . $id . "\">" . $nome . "</button></li>";
                        }

                        foreach ($categoriaSearchList as $id => $nome) {
                            echo "<li><button type=\"submit\" name=\"searchCategoriaObj\" value=\"" . $id . "\">" . $nome . "</button></li>";
                        }

                        foreach ($generoSearchList as $id => $nome) {
                            echo "<li><button type=\"submit\" name=\"searchGeneroObj\" value=\"" . $id . "\">" . $nome . "</button></li>";
                        }
                        ?>
                    </ul>
                </form>

            </div>
            <!--  //////////////// Barra de Pesquisa ////////////////-->


            <!--////////////////////////////  Icones Superiores  /////////////////////////////-->
            <div id="itensDireita">

                <div class="iconeDireita">
                    <!--botão de ofertas-->
                    <div class="iconeEsquerdaIcon"><i class="fa-solid fa-tag fa-xl" style="color: #ffffff;"></i></div>
                    <p>Ofertas</p>

                </div>

                <!--botão do carrinho-->
                <div class="iconeDireita" onclick="<?php if (isset($usuarioId)) {
                    echo "window.location.href = 'paginaCarrinho.php'";
                } else {
                    echo "window.location.href = 'thrashTreasuresLogin.php'";
                } ?>">
                    <i class="fa-solid fa-cart-shopping fa-xl" style="color: #ffffff;"></i>
                    <p>Carrinho</p>

                </div>
                <!--botão de usuario-->
                <div class="iconeDireita" onclick="window.location.href = 'thrashTreasuresLogin.php'">
                    <?php
                    if (isset($admId) && $admFoto != "") {
                        echo "<img src=\"" . $admFoto . "\" title=\"" . $admName . "\" id=\"fotoPerfil\">
                <p id=\"nomeUser\">" . $admName . "</p>";
                    } else if (isset($admId) && $admFoto == "") {
                        echo "<i class=\"fa-solid fa-user fa-xl\" style=\"color: #ffffff;\"></i>
                    <p>" . $admName . "</p>";
                    } else {
                        echo "<i class=\"fa-solid fa-user fa-xl\" style=\"color: #ffffff;\"></i>
                <p id=\"nomeUser\">Entrar</p>";
                    }
                    ?>

                </div>
                <!--//////////////////////  Icones Superiores  //////////////////////////////-->


            </div>
        </div>
        <!--   ////////////////////////////////   Menu Superior   ////////////////////////////////-->

        <div class="barraTapaBuraco"></div>

        <!--botões do menu do workspace-->
        <!--caso o adm não tenha certa autorização, o botão fica bloqueado para ele-->
        <div class="barraCimaBtnContainer">
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" onclick="window.location.href='thrashTreasuresDadosGerais.php'">Dados Gerais</p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permCadProduct) && $permCadProduct) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputProdutos.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>>
                <?php if (!isset($permCadProduct) || !$permCadProduct) {
                    echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
                } ?>Produtos
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permCadProduct) && $permCadProduct) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputCategorias.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>>
                <?php if (!isset($permCadProduct) || !$permCadProduct) {
                    echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
                } ?>Categorias
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permCadProduct) && $permCadProduct) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputBandas.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>>
                <?php if (!isset($permCadProduct) || !$permCadProduct) {
                    echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
                } ?>Bandas
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permCadProduct) && $permCadProduct) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputGenero.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>>
                <?php if (!isset($permCadProduct) || !$permCadProduct) {
                    echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
                } ?>Gêneros
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaSelectedBtn">Funcionarios
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permCadFunc) && $permCadFunc) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputCargos.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>><?php if (!isset($permCadFunc) || !$permCadFunc) {
                 echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
             } ?>Cargos
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permVisualClient) && $permVisualClient) {
                echo "onclick=\"window.location.href = 'thrashTreasuresListaClientes.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>><?php if (!isset($permVisualClient) || !$permVisualClient) {
                 echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
             } ?>Clientes
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permHistVendas) && $permHistVendas) {
                echo "onclick=\"window.location.href = 'thrashTreasuresHistoricoVendas.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>><?php if (!isset($permHistVendas) || !$permHistVendas) {
                 echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>Historico de...";
             } else {
                 echo "Historico de vendas";
             } ?>
            </p>
            <div class="espacoBtns"></div>
        </div>
    </div>

    <div class="contentConteiner" id="cadastroFunc">

        <h3>
            <?php echo $mensagem ?>
        </h3>

        <form method="post" action="thrashTreasuresInputFuncionarios.php" enctype="multipart/form-data">
            <input type="hidden" name="funcId" value="<?php echo $funcId ?>">
            <input type="hidden" name="funcSelectFeito" value="<?php echo $selectFeito ?>">
            <input type="hidden" name="funcRadioInsertChecked" value="<?php echo $radioInsertChecked ?>">
            <input type="hidden" name="funcRadioUpdateChecked" value="<?php echo $radioUpdateChecked ?>">

            <input type="radio" name="funcRadio" id="funcInsertRadio" value="insert" <?php echo $radioInsertChecked ?>>
            <label for="funcInsertRadio">Inserir func</label>
            <input type="radio" name="funcRadio" id="funcUpdateRadio" value="update" <?php echo $radioUpdateChecked ?>>
            <label for="funcUpdateRadio">Atualizar func</label>

            <input type="file" name="funcPerfilImage" id="funcPerfilImageInput" class="ImageInput"
                onchange="mostraNovaImagemPerfil(this);">
            <img src="<?php
            if (!empty($funcFotoPerfil)) {
                echo $funcFotoPerfil;
            } else {
                echo "boxIcon.jpg";
            }
            ?>" class="fotoPerfilOutput">

            <input type="text" name="funcName" id="funcNameInput" placeholder="Nome" value="<?php echo $funcName ?>"
                required>
            <input type="email" name="funcEmail" id="funcEmailInput" class="changebleRequirement" placeholder="Email"
                value="<?php echo $funcEmail ?>" required>
            <div id="funcSenhaContainer">
                <div class="senhaView">
                    <input type="password" name="funcSenhaA" id="inputFuncSenhaA" class="changebleRequirement"
                        placeholder="Senha" required>
                    <input type="button" id="inputFuncSenhaBtnA" onclick="mostraSenha(this);">
                </div>
                <div class="senhaView">
                    <input type="password" name="funcSenhaB" id="inputFuncSenhaB" class="changebleRequirement"
                        placeholder="Confirmar senha" required>
                    <input type="button" onclick="mostraSenha(this);">
                </div>
            </div>

            <select name="funcCargo" id="funcCargoInput" class="changebleRequirement" required>
                <?php
                $stmt = $pdo->query("SELECT * FROM cargos");
                while ($row = $stmt->fetch()) {
                    echo "<option value=\"" . $row["id"] . "\"";
                    if ($funcCargo == $row["id"]) {
                        echo " selected";
                    }
                    echo ">" . $row["cargo"] . "</option>";
                }
                ?>
            </select>
            <?php
            if ($selectFeito) {
                echo "<input type=\"checkbox\" name=\"deleteCheckbox\" id=\"funcDeleteCheckbox\">
                        <label for=\"funcDeleteCheckbox\" value=\"true\">Apagar produto</label>";
            }
            ?>
            <input type="submit" name="submit" value="Enviar">
    </div>
    </form>

    <form method="get" action="thrashTreasuresInputFuncionarios.php">
        <div class="selectList" id="selectListFunc">
            <div class="selectListTop selectListGridFunc">
                <p class="selectListColumnName">Nome</p>
                <p class="selectListColumnName">Email</p>
                <p class="selectListColumnName">Cargo</p>
                <p class="selectListColumnName">Perm. de cadastro de produto</p>
                <p class="selectListColumnName">Perm. de cadastro de funcionario</p>
                <p class="selectListColumnName">Perm. de visual. do historico de vendas</p>
                <p class="selectListColumnName">Perm. de visual. de clientes</p>
            </div>
            <?php
            $stmt = $pdo->query("SELECT administrador.id AS id, nome, email, cargos.cargo AS cargo, cargos.permissao_cadastro_produtos AS perm_cad_prod, cargos.permissao_cadastro_func AS perm_cad_func, cargos.permissao_historico_vendas AS perm_hist_vendas,
            cargos.permissao_visualizacao_clientes AS perm_visual_client FROM administrador
            INNER JOIN cargos ON administrador.id_cargo = cargos.id;");

            while ($row = $stmt->fetch()) {
                if (strlen($row["nome"]) > 37) {
                    $nomeCortado = substr($row["nome"], 0, 34) . "...";
                } else {
                    $nomeCortado = $row["nome"];
                }

                if (strlen($row["email"]) > 36) {
                    $emailCortado = substr($row["email"], 0, 33) . "...";
                } else {
                    $emailCortado = $row["email"];
                }

                if ($row["perm_cad_prod"] == 1) {
                    $permCadProdMsg = "sim";
                } else {
                    $permCadProdMsg = "não";
                }

                if ($row["perm_cad_func"] == 1) {
                    $permCadFuncMsg = "sim";
                } else {
                    $permCadFuncMsg = "não";
                }

                if ($row["perm_hist_vendas"] == 1) {
                    $permHistVendasMsg = "sim";
                } else {
                    $permHistVendasMsg = "não";
                }

                if ($row["perm_visual_client"] == 1) {
                    $permVisualClientMsg = "sim";
                } else {
                    $permVisualClientMsg = "não";
                }

                echo "<input type=\"radio\" name=\"funcList\" id=\"" . $row["id"] . "\" class=\"radioHidden\" value=\"" . $row["nome"] . "\"";
                if ($funcId == $row["id"]) {
                    echo " checked";
                }
                echo ">
                    <label for=\"" . $row["id"] . "\" class=\"selectListRow selectListGridFunc\">
                        <p class=\"selectListItem\">" . $nomeCortado . "</p>
                        <p class=\"selectListItem\">" . $emailCortado . "</p>
                        <p class=\"selectListItem\">" . $row["cargo"] . "</p>
                        <p class=\"selectListItem\">" . $permCadProdMsg . "</p>
                        <p class=\"selectListItem\">" . $permCadFuncMsg . "</p>
                        <p class=\"selectListItem\">" . $permHistVendasMsg . "</p>
                        <p class=\"selectListItem\">" . $permVisualClientMsg . "</p>
                    </label>";
            }
            ?>
        </div>
        <input type="submit" value="Procurar">
    </form>
    </div>

    <script src="scriptThrashTreasuresWorkspace.js"></script>
</body>

</html>