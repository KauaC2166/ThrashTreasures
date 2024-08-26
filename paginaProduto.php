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

    //verifica se o produto irá ser comrpa ou foi adicionado ao carrinho
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["compraOuCarrinho"])) {
        if (isset($usuarioId)) {
            $compraOuCarrinho = $_POST["compraOuCarrinho"];
            $productId = $_POST["productId"];
            $productPrecoVenda = $_POST["valorUnitario"];
            $productQuantCompra = $_POST["quantCompra"];
            $productName = $_POST["productName"];
            $quantEstoque = $_POST["quantEstoque"];
            $productDescricao = $_POST["productDescricao"];

            //adiciona o produto ao carrinho
            if ($compraOuCarrinho == "carrinho") {
                $sql = "INSERT INTO carrinho (id_cliente, id_produto, quantidade, valor_unitario) VALUES (?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuarioId, $productId, $productQuantCompra, $productPrecoVenda]);

                echo "inserido com sucesso";

                //adiciona o produto ao carrinho e o transfere para pagina de compra
            } else if ($compraOuCarrinho == "compra") {
                $sql = "INSERT INTO carrinho (id_cliente, id_produto, quantidade, valor_unitario) VALUES (?,?,?,?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuarioId, $productId, $productQuantCompra, $productPrecoVenda]);

                $stmt = $pdo->prepare("SELECT * FROM carrinho
            WHERE id_cliente=? AND id_produto=? AND data_adiciona=CURRENT_DATE AND comprado = false
            ORDER BY id DESC
            LIMIT 1;");
                $stmt->execute([$usuarioId, $productId]);

                if ($row = $stmt->fetch()) {
                    $_SESSION["idCarrinhoCompra"] = $row["id"];
                }

                $link = "compraProduto.php";
                header("Location: $link");
                exit();
            }
        } else {
            $link = "thrashTreasuresLogin.php";
            header("Location: $link");
            exit();
        }
    }

    //verifica se o id do produto foi setado
    if (isset($_GET["productId"])) {
        $productId = $_GET["productId"];
    } else if (isset($_SESSION["abreProductCarrinho"])) {
        $productId = $_SESSION["abreProductCarrinho"];

        unset($_SESSION["abreProductCarrinho"]);
    } else if (!isset($productId)) {
        $productId = 0;
    }

    //procura pelo produto
    if (isset($productId)) {

        $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id=?
            LIMIT 1;");
        $stmt->execute([$productId]);

        if ($row = $stmt->fetch()) {
            $productName = $row["nome"];
            $productDescricao = $row["descricao"];
            $productIdBanda = $row["id_banda"];
            $quantEstoque = $row["quantidade_estoque"];
            $bandaId = $row["id_banda"];

            //formata o valor do produto para 'R$ X,XX'
            if (isset($row["preco_venda"])) {
                $productPrecoVenda = $row["preco_venda"];
                if (strpos($productPrecoVenda, '.') !== false) {
                    $productPrecoVendaFormatado = str_replace('.', ',', $productPrecoVenda);
                    $decimal_part = explode(',', $productPrecoVendaFormatado)[1];
                    if (strlen($decimal_part) == 1) {
                        $productPrecoVendaFormatado .= '0';
                    } elseif (strlen($decimal_part) > 2) {
                        $productPrecoVendaFormatado = substr($productPrecoVendaFormatado, 0, strpos($productPrecoVendaFormatado, ',') + 3);
                    }
                } else {
                    $productPrecoVendaFormatado .= ',00';
                }
            } else {
                $productPrecoVendaFormatado = "0,00";
            }

            //seleciona as fotos do produto
            $foto_stmt = $pdo->prepare("SELECT * FROM produto_fotos WHERE id_produto=?");
            $foto_stmt->execute([$productId]);

            while ($foto_row = $foto_stmt->fetch()) {
                $productFotos[] = $foto_row["foto"];
            }

            $banda_stmt = $pdo->prepare("SELECT * FROM bandas WHERE id=?");
            $banda_stmt->execute([$bandaId]);

            if ($banda_row = $banda_stmt->fetch()) {
                $bandaName = $banda_row["nome"];
                $bandaFotoPerfil = $banda_row["foto_perfil"];
                $bandaDescricao = $banda_row["descricao"];
            }
        } else {
            $productName = "";
            $productDescricao = "";
            $productPrecoVenda = 0;
            $productPrecoVendaFormatado = "0,00";
            $productFotos = array();
            $quantEstoque = 0;
        }
    } else if ($_SERVER["REQUEST_METHOD"] != "POST") {
        $productName = "";
        $productDescricao = "";
        $productPrecoVenda = 0;
        $productPrecoVendaFormatado = "0,00";
        $productFotos = array();
        $quantEstoque = 0;
    }


} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8" />
    <title>
        <?php echo $productName; ?> - Thrash Treasures
    </title>
    <link rel="stylesheet" type="text/css" href="frontEnd.css">
    <link rel="icon" type="images/favicon" href="favicon.ico">
    <script src="https://kit.fontawesome.com/a1f82f34b1.js" crossorigin="anonymous"></script>
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
    <!--Menu Superior/-->

    <div id="mainGrid">

        <div class="colunaUm">
            <!--scroll das imagens do produto-->
            <div class="productPageImageScrollConteiner">
                <div class="imageScrollUp" onclick="sobeImagens();">⮝</div>
                <div class="otherImagesContainer">
                    <?php
                    if (!empty($productFotos)) {
                        foreach ($productFotos as $diretorio) {
                            //deixa a primeira imagem pre-selecionada
                            if ($diretorio == $productFotos[0]) {
                                echo " <div class=\"imageCheckboxContainer\">
                                        <img src=\"" . $diretorio . "\" class=\"otherImages otherImageSelected\" onclick=\"mostraImagemSelecionada(this);\" title=\"Expandir a imagem\">
                                    </div>";
                            } else {
                                echo " <div class=\"imageCheckboxContainer\">
                            <img src=\"" . $diretorio . "\" class=\"otherImages\" onclick=\"mostraImagemSelecionada(this);\" title=\"Expandir a imagem\">
                        </div>";
                            }
                        }
                    }
                    ?>
                </div>
                <div class="imageScrollDown" onclick="desceImagens();">⮟</div>
            </div>

            <!--lupa da imagem-->
            <div class="imgMagnifierContainer">
                <img class="produtoFoto" id="imgOutput" src="<?php echo $productFotos[0] ?>" alt="fotoGrande">
            </div>

        </div>

        <div class="colunaDois">

            <div class="produtoTexto">

                <div>
                    <!--nome do produto-->
                    <h1 class="produtoTitulo">
                        <?php echo $productName; ?>
                    </h1>
                </div>

                <div class="tituloPreco">

                    <!--preço do produto-->
                    <h2 class="produtoPreco">R$
                        <?php echo $productPrecoVendaFormatado; ?>
                    </h2>

                    <!--botão pra calcular o frete (funciona)-->
                    <button class="botaoFrete" onclick="abreConsultaCEP();">Calcular Frete</button>
                </div>

            </div>

            <?php
            //caso o produto esteja fora de estoque, o codigo não mostra o formulario de compra
            if ($quantEstoque > 0) {
                ?>
                <form action="paginaProduto.php" method="post">
                    <input type="hidden" name="productId" value="<?php echo $productId; ?>">
                    <input type="hidden" name="valorUnitario" value="<?php echo $productPrecoVenda; ?>">
                    <input type="hidden" name="productName" value="<?php echo $productName; ?>">
                    <input type="hidden" name="quantEstoque" value="<?php echo $quantEstoque; ?>">
                    <input type="hidden" name="productDescricao" value="<?php echo $productDescricao ?>">
                    <input type="hidden" id="maxQuant" value="<?php echo $quantEstoque; ?>">

                    <!--seletor de quantidade (foi um pouco chato de se fazer isso funcionar)-->
                    <div class="quantidade">
                        <div class="quantidadeSeletor">
                            <div onclick="diminuiQuantCompra();">
                                <p>-</p>
                            </div>
                            <input type="number" name="quantCompra" value="1" id="quantCompraInput">
                            <div onclick="aumentaQuantCompra();">
                                <p>+</p>
                            </div>
                        </div>

                        <!--botão para adcionar o produto ao carrinho-->
                        <button type="submit" name="compraOuCarrinho" value="carrinho" class="botaoCompra"><i
                                class="fa-solid fa-cart-shopping fa-xl" style="color: #ffffff;"></i></button>

                        <!--botão para compra o produto-->
                        <button type="submit" name="compraOuCarrinho" value="compra" class="botaoCompra"><i
                                class="fa-solid fa-basket-shopping fa-xl" style="color: #ffffff;"></i></button>

                    </div>
                </form>
                <?php
            } else {
                echo "<h3>Produto fora de estoque</h3>";
            }
            ?>

            <div class="bandaDesc">
                <!--quando o usuario clicka na banda, é pesquisado por produtos desta banda (mesmo sistema da barra de pesquisa)-->
                <form action="paginaPesquisa.php" method="post">
                    <input type="submit" value="<?php echo $bandaId; ?>" name="searchBandaObj" id="hiddenBandBtn">
                    <label class="bandaDescNome" for="hiddenBandBtn">
                        <!--foto da banda-->
                        <img class="fotoBanda" src="<?php echo $bandaFotoPerfil; ?>" alt="band photo">
                        <!--nome da banda-->
                        <h3>
                            <?php echo $bandaName; ?>
                        </h3>
                    </label>
                </form>

                <!--descrição da banda-->
                <div class="bandaSobre">
                    <h3>Sobre</h3>
                    <p>
                        <?php echo $bandaDescricao; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>



    <!--descrição do produto-->
    <div class="produtoDescricao">
        <div>
            <h1 class="descricaoTitulo">DESCRIÇÃO</h1>
        </div>

        <div>
            <p class="descricao">
                <?php echo $productDescricao; ?>
            </p>
        </div>
    </div>




    </div>

    <!--Mais Produtos-->

    <div class="tagGrade">
        <h1>Você Pode Gostar Também De</h1>
    </div>

    <form method="get" action="paginaProduto.php" class="imageScrollConteiner">
        <div class="imageScrollLeft">⮜</div>
        <div class="gradeContainer">

            <!--seleciona produtos da mesma banda-->
            <?php
            $stmt = $pdo->prepare("SELECT id, nome, preco_venda FROM produtos
            WHERE id_banda=? AND NOT id=?
            ORDER BY quant_vendas DESC
            LIMIT 20;");
            $stmt->execute([$productIdBanda, $productId]);

            while ($row = $stmt->fetch()) {
                $productListId = $row["id"];
                $productListName = $row["nome"];
                if (isset($row["preco_venda"])) {
                    $productListPrecoVenda = $row["preco_venda"];
                    $productListPrecoVendaFormatado = $productListPrecoVenda;
                    if (strpos($productListPrecoVendaFormatado, '.') !== false) {
                        $productListPrecoVendaFormatado = str_replace('.', ',', $productListPrecoVendaFormatado);
                        $parts = explode(',', $productListPrecoVendaFormatado);
                        if (isset($parts[1])) {
                            $decimal_partList = $parts[1];
                        }
                        if (strlen($decimal_partList) == 1) {
                            $productListPrecoVendaFormatado .= '0';
                        } elseif (strlen($decimal_partList) > 2) {
                            $productListPrecoVendaFormatado = substr($productListPrecoVendaFormatado, 0, strpos($productListPrecoVendaFormatado, ',') + 3);
                        }
                    } else {
                        $productListPrecoVendaFormatado .= ',00';
                    }
                } else {
                    $productListPrecoVendaFormatado = "0,00";
                }

                $foto_stmt = $pdo->prepare("SELECT foto FROM produto_fotos WHERE id_produto=?
                ORDER BY id DESC
                LIMIT 1;");
                $foto_stmt->execute([$productListId]);
                if ($foto_row = $foto_stmt->fetch()) {
                    if (strlen($productListName) > 27) {
                        $nomeCortado = substr($productListName, 0, 24) . "...";
                    } else {
                        $nomeCortado = $productListName;
                    }

                    echo "<input type=\"submit\" name=\"productId\" id=\"" . $productListId . "\" value=\"" . $productListId . "\" class=\"btnHidden\">
                    
                    <label for=\"" . $productListId . "\" class=\"produtoGrade\">
                    <div class=\"imgGradeContainer\">
                            <img class=\"imgGrade\" src=\"" . $foto_row["foto"] . "\" alt=\"" . $productListName . "\">
                            </div>
                            <p class=\"tituloGrade\">" . $nomeCortado . "</p>
                            <div class=\"precoGrade\">
                            <p class=\"r4\"><b>R$</b></p>
                            <p><b>" . $productListPrecoVendaFormatado . "</b></p>
                            </div>
                        </label>";
                }
            }
            ?>
        </div>
        <div class="imageScrollRight">⮞</div>
    </form>

    <!--Info fim da página -->

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
            <!--lista de categorias clickavel (mesmo sistem da barra de pesquisa) -->
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

    <!--janela que abre para a consulta do frete (realmente funciona)-->
    <div id="calculaFreteContainer">
        <input type="text" id="freteInput" onblur="formataCEP(this); calculaFrete(this);">
        <p id="CEPmsg">Digite um CEP valido</p>
        <div id="calculaFreteBottom">
            <h3>Valor frete:</h3>
            <h3 id="valorFrete"></h3>
        </div>
        <button onclick="fechaConsultaCEP();">Fechar</button>
    </div>
    <script src="scriptThrashTreasuresIndex.js"></script>
</body>

</html>