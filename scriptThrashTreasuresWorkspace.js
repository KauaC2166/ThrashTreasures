barraCimaSelectedBtn = document.querySelector(".barraCimaSelectedBtn");
barraCimaBtns = document.querySelectorAll(".barraCimaBtn");
barraCimaEspacos = document.querySelectorAll(".espacoBtns");

barraCimaSelectedBtn.addEventListener("mouseover", function () {

    for (let i = 0; i < barraCimaBtns.length; i++) {
        barraCimaBtns[i].style.borderBottomColor = "#ffe600";
        barraCimaEspacos[i].style.borderBottomColor = "#ffe600";
    }
});

barraCimaSelectedBtn.addEventListener("mouseout", function () {
    for (let i = 0; i < barraCimaBtns.length; i++) {
        barraCimaBtns[i].style.borderBottomColor = "#ECD504";
        barraCimaEspacos[i].style.borderBottomColor = "#ECD504";
    }
});

function mostraSenha(btn) {

    inputSenha = btn.previousElementSibling;

    if (inputSenha.type == 'password') {
        inputSenha.type = 'text';
    } else {
        inputSenha.type = 'password';
    }
}

function inputFuncMostraInsertSenha() {
    funcUpdateRadio = document.getElementById("funcUpdateRadio");
    funcSenhaContainer = document.getElementById("funcSenhaContainer");

    if (!funcUpdateRadio.checked) {
        funcSenhaContainer.style.display = "block";
    } else {
        funcSenhaContainer.style.display = "none";
    }
}

document.addEventListener("DOMContentLoaded", function () {
    // Chama a função após a página ser completamente carregada
    inputFuncMostraInsertSenha();

    // Adiciona um ouvinte de evento para o elemento funcUpdateRadio
    funcUpdateRadio = document.getElementById("funcUpdateRadio");
    funcUpdateRadio.addEventListener("change", inputFuncMostraInsertSenha);
    funcUpdateRadio.addEventListener("change", mudaRequired(funcUpdateRadio));
});

// Função para obter e exibir a posição do scroll
function obterPosicaoScroll() {
    var posicaoX = window.scrollX || window.pageXOffset;
    var posicaoY = window.scrollY || window.pageYOffset;

    console.log("Posição do Scroll - Horizontal: " + posicaoX + ", Vertical: " + posicaoY);
}

// Adicione um ouvinte de evento para chamar a função quando houver rolagem
window.addEventListener("scroll", obterPosicaoScroll);

// Chame a função inicialmente para exibir a posição inicial do scroll
obterPosicaoScroll();

const dadosGeraisScrollTrigger = 20;

botaoSelecionado = document.querySelector(".barraCimaSelectedBtn");

const botaoConteudo = botaoSelecionado.innerHTML;

//sistema pra que a cor de fundo da barra de cima apareça quando o scroll passa de um certo ponto
window.addEventListener('scroll', () => {
    const scrollPosition = window.scrollY;

    if (scrollPosition >= dadosGeraisScrollTrigger) {
        botaoSelecionado.innerHTML = "";
        botaoSelecionado.style.backgroundColor = "transparent";

    } else {
        botaoSelecionado.innerHTML = botaoConteudo;
        botaoSelecionado.style.backgroundColor = "#1c1919";

    }
});

function sobeImagens() {
    const scrollContent = document.querySelector('.otherImagesContainer');
    scrollContent.scrollBy(0, -60);
}

function desceImagens() {
    const scrollContent = document.querySelector('.otherImagesContainer');
    scrollContent.scrollBy(0, 60);
}

function mudaRequired(radio) {
    const changebleRequirement = document.querySelectorAll(".changebleRequirement");

    changebleRequirement.forEach(function (input) {
        input.required = !radio.checked;
    });
}

function imageZoom(imgClass, resultClass) {
    var img, lens, result, cx, cy;
    img = document.querySelector(imgClass);
    result = document.querySelector(resultClass);
    /* Create lens: */
    lens = document.createElement("DIV");
    lens.setAttribute("class", "imgZoomLens");
    /* Insert lens: */
    img.parentElement.insertBefore(lens, img);
    /* Calculate the ratio between result DIV and lens: */
    cx = result.offsetWidth / lens.offsetWidth;
    cy = result.offsetHeight / lens.offsetHeight;
    /* Set background properties for the result DIV */
    result.style.backgroundImage = "url('" + img.src + "')";
    result.style.backgroundSize = (img.width * cx) + "px " + (img.height * cy) + "px";
    /* Execute a function when someone moves the cursor over the image, or the lens: */
    lens.addEventListener("mousemove", moveLens);
    img.addEventListener("mousemove", moveLens);
    /* And also for touch screens: */
    lens.addEventListener("touchmove", moveLens);
    img.addEventListener("touchmove", moveLens);
    function moveLens(e) {
        var pos, x, y;
        /* Prevent any other actions that may occur when moving over the image */
        e.preventDefault();
        /* Get the cursor's x and y positions: */
        pos = getCursorPos(e);
        /* Calculate the position of the lens: */
        x = pos.x - (lens.offsetWidth / 2);
        y = pos.y - (lens.offsetHeight / 2);
        /* Prevent the lens from being positioned outside the image: */
        if (x > img.width - lens.offsetWidth) { x = img.width - lens.offsetWidth; }
        if (x < 0) { x = 0; }
        if (y > img.height - lens.offsetHeight) { y = img.height - lens.offsetHeight; }
        if (y < 0) { y = 0; }
        /* Set the position of the lens: */
        lens.style.left = x + "px";
        lens.style.top = y + "px";
        /* Display what the lens "sees": */
        result.style.backgroundPosition = "-" + (x * cx) + "px -" + (y * cy) + "px";
    }
    function getCursorPos(e) {
        var a, x = 0, y = 0;
        e = e || window.event;
        /* Get the x and y positions of the image: */
        a = img.getBoundingClientRect();
        /* Calculate the cursor's x and y coordinates, relative to the image: */
        x = e.pageX - a.left;
        y = e.pageY - a.top;
        /* Consider any page scrolling: */
        x = x - window.scrollX;
        y = y - window.scrollY;
        return { x: x, y: y };
    }
}

function mostraImagemSelecionada(imagem) {
    const imageOutputContainer = document.querySelector(".bigImgOutput");

    imageOutputContainer.src = imagem.src;

    if (window.location.pathname.includes('thrashTreasuresInputProdutos.php')) {
        imageZoom(".bigImgOutput", ".imgZoomResult");
    }
}

function mostraNovasImagens(input) {
    const otherImagesContainer = document.querySelector(".otherImagesContainer");

    const existingNewImageContainers = document.querySelectorAll(".newImageContainer");
    existingNewImageContainers.forEach(container => {
        otherImagesContainer.removeChild(container);
    });

    const imageOutputContainer = document.querySelector(".bigImgOutput");

    for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];

        const newImageContainer = document.createElement("div");
        newImageContainer.className = "newImageContainer";
        newImageContainer.innerHTML = "<img src=\"" + URL.createObjectURL(file) + "\" class=\"otherImages newOtherImages\" onclick=\"mostraImagemSelecionada(this);\" title=\"Expandir a imagem\">";

        otherImagesContainer.appendChild(newImageContainer);

        imageOutputContainer.src = URL.createObjectURL(file);

        if (window.location.pathname.includes('thrashTreasuresInputProdutos.php')) {
            imageZoom(".bigImgOutput", ".imgZoomResult");
        }
    }
}

function mostraNovaImagemPerfil(input) {
    const imagePerfilContainer = document.querySelector(".fotoPerfilOutput");

    const files = input.files;

    if (files.length > 0) {
        const file = files[0];
        imagePerfilContainer.src = URL.createObjectURL(file);
    }
}

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

    // Loop through all list items, and hide those who don't match the search query
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