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
    if (isset($_POST["categoriaRadio"])) {
        $categoriaRadio = $_POST["categoriaRadio"];
    } else {
        $categoriaRadio = "";
    }

    if (isset($_POST["categoriaId"]) && $_POST["categoriaId"] !== "") {
        $categoriaId = intval($_POST["categoriaId"]);
    } else {
        $categoriaId = null;
    }

    if (isset($_POST["categoriaSelectFeito"]) && $_POST["categoriaSelectFeito"] !== "") {
        $selectFeito = boolval($_POST["categoriaSelectFeito"]);
    } else {
        $selectFeito = "";
    }

    if (isset($_POST["categoriaRadioInsertChecked"]) && isset($_POST["categoriaRadioUpdateChecked"])) {
        $radioInsertChecked = $_POST["categoriaRadioInsertChecked"];
        $radioUpdateChecked = $_POST["categoriaRadioUpdateChecked"];
    } else {
        $radioInsertChecked = "checked";
        $radioUpdateChecked = "";
    }

    if (isset($_POST["deleteCheckbox"]) && $_POST["deleteCheckbox"] !== "") {
        $deleteCheckbox = boolval($_POST["deleteCheckbox"]);
    } else {
        $deleteCheckbox = false;
    }

    if (isset($_POST["categoriaName"])) {
        $categoriaName = $_POST["categoriaName"];
    } else {
        $categoriaName = "";
    }

    if (isset($_GET["categoriaList"]) && $_GET["categoriaList"] !== "") {
        $categoriaList = $_GET["categoriaList"];
    } else {
        $categoriaList = "";
    }

    $mensagem = "";

    //caso o adm for inserir uma categoria
    if ($categoriaRadio == 'insert') {
        if (strlen($categoriaName) > 30) {
            $mensagem = "O nome da categoria é muito longo";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else {
            $sql = "INSERT INTO categorias (nome) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$categoriaName]);

            $mensagem = "Categoria inserida";

            $deleteCheckbox = false;
            $categoriaId = null;
            $categoriaName = "";

            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";
        }

        //caso o adm selecione uma categoria da lista
    } else if ($categoriaList != "" && !$selectFeito) {
        $stmt = $pdo->prepare("SELECT id, nome FROM categorias
        WHERE nome = ?
        LIMIT 1;");
        $stmt->execute([$categoriaList]);

        if ($row = $stmt->fetch()) {
            $categoriaId = $row["id"];
            $categoriaName = $row["nome"];
        }

        $mensagem = "Categoria encotranda";

        $selectFeito = true;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

        //caso o adm procure por uma categoria pelo input de nome
    } else if ($categoriaRadio == 'update' && !$selectFeito && !empty($categoriaName)) {
        $stmt = $pdo->prepare("SELECT id, nome FROM categorias
        WHERE nome LIKE ?
        LIMIT 1;");
        $stmt->execute(["%$categoriaName%"]);

        if ($row = $stmt->fetch()) {
            $categoriaId = $row["id"];
            $categoriaName = $row["nome"];

            $mensagem = "Categoria encontrada";

            $selectFeito = true;
        } else {
            $mensagem = "Não foi possível encontrar esta categoria";

            $selectFeito = false;
        }

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

    } else if ($categoriaRadio == 'update' && $selectFeito && !$deleteCheckbox) {
        if (strlen($categoriaName) > 30) {
            $mensagem = "O nome da categoria é muito longo";

            $selectFeito = true;
            $deleteCheckbox = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

        } else {
            $sql = "UPDATE categorias SET nome=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$categoriaName, $categoriaId]);

            $selectFeito = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

            $mensagem = "Categoria atualizada";

            $deleteCheckbox = false;
            $categoriaId = null;
            $categoriaName = "";
        }

        //caso o adm for deletar uma categoria
    } else if ($categoriaRadio == 'update' && $selectFeito && $deleteCheckbox) {
        $sql = "DELETE FROM categorias WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$categoriaId]);

        $selectFeito = false;
        $deleteCheckbox = false;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

        $mensagem = "Categoria deletada";

        $deleteCheckbox = false;
        $categoriaId = null;
        $categoriaName = "";
    }

} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!--pagina de input de categorias da Thrash Treasures-->
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
    <title>Thrash Treasures - Categorias</title>
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
            <p class="barraCimaSelectedBtn">Categorias
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

    <div class="contentConteiner" id="cadastroCategoria">

        <!--mensagem-->
        <h3 class="mensagem">
            <?php echo $mensagem ?>
        </h3>

        <!--formulario das categorias-->
        <form method="post" action="thrashTreasuresInputCategorias.php" id="cadastroCategoriaForm">
            <input type="hidden" name="categoriaId" value="<?php echo $categoriaId ?>">
            <input type="hidden" name="categoriaSelectFeito" value="<?php echo $selectFeito ?>">
            <input type="hidden" name="categoriaRadioInsertChecked" value="<?php echo $radioInsertChecked ?>">
            <input type="hidden" name="categoriaRadioUpdateChecked" value="<?php echo $radioUpdateChecked ?>">

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
                        echo "<input type=\"checkbox\" name=\"deleteCheckbox\" id=\"categoriaDeleteCheckbox\" class=\"deleteCheckbox\">
                        <label for=\"categoriaDeleteCheckbox\" class=\"deleteCheckboxLabel\"><i class=\"fa-solid fa-trash\"></i></label>";
                    }
                    ?>
                    <button type="submit" name="submit" class="formSubmitBtn" value="Enviar" title="Enviar"><i
                            class="fa-solid fa-check"></i></button>
                </div>
            </div>
            <div>
                <input type="text" name="categoriaName" id="categoriaNameInput" placeholder="Nome"
                    value="<?php echo $categoriaName ?>" required>

            </div>
    </div>
    </form>

    <!--lista de categorias-->
    <form method="get" action="thrashTreasuresInputCategorias.php" id="categoriaListContainer">
        <button type="submit" value="Procurar" class="selectListSearchBtn"><i
                class="fa-solid fa-magnifying-glass"></i></button>
        <div class="selectList" id="selectListCategorias">
            <div class="selectListTop selectListGridCategorias">
                <p class="selectListColumnName">Nome</p>
                <p class="selectListColumnName">Quant. produtos</p>
                <p class="selectListColumnName">Quant. estoque</p>
                <p class="selectListColumnName">Quant. vendas</p>
            </div>
            <?php
            $stmt = $pdo->query("SELECT categorias.id AS id, categorias.nome AS nome, count(produtos.id_categoria) AS quant_produtos, sum(produtos.quantidade_estoque) AS quant_estoque, sum(produtos.quant_vendas) AS quant_vendas FROM categorias
            LEFT JOIN produtos ON produtos.id_categoria = categorias.id
            GROUP BY categorias.id");

            while ($row = $stmt->fetch()) {
                if (strlen($row["nome"]) > 19) {
                    $nomeCortado = substr($row["nome"], 0, 19) . "...";
                } else {
                    $nomeCortado = $row["nome"];
                }

                echo "<input type=\"radio\" name=\"categoriaList\" id=\"" . $row["id"] . "\" class=\"radioHidden\" value=\"" . $row["nome"] . "\"";
                if ($categoriaId == $row["id"]) {
                    echo " checked";
                }
                echo ">
                    <label for=\"" . $row["id"] . "\" class=\"selectListRow selectListGridCategorias\">
                        <p class=\"selectListItem\">" . $nomeCortado . "</p>
                        <p class=\"selectListItem\">" . $row["quant_produtos"] . "</p>
                        <p class=\"selectListItem\">" . $row["quant_estoque"] . "</p>
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