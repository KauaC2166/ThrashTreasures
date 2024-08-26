//function para mostrar oque foi escrito em um input do tipo password
function mostraSenha(btn) {

    inputSenha = btn.previousElementSibling;

    if (inputSenha.type == 'password') {
        inputSenha.type = 'text';
    } else {
        inputSenha.type = 'password';
    }
}

//function para que caso o input de tipo data esteja vazio, seja mostrado um placeholder, no caso, para que isso seja possivel, é nescessario trocar o input do tipo date para um input de tipo text normal
function voltaInputData() {
    if (dataInput.value == "") {
        dataInput.type = "text";
    }
}

//function para mostrar a imagem de perfil selecionada pelo usuario
function mostraImagem(input) {
    const perfilIcon = document.getElementById("perfilIcon");
    const perfilImage = document.getElementById("perfilImage");

    if (input.files.length != 0) {
        const file = input.files[0];
        perfilIcon.style.display = "none";
        perfilImage.src = URL.createObjectURL(file);
        perfilImage.style.display = "block";
    } else {
        perfilImage.style.display = "none";
        perfilIcon.style.display = "block";
    }
}
