//funções para mostrar e esconder a barra de pesquisa
const lista = document.getElementById("barraPesquisaList");
function mostraSearchList() {
    lista.style.display = "block";
}
function escondeSearchList() {
    lista.style.display = "none";
}

document.addEventListener('click', function (event) {
    var searchBarForm = document.getElementById('searchBarForm');
    var targetElement = event.target;

    if (targetElement !== searchBarForm && !searchBarForm.contains(targetElement) && targetElement !== lista && !lista.contains(targetElement)) {
        escondeSearchList();
    }
});

function searchBar() {
    var input, filter, ul, li, input, i, txtValue;
    input = document.getElementById('barraDePesquisa');
    filter = input.value.toUpperCase();
    ul = document.getElementById("barraPesquisaList");
    li = ul.getElementsByTagName('li');

    for (i = 0; i < li.length; i++) {
        input = li[i].getElementsByTagName("button")[0];
        txtValue = input.innerHTML;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}

//função para o scroll dos pordutos na pagina inical
let scrollInterval;

function startScroll(direction) {
    const scrollContent = document.querySelector('.gradeContainer');
    const scrollAmount = 5;

    scrollInterval = setInterval(() => {
        if (direction === 'left') {
            scrollContent.scrollLeft -= scrollAmount;
        } else if (direction === 'right') {
            scrollContent.scrollLeft += scrollAmount;
        }
    }, 10);
}

function stopScroll() {
    clearInterval(scrollInterval);
}

const leftButton = document.querySelector('.imageScrollLeft');
const rightButton = document.querySelector('.imageScrollRight');

leftButton.addEventListener('mousedown', () => startScroll('left'));
leftButton.addEventListener('mouseup', stopScroll);

rightButton.addEventListener('mousedown', () => startScroll('right'));
rightButton.addEventListener('mouseup', stopScroll);

//funções para o scroll de imagens da pagina de produto
function sobeImagens() {
    const scrollContent = document.querySelector('.otherImagesContainer');
    scrollContent.scrollBy(0, -60);
}

function desceImagens() {
    const scrollContent = document.querySelector('.otherImagesContainer');
    scrollContent.scrollBy(0, 60);
}

//função para mstrar a imagem selecionada na pagina de produtos
function mostraImagemSelecionada(imagem) {
    const imageOutputContainer = document.querySelector(".produtoFoto");

    imageOutputContainer.src = imagem.src;

    var elementos = document.querySelectorAll('.otherImageSelected');


    elementos.forEach(function (elemento) {
        elemento.classList.remove("otherImageSelected");
    });

    imagem.classList.add("otherImageSelected");

    if (window.location.pathname.includes('paginaProduto.php')) {
        magnify("imgOutput", 3);
    }
}

//função para a lente de zoom da pagina de produtos (w3school)
function magnify(imgID, zoom) {
    var img, glass, w, h, bw;
    img = document.getElementById(imgID);

    existingGlasses = document.querySelectorAll(".imgMagnifierGlass");

    existingGlasses.forEach(function (existingGlass) {
        existingGlass.remove();
    });

    glass = document.createElement("DIV");
    glass.setAttribute("class", "imgMagnifierGlass");

    img.parentElement.insertBefore(glass, img);

    glass.style.backgroundImage = "url('" + img.src + "')";
    glass.style.backgroundRepeat = "no-repeat";
    glass.style.backgroundSize = (img.width * zoom) + "px " + (img.height * zoom) + "px";
    bw = 3;
    w = glass.offsetWidth / 2;
    h = glass.offsetHeight / 2;

    glass.addEventListener("mousemove", moveMagnifier);
    img.addEventListener("mousemove", moveMagnifier);

    glass.addEventListener("touchmove", moveMagnifier);
    img.addEventListener("touchmove", moveMagnifier);
    function moveMagnifier(e) {
        var pos, x, y;
        e.preventDefault();
        pos = getCursorPos(e);
        x = pos.x;
        y = pos.y;
        if (x > img.width - (w / zoom)) { x = img.width - (w / zoom); }
        if (x < w / zoom) { x = w / zoom; }
        if (y > img.height - (h / zoom)) { y = img.height - (h / zoom); }
        if (y < h / zoom) { y = h / zoom; }
        glass.style.left = (x - w) + "px";
        glass.style.top = (y - h) + "px";
        glass.style.backgroundPosition = "-" + ((x * zoom) - w + bw) + "px -" + ((y * zoom) - h + bw) + "px";
    }

    function getCursorPos(e) {
        var a, x = 0, y = 0;
        e = e || window.event;
        a = img.getBoundingClientRect();
        x = e.pageX - a.left;
        y = e.pageY - a.top;
        x = x - window.scrollX;
        y = y - window.scrollY;
        return { x: x, y: y };
    }
}

window.onload = function () {
    magnify("imgOutput", 3);
};

//funçaõ para formatar o CEP
function formataCEP(input) {
    CEP = input.value;

    if (CEP != "") {
        if (CEP[5] != "-") {
            inicioCEP = CEP.slice(0, 5);
            fimCEP = CEP.slice(5, 8);

            CEP = inicioCEP + "-" + fimCEP;
        }
    }

    input.value = CEP;
}

//função para formatar o CPF
function formataCPF(input) {
    var CPF = input.value;

    if (CPF != "") {
        CPF = CPF.replace(/\D/g, '');

        var subCPF1 = CPF.slice(0, 3);
        var subCPF2 = CPF.slice(3, 6);
        var subCPF3 = CPF.slice(6, 9);
        var subCPF4 = CPF.slice(9, 11);

        CPF = subCPF1 + "." + subCPF2 + "." + subCPF3 + "-" + subCPF4;

        input.value = CPF;
    }
}

//função para selecionar a forma de pagamento
function mostraFormaPagamento(radio) {
    const changebleRequirement = document.querySelectorAll(".changebleRequirement");

    if (radio.id == "radioCartao") {
        document.getElementById("pagamentoCartao").style.display = "block";
        document.getElementById("pagamentoPix").style.display = "none";
        document.getElementById("pagamentoBoleto").style.display = "none";

        changebleRequirement.forEach(function (input) {
            input.required = true;
        });
    } else if (radio.id == "radioPix") {
        document.getElementById("pagamentoPix").style.display = "block";
        document.getElementById("pagamentoCartao").style.display = "none";
        document.getElementById("pagamentoBoleto").style.display = "none";

        changebleRequirement.forEach(function (input) {
            input.required = false;
        });
    } else if (radio.id == "radioBoleto") {
        document.getElementById("pagamentoPix").style.display = "none";
        document.getElementById("pagamentoCartao").style.display = "none";
        document.getElementById("pagamentoBoleto").style.display = "block";

        changebleRequirement.forEach(function (input) {
            input.required = false;
        });
    } else {
        document.getElementById("pagamentoPix").style.display = "none";
        document.getElementById("pagamentoCartao").style.display = "none";
        document.getElementById("pagamentoBoleto").style.display = "none";

        changebleRequirement.forEach(function (input) {
            input.required = false;
        });
    }
}

//função para formatar o numero do cartão
function formataNumCartao(input) {
    var numCartao = input.value;

    numCartao = numCartao.replace(/\D/g, '');

    var subNum1 = numCartao.slice(0, 4);
    var subNum2 = numCartao.slice(4, 8);
    var subNum3 = numCartao.slice(8, 12);
    var subNum4 = numCartao.slice(12, 16);

    numCartao = subNum1 + " " + subNum2 + " " + subNum3 + " " + subNum4;

    input.value = numCartao;
}

//funções para o seletor de quantidade do produto
function aumentaQuantCompra() {
    inputQuant = document.getElementById("quantCompraInput");
    maxQuant = parseInt(document.getElementById("maxQuant").value);
    valor = parseInt(inputQuant.value);
    if (valor < maxQuant) {
        valor++;
    }
    inputQuant.value = valor;
}

function diminuiQuantCompra() {
    inputQuant = document.getElementById("quantCompraInput");
    valor = parseInt(inputQuant.value);
    if (valor > 1) {
        valor--;
    }
    inputQuant.value = valor;
}

//função pra calcular o frete
function calculaFrete(input) {
    CEP = input.value;
    valorFrete = document.getElementById("valorFrete");
    valorFreteCarrinho = document.getElementById("valorFreteCarrinho");
    CEPmsg = document.getElementById("CEPmsg");

    CEP = CEP.replace(/\D/g, '');

    inicioCEP = parseInt(CEP.slice(0, 5));

    if (inicioCEP != "") {
        if (inicioCEP >= 1000 && inicioCEP <= 7999) {
            valorFrete.innerHTML = "R$ 6,60";
            valorFreteCarrinho.innerHTML = "R$ 6,60";
        } else if (inicioCEP >= 8000 && inicioCEP <= 9999) {
            valorFrete.innerHTML = "R$ 6,60";
            valorFreteCarrinho.innerHTML = "R$ 6,60";
        } else if (inicioCEP >= 11000 && inicioCEP <= 39999) {
            valorFrete.innerHTML = "R$ 6,60";
            valorFreteCarrinho.innerHTML = "R$ 6,60";
        } else if (inicioCEP >= 40000 && inicioCEP <= 65999) {
            valorFrete.innerHTML = "R$ 19,80";
            valorFreteCarrinho.innerHTML = "R$ 19,80";
        } else if (inicioCEP >= 66000 && inicioCEP <= 69999) {
            valorFrete.innerHTML = "R$ 26,40";
            valorFreteCarrinho.innerHTML = "R$ 26,40";
        } else if (inicioCEP >= 76800 && inicioCEP <= 77999) {
            valorFrete.innerHTML = "R$ 26,40";
            valorFreteCarrinho.innerHTML = "R$ 26,40";
        } else if (inicioCEP >= 70000 && inicioCEP <= 76799) {
            valorFrete.innerHTML = "R$ 13,20";
            valorFreteCarrinho.innerHTML = "R$ 13,20";
        } else if (inicioCEP >= 78000 && inicioCEP <= 79999) {
            valorFrete.innerHTML = "R$ 13,20";
            valorFreteCarrinho.innerHTML = "R$ 13,20";
        } else if (inicioCEP >= 80000 && inicioCEP <= 99999) {
            valorFrete.innerHTML = "Grátis";
            valorFreteCarrinho.innerHTML = "Grátis";
        } else {
            CEPmsg.innerHTML = "Digite um CEP valido";
        }
    } else {
        CEPmsg.innerHTML = "Digite um CEP valido";
    }
}

//funções pra abrir e fechar a janela de consulta do CEP
function fechaConsultaCEP() {
    document.getElementById("calculaFreteContainer").style.display = "none";
}

function abreConsultaCEP() {
    document.getElementById("calculaFreteContainer").style.display = "block";
}

//função para formatar o nome do proprietario do cartão
function formaNomeCartao(input) {
    nome = input.value;

    input.value = nome.toUpperCase();
}

//função para somar o total da compra com o frete
function somaTotalCompra() {
    var valorFrete = document.getElementById("valorFrete").textContent.trim();
    var valorTotalProdutos = document.getElementById("valorTotalProdutos").textContent.trim();

    var valorFreteNumerico;
    var valorTotalProdutosNumerico;

    if (valorFrete !== "Grátis") {
        valorFreteNumerico = parseFloat(valorFrete.replace("R$", "").replace(",", "."));
    } else {
        valorFreteNumerico = 0.0;
    }

    valorTotalProdutosNumerico = parseFloat(valorTotalProdutos.replace("R$", "").replace(",", "."));

    var totalFormatado;

    if (!isNaN(valorFreteNumerico) && !isNaN(valorTotalProdutosNumerico)) {
        var totalCompra = valorFreteNumerico + valorTotalProdutosNumerico;

        totalCompra = totalCompra.toFixed(2);

        totalFormatado = "R$ " + totalCompra.replace(".", ",");
    } else if (isNaN(valorFreteNumerico)) {
        totalFormatado = valorTotalProdutos;
    } else if (isNaN(valorTotalProdutosNumerico) && valorFrete !== "Grátis") {
        totalFormatado = valorFrete;
    } else {
        totalFormatado = "R$ 0,00";
    }

    document.getElementById("valorTotalCompra").textContent = totalFormatado;
}

//função para trocar os slides da pagina inicial (w3school)
let slideIndex = 0;
showSlides();

function showSlides() {
    let i;
    let slides = document.getElementsByClassName("slides");
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    slideIndex++;
    if (slideIndex > slides.length) { slideIndex = 1 }
    slides[slideIndex - 1].style.display = "block";
    setTimeout(showSlides, 2000);
}