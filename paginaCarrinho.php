<?php
try {
    //inicia a sessão
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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //caso o usuario tenha clickado no produto, ele é enviado para a pagina do respectivio produto
        if (isset($_POST["abreProductCarrinho"])) {
            $_SESSION["abreProductCarrinho"] = $_POST["abreProductCarrinho"];

            $link = "paginaProduto.php";
            header("Location: $link");
            exit();
            //caso o usuario tenha clickado em comprar o produto, ele será enviado para a pagina de compra
        } else if (isset($_POST["compraProductCarrinho"])) {
            $_SESSION["idCarrinhoCompra"] = $_POST["compraProductCarrinho"];

            $link = "compraProduto.php";
            header("Location: $link");
            exit();
            //caso o usuario tenha clickado em comprar todos os produtos do carrinho, ele será enviado para a pagina de compra
        } else if (isset($_POST["compraTodoCarrinho"])) {
            $_SESSION["idClienteCarrinho"] = $usuarioId;

            $link = "compraProduto.php";
            header("Location: $link");
            exit();
            //caso o usuario tenha clickado em apagar produto, o produto será exclluido do carrinho
        } else if (isset($_POST["apagaProductCarrinho"])) {
            $carrinhoApagadoId = intval($_POST["apagaProductCarrinho"]);
            $stmt = $pdo->prepare("DELETE FROM carrinho WHERE id=?;");
            $stmt->execute([$carrinhoApagadoId]);
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
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="images/favicon" href="favicon.ico">
    <title>Thrash Treasures - Carrinho</title>
    <link rel="stylesheet" href="styleCompra.css">
    <script src="https://kit.fontawesome.com/a1f82f34b1.js" crossorigin="anonymous"></script>
</head>

<body>
    <!--///////////////////////////////////////   Menu Superior  //////////////////////////////////-->
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
    <!--   ////////////////////////////////   Menu Superior   ////////////////////////////////-->
    <form action="paginaCarrinho.php" method="post" id="carrinhoContent">
        <ul>
            <?php
            //seleciona os produtos que estão no carrinho do cliente
            $valorTotal = 0;
            $stmt = $pdo->prepare("SELECT produtos.id AS id_produto, produtos.nome AS nome_produto, produtos.quantidade_estoque AS quant_estoque, carrinho.id AS carrinho_id, carrinho.quantidade AS quant, carrinho.valor_unitario AS valor_unitario, carrinho.data_adiciona AS data_adicionado FROM carrinho
            INNER JOIN produtos ON produtos.id = carrinho.id_produto
            WHERE comprado=false AND carrinho.id_cliente=?
            ORDER BY carrinho.data_adiciona DESC;");
            $stmt->execute([$usuarioId]);

            while ($row = $stmt->fetch()) {
                $carrinhoId = $row["carrinho_id"];
                $productId = $row["id_produto"];
                $productName = $row["nome_produto"];
                $productQuantCompra = $row["quant"];
                $productDataAdic = $row["data_adicionado"];
                $quantEstoque = $row["quant_estoque"];

                //formata o valor do produto para "R$ X,XX"
                if (isset($row["valor_unitario"])) {
                    $productValorUnitario = $row["valor_unitario"];
                    $productValorUnitarioFormatado = $row["valor_unitario"];
                    if (strpos($productValorUnitarioFormatado, '.') !== false) {
                        $productValorUnitarioFormatado = str_replace('.', ',', $productValorUnitarioFormatado);
                        $decimal_part = explode(',', $productValorUnitarioFormatado)[1];
                        if (strlen($decimal_part) == 1) {
                            $productValorUnitarioFormatado .= '0';
                        } elseif (strlen($decimal_part) > 2) {
                            $productValorUnitarioFormatado = substr($productValorUnitarioFormatado, 0, strpos($productValorUnitarioFormatado, ',') + 3);
                        }
                    } else {
                        $productValorUnitarioFormatado .= ',00';
                    }
                } else {
                    $productValorUnitarioFormatado = "0,00";
                }

                //soma o valor total de produtos
                $valorTotal += $productValorUnitario * $productQuantCompra;

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

                    echo " <li class=\"carrinhoListObjContainer\"><button type=\"submit\" name=\"abreProductCarrinho\" value=\"" . $productId . "\" id=\"" . $productId . "\" class=\"hiddenBtn\"></button><label for=\"" . $productId . "\" class=\"carrinhoListObj\"><div class=\"carrinhoImgContainer\"><img src=\"" . $foto_row["foto"] . "\" alt=\"" . $productName . "\"></div><div class=\"carrinhoTextConteiner\"><h3>" . $productName . "</h3><div class=\"carrinhoValorContainer\"><h4>R$" . $productValorUnitarioFormatado . "</h4><p>x" . $productQuantCompra . "</p></div></div><button type=\"submit\" name=\"compraProductCarrinho\" class=\"botaoCarrinho\" value=\"" . $carrinhoId . "\"";
                    if ($quantEstoque > 0) {
                        echo ">Comprar";
                    } else {
                        echo "disabled>Fora de Estoque";
                    }
                    echo "</button><button type=\"submit\" name=\"apagaProductCarrinho\" value=\"" . $carrinhoId . "\" class=\"apagaCarrinhoBtn\">Retirar do carrinho</button></label></li>";
                }
            }
            ?>
        </ul>

        <!--parte de baixo da pagina-->
        <div id="carrinhoBottom">
            <div id="compraBottomTxt">
                <div>
                    <h4>Valor da Compra:</h4>
                    <h4>Valor Frete:</h4>
                    <h2>Valor Total:</h2>
                </div>
                <div id="compraBottomTxtRight">
                    <h4 id="valorTotalProdutos">R$
                        <?php
                        //formata o valor total para "R$ X,XX"
                        if (isset($valorTotal)) {
                            $valorTotalFormatado = $valorTotal;
                            if (strpos($valorTotalFormatado, '.') !== false) {
                                $valorTotalFormatado = str_replace('.', ',', $valorTotalFormatado);
                                $decimal_part = explode(',', $valorTotalFormatado)[1];
                                if (strlen($decimal_part) == 1) {
                                    $valorTotalFormatado .= '0';
                                } elseif (strlen($decimal_part) > 2) {
                                    $valorTotalFormatado = substr($valorTotalFormatado, 0, strpos($valorTotalFormatado, ',') + 3);
                                }
                            } else {
                                $valorTotalFormatado .= ',00';
                            }
                        } else {
                            $valorTotalFormatado = "0,00";
                        }
                        echo $valorTotalFormatado;
                        ?>
                    </h4>
                    <h4 id="valorFrete"></h4>
                    <h2 id="valorTotalCompra"></h2>
                </div>
            </div>
            <div id="bottomBtnContainer">
                <!--botão de compra-->
                <input type="submit" value="Comprar" name="compraTodoCarrinho" class="botaoCarrinho">
                <!--botão de calculo de frete-->
                <button type="button" class="botaoCarrinho" onclick="abreConsultaCEP();">Calcular Frete</button>
            </div>
        </div>
    </form>

    <!--janela que abre para a consulta do frete (realmente funciona)-->
    <div id="calculaFreteContainer">
        <input type="text" id="freteInput" onblur="formataCEP(this); calculaFrete(this); somaTotalCompra();">
        <p id="CEPmsg">Digite um CEP valido</p>
        <div id="calculaFreteBottom">
            <h3>Valor frete:</h3>
            <h3 id="valorFreteCarrinho"></h3>
        </div>
        <button onclick="fechaConsultaCEP();">Fechar</button>
    </div>
    <script src="scriptThrashTreasuresIndex.js">
    </script>
</body>

</html>