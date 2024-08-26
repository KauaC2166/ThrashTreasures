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

    if (isset($_POST["generoRadio"])) {
        $generoRadio = $_POST["generoRadio"];
    } else {
        $generoRadio = "";
    }

    if (isset($_POST["generoId"]) && $_POST["generoId"] !== "") {
        $generoId = intval($_POST["generoId"]);
    } else {
        $generoId = null;
    }

    if (isset($_POST["generoSelectFeito"]) && $_POST["generoSelectFeito"] !== "") {
        $selectFeito = boolval($_POST["generoSelectFeito"]);
    } else {
        $selectFeito = "";
    }

    if (isset($_POST["generoRadioInsertChecked"]) && isset($_POST["generoRadioUpdateChecked"])) {
        $radioInsertChecked = $_POST["generoRadioInsertChecked"];
        $radioUpdateChecked = $_POST["generoRadioUpdateChecked"];
    } else {
        $radioInsertChecked = "checked";
        $radioUpdateChecked = "";
    }

    if (isset($_POST["deleteCheckbox"]) && $_POST["deleteCheckbox"] !== "") {
        $deleteCheckbox = boolval($_POST["deleteCheckbox"]);
    } else {
        $deleteCheckbox = false;
    }

    if (isset($_POST["generoName"])) {
        $generoName = $_POST["generoName"];
    } else {
        $generoName = "";
    }

    if (isset($_GET["generoList"]) && $_GET["generoList"] !== "") {
        $generoList = $_GET["generoList"];
    } else {
        $generoList = "";
    }

    $mensagem = "";

    if ($generoRadio == 'insert') {
        if (strlen($generoName) > 30) {
            $mensagem = "O nome é muito longo";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else {
            $sql = "INSERT INTO genero (nome) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$generoName]);

            $mensagem = "Insert feito";

            $deleteCheckbox = false;
            $generoId = null;
            $generoName = "";

            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";
        }

    } else if ($generoList != "" && !$selectFeito) {
        $stmt = $pdo->prepare("SELECT id, nome FROM genero
        WHERE nome = ?
        LIMIT 1;");
        $stmt->execute([$generoList]);

        if ($row = $stmt->fetch()) {
            $generoId = $row["id"];
            $generoName = $row["nome"];
        }

        $mensagem = "genero encotranda com sucesso!";

        $selectFeito = true;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

    } else if ($generoRadio == 'update' && !$selectFeito && !empty($generoName)) {
        $stmt = $pdo->prepare("SELECT id, nome FROM genero
        WHERE nome LIKE ?
        LIMIT 1;");
        $stmt->execute(["%$generoName%"]);

        if ($row = $stmt->fetch()) {
            $generoId = $row["id"];
            $generoName = $row["nome"];

            $mensagem = "genero encontrada com sucesso!";

            $selectFeito = true;
        } else {
            $mensagem = "Não foi possível encontrar esta genero";

            $selectFeito = false;
        }

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

    } else if ($generoRadio == 'update' && $selectFeito && !$deleteCheckbox) {
        if (strlen($generoName) > 30) {
            $mensagem = "O nome é muito longo";

            $selectFeito = true;
            $deleteCheckbox = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

        } else {
            $sql = "UPDATE genero SET nome=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$generoName, $generoId]);
            echo "Atualização realizada com sucesso.";

            $selectFeito = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

            $mensagem = "update feito";

            $deleteCheckbox = false;
            $generoId = null;
            $generoName = "";
        }

    } else if ($generoRadio == 'update' && $selectFeito && $deleteCheckbox) {
        $sql = "DELETE FROM genero WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$generoId]);

        $selectFeito = false;
        $deleteCheckbox = false;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

        $mensagem = "genero deletada";

        $deleteCheckbox = false;
        $generoId = null;
        $generoName = "";
    }

} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!--pagina de input de generos da Thrash Treasures-->
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
    <title>Thrash Treasures - Generos</title>
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
            <p class="barraCimaSelectedBtn">Gêneros
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaBtn" <?php if (isset($permCadFunc) && $permCadFunc) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputFuncionarios.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>>
                <?php if (!isset($permCadFunc) || !$permCadFunc) {
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

    <div class="contentConteiner" id="cadastrogenero">

        <h3>
            <?php echo $mensagem ?>
        </h3>

        <form method="post" action="thrashTreasuresInputGenero.php" id="cadastroGeneroForm">
            <input type="hidden" name="generoId" value="<?php echo $generoId ?>">
            <input type="hidden" name="generoSelectFeito" value="<?php echo $selectFeito ?>">
            <input type="hidden" name="generoRadioInsertChecked" value="<?php echo $radioInsertChecked ?>">
            <input type="hidden" name="generoRadioUpdateChecked" value="<?php echo $radioUpdateChecked ?>">

            <input type="radio" name="generoRadio" id="insertRadio" value="insert" <?php echo $radioInsertChecked ?>>
            <label for="insertRadio">Inserir genero</label>
            <input type="radio" name="generoRadio" id="updateRadio" value="update" <?php echo $radioUpdateChecked ?>>
            <label for="updateRadio">Atualizar genero</label>
            <div>
                <input type="text" name="generoName" id="generoNameInput" placeholder="Nome"
                    value="<?php echo $generoName ?>" required>
                <?php
                if ($selectFeito) {
                    echo "<input type=\"checkbox\" name=\"deleteCheckbox\" id=\"generoDeleteCheckbox\">
                        <label for=\"generoDeleteCheckbox\" value=\"true\">Apagar genero</label>";
                }
                ?>
                <input type="submit" value="Enviar">
            </div>
    </div>
    </form>

    <form method="get" action="thrashTreasuresInputGenero.php">
        <div class="selectList" id="selectListGenero">
            <div class="selectListTop selectListGridGenero">
                <p class="selectListColumnName">Nome</p>
                <p class="selectListColumnName">Quant. bandas</p>
            </div>
            <?php
            $stmt = $pdo->query("SELECT genero.id AS id, genero.nome AS nome, count(bandas.genero_id) AS quant_bandas FROM genero
            LEFT JOIN bandas ON bandas.genero_id = genero.id
            GROUP BY genero.id");

            while ($row = $stmt->fetch()) {
                if (strlen($row["nome"]) > 23) {
                    $nomeCortado = substr($row["nome"], 0, 23) . "...";
                } else {
                    $nomeCortado = $row["nome"];
                }

                echo "<input type=\"radio\" name=\"generoList\" id=\"" . $row["id"] . "\" class=\"radioHidden\" value=\"" . $row["nome"] . "\"";
                if ($generoId == $row["id"]) {
                    echo " checked";
                }
                echo ">
                    <label for=\"" . $row["id"] . "\" class=\"selectListRow selectListGridGenero\">
                        <p class=\"selectListItem\">" . $nomeCortado . "</p>
                        <p class=\"selectListItem\">" . $row["quant_bandas"] . "</p>
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