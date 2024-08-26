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

    if (isset($_POST["bandaRadio"])) {
        $bandaRadio = $_POST["bandaRadio"];
    } else {
        $bandaRadio = "";
    }

    if (isset($_POST["bandaId"]) && $_POST["bandaId"] !== "") {
        $bandaId = intval($_POST["bandaId"]);
    } else {
        $bandaId = null;
    }

    if (isset($_POST["bandaSelectFeito"]) && $_POST["bandaSelectFeito"] !== "") {
        $selectFeito = boolval($_POST["bandaSelectFeito"]);
    } else {
        $selectFeito = "";
    }

    if (isset($_POST["bandaRadioInsertChecked"]) && isset($_POST["bandaRadioUpdateChecked"])) {
        $radioInsertChecked = $_POST["bandaRadioInsertChecked"];
        $radioUpdateChecked = $_POST["bandaRadioUpdateChecked"];
    } else {
        $radioInsertChecked = "checked";
        $radioUpdateChecked = "";
    }

    if (isset($_POST["deleteCheckbox"]) && $_POST["deleteCheckbox"] !== "") {
        $deleteCheckbox = boolval($_POST["deleteCheckbox"]);
    } else {
        $deleteCheckbox = false;
    }

    if (isset($_POST["bandaName"])) {
        $bandaName = $_POST["bandaName"];
    } else {
        $bandaName = "";
    }

    if (isset($_POST["bandaDescription"])) {
        $bandaDescription = $_POST["bandaDescription"];
    } else {
        $bandaDescription = "";
    }

    if (isset($_POST["bandaGenero"]) && $_POST["bandaGenero"] !== "") {
        $bandaGenero = intval($_POST["bandaGenero"]);
    } else {
        $bandaGenero = null;
    }

    if (isset($_GET["bandaList"]) && $_GET["bandaList"] !== "") {
        $bandaList = $_GET["bandaList"];
    } else {
        $bandaList = "";
    }

    $mensagem = "";

    if ($bandaRadio == 'insert') {
        if (strlen($bandaName) > 50) {
            $mensagem = "O nome é muito longo";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else if (strlen($bandaDescription) > 800) {
            $mensagem = "A descrição é muito longa";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else {
            $sql = "INSERT INTO bandas (nome, descricao, genero_id) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$bandaName, $bandaDescription, $bandaGenero]);

            $stmt = $pdo->prepare("SELECT bandas.id AS id FROM bandas
            WHERE nome = ?
            LIMIT 1;");
            $stmt->execute([$bandaName]);

            if ($row = $stmt->fetch()) {
                $bandaId = $row["id"];

                if (isset($_FILES["bandaPerfilImage"])) {
                    $target_dir = "uploads/";
                    $target_file = $target_dir . "banda_foto_perfil_" . basename($_FILES["bandaPerfilImage"]["name"]);
                    $uploadOk = 1;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    // Check if image file is a actual image or fake image
                    if (isset($_POST["submit"])) {
                        if (!empty($_FILES["bandaPerfilImage"]["tmp_name"])) {
                            $check = getimagesize($_FILES["bandaPerfilImage"]["tmp_name"]);
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
                    if ($_FILES["bandaPerfilImage"]["size"] > 500000) {
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
                        $sql = "UPDATE bandas SET foto_perfil=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$target_file, $bandaId]);

                        $mensagem = "Insert feito";

                    } else {
                        if (move_uploaded_file($_FILES["bandaPerfilImage"]["tmp_name"], $target_file)) {
                            $sql = "UPDATE bandas SET foto_perfil=? WHERE id=?";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$target_file, $bandaId]);

                            $mensagem = "Insert feito";

                        }
                    }
                }

                if (isset($_FILES["bandaImages"])) {

                    $totalFiles = count($_FILES['bandaImages']['name']);

                    for ($i = 0; $i < $totalFiles; $i++) {
                        $target_dir = "uploads/";
                        $target_file = $target_dir . "banda_foto_" . basename($_FILES["bandaImages"]["name"][$i]);
                        $uploadOk = 1;
                        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                        // Check if image file is a actual image or fake image
                        if (isset($_POST["submit"])) {
                            if (!empty($_FILES["bandaImages"]["tmp_name"][$i])) {
                                $check = getimagesize($_FILES["bandaImages"]["tmp_name"][$i]);
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
                        if ($_FILES["bandaImages"]["size"][$i] > 500000) {
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

                            $sql = "INSERT INTO banda_fotos (id_banda, foto) VALUES (?,?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$bandaId, $target_file]);
                        } else {
                            if (move_uploaded_file($_FILES["bandaImages"]["tmp_name"][$i], $target_file)) {


                                $sql = "INSERT INTO banda_fotos (id_banda, foto) VALUES (?,?)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$bandaId, $target_file]);
                            }
                        }
                    }
                }
            }

            $deleteCheckbox = false;
            $bandaId = null;
            $bandaName = "";
            $bandaDescription = "";
            $bandaGenero = null;

            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";
        }

    } else if ($bandaList != "" && !$selectFeito) {
        $stmt = $pdo->prepare("SELECT bandas.id AS id, bandas.nome AS nome, bandas.descricao AS descricao, genero_id AS genero, foto_perfil FROM bandas WHERE bandas.nome = ?
        LIMIT 1;");
        $stmt->execute([$bandaList]);

        if ($row = $stmt->fetch()) {
            $bandaId = $row["id"];
            $bandaName = $row["nome"];
            $bandaDescription = $row["descricao"];
            $bandaGenero = $row["genero"];
            $bandaFotoPerfil = $row["foto_perfil"];
        }

        $stmt = $pdo->prepare("SELECT * FROM banda_fotos
        WHERE id_banda = ?;");
        $stmt->execute([$bandaId]);

        while ($row = $stmt->fetch()) {
            $selectedImages[$row["id"]] = $row["foto"];
        }

        $mensagem = "Banda encontrado com sucesso!";

        $selectFeito = true;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

    } else if ($bandaRadio == 'update' && !$selectFeito && !empty($bandaName)) {
        $stmt = $pdo->prepare("SELECT bandas.id AS id, bandas.nome AS nome, bandas.descricao AS descricao, genero_id AS genero, foto_perfil FROM bandas WHERE bandas.nome LIKE ?
        LIMIT 1;");
        $stmt->execute(["%$bandaName%"]);

        if ($row = $stmt->fetch()) {
            $bandaId = $row["id"];
            $bandaName = $row["nome"];
            $bandaDescription = $row["descricao"];
            $bandaGenero = $row["genero"];
            $bandaFotoPerfil = $row["foto_perfil"];

            $mensagem = "Produto encontrado com sucesso!";

            $selectFeito = true;
        } else {
            $mensagem = "Não foi possível encontrar este produto";

            $selectFeito = false;
        }

        $stmt = $pdo->prepare("SELECT * FROM banda_fotos
        WHERE id_banda = ?;");
        $stmt->execute([$bandaId]);

        while ($row = $stmt->fetch()) {
            $selectedImages[$row["id"]] = $row["foto"];
        }

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

    } else if ($bandaRadio == 'update' && $selectFeito && !$deleteCheckbox) {
        if (strlen($bandaName) > 50) {
            $mensagem = "O nome é muito longo";

            $selectFeito = true;
            $deleteCheckbox = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

        } else if (strlen($bandaDescription) > 800) {
            $mensagem = "A descrição é muito longa";

            $selectFeito = true;
            $deleteCheckbox = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

        } else {
            $sql = "UPDATE bandas SET nome=?, descricao=?, genero_id=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$bandaName, $bandaDescription, $bandaGenero, $bandaId]);
            echo "Atualização realizada com sucesso.";

            if (isset($_FILES["bandaPerfilImage"])) {
                $target_dir = "uploads/";
                $target_file = $target_dir . "banda_foto_perfil_" . basename($_FILES["bandaPerfilImage"]["name"]);
                $uploadOk = 1;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                // Check if image file is a actual image or fake image
                if (isset($_POST["submit"])) {
                    if (!empty($_FILES["bandaPerfilImage"]["tmp_name"])) {
                        $check = getimagesize($_FILES["bandaPerfilImage"]["tmp_name"]);
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
                if ($_FILES["bandaPerfilImage"]["size"] > 500000) {
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
                    $sql = "UPDATE bandas SET foto_perfil=? WHERE id=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$target_file, $bandaId]);

                    $mensagem = "update feito";

                } else {
                    if (move_uploaded_file($_FILES["bandaPerfilImage"]["tmp_name"], $target_file)) {
                        $sql = "UPDATE bandas SET foto_perfil=? WHERE id=?";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$target_file, $bandaId]);

                        $mensagem = "update feito";

                    }
                }
            }

            if (isset($_FILES["bandaImages"])) {

                $totalFiles = count($_FILES['bandaImages']['name']);

                for ($i = 0; $i < $totalFiles; $i++) {
                    $target_dir = "uploads/";
                    $target_file = $target_dir . "banda_foto_" . basename($_FILES["bandaImages"]["name"][$i]);
                    $uploadOk = 1;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    // Check if image file is a actual image or fake image
                    if (isset($_POST["submit"])) {
                        if (!empty($_FILES["bandaImages"]["tmp_name"][$i])) {
                            $check = getimagesize($_FILES["bandaImages"]["tmp_name"][$i]);
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
                    if ($_FILES["bandaImages"]["size"][$i] > 500000) {
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

                        $sql = "INSERT INTO banda_fotos (id_banda, foto) VALUES (?,?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$bandaId, $target_file]);
                    } else {
                        if (move_uploaded_file($_FILES["bandaImages"]["tmp_name"][$i], $target_file)) {


                            $sql = "INSERT INTO banda_fotos (id_banda, foto) VALUES (?,?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$bandaId, $target_file]);
                        }
                    }
                }
            }

            if (isset($_POST["deleteImageCheckbox"])) {
                $totalDeleteImgs = count($_POST["deleteImageCheckbox"]);

                for ($i = 0; $i < $totalDeleteImgs; $i++) {
                    $idImageDelete = $_POST["deleteImageCheckbox"][$i];

                    $sql = "DELETE FROM banda_fotos WHERE id=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$idImageDelete]);
                }
            }

            $selectFeito = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

            $mensagem = "update feito";

            $deleteCheckbox = false;
            $bandaId = null;
            $bandaName = "";
            $bandaDescription = "";
            $bandaGenero = null;
        }

    } else if ($bandaRadio == 'update' && $selectFeito && $deleteCheckbox) {
        $sql = "DELETE FROM banda_fotos WHERE id_banda=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bandaId]);

        $sql = "DELETE FROM bandas WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$bandaId]);

        $selectFeito = false;
        $deleteCheckbox = false;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

        $mensagem = "produto deletado";

        $deleteCheckbox = false;
        $bandaId = null;
        $bandaName = "";
        $bandaDescription = "";
        $bandaGenero = null;
    }

} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!--pagina de input de bandas da Thrash Treasures-->
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
    <title>Thrash Treasures - Bandas</title>
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
            } ?>><?php if (!isset($permCadProduct) || !$permCadProduct) {
                 echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
             } ?>Produtos
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permCadProduct) && $permCadProduct) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputCategorias.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>><?php if (!isset($permCadProduct) || !$permCadProduct) {
                 echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
             } ?>Categorias
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaSelectedBtn">Bandas
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permCadProduct) && $permCadProduct) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputGenero.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>><?php if (!isset($permCadProduct) || !$permCadProduct) {
                 echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
             } ?>Gêneros
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permCadFunc) && $permCadFunc) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputFuncionarios.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>><?php if (!isset($permCadFunc) || !$permCadFunc) {
                 echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
             } ?>Funcionarios
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

    <div class="contentConteiner" id="cadastroBanda">

        <!--mensagem-->
        <h3 class="mensagem">
            <?php echo $mensagem ?>
        </h3>

        <!--formulario das bandas-->
        <form method="post" action="thrashTreasuresInputBandas.php" id="cadastroBandaForm"
            enctype="multipart/form-data">
            <input type="hidden" name="bandaId" value="<?php echo $bandaId ?>">
            <input type="hidden" name="bandaSelectFeito" value="<?php echo $selectFeito ?>">
            <input type="hidden" name="bandaRadioInsertChecked" value="<?php echo $radioInsertChecked ?>">
            <input type="hidden" name="bandaRadioUpdateChecked" value="<?php echo $radioUpdateChecked ?>">

            <div class="formBtnsContainer">
                <div class="radioSelectArea">
                    <input type="radio" name="productRadio" id="insertRadio" class="handleOpt" value="insert" <?php echo $radioInsertChecked ?>>
                    <label for="insertRadio" class="radioSelectLabel" title="Inserir produto" id="insertLabel"><i
                            class="fa-solid fa-file-import"></i>
                    </label>

                    <input type="radio" name="productRadio" id="updateRadio" class="handleOpt" value="update"
                        onchange="mudaRequired(this);" <?php echo $radioUpdateChecked ?>>
                    <label for="updateRadio" class="radioSelectLabel" id="updateLabel" title="Atualizar produto"><i
                            class="fa-solid fa-file-pen"></i>
                    </label>
                </div>
                <div class="radioSelectArea">
                    <?php
                    if ($selectFeito) {
                        echo "<input type=\"checkbox\" name=\"deleteCheckbox\" id=\"bandaDeleteCheckbox\" class=\"deleteCheckbox\">
                        <label for=\"bandaDeleteCheckbox\" class=\"deleteCheckboxLabel\"><i class=\"fa-solid fa-trash\"></i></label>";
                    }
                    ?>
                    <button type="submit" name="submit" class="formSubmitBtn" value="Enviar" title="Enviar"><i
                            class="fa-solid fa-check"></i></button>
                </div>
            </div>
            <div id="formBandaMiddle">
                <label for="bandaPerfilImageInput"> <img src="<?php
                if (!empty($bandaFotoPerfil)) {
                    echo $bandaFotoPerfil;
                } else {
                    echo "imagePlaceholder.jpg";
                }
                ?>" class="fotoPerfilOutput"></label>
                <input type="file" name="bandaPerfilImage" id="bandaPerfilImageInput" class="ImageInput"
                    onchange="mostraNovaImagemPerfil(this);">

                <input type="text" name="bandaName" id="bandaNameInput" placeholder="Nome"
                    value="<?php echo $bandaName ?>" required>

                <textarea name="bandaDescription" cols="30" rows="10" class="changebleRequirement"
                    placeholder="Descrição" required><?php echo $bandaDescription ?></textarea>

                <select name="bandaGenero" id="bandaGeneroInput" class="changebleRequirement" required>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM genero");
                    while ($row = $stmt->fetch()) {
                        echo "<option value=\"" . $row["id"] . "\"";
                        if ($bandaGenero == $row["id"]) {
                            echo " selected";
                        }
                        echo ">" . $row["nome"] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <input type="file" name="bandaImages[]" id="bandaImageInput" class="ImageInput"
                    onchange="mostraNovasImagens(this);" multiple>
                <div class="imagesConteiner">
                    <div class="imageScrollConteiner">
                        <div class="imageScrollUp" onclick="sobeImagens();">⮝</div>
                        <div class="otherImagesContainer">
                            <?php
                            if (!empty($selectedImages)) {
                                foreach ($selectedImages as $imagemId => $diretorio) {
                                    echo " <div class=\"imageCheckboxContainer\">
                                        <input type=\"checkbox\" name=\"deleteImageCheckbox[]\" value=\"" . $imagemId . "\" class=\"imageCheckbox\" title=\"Apagar imagem\">
                                        <img src=\"" . $diretorio . "\" class=\"otherImages\" onclick=\"mostraImagemSelecionada(this);\" title=\"Expandir a imagem\">
                                    </div>";
                                }
                            }
                            ?>
                        </div>
                        <div class="imageScrollDown" onclick="desceImagens();">⮟</div>
                    </div>
                    <img src="<?php
                    if (!empty($selectedImages)) {
                        echo reset($selectedImages);
                    } else {
                        echo "imagePlaceholder.jpg";
                    }
                    ?>" class="bigImgOutput">
                </div>
            </div>
        </form>

        <form method="get" action="thrashTreasuresInputBandas.php">
            <div class="selectList" id="selectListBandas">
                <div class="selectListTop selectListGridBandas">
                    <p class="selectListColumnName">Nome</p>
                    <p class="selectListColumnName">Descrição</p>
                    <p class="selectListColumnName">Gênero</p>
                    <p class="selectListColumnName">Quant. produtos</p>
                </div>
                <?php
                $stmt = $pdo->query("SELECT bandas.id AS id, bandas.nome AS nome, bandas.descricao AS descricao, genero.nome AS genero, count(produtos.id_banda) AS quant_produtos FROM bandas
                LEFT JOIN genero ON genero.id = bandas.genero_id
                LEFT JOIN produtos ON produtos.id_banda = bandas.id
                GROUP BY bandas.id, genero.nome;");

                while ($row = $stmt->fetch()) {
                    if (strlen($row["nome"]) > 50) {
                        $nomeCortado = substr($row["nome"], 0, 47) . "...";
                    } else {
                        $nomeCortado = $row["nome"];
                    }

                    if (strlen($row["descricao"]) > 100) {
                        $descricaoCortada = substr($row["descricao"], 0, 97) . "...";
                    } else {
                        $descricaoCortada = $row["descricao"];
                    }

                    echo "<input type=\"radio\" name=\"bandaList\" id=\"" . $row["id"] . "\" class=\"radioHidden\" value=\"" . $row["nome"] . "\"";
                    if ($bandaId == $row["id"]) {
                        echo " checked";
                    }
                    echo ">
                    <label for=\"" . $row["id"] . "\" class=\"selectListRow selectListGridBandas\">
                        <p class=\"selectListItem\">" . $nomeCortado . "</p>
                        <p class=\"selectListItem\">" . $descricaoCortada . "</p>
                        <p class=\"selectListItem\">" . $row["genero"] . "</p>
                        <p class=\"selectListItem\">" . $row["quant_produtos"] . "</p>
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