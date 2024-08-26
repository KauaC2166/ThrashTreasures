<?php
try {
    session_start();
    $pdo = new PDO('pgsql:host=localhost;dbname=thrashTreasures', "postgres", "210606");

    $dataEmissao = date("Y-m-d");
    $dataVencimento = date("Y-m-d", strtotime($dataEmissao . " +3 days"));

    $dataEmissaoFormatado = date("d/m/Y", strtotime($dataEmissao));
    $dataVencimentoFormatado = date("d/m/Y", strtotime($dataVencimento));

    $codigoCima = rand(10000, 99999) . "." . rand(10000, 99999) . " 80000." . rand(100000, 999999) . " " . rand(10000, 99999) . "." . rand(100000, 999999) . " " . rand(1, 9) . " " . rand(1000, 9999) . "00000" . rand(100, 999) . "00";

    $letras = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");

    $numDocumento = $letras[array_rand($letras, 1)] . rand(10000, 99999);

    $nossoNumero = "007/000000" . rand(10000, 99999) . "-" . rand(1, 9);

    if (isset($_SESSION["nomeBoleto"]) && isset($_SESSION["idRuaBoleto"]) && isset($_SESSION["idCidadeBoleto"]) && isset($_SESSION["idEstadoBoleto"]) && isset($_SESSION["numCasaBoleto"]) && isset($_SESSION["valorBoleto"])) {
        $nomeSacado = $_SESSION["nomeBoleto"];
        $numCasaBoleto = $_SESSION["numCasaBoleto"];
        $idRuaBoleto = $_SESSION["idRuaBoleto"];
        $idCidadeBoleto = $_SESSION["idCidadeBoleto"];
        $idEstadoBoleto = $_SESSION["idEstadoBoleto"];
        $valorBoleto = $_SESSION["valorBoleto"];

        if (isset($valorBoleto)) {
            $valorBoletoFormatado = $valorBoleto;
            if (strpos($valorBoletoFormatado, '.') !== false) {
                $valorBoletoFormatado = str_replace('.', ',', $valorBoletoFormatado);
                $decimal_part = explode(',', $valorBoletoFormatado)[1];
                if (strlen($decimal_part) == 1) {
                    $valorBoletoFormatado .= '0';
                } elseif (strlen($decimal_part) > 2) {
                    $valorBoletoFormatado = substr($valorBoletoFormatado, 0, strpos($valorBoletoFormatado, ',') + 3);
                }
            } else {
                $valorBoletoFormatado .= ',00';
            }
        } else {
            $valorBoletoFormatado = "0,00";
        }

        $stmt = $pdo->prepare("SELECT * FROM enderecos WHERE id=?;");
        $stmt->execute([$idRuaBoleto]);

        if ($row = $stmt->fetch()) {
            $nomeRuaBoleto = $row["endereco"];
            $cepBoleto = $row["cep"];
        }

        $stmt = $pdo->prepare("SELECT * FROM cidades WHERE id=?;");
        $stmt->execute([$idCidadeBoleto]);

        if ($row = $stmt->fetch()) {
            $nomeCidadeBoleto = $row["cidade"];
        }

        $stmt = $pdo->prepare("SELECT * FROM estados WHERE id=?;");
        $stmt->execute([$idEstadoBoleto]);

        if ($row = $stmt->fetch()) {
            $nomeEstadoBoleto = $row["estado"];
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
    <link rel="stylesheet" href="styleBoleto.css">
    <title>
        <?php echo "thrashTrasures-boleto-" . $numDocumento; ?>
    </title>
</head>

<body>
    <div id="boleto">
        <div class="borda-cima borda-esquerda borda-direita" id="topRow">
            <div class="borda-direita" id="topRowA">
                <h2>Bradesco</h2>
            </div>
            <div class="borda-direita" id="topRowB">
                <h1>354-8</h1>
            </div>
            <div id="topRowC">
                <h3>
                    <?php echo $codigoCima; ?><b> - Recibo do Sacado</b>
                </h3>
            </div>
        </div>
        <div class="borda-cima borda-esquerda borda-direita" id="middleRow">
            <div class="borda-direita" id="middleRowA">
                <div class="borda-baixo" id="middleRowAA">
                    <p>LOCAL DE PAGAMENTO</p>
                    <p><b>PAGAVEL EM QUALQUER BANCO ATÉ O VENCIMENTO.</b></p>
                </div>
                <div class="borda-baixo" id="middleRowAB">
                    <p>BENEFICIÁRIO</p>
                    <p id="middleRowABB">LOJAS THRASH TREASURES LTDA CNPJ: 45354839000140</p>
                </div>
                <div class="borda-baixo" id="middleRowAC">
                    <div class="borda-direita rowGrid">
                        <p>DATA DE EMISSÃO</p>
                        <p class="middleRowACInfo">
                            <?php echo $dataEmissaoFormatado; ?>
                        </p>
                    </div>
                    <div class="borda-direita rowGrid">
                        <p>N° DO DOCUMENTO</p>
                        <p class="middleRowACInfo">
                            <?php echo $numDocumento; ?>
                        </p>
                    </div>
                    <div class="borda-direita rowGrid">
                        <p>ESPÉCIE DOC.</p>
                        <p class="middleRowACInfo">DM</p>
                    </div>
                    <div class="borda-direita rowGrid">
                        <p>ACEITE</p>
                        <p class="middleRowACInfo">N</p>
                    </div>
                    <div class="rowGrid">
                        <p>DATA PROCESSAMENTO</p>
                        <p class="middleRowACInfo">
                            <?php echo $dataEmissaoFormatado; ?>
                        </p>
                    </div>
                </div>
                <div class="borda-baixo" id="middleRowAD">
                    <div class="borda-direita rowGrid">
                        <p>USO DO BANCO</p>
                        <p class="middleRowACInfo"></p>
                    </div>
                    <div class="borda-direita rowGrid">
                        <p>CARTEIRA</p>
                        <p class="middleRowACInfo">007</p>
                    </div>
                    <div class="borda-direita rowGrid">
                        <p>ESPÉCIE</p>
                        <p class="middleRowACInfo">R$</p>
                    </div>
                    <div class="borda-direita rowGrid">
                        <p>QUANTIDADE DE MOEDA</p>
                        <p class="middleRowACInfo"></p>
                    </div>
                    <div class="rowGrid">
                        <p>VALOR DA MOEDA</p>
                        <p class="middleRowACInfo"></p>
                    </div>
                </div>
                <div>
                    <p>AS INFORMAÇÕES FORNECIDAS NESTE CAMPO SÃO DE EXCLUSIVA RESPONSABILIDADE DA EMPRESA.<br>
                        <br>
                        <br>
                        COBRAR JUROS DE R$ 1,48 POR DIA DE ATRASO.<br>
                        MULTA DE 2,00% APÓS O VENCIMENTO<br>
                        ATENÇÃO!! Protesto automático após 5 dias do vencimento<br>
                        ATENÇÃO!! O pagamento deste titulo através de depósito direto na conta do sacador/avalista NÃO É
                        PERMITIDO<br>
                        <br>
                        <br>
                        >>>>>>>>>>Contatos:<<<<<<<<<< <br>
                            Vendas - (53) 99152-0052<br>
                            Administração - (53) 98453-1421<br>
                            <br>
                            >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>ThrashTreasures.com.br
                            <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<< </p>
                                <p id="mao">\m/</p>
                </div>
            </div>
            <div id="middleRowB">
                <div class="borda-baixo rowGrid">
                    <p>VENCIMENTO</p>
                    <p class="middleRowBInfo">
                        <?php echo $dataVencimentoFormatado; ?>
                    </p>
                </div>
                <div class="borda-baixo rowGrid">
                    <p>AGENCIA N°/COD BENEFICIÁRIO</p>
                    <p class="middleRowBInfo">5401-/05489-4</p>
                </div>
                <div class="borda-baixo rowGrid">
                    <p>NOSSO NUMERO</p>
                    <p class="middleRowBInfo">
                        <?php echo $nossoNumero; ?>
                    </p>
                </div>
                <div class="borda-baixo rowGrid">
                    <p>(=)VALOR DO DOCUMENTO</p>
                    <p class="middleRowBInfo">
                        <?php if (isset($valorBoletoFormatado)) {
                            echo $valorBoletoFormatado;
                        } ?>
                    </p>
                </div>
                <div class="borda-baixo rowGrid">
                    <p>(-)DESCONTO ABATIMENTO</p>
                    <p class="middleRowBInfo"></p>
                </div>
                <div class="borda-baixo rowGrid">
                    <p>(-)OUTRAS DEDUÇÕES</p>
                    <p class="middleRowBInfo"></p>
                </div>
                <div class="borda-baixo rowGrid">
                    <p>(+)MORA/MULTA</p>
                    <p class="middleRowBInfo"></p>
                </div>
                <div class="borda-baixo rowGrid">
                    <p>(+)OUTROS ACRESCIMOS</p>
                    <p class="middleRowBInfo"></p>
                </div>
                <div class="rowGrid">
                    <p>(=)VALOR COBRADO</p>
                    <p class="middleRowBInfo"></p>
                </div>
            </div>
        </div>
        <div class="bordas">
            <p>Sacado:</p>
            <p class="sacadoTxt">
                <?php if (isset($nomeSacado)) {
                    echo $nomeSacado;
                } ?>
            </p>
            <p class="sacadoTxt">
                <?php if (isset($nomeRuaBoleto) && isset($numCasaBoleto)) {
                    echo $nomeRuaBoleto . ", " . $numCasaBoleto;
                } ?>
            </p>
            <p class="sacadoTxt">
                <?php if (isset($nomeCidadeBoleto) && isset($nomeEstadoBoleto) && isset($cepBoleto)) {
                    echo $nomeCidadeBoleto . " - " . $nomeEstadoBoleto . " - " . $cepBoleto;
                } ?>
            </p>
            <p>Sacador/Avalista:</p>
        </div>
        <div id="codigoBarraContainer">
            <img src="codigoBarras.png" alt="" id="codigoBarras">
            <div>
                <h3>Ficha de<br>compensação</h3>
                <p>Autenticação Mecânica</p>
            </div>
        </div>
    </div>


    <script>
        window.onload = function () {
            window.print();
        };

        window.onafterprint = function () {
            window.location.href = "compraProdutoMsg.html";
        };
    </script>
</body>

</html>