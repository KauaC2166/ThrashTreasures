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

    //informações do grafico do lucro da semana
    $xGrafVendasValues = array();
    $yGrafVendasValues = array();
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));

        $stmt = $pdo->prepare("SELECT SUM((carrinho.valor_unitario * carrinho.quantidade) - (produtos.preco_custo * carrinho.quantidade)) AS lucro
        FROM carrinho
        LEFT JOIN produtos ON carrinho.id_produto = produtos.id
        WHERE DATE(carrinho.data_compra)=?;");
        $stmt->execute([$date]);

        if ($row = $stmt->fetch()) {
            $xGrafVendasValues[] = $date;
            if (isset($row["lucro"]) && $row["lucro"] > 0 && !empty($row["lucro"])) {
                $yGrafVendasValues[] = $row["lucro"];
            } else {
                $yGrafVendasValues[] = 0;
            }
        }

    }

    $xGrafVendasValuesJson = json_encode($xGrafVendasValues);
    $yGrafVendasValuesJson = json_encode($yGrafVendasValues);

    //informações do grafico de bandas mais vendidas
    $stmt = $pdo->query("SELECT bandas.nome AS banda, SUM(produtos.quant_vendas) AS quant_vendas FROM bandas
    LEFT JOIN produtos ON bandas.id = produtos.id_banda
    GROUP BY bandas.id
    ORDER BY SUM(produtos.quant_vendas) ASC
    LIMIT 5;");

    $xGrafBandasValues = array();
    $yGrafBandasValues = array();

    while ($row = $stmt->fetch()) {
        $xGrafBandasValues[] = $row["banda"];
        $yGrafBandasValues[] = $row["quant_vendas"];
    }
    $xGrafBandasValuesJson = json_encode($xGrafBandasValues);
    $yGrafBandasValuesJson = json_encode($yGrafBandasValues);

    //informações do grafico de categorias mais vendidas
    $stmt = $pdo->query("SELECT categorias.nome AS categoria, SUM(produtos.quant_vendas) AS quant_vendas FROM categorias
    LEFT JOIN produtos ON categorias.id = produtos.id_categoria
    GROUP BY categorias.id
    ORDER BY SUM(produtos.quant_vendas) ASC
    LIMIT 5;");

    $xGrafCategoriasValues = array();
    $yGrafCategoriasValues = array();

    while ($row = $stmt->fetch()) {
        $xGrafCategoriasValues[] = $row['categoria'];
        $yGrafCategoriasValues[] = $row['quant_vendas'];
    }

    $xGrafCategoriasValuesJson = json_encode($xGrafCategoriasValues);
    $yGrafCategoriasValuesJson = json_encode($yGrafCategoriasValues);

    //informações do grafico de generos mais vendidos
    $stmt = $pdo->query("SELECT genero.nome AS genero, SUM(produtos.quant_vendas) AS quant_vendas FROM genero
LEFT JOIN bandas ON genero.id = bandas.genero_id
LEFT JOIN produtos ON bandas.id = produtos.id_banda
GROUP BY genero.id
ORDER BY SUM(produtos.quant_vendas) DESC
LIMIT 5;");

    $xGrafGenerosValues = array();
    $yGrafGenerosValues = array();

    while ($row = $stmt->fetch()) {
        $xGrafGenerosValues[] = $row['genero'];
        $yGrafGenerosValues[] = $row['quant_vendas'];
    }

    $xGrafGenerosValuesJson = json_encode($xGrafGenerosValues);
    $yGrafGenerosValuesJson = json_encode($yGrafGenerosValues);

} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "<br/>";
    die();
} finally {
    $dbh = null;
}
?>

<!--pagina de dados gerais da Thrash Treasures-->
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
    <title>Thrash Treasures - Dados Gerais</title>
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
        <!--caso o adm não tenha serta autorização, o botão fica bloqeuado para ele-->
        <div class="barraCimaBtnContainer">
            <div class="espacoBtns"></div>
            <p class="barraCimaSelectedBtn">Dados Gerais</p>
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

    <div class="contentConteiner" id="dadosGerais">
        <div id="topContentDg">
            <h3>Lucro dos ultimos 7 dias</h3>
            <h3>Ultimas Vendas</h3>

            <!--grafico do lucro da semana-->
            <canvas class="grafico" id="graficoVendas"></canvas>

            <!--lista das ultimas 50 vendas-->
            <div class="selectList" id="ultimasVendasList">
                <div class="selectListTop selectListUltimasVendas">
                    <p class="selectListColumnName"><b>Produto</b></p>
                    <p class="selectListColumnName"><b>Cliente</b></p>
                    <p class="selectListColumnName"><b>Quantidade</b></p>
                    <p class="selectListColumnName"><b>Valor unitario</b></p>
                    <p class="selectListColumnName"><b>Valor total</b></p>
                    <p class="selectListColumnName"><b>Data da compra</b></p>
                </div>
                <?php
                $stmt = $pdo->query("SELECT clientes.nome AS cliente, produtos.nome AS produto, carrinho.data_compra AS data_compra,
                carrinho.valor_unitario AS valor_unitario, carrinho.quantidade AS quantidade,
                (carrinho.valor_unitario + carrinho.quantidade) AS valor_total FROM carrinho
                INNER JOIN clientes ON clientes.id = carrinho.id_cliente
                INNER JOIN produtos ON produtos.id = carrinho.id_produto
                WHERE comprado=true
                ORDER BY carrinho.data_compra DESC
                LIMIT 50;");

                while ($row = $stmt->fetch()) {
                    if (strlen($row["produto"]) > 27) {
                        $produtoCortado = substr($row["produto"], 0, 24) . "...";
                    } else {
                        $produtoCortado = $row["produto"];
                    }

                    if (strlen($row["cliente"]) > 23) {
                        $clienteCortado = substr($row["cliente"], 0, 20) . "...";
                    } else {
                        $clienteCortado = $row["cliente"];
                    }

                    $productValorUnitario = $row["valor_unitario"];
                    if (strpos($productValorUnitario, '.') !== false) {
                        $productValorUnitario = str_replace('.', ',', $productValorUnitario);
                        $decimal_part = explode(',', $productValorUnitario)[1];
                        if (strlen($decimal_part) == 1) {
                            $productValorUnitario .= '0';
                        } elseif (strlen($decimal_part) > 2) {
                            $productValorUnitario = substr($productValorUnitario, 0, strpos($productValorUnitario, ',') + 3);
                        }
                    } else {
                        $productValorUnitario .= ',00';
                    }

                    $productValorTotal = $row["valor_unitario"];
                    if (strpos($productValorTotal, '.') !== false) {
                        $productValorTotal = str_replace('.', ',', $productValorTotal);
                        $decimal_part = explode(',', $productValorTotal)[1];
                        if (strlen($decimal_part) == 1) {
                            $productValorTotal .= '0';
                        } elseif (strlen($decimal_part) > 2) {
                            $productValorTotal = substr($productValorTotal, 0, strpos($productValorTotal, ',') + 3);
                        }
                    } else {
                        $productValorTotal .= ',00';
                    }

                    echo "
                    <div class=\"selectListRow selectListUltimasVendas\">
                        <p class=\"selectListItem\">" . $produtoCortado . "</p>
                        <p class=\"selectListItem\">" . $row["cliente"] . "</p>
                        <p class=\"selectListItem\">" . $row["quantidade"] . "</p>
                        <p class=\"selectListItem\">R$ " . $productValorUnitario . "</p>
                        <p class=\"selectListItem\">R$ " . $productValorTotal . "</p>
                        <p class=\"selectListItem\">" . $row["data_compra"] . "</p>
                    </div>";
                }
                ?>
            </div>
        </div>

        <div id="bottomContentDg">
            <h4>Categorias mais vendidas</h4>
            <h4>Bandas mais vendidas</h4>
            <h4>Gêneros mais vendidos</h4>

            <!--grafico das categorias mais vendidas-->
            <canvas class="grafico" id="graficoCategorias"></canvas>
            <!--grafico das bandas mais vendidas-->
            <canvas class="grafico" id="graficoBandas"></canvas>
            <!--grafico dos generos mais vendidos-->
            <canvas class="grafico" id="graficoGeneros"></canvas>

        </div>
    </div>

    <script>
        //grafico do lucro da semana da pagina Dados Gerais
        const xGrafVendasValues = <?php echo $xGrafVendasValuesJson; ?>;
        const yGrafVendasValues = <?php echo $yGrafVendasValuesJson; ?>;

        const grafVendasBarColors = ["grey", "grey", "grey", "grey", "grey", "grey", "#b40000"];

        new Chart("graficoVendas", {
            type: "bar",
            data: {
                labels: xGrafVendasValues,
                datasets: [{
                    backgroundColor: grafVendasBarColors,
                    data: yGrafVendasValues
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        ticks: {
                            fontColor: '#c5c5c5',
                            fontSize: 30,
                            fontStyle: 'bold',
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#c5c5c5',
                            fontSize: 30,
                            fontStyle: 'bold',
                        }
                    }]
                },
                tooltips: {
                    enabled: true,
                    titleFontSize: 27,
                    bodyFontSize: 25
                },
                legend: {
                    display: false
                }
            }
        });

        const bottomGrafBarColors = ["grey", "grey", "grey", "grey", "grey"];

        //grafico das bandas mais vendidas da pagina Dados Gerais
        const xGrafBandasValues = <?php echo $xGrafBandasValuesJson; ?>;
        const yGrafBandasValues = <?php echo $yGrafBandasValuesJson; ?>;

        new Chart("graficoBandas", {
            type: "bar",
            data: {
                labels: xGrafBandasValues,
                datasets: [{
                    backgroundColor: bottomGrafBarColors,
                    data: yGrafBandasValues
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        ticks: {
                            fontColor: '#c5c5c5',
                            fontSize: 35,
                            fontStyle: 'bold',
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#c5c5c5',
                            fontSize: 35,
                            fontStyle: 'bold',
                        }
                    }]
                },
                tooltips: {
                    enabled: true,
                    titleFontSize: 37,
                    bodyFontSize: 35
                },
                legend: {
                    display: false
                }
            }
        });

        //grafico dos generos mais vendidos da pagina Dados Gerais
        const xGrafGenerosValues = <?php echo $xGrafGenerosValuesJson; ?>;
        const yGrafGenerosValues = <?php echo $yGrafGenerosValuesJson; ?>;

        new Chart("graficoGeneros", {
            type: "bar",
            data: {
                labels: xGrafGenerosValues,
                datasets: [{
                    backgroundColor: bottomGrafBarColors,
                    data: yGrafGenerosValues
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        ticks: {
                            fontColor: '#c5c5c5',
                            fontSize: 35,
                            fontStyle: 'bold',
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#c5c5c5',
                            fontSize: 35,
                            fontStyle: 'bold',
                        }
                    }]
                },
                tooltips: {
                    enabled: true,
                    titleFontSize: 37,
                    bodyFontSize: 35
                },
                legend: {
                    display: false
                }
            }
        });

        //grafico das categorias mais vendidas da pagina Dados Gerais
        const xGrafCategoriasValues = <?php echo $xGrafCategoriasValuesJson; ?>;
        const yGrafCategoriasValues = <?php echo $yGrafCategoriasValuesJson; ?>;

        new Chart("graficoCategorias", {
            type: "bar",
            data: {
                labels: xGrafCategoriasValues,
                datasets: [{
                    backgroundColor: bottomGrafBarColors,
                    data: yGrafCategoriasValues
                }]
            },
            options: {
                scales: {
                    xAxes: [{
                        ticks: {
                            fontColor: '#c5c5c5',
                            fontSize: 35,
                            fontStyle: 'bold',
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            fontColor: '#c5c5c5',
                            fontSize: 35,
                            fontStyle: 'bold',
                        }
                    }]
                },
                tooltips: {
                    enabled: true,
                    titleFontSize: 37,
                    bodyFontSize: 35
                },
                legend: {
                    display: false
                }
            }
        });
    </script>
    <script src="scriptThrashTreasuresWorkspace.js"></script>
</body>

</html>