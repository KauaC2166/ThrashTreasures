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


    //verifica se os inputs foram usados
    if (isset($_POST["productRadio"])) {
        $productRadio = $_POST["productRadio"];
    } else {
        $productRadio = "";
    }

    if (isset($_POST["productId"]) && $_POST["productId"] !== "") {
        $productId = intval($_POST["productId"]);
    } else {
        $productId = null;
    }

    if (isset($_POST["productSelectFeito"]) && $_POST["productSelectFeito"] !== "") {
        $selectFeito = boolval($_POST["productSelectFeito"]);
    } else {
        $selectFeito = "";
    }

    if (isset($_POST["productRadioInsertChecked"]) && isset($_POST["productRadioUpdateChecked"])) {
        $radioInsertChecked = $_POST["productRadioInsertChecked"];
        $radioUpdateChecked = $_POST["productRadioUpdateChecked"];
    } else {
        $radioInsertChecked = "checked";
        $radioUpdateChecked = "";
    }

    if (isset($_POST["deleteCheckbox"]) && $_POST["deleteCheckbox"] !== "") {
        $deleteCheckbox = boolval($_POST["deleteCheckbox"]);
    } else {
        $deleteCheckbox = false;
    }

    if (isset($_POST["productName"])) {
        $productName = $_POST["productName"];
    } else {
        $productName = "";
    }

    if (isset($_POST["productDescription"])) {
        $productDescription = $_POST["productDescription"];
    } else {
        $productDescription = "";
    }

    if (isset($_POST["productCategoria"]) && $_POST["productCategoria"] !== "") {
        $productCategoria = intval($_POST["productCategoria"]);
    } else {
        $productCategoria = null;
    }

    if (isset($_POST["productBanda"]) && $_POST["productBanda"] !== "") {
        $productBanda = intval($_POST["productBanda"]);
    } else {
        $productBanda = null;
    }

    if (isset($_POST["valorCusto"]) && $_POST["valorCusto"] !== "") {
        $productValorCusto = floatval($_POST["valorCusto"]);
    } else {
        $productValorCusto = null;
    }

    if (isset($_POST["valorVenda"]) && $_POST["valorVenda"] !== "") {
        $productValorVenda = floatval($_POST["valorVenda"]);
    } else {
        $productValorVenda = null;
    }

    if (isset($_POST["productQuantEstoque"]) && $_POST["productQuantEstoque"] !== "") {
        $productQuantEstoque = intval($_POST["productQuantEstoque"]);
    } else {
        $productQuantEstoque = null;
    }

    if (isset($_GET["productList"]) && $_GET["productList"] !== "") {
        $productList = $_GET["productList"];
    } else {
        $productList = "";
    }

    $mensagem = "";

    $selectedImages = array();

    //caso o adm esteja inserindo um produto no bd
    if ($productRadio == 'insert') {
        if (strlen($productName) > 50) {
            $mensagem = "O nome do produto é muito longo";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else if (strlen($productDescription) > 1000) {
            $mensagem = "A descrição é muito longa";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else {
            $sql = "INSERT INTO produtos (nome, descricao, preco_custo, preco_venda, quantidade_estoque, id_categoria, id_banda) VALUES (?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productName, $productDescription, $productValorCusto, $productValorVenda, $productQuantEstoque, $productCategoria, $productBanda]);

            //codigo para inserção de imagem
            if (isset($_FILES["productImages"])) {

                $stmt = $pdo->prepare("SELECT produtos.id AS id FROM produtos
                WHERE nome = ?
                LIMIT 1;");
                $stmt->execute([$productName]);

                if ($row = $stmt->fetch()) {
                    $productId = $row["id"];

                    $totalFiles = count($_FILES['productImages']['name']);

                    for ($i = 0; $i < $totalFiles; $i++) {
                        $target_dir = "uploads/";
                        $target_file = $target_dir . "produto_" . basename($_FILES["productImages"]["name"][$i]);
                        $uploadOk = 1;
                        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                        if (isset($_POST["submit"])) {
                            if (!empty($_FILES["productImages"]["tmp_name"][$i])) {
                                $check = getimagesize($_FILES["productImages"]["tmp_name"][$i]);
                                if ($check !== false) {
                                    $uploadOk = 1;
                                } else {
                                    $uploadOk = 0;
                                }
                            } else {
                                $uploadOk = 0;
                            }
                        }

                        if ($_FILES["productImages"]["size"][$i] > 500000) {
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

                            $sql = "INSERT INTO produto_fotos (id_produto, foto) VALUES (?,?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$productId, $target_file]);
                        } else {
                            if (move_uploaded_file($_FILES["productImages"]["tmp_name"][$i], $target_file)) {


                                $sql = "INSERT INTO produto_fotos (id_produto, foto) VALUES (?,?)";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$productId, $target_file]);
                            }
                        }
                    }
                }
            }

            $mensagem = "Produto inserido";

            $deleteCheckbox = false;
            $productId = null;
            $productName = "";
            $productDescription = "";
            $productCategoria = null;
            $productBanda = null;
            $productValorCusto = null;
            $productValorVenda = null;
            $productQuantEstoque = null;


            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";
        }

        //caso o adm esteja procurando por um produto pela lista de pordutos
    } else if ($productList != "" && !$selectFeito) {
        $stmt = $pdo->prepare("SELECT produtos.id AS id, produtos.nome AS nome, produtos.descricao AS descricao, preco_custo, preco_venda, quantidade_estoque, id_categoria, id_banda FROM produtos
        WHERE nome = ?
        LIMIT 1;");
        $stmt->execute([$productList]);

        if ($row = $stmt->fetch()) {
            $productId = $row["id"];
            $productName = $row["nome"];
            $productDescription = $row["descricao"];
            $productValorCusto = $row["preco_custo"];
            $productValorVenda = $row["preco_venda"];
            $productQuantEstoque = $row["quantidade_estoque"];
            $productCategoria = $row["id_categoria"];
            $productBanda = $row["id_banda"];
        }

        $stmt = $pdo->prepare("SELECT * FROM produto_fotos
        WHERE id_produto = ?;");
        $stmt->execute([$productId]);

        while ($row = $stmt->fetch()) {
            $selectedImages[$row["id"]] = $row["foto"];
        }

        $mensagem = "Produto encontrado";

        $selectFeito = true;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

        //caso o adm esteja procurando por um porduto pelo input de nome
    } else if ($productRadio == 'update' && !$selectFeito && !empty($productName)) {
        $stmt = $pdo->prepare("SELECT produtos.id AS id, produtos.nome AS nome, produtos.descricao AS descricao, preco_custo, preco_venda, quantidade_estoque, id_categoria, id_banda FROM produtos
                        WHERE nome LIKE ?
                        LIMIT 1;");
        $stmt->execute(["%$productName%"]);

        if ($row = $stmt->fetch()) {
            $productId = $row["id"];
            $productName = $row["nome"];
            $productDescription = $row["descricao"];
            $productValorCusto = $row["preco_custo"];
            $productValorVenda = $row["preco_venda"];
            $productQuantEstoque = $row["quantidade_estoque"];
            $productCategoria = $row["id_categoria"];
            $productBanda = $row["id_banda"];

            $mensagem = "Produto encontrado";

            $selectFeito = true;
        } else {
            $mensagem = "Não foi possível encontrar este produto";

            $selectFeito = false;
        }

        $stmt = $pdo->prepare("SELECT * FROM produto_fotos
        WHERE id_produto = ?;");
        $stmt->execute([$productId]);

        while ($row = $stmt->fetch()) {
            $selectedImages[$row["id"]] = $row["foto"];
        }

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

        //atualiza o produto
    } else if ($productRadio == 'update' && $selectFeito && !$deleteCheckbox) {
        if (strlen($productName) > 50) {
            $mensagem = "O nome do produto é muito longo";

            $selectFeito = true;
            $deleteCheckbox = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

        } else if (strlen($productDescription) > 1000) {
            $mensagem = "A descrição é muito longa";

            $selectFeito = true;
            $deleteCheckbox = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

        } else {
            $sql = "UPDATE produtos SET nome=?, descricao=?, preco_custo=?, preco_venda=?, quantidade_estoque=?, id_categoria=?, id_banda=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productName, $productDescription, $productValorCusto, $productValorVenda, $productQuantEstoque, $productCategoria, $productBanda, $productId]);

            if (isset($_FILES["productImages"])) {

                $totalFiles = count($_FILES['productImages']['name']);

                for ($i = 0; $i < $totalFiles; $i++) {
                    $target_dir = "uploads/";
                    $target_file = $target_dir . "produto_" . basename($_FILES["productImages"]["name"][$i]);
                    $uploadOk = 1;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    if (isset($_POST["submit"])) {
                        if (!empty($_FILES["productImages"]["tmp_name"][$i])) {
                            $check = getimagesize($_FILES["productImages"]["tmp_name"][$i]);
                            if ($check !== false) {
                                $uploadOk = 1;
                            } else {
                                $uploadOk = 0;
                            }
                        } else {
                            $uploadOk = 0;
                        }
                    }

                    if ($_FILES["productImages"]["size"][$i] > 500000) {
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

                        $sql = "INSERT INTO produto_fotos (id_produto, foto) VALUES (?,?)";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([$productId, $target_file]);
                    } else {
                        if (move_uploaded_file($_FILES["productImages"]["tmp_name"][$i], $target_file)) {


                            $sql = "INSERT INTO produto_fotos (id_produto, foto) VALUES (?,?)";
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute([$productId, $target_file]);
                        }
                    }
                }
            }

            if (isset($_POST["deleteImageCheckbox"])) {
                $totalDeleteImgs = count($_POST["deleteImageCheckbox"]);

                for ($i = 0; $i < $totalDeleteImgs; $i++) {
                    $idImageDelete = $_POST["deleteImageCheckbox"][$i];

                    $sql = "DELETE FROM produto_fotos WHERE id=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$idImageDelete]);
                }
            }

            $selectFeito = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

            $mensagem = "Produto atualizado";

            $deleteCheckbox = false;
            $productId = null;
            $productName = "";
            $productDescription = "";
            $productCategoria = null;
            $productBanda = null;
            $productValorCusto = null;
            $productValorVenda = null;
            $productQuantEstoque = null;
        }

        //caso o adm esteja apagando o produto
    } else if ($productRadio == 'update' && $selectFeito && $deleteCheckbox) {
        $sql = "DELETE FROM produto_fotos WHERE id_produto=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId]);

        $sql = "DELETE FROM produtos WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId]);

        $selectFeito = false;
        $deleteCheckbox = false;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

        $mensagem = "Produto deletado";

        $deleteCheckbox = false;
        $productId = null;
        $productName = "";
        $productDescription = "";
        $productCategoria = null;
        $productBanda = null;
        $productValorCusto = null;
        $productValorVenda = null;
        $productQuantEstoque = null;
    }

} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!--pagina de input de produtos da Thrash Treasures-->
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
    <title>Thrash Treasures - Produtos</title>
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
            <p class="barraCimaSelectedBtn">Produtos</p>
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
            <p class="barraCimaBtn" <?php if (isset($permCadProduct) && $permCadProduct) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputBandas.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>><?php if (!isset($permCadProduct) || !$permCadProduct) {
                 echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
             } ?>Bandas
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

    <div class="contentConteiner" id="cadastroProduto">

        <!--mensagem-->
        <h3 class="mensagem">
            <?php echo $mensagem ?>
        </h3>

        <!--formulario do produto-->
        <form method="post" action="thrashTreasuresInputProdutos.php" id="cadastroProdutoForm"
            enctype="multipart/form-data">
            <input type="hidden" name="productId" value="<?php echo $productId ?>">
            <input type="hidden" name="productSelectFeito" value="<?php echo $selectFeito ?>">
            <input type="hidden" name="productRadioInsertChecked" value="<?php echo $radioInsertChecked ?>">
            <input type="hidden" name="productRadioUpdateChecked" value="<?php echo $radioUpdateChecked ?>">

            <!--radio buttons para selecionar entre iserir ou atualizar o produto-->
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
                        echo "<input type=\"checkbox\" name=\"deleteCheckbox\" id=\"productDeleteCheckbox\" class=\"deleteCheckbox\">
                        <label for=\"productDeleteCheckbox\" class=\"deleteCheckboxLabel\"><i class=\"fa-solid fa-trash\"></i></label>";
                    }
                    ?>
                    <button type="submit" name="submit" class="formSubmitBtn" value="Enviar" title="Enviar"><i
                            class="fa-solid fa-check"></i></button>
                </div>
            </div>

            <!--campos do porduto-->
            <div class="productMiddleForm">
                <div>
                    <input type="text" name="productName" id="productNameInput" placeholder="Nome"
                        value="<?php echo $productName ?>" required>
                    <textarea name="productDescription" id="produtctDescriptionInput" cols="30" rows="10"
                        placeholder="Descrição" class="changebleRequirement"
                        required><?php echo $productDescription ?></textarea>
                </div>
                <div>
                    <select name="productCategoria" id="productCategoriaInput" class="changebleRequirement" required>
                        <option disabled selected>Categoria</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM categorias");
                        while ($row = $stmt->fetch()) {
                            echo "<option value=\"" . $row["id"] . "\"";
                            if ($productCategoria == $row["id"]) {
                                echo " selected";
                            }
                            echo ">" . $row["nome"] . "</option>";
                        }
                        ?>
                    </select>
                    <select name="productBanda" id="productBandaInput" class="changebleRequirement" required>
                        <option disabled selected>Banda</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM bandas");
                        while ($row = $stmt->fetch()) {
                            echo "<option value=\"" . $row["id"] . "\"";
                            if ($productBanda == $row["id"]) {
                                echo " selected";
                            }
                            echo ">" . $row["nome"] . "</option>";
                        }
                        ?>
                    </select>
                    <input type="text" name="valorCusto" id="valorCustoInput" placeholder="Valor de custo"
                        class="changebleRequirement" value="<?php echo $productValorCusto ?>" required>
                    <input type="text" name="valorVenda" id="valorVendaInput" placeholder="Valor de venda"
                        class="changebleRequirement" value="<?php echo $productValorVenda ?>" required>
                    <input type="number" name="productQuantEstoque" id="productQuantEstoqueInput"
                        placeholder="Quantidade no estoque" class="changebleRequirement"
                        value="<?php echo $productQuantEstoque ?>" required>
                </div>
            </div>
            <!--input de imagem-->
            <div id="productFormBottom">
                <div class="imageInputLabelContainer">
                    <label for="productImageInput" class="imageInputLabel"><i class="fa-solid fa-image"></i></label>
                </div>
                <input type="file" name="productImages[]" id="productImageInput" class="ImageInput"
                    onchange="mostraNovasImagens(this);" multiple>
                <div class="productImagesConteiner">
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
                    <div class="zoomContainer">
                        <img src="<?php
                        if (!empty($selectedImages)) {
                            echo reset($selectedImages);
                        } else {
                            echo "imagePlaceholder.jpg";
                        }
                        ?>" class="bigImgOutput" onload="imageZoom('.bigImgOutput', '.imgZoomResult');">
                        <div id="myresult" class="imgZoomResult"></div>
                    </div>
                </div>
            </div>
        </form>

        <!--lista de produtos existentes (é possivel selecionar o porduto por esta lista)-->
        <form method="get" action="thrashTreasuresInputProdutos.php" id="productListContainer">
            <button type="submit" value="Procurar" class="selectListSearchBtn"><i
                    class="fa-solid fa-magnifying-glass"></i></button>
            <div class="selectList" id="selectListProdutos">
                <div class="selectListTop selectListGridProdutos">
                    <p class="selectListColumnName">Nome</p>
                    <p class="selectListColumnName">Descrição</p>
                    <p class="selectListColumnName">Categoria</p>
                    <p class="selectListColumnName">Banda</p>
                    <p class="selectListColumnName">Valor de custo</p>
                    <p class="selectListColumnName">Valor de venda</p>
                    <p class="selectListColumnName">Quant. estoque</p>
                    <p class="selectListColumnName">Quant. vendas</p>
                </div>
                <?php
                $stmt = $pdo->query("SELECT produtos.id AS id, produtos.nome AS nome, produtos.descricao AS descricao, preco_custo, preco_venda, quantidade_estoque, categorias.nome AS categoria, bandas.nome AS banda, quantidade_estoque, quant_vendas FROM produtos
                INNER JOIN categorias ON produtos.id_categoria = categorias.id
                INNER JOIN bandas ON produtos.id_banda = bandas.id
                ORDER BY id DESC
                 ");

                while ($row = $stmt->fetch()) {
                    if (strlen($row["nome"]) > 32) {
                        $nomeCortado = substr($row["nome"], 0, 32) . "...";
                    } else {
                        $nomeCortado = $row["nome"];
                    }

                    if (strlen($row["descricao"]) > 49) {
                        $descricaoCortada = substr($row["descricao"], 0, 49) . "...";
                    } else {
                        $descricaoCortada = $row["descricao"];
                    }

                    echo "<input type=\"radio\" name=\"productList\" id=\"" . $row["id"] . "\" class=\"radioHidden\" value=\"" . $row["nome"] . "\"";
                    if ($productId == $row["id"]) {
                        echo " checked";
                    }
                    echo ">
                    <label for=\"" . $row["id"] . "\" class=\"selectListRow selectListGridProdutos\">
                        <p class=\"selectListItem\">" . $nomeCortado . "</p>
                        <p class=\"selectListItem\">" . $descricaoCortada . "</p>
                        <p class=\"selectListItem\">" . $row["categoria"] . "</p>
                        <p class=\"selectListItem\">" . $row["banda"] . "</p>
                        <p class=\"selectListItem\">R$ " . $row["preco_custo"] . "</p>
                        <p class=\"selectListItem\">R$ " . $row["preco_venda"] . "</p>
                        <p class=\"selectListItem\">" . $row["quantidade_estoque"] . "</p>
                        <p class=\"selectListItem\">" . $row["quant_vendas"] . "</p>
                    </label>";
                }
                ?>
            </div>
        </form>
    </div>

    <script src="scriptThrashTreasuresWorkspace.js"></script>
</body>

</html>