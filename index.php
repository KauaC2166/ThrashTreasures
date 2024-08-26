<?php
//abre a sessão
session_start();
//pdo
$pdo = new PDO('pgsql:host=localhost;dbname=thrashTreasures', "postgres", "210606");

//verifica se o cliente está logado
if (isset($_SESSION["usuarioId"])) {
    $usuarioId = $_SESSION["usuarioId"];

    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id=?
    LIMIT 1;");
    $stmt->execute([$usuarioId]);

    if ($row = $stmt->fetch()) {
        $usuarioName = $row["nome"];
        $usuarioFoto = $row["foto_perfil"];
    }

    //verifica se um adm está logado
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

//seleção de categorias que serão mostrados na pagina inicial
$selectedCategorias = array();

$stmt = $pdo->query("SELECT categorias.id AS id, categorias.nome AS nome, count(produtos.id_categoria) AS quant_produtos,
sum(produtos.quant_vendas) AS quant_vendas FROM categorias
INNER JOIN produtos ON produtos.id_categoria = categorias.id
GROUP BY categorias.id, categorias.nome
HAVING count(produtos.id_categoria) >= 5
ORDER BY quant_vendas DESC
LIMIT 3;");
while ($row = $stmt->fetch()) {
    $selectedCategorias[$row["id"]] = $row["nome"];
}

//seleção de bandas que serão mostrados na pagina inicial
$selectedBandas = array();

$stmt = $pdo->query("SELECT bandas.id AS id, bandas.nome AS nome, count(produtos.id_banda) AS quant_produtos,
sum(produtos.quant_vendas) AS quant_vendas FROM bandas
INNER JOIN produtos ON produtos.id_banda = bandas.id
GROUP BY bandas.id, bandas.nome
HAVING count(produtos.id_banda) >= 5
ORDER BY quant_vendas DESC
LIMIT 3;");
while ($row = $stmt->fetch()) {
    $selectedBandas[$row["id"]] = $row["nome"];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <title>Thrash Treasures - Início</title>
    <link rel="stylesheet" type="text/css" href="frontEnd.css">
    <link rel="icon" type="images/favicon" href="favicon.ico">
    <script src="https://kit.fontawesome.com/a1f82f34b1.js" crossorigin="anonymous"></script>

</head>

<body>

    <!--///////////////////////////////////////   Menu Superior  //////////////////////////////////-->
    <div id="menuSuperior">

        <!--  ///////////// Logo1  /////////////-->
        <div><img id="logoImg" src="log.png" alt="Logotipo" onclick="window.location.href='index.php'"></div>
        <!--  ////////////////  Logo1  //////////////////-->

        <!-- ///////////////////  Barra de Pesquisa  //////////////////////-->
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
            <!--//////////////////////  Icones Superiores  //////////////////////////////-->


        </div>
    </div>
    <!--   ////////////////////////////////   Menu Superior   ////////////////////////////////-->

    <!--///////////////////////   Slide Central   //////////////////////-->
    <div id="fotoInicial">

        <?php for ($i = 2; $i < 12; $i++) {
            echo "<div class=\"slides\">
                <img src=\"slide" . $i . ".png\">
            </div>";
        }
        ?>
    </div>
    <!--//////////////////////////   Slide Central   ////////////////////////////////-->

    <!-- /////////////////////////  Grade de Produtos  //////////////////////////-->
    <div class="tagGrade">

        <h1>Mais Vendidos</h1>

    </div>

    <!--formulario para selecionar o produto-->
    <form method="get" action="paginaProduto.php" class="imageScrollConteiner">
        <div class="imageScrollLeft">⮜</div>
        <div class="gradeContainer">

            <?php
            $stmt = $pdo->query("SELECT id, nome, preco_venda FROM produtos
            WHERE quantidade_estoque > 0
            ORDER BY quant_vendas ASC
            LIMIT 20");

            while ($row = $stmt->fetch()) {
                $productId = $row["id"];
                $productName = $row["nome"];
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

                $foto_stmt = $pdo->prepare("SELECT foto FROM produto_fotos WHERE id_produto=?
                ORDER BY id DESC
                LIMIT 1;");
                $foto_stmt->execute([$productId]);
                if ($foto_row = $foto_stmt->fetch()) {
                    if (strlen($productName) > 27) {
                        $nomeCortado = substr($productName, 0, 24) . "...";
                    } else {
                        $nomeCortado = $productName;
                    }

                    echo "<input type=\"submit\" name=\"productId\" id=\"" . $productId . "\" value=\"" . $productId . "\" class=\"btnHidden\">
                    
                    <label for=\"" . $productId . "\" class=\"produtoGrade\">
                    <div class=\"imgGradeContainer\">
                            <img class=\"imgGrade\" src=\"" . $foto_row["foto"] . "\" alt=\"" . $productName . "\">
                        </div>
                            <p class=\"tituloGrade\">" . $nomeCortado . "</p>
                            <div class=\"precoGrade\">
                            <p class=\"r4\"><b>R$</b></p>
                            <p><b>" . $productPrecoVenda . "</b></p>
                            </div>
                        </label>";
                }
            }
            ?>
        </div>
        <div class="imageScrollRight">⮞</div>
    </form>

    <!--sistema pra mostrar os produtos das categorias-->
    <?php
    foreach ($selectedCategorias as $CategoriaId => $CategoriaName) {
        echo "<div class=\"tagGrade\">

        <h1>" . $CategoriaName . "</h1>

    </div>

    <form method=\"get\" action=\"paginaProduto.php\" class=\"imageScrollConteiner\">
        <div class=\"imageScrollLeft\">⮜</div>
        <div class=\"gradeContainer\">";

        $stmt = $pdo->prepare("SELECT id, nome, preco_venda FROM produtos WHERE id_categoria=? AND quantidade_estoque > 0
        ORDER BY quant_vendas DESC
        LIMIT 20;");
        $stmt->execute([$CategoriaId]);

        while ($row = $stmt->fetch()) {
            $productId = $row["id"];
            $productName = $row["nome"];
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

            $foto_stmt = $pdo->prepare("SELECT foto FROM produto_fotos WHERE id_produto=?
                ORDER BY id DESC
                LIMIT 1;");
            $foto_stmt->execute([$productId]);
            if ($foto_row = $foto_stmt->fetch()) {
                if (strlen($productName) > 27) {
                    $nomeCortado = substr($productName, 0, 24) . "...";
                } else {
                    $nomeCortado = $productName;
                }

                echo "<input type=\"submit\" name=\"productId\" id=\"" . $productId . "\" value=\"" . $productId . "\" class=\"btnHidden\">
                    
                    <label for=\"" . $productId . "\" class=\"produtoGrade\">
                        <div class=\"imgGradeContainer\">
                            <img class=\"imgGrade\" src=\"" . $foto_row["foto"] . "\" alt=\"" . $productName . "\">
                        </div>
                            <p class=\"tituloGrade\">" . $nomeCortado . "</p>
                            <div class=\"precoGrade\">
                            <p class=\"r4\"><b>R$</b></p>
                            <p><b>" . $productPrecoVenda . "</b></p>
                            </div>
                        </label>";
            }
        }

        echo "</div>
        <div class=\"imageScrollRight\">⮞</div>
    </form>";
    }
    ?>

    <!--sistema pra mostrar os produtos das bandas-->
    <?php
    foreach ($selectedBandas as $bandaId => $bandaName) {
        echo "<div class=\"tagGrade\">

        <h1>" . $bandaName . "</h1>

    </div>

    <form method=\"get\" action=\"paginaProduto.php\" class=\"imageScrollConteiner\">
        <div class=\"imageScrollLeft\">⮜</div>
        <div class=\"gradeContainer\">";

        $stmt = $pdo->prepare("SELECT id, nome, preco_venda FROM produtos WHERE id_banda=? AND quantidade_estoque > 0
        ORDER BY quant_vendas DESC
        LIMIT 20;");
        $stmt->execute([$bandaId]);

        while ($row = $stmt->fetch()) {
            $productId = $row["id"];
            $productName = $row["nome"];

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

            $foto_stmt = $pdo->prepare("SELECT foto FROM produto_fotos WHERE id_produto=?
                ORDER BY id DESC
                LIMIT 1;");
            $foto_stmt->execute([$productId]);
            if ($foto_row = $foto_stmt->fetch()) {
                if (strlen($productName) > 27) {
                    $nomeCortado = substr($productName, 0, 24) . "...";
                } else {
                    $nomeCortado = $productName;
                }

                echo "<input type=\"submit\" name=\"productId\" id=\"" . $productId . "\" value=\"" . $productId . "\" class=\"btnHidden\">
                    
                <label for=\"" . $productId . "\" class=\"produtoGrade\">
                <div class=\"imgGradeContainer\">
                            <img class=\"imgGrade\" src=\"" . $foto_row["foto"] . "\" alt=\"" . $productName . "\">
                            </div>
                            <p class=\"tituloGrade\">" . $nomeCortado . "</p>
                            <div class=\"precoGrade\">
                            <p class=\"r4\"><b>R$</b></p>
                            <p><b>" . $productPrecoVenda . "</b></p>
                            </div>
                        </label>";
            }
        }

        echo "</div>
        <div class=\"imageScrollRight\">⮞</div>
    </form>";
    }
    ?>

    <!--//////////////////  Info Fim de Página  ///////////////////-->

    <div class="infoFimPag">

        <div class="colunaUmEnd">
            <img class="logoEnd" src="log.png" alt="logo fim">

            <p class="listaCatTitulo"><b>CRIADORES</b></p>

            <ul class="listaCategoriasEnd">
                <li>Murilo C. Araujo</li>
                <li>Kauã C. Seabra</li>

            </ul>
        </div>

        <!--lista de categorias clickavel (mesmo sistem da barra de pesquisa) -->
        <div class="colunaDoisEnd">

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

                <li><a href="">Cadastro</a></li>
                <li><a href="">Sobre</a></li>
                <li><a href="">Lojas Parceiras</a></li>

        </div>

        <div class="colunaQuatroEnd">

            <p class="listaCatTitulo"><b>CONTATOS</b></p>

            <ul class="listaCategoriasEnd">

                <li>Vendas: (53) 99152-0052</li>
                <li>Administração: (53) 98453-1421</li>

        </div>

    </div>

    <!--//////////////////  Info Fim de Página  ///////////////////-->
    <script src="scriptThrashTreasuresIndex.js"></script>
</body>

</html>