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
            <p class="barraCimaBtn" <?php if (isset($permCadFunc) && $permCadFunc) {
                echo "onclick=\"window.location.href = 'thrashTreasuresInputCargos.php'\"";
            } else {
                echo "title=\"Você não tem acesso á esta pagina\"";
            } ?>>
                <?php if (!isset($permCadFunc) || !$permCadFunc) {
                    echo "<i class=\"fa-solid fa-lock\" style=\"color: #FFD43B;\"></i>";
                } ?>Cargos
            </p>
            <div class="espacoBtns"></div>
            <p class="barraCimaSelectedBtn">
                Clientes
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

    <div class="contentConteiner" id="listaClientes">
        <div class="selectList" id="listaClientesList">
            <div class="selectListTop listaClientesGrid">
                <p class="selectListColumnName">Cliente</p>
                <p class="selectListColumnName">Email</p>
                <p class="selectListColumnName">Total de produtos comprados</p>
                <p class="selectListColumnName">Valor total gasto na loja</p>
            </div>
            <?php
            $pdo = new PDO('pgsql:host=localhost;dbname=thrashTreasures', "postgres", "210606");

            $stmt = $pdo->query("SELECT clientes.nome AS cliente, clientes.email AS email, sum(carrinho.quantidade) AS quant_total,
            SUM(carrinho.valor_unitario) AS valor_total FROM clientes
            INNER JOIN carrinho ON carrinho.id_cliente = clientes.id
            GROUP BY clientes.id;");

            while ($row = $stmt->fetch()) {
                echo "
                    <div class=\"selectListRow listaClientesGrid\">
                        <p class=\"selectListItem\">" . $row["cliente"] . "</p>
                        <p class=\"selectListItem\">" . $row["email"] . "</p>
                        <p class=\"selectListItem\">" . $row["quant_total"] . "</p>
                        <p class=\"selectListItem\">" . $row["valor_total"] . "</p>
                    </div>";
            }
            ?>
        </div>
    </div>

    <script src="scriptThrashTreasuresWorkspace.js"></script>

</body>

</html>