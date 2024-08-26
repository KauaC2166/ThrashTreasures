<?php
try {
    //inicia sessão
    session_start();
    //pdo
    $pdo = new PDO('pgsql:host=localhost;dbname=thrashTreasures', "postgres", "210606");

    //verifica se o usuario/adm está logado
    if (isset($_SESSION["usuarioId"])) {
        $usuarioId = $_SESSION["usuarioId"];

        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id=?
    LIMIT 1;");
        $stmt->execute([$usuarioId]);

        if ($row = $stmt->fetch()) {
            $usuarioName = $row["nome"];
            $usuarioFoto = $row["foto_perfil"];
        }

    } else if (isset($_SESSION["admID"])) {
        $admId = $_SESSION["admID"];

        $stmt = $pdo->prepare("SELECT * FROM administrador WHERE id=?
    LIMIT 1;");
        $stmt->execute([$admId]);

        if ($row = $stmt->fetch()) {
            $admName = $row["nome"];
            $admFoto = $row["foto_perfil"];
        }
    }

    //arrays da barra de pesquisa
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

    //a pesquisa
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $resultadosPesquisa = array();
        //procurando por produtos da banda procurada
        if (isset($_POST["searchBandaObj"])) {
            $idBanda = $_POST["searchBandaObj"];
            $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id_banda=?;");
            $stmt->execute([$idBanda]);
            while ($row = $stmt->fetch()) {
                $resultadosPesquisa[] = $row["id"];
            }
            //procurando por pordutos da categoria procurada
        } else if (isset($_POST["searchCategoriaObj"])) {
            $idCategoria = $_POST["searchCategoriaObj"];
            $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id_categoria=?;");
            $stmt->execute([$idCategoria]);
            while ($row = $stmt->fetch()) {
                $resultadosPesquisa[] = $row["id"];
            }
            //procurado por produtos do genero procurado
        } else if (isset($_POST["searchGeneroObj"])) {
            $idGenero = $_POST["searchGeneroObj"];
            $stmt = $pdo->prepare("SELECT * FROM produtos
            INNER JOIN bandas ON produtos.id_banda = bandas.id
            WHERE bandas.genero_id=?;");
            $stmt->execute([$idGenero]);
            while ($row = $stmt->fetch()) {
                $resultadosPesquisa[] = $row["id"];
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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php if (isset($idBanda)) {
            $title_stmt = $pdo->prepare("SELECT * FROM bandas WHERE id=?;");
            $title_stmt->execute([$idBanda]);

            if ($title_row = $title_stmt->fetch()) {
                echo $title_row["nome"] . " - Thrash Treasures";
            }
        } else if (isset($idCategoria)) {
            $title_stmt = $pdo->prepare("SELECT * FROM categorias WHERE id=?;");
            $title_stmt->execute([$idCategoria]);

            if ($title_row = $title_stmt->fetch()) {
                echo $title_row["nome"] . " - Thrash Treasures";
            }
        } else if (isset($idGenero)) {
            $title_stmt = $pdo->prepare("SELECT * FROM genero WHERE id=?;");
            $title_stmt->execute([$idGenero]);

            if ($title_row = $title_stmt->fetch()) {
                echo $title_row["nome"] . " - Thrash Treasures";
            }
        } else {
            echo "Pesquisa - Thrash Treasures";
        }
        ?>
    </title>
    <link rel="stylesheet" href="frontEnd.css">
    <script src="https://kit.fontawesome.com/a1f82f34b1.js" crossorigin="anonymous"></script>
    <link rel="icon" type="images/favicon" href="favicon.ico">
</head>

<body>

    <!--Menu Superior-->
    <div id="menuSuperior">

        <!--Logo1-->
        <div><img id="logoImg" src="log.png" alt="Logotipo" onclick="window.location.href='index.php'"></div>
        <!--Logo1/-->

        <!--Barra de Pesquisa-->
        <div id="barraDiv">

            <form action="paginaPesquisa.php" method="post" id="searchBarForm">
                <input type="search" id="barraDePesquisa" placeholder="Encontre seu álbum favorito" name="searchInput"
                    onkeyup="searchBar();" onfocus="mostraSearchList();">
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
        <!--Barra de Pesquisa/-->


        <!--Icones Superiores-->
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
            <div class="iconeDireita" onclick="<?php if (!isset($usuarioId) && !isset($admId)) {
                echo "window.location.href = 'thrashTreasuresLogin.php'";
            } else if (isset($usuarioId)) {
                echo "window.location.href = 'thrashTreasuresLogin.php'";
            } else if (isset($admId)) {
                echo "window.location.href = 'thrashTreasuresDadosGerais.php'";
            } ?>">
                <?php
                if (isset($usuarioId) && $usuarioFoto != "") {
                    echo "<img src=\"" . $usuarioFoto . "\" title=\"" . $usuarioName . "\" id=\"fotoPerfil\">
            <p id=\"nomeUser\">" . $usuarioName . "</p>";
                } else if (isset($usuarioId) && $usuarioFoto == "") {
                    echo "<i class=\"fa-solid fa-user fa-xl\" style=\"color: #ffffff;\"></i>
        <p id=\"nomeUser\">" . $usuarioName . "</p>";
                } else if (isset($admId) && $admFoto != "") {
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
        </div>
    </div>

    <!---Grade de Produtos--->

    <!--formulario para enviar o produto para a pagina depvisualização de produtos-->
    <form method="get" action="paginaProduto.php" id="paginaPesquisaForm">

        <?php
        foreach ($resultadosPesquisa as $productId) {
            $stmt = $pdo->prepare("SELECT id, nome, preco_venda, id_banda FROM produtos
            WHERE quantidade_estoque > 0 AND id=?
            ORDER BY quant_vendas ASC
            LIMIT 20");
            $stmt->execute([$productId]);

            if ($row = $stmt->fetch()) {
                $productName = $row["nome"];
                $productIdBanda = $row["id_banda"];
                //codgio de formatação do valor do produto para "R$ X,XX"
                if (isset($row["preco_venda"])) {
                    $productPrecoVenda = $row["preco_venda"];
                    if (strpos($productPrecoVenda, '.') !== false) {
                        $productPrecoVenda = str_replace('.', ',', $productPrecoVenda);
                        $decimal_part = explode(',', $productPrecoVenda)[1];
                        if (strlen($decimal_part) == 1) {
                            $productPrecoVenda .= '0';
                        } elseif (strlen($decimal_part) > 2) {
                            $productPrecoVenda = substr($productPrecoVenda, 0, strpos($productPrecoVenda, ',') + 3);
                        }
                    } else {
                        $productPrecoVenda .= ',00';
                    }
                } else {
                    $productPrecoVenda = "0,00";
                }

                $banda_stmt = $pdo->prepare("SELECT * FROM bandas WHERE id=?;");
                $banda_stmt->execute([$productIdBanda]);

                if ($banda_row = $banda_stmt->fetch()) {
                    $nomeBanda = $banda_row["nome"];
                    $fotoBanda = $banda_row["foto_perfil"];
                }

                $foto_stmt = $pdo->prepare("SELECT foto FROM produto_fotos WHERE id_produto=?
                ORDER BY id DESC
                LIMIT 1;");
                $foto_stmt->execute([$productId]);
                if ($foto_row = $foto_stmt->fetch()) {
                    echo "<input type=\"submit\" name=\"productId\" id=\"" . $productId . "\" value=\"" . $productId . "\" class=\"btnHidden\">
                    <label for=\"" . $productId . "\"class=\"gradeMainDiv\">
                    <div class=\"pesquisaFotoContainer\">
            <img class=\"pesquisaFoto\" src=\"" . $foto_row["foto"] . "\" alt=\"foto da grade\">
            </div>
            <div>
                <div class=\"pesquisaColunaDois\">
                    <h1>" . $productName . "</h1>
                    <h3>R$ " . $productPrecoVenda . "</h3>
                </div>
                <div class=\"colunaDoisBanda\">
                    <img src=\"" . $fotoBanda . "\" alt=\"banda logo\">
                    <h4>" . $nomeBanda . "</h4>
                </div>
            </div>
        </label>";
                }
            }
        }
        ?>
    </form>

    <!--Info fim da página-->

    <div class="infoFimPag">

        <div class="colunaUmEnd">
            <img class="logoEnd" src="log.png" alt="logo fim">

            <p class="listaCatTitulo"><b>CRIADORES</b></p>

            <ul class="listaCategoriasEnd">
                <li>Murilo C. Araujo</li>
                <li>Kauã C. Seabra</li>

            </ul>
        </div>

        <div class="colunaDoisEnd">

            <!--categorias clickaveis (mesmo sistema da barra de pesquisa)-->
            <p class="listaCatTitulo"><b>AQUI VOCÊ ENCONTRA</b></p>
            <form action="paginaPesquisa.php" method="post">
                <ul class="listaCategoriasEnd">
                    <?php
                    foreach ($categoriaSearchList as $id => $nome) {
                        echo "<li><button type=\"submit\" name=\"searchCategoriaObj\" value=\"" . $id . "\">" . $nome . "</button></li>";
                    }
                    ?>
                </ul>
            </form>
        </div>
        <div class="colunaTresEnd">

            <p class="listaCatTitulo"><b>LINKS ÚTEIS</b></p>

            <ul class="listaCategoriasEnd">

                <li><a href="">Instagram</a></li>
                <li><a href="">Facebook</a></li>
                <li><a href="">Twitter</a></li>

        </div>

        <div class="colunaQuatroEnd">

            <p class="listaCatTitulo"><b>CONTATOS</b></p>

            <ul class="listaCategoriasEnd">

                <li>Vendas: (53) 99152-0052</li>
                <li>Administração: (53) 98453-1421</li>

        </div>

    </div>

    <script src="scriptThrashTreasuresIndex.js"></script>
</body>

</html>