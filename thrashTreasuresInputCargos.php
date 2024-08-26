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

    if (isset($_POST["cargoRadio"])) {
        $cargoRadio = $_POST["cargoRadio"];
    } else {
        $cargoRadio = "";
    }

    if (isset($_POST["cargoId"]) && $_POST["cargoId"] !== "") {
        $cargoId = intval($_POST["cargoId"]);
    } else {
        $cargoId = null;
    }

    if (isset($_POST["cargoSelectFeito"]) && $_POST["cargoSelectFeito"] !== "") {
        $selectFeito = boolval($_POST["cargoSelectFeito"]);
    } else {
        $selectFeito = false;
    }

    if (isset($_POST["cargoRadioInsertChecked"]) && isset($_POST["cargoRadioUpdateChecked"])) {
        $radioInsertChecked = $_POST["cargoRadioInsertChecked"];
        $radioUpdateChecked = $_POST["cargoRadioUpdateChecked"];
    } else {
        $radioInsertChecked = "checked";
        $radioUpdateChecked = "";
    }

    if (isset($_POST["deleteCheckbox"]) && !empty($_POST["deleteCheckbox"])) {
        $deleteCheckbox = true;
    } else {
        $deleteCheckbox = false;
    }

    if (isset($_POST["cargoName"])) {
        $cargoName = $_POST["cargoName"];
    } else {
        $cargoName = "";
    }

    if (isset($_POST["permCadProduct"]) && $_POST["permCadProduct"] === "on") {
        $permCadProduct = 1;
    } else {
        $permCadProduct = 0;
    }

    if (isset($_POST["permCadFuncionario"]) && $_POST["permCadFuncionario"] === "on") {
        $permCadFuncionario = 1;
    } else {
        $permCadFuncionario = 0;
    }

    if (isset($_POST["permHistoricoVendas"]) && $_POST["permHistoricoVendas"] === "on") {
        $permHistoricoVendas = 1;
    } else {
        $permHistoricoVendas = 0;
    }

    if (isset($_POST["permVisualCliente"]) && $_POST["permVisualCliente"] === "on") {
        $permVisualCliente = 1;
    } else {
        $permVisualCliente = 0;
    }

    if (isset($_GET["cargoList"]) && $_GET["cargoList"] !== "") {
        $cargoList = $_GET["cargoList"];
    } else {
        $cargoList = "";
    }

    $mensagem = "";

    if ($cargoRadio == 'insert') {
        if (strlen($cargoName) > 100) {
            $mensagem = "O nome é muito longo";

            $deleteCheckbox = false;
            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";

        } else {
            $sql = "INSERT INTO cargos (cargo, permissao_cadastro_produtos, permissao_cadastro_func, permissao_historico_vendas, permissao_visualizacao_clientes) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cargoName, $permCadProduct, $permCadFuncionario, $permHistoricoVendas, $permVisualCliente]);

            $mensagem = "Insert feito";

            $deleteCheckbox = false;
            $cargoId = null;
            $cargoName = "";
            $permCadProduct = false;
            $permCadFuncionario = false;
            $permHistoricoVendas = false;
            $permVisualCliente = false;

            $selectFeito = false;

            $radioInsertChecked = "checked";
            $radioUpdateChecked = "";
        }

    } else if ($cargoList != "" && !$selectFeito) {
        $stmt = $pdo->prepare("SELECT cargos.id AS id, cargos.cargo AS nome, permissao_cadastro_produtos, permissao_cadastro_func, permissao_historico_vendas, permissao_visualizacao_clientes FROM cargos
        WHERE cargos.cargo LIKE ?
        LIMIT 1;");
        $stmt->execute(["%$cargoList%"]);

        if ($row = $stmt->fetch()) {
            $cargoId = $row["id"];
            $cargoName = $row["nome"];
            $permCadProduct = boolval($row["permissao_cadastro_produtos"]);
            $permCadFuncionario = boolval($row["permissao_cadastro_func"]);
            $permHistoricoVendas = boolval($row["permissao_historico_vendas"]);
            $permVisualCliente = boolval($row["permissao_visualizacao_clientes"]);
        }

        $mensagem = "Produto encontrado com sucesso!";

        $selectFeito = true;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

    } else if ($cargoRadio == 'update' && !$selectFeito && !empty($cargoName)) {
        $stmt = $pdo->prepare("SELECT cargos.id AS id, cargos.cargo AS nome, permissao_cadastro_produtos, permissao_cadastro_func, permissao_historico_vendas, permissao_visualizacao_clientes FROM cargos
        WHERE cargos.cargo LIKE ?
        LIMIT 1;");
        $stmt->execute([$cargoName]);

        if ($row = $stmt->fetch()) {
            $cargoId = $row["id"];
            $cargoName = $row["nome"];
            $permCadProduct = boolval($row["permissao_cadastro_produtos"]);
            $permCadFuncionario = boolval($row["permissao_cadastro_func"]);
            $permHistoricoVendas = boolval($row["permissao_historico_vendas"]);
            $permVisualCliente = boolval($row["permissao_visualizacao_clientes"]);

            $mensagem = "Produto encontrado com sucesso!";

            $selectFeito = true;
        } else {
            $mensagem = "Não foi possível encontrar este produto";

            $selectFeito = false;
        }

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

    } else if ($cargoRadio == 'update' && $selectFeito && !$deleteCheckbox) {
        if (strlen($cargoName) > 100) {
            $mensagem = "O nome é muito longo";

            $selectFeito = true;
            $deleteCheckbox = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

        } else {
            $sql = "UPDATE cargos SET cargo=?, permissao_cadastro_produtos=?, permissao_cadastro_func=?, permissao_historico_vendas=?,
            permissao_visualizacao_clientes=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$cargoName, $permCadProduct, $permCadFuncionario, $permHistoricoVendas, $permVisualCliente, $cargoId]);

            $selectFeito = false;

            $radioInsertChecked = "";
            $radioUpdateChecked = "checked";

            $mensagem = "update feito";

            $deleteCheckbox = false;
            $cargoId = null;
            $cargoName = "";
            $permCadProduct = false;
            $permCadFuncionario = false;
            $permHistoricoVendas = false;
            $permVisualCliente = false;
        }

    } else if ($cargoRadio == 'update' && $selectFeito && $deleteCheckbox) {
        $sql = "DELETE FROM cargos WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cargoId]);

        $selectFeito = false;
        $deleteCheckbox = false;

        $radioInsertChecked = "";
        $radioUpdateChecked = "checked";

        $mensagem = "produto deletado";

        $deleteCheckbox = false;
        $cargoId = null;
        $cargoName = "";
        $permCadProduct = false;
        $permCadFuncionario = false;
        $permHistoricoVendas = false;
        $permVisualCliente = false;
    }

} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!--pagina de input de cargos da Thrash Treasures-->
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
    <title>Thrash Treasures - Lista de Clientes</title>
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
            <p class="barraCimaSelectedBtn">
                Cargos
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

    <div class="contentConteiner" id="cadastroCargo">

        <h3>
            <?php echo $mensagem ?>
        </h3>

        <form method="post" action="thrashTreasuresInputCargos.php" id="cadastroCargoForm">
            <input type="hidden" name="cargoId" value="<?php echo $cargoId ?>">
            <input type="hidden" name="cargoSelectFeito" value="<?php echo $selectFeito ?>">
            <input type="hidden" name="cargoRadioInsertChecked" value="<?php echo $radioInsertChecked ?>">
            <input type="hidden" name="cargoRadioUpdateChecked" value="<?php echo $radioUpdateChecked ?>">

            <input type="radio" name="cargoRadio" id="insertRadio" value="insert" <?php echo $radioInsertChecked ?>>
            <label for="insertRadio">Inserir cargo</label>
            <input type="radio" name="cargoRadio" id="updateRadio" value="update" <?php echo $radioUpdateChecked ?>>
            <label for="updateRadio">Atualizar cargo</label>

            <input type="text" name="cargoName" id="cargoNameInput" placeholder="Nome" value="<?php echo $cargoName ?>"
                required>

            <div id="cargosCheckboxContainer">
                <div class="cargosCheckboxLabelContainer">
                    <input type="checkbox" name="permCadProduct" id="permCadProduct" <?php if ($permCadProduct) {
                        echo "checked";
                    } ?>>
                    <label for="permCadProduct">Permissão de cadastro de produto</label>
                </div>

                <div class="cargosCheckboxLabelContainer">
                    <input type="checkbox" name="permCadFuncionario" id="permCadFuncionario" <?php if ($permCadFuncionario) {
                        echo "checked";
                    } ?>>
                    <label for="permCadFuncionario">Permissão de cadastro de funcionario</label>
                </div>

                <div class="cargosCheckboxLabelContainer">
                    <input type="checkbox" name="permHistoricoVendas" id="permHistoricoVendas" <?php if ($permHistoricoVendas) {
                        echo "checked";
                    } ?>>
                    <label for="permHistoricoVendas">Permissão de visialização do historico de vendas</label>
                </div>

                <div class="cargosCheckboxLabelContainer">
                    <input type="checkbox" name="permVisualCliente" id="permVisualCliente" <?php if ($permVisualCliente) {
                        echo "checked";
                    } ?>>
                    <label for="permVisualCliente">Permissão de visualização de clientes</label>
                </div>
            </div>

            <?php
            if ($selectFeito) {
                echo "<input type=\"checkbox\" name=\"deleteCheckbox\" id=\"cargoDeleteCheckbox\">
                        <label for=\"cargoDeleteCheckbox\">Apagar cargo</label>";
            }
            ?>
            <input type="submit" value="Enviar">
        </form>

        <form method="get" action="thrashTreasuresInputCargos.php">
            <div class="selectList" id="selectListCargos">
                <div class="selectListTop selectListGridCargos">
                    <p class="selectListColumnName">Cargo</p>
                    <p class="selectListColumnName">Perm. de cadastro de produto</p>
                    <p class="selectListColumnName">Perm. de cadastro de funcionario</p>
                    <p class="selectListColumnName">Perm. de visialização do historico de vendas</p>
                    <p class="selectListColumnName">Perm. de visualização de clientes</p>
                    <p class="selectListColumnName">Quant. func. com este cargo</p>
                </div>
                <?php
                $stmt = $pdo->query("SELECT cargos.id AS id, cargos.cargo AS nome, permissao_cadastro_produtos, permissao_cadastro_func, permissao_historico_vendas, permissao_visualizacao_clientes, count(administrador.id_cargo) AS quant_func FROM cargos
                LEFT JOIN administrador ON administrador.id_cargo = cargos.id
                GROUP BY cargos.id;");

                while ($row = $stmt->fetch()) {
                    if (strlen($row["nome"]) > 32) {
                        $nomeCortado = substr($row["nome"], 0, 32) . "...";
                    } else {
                        $nomeCortado = $row["nome"];
                    }

                    if ($row["permissao_cadastro_produtos"] == 1) {
                        $permCadProductSelect = "sim";
                    } else {
                        $permCadProductSelect = "não";
                    }

                    if ($row["permissao_cadastro_func"] == 1) {
                        $permCadFuncSelect = "sim";
                    } else {
                        $permCadFuncSelect = "não";
                    }

                    if ($row["permissao_historico_vendas"] == 1) {
                        $permHistVendasSelect = "sim";
                    } else {
                        $permHistVendasSelect = "não";
                    }

                    if ($row["permissao_visualizacao_clientes"] == 1) {
                        $permVisualClientesSelect = "sim";
                    } else {
                        $permVisualClientesSelect = "não";
                    }

                    echo "<input type=\"radio\" name=\"cargoList\" id=\"" . $row["id"] . "\" class=\"radioHidden\" value=\"" . $row["nome"] . "\"";
                    if ($cargoId == $row["id"]) {
                        echo " checked";
                    }
                    echo ">
                    <label for=\"" . $row["id"] . "\" class=\"selectListRow selectListGridCargos\">
                        <p class=\"selectListItem\">" . $row["nome"] . "</p>
                        <p class=\"selectListItem\">" . $permCadProductSelect . "</p>
                        <p class=\"selectListItem\">" . $permCadFuncSelect . "</p>
                        <p class=\"selectListItem\">" . $permHistVendasSelect . "</p>
                        <p class=\"selectListItem\">" . $permVisualClientesSelect . "</p>
                        <p class=\"selectListItem\">" . $row["quant_func"] . "</p>
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