const perfilContainer = document.getElementById("perfilContainerID");

const perfilNavbarEsquerdo = document.getElementById("perfilNavbarEsquerdoID");
const perfilNavbarEsquerdoDiv = document.getElementById("perfilNavbarEsquerdoDivID");

const perfilNavbarEsquerdoPerfilButton = document.querySelector(".perfilNavbarEsquerdoDiv button");
const perfilNavbarEsquerdoPerfilButton02 = document.getElementById("perfilNavbarEsquerdoPerfilButton02");

const perfilNavbarEsquerdoButtonArrowIcon = document.getElementById("perfilNavbarEsquerdoButtonArrowIcon");
const perfilNavbarEsquerdoButtonArrowIcon02 = document.getElementById("perfilNavbarEsquerdoButtonArrowIcon02");

const perfilNavbarEsquerdoPerfilUl = document.getElementById("perfilNavbarEsquerdoPerfilUlID");
const perfilNavbarEsquerdoPerfilUl02 = document.getElementById("perfilNavbarEsquerdoPerfilUl02_ID");

const perfilNavbarEsquerdoPerfilLi = document.querySelectorAll(".perfilNavbarEsquerdoPerfilUl li");

const perfilDados = document.getElementById("perfilDadosID");

const voltarPerfilUl = document.getElementById("voltarPerfilUlID");
const voltarPerfilUlTexto = document.getElementById("voltarPerfilUlTextoID");

let perfilButtonClick = 0;
let perfilButtonClick02 = 0;



perfilNavbarEsquerdoPerfilButton.addEventListener('click', function() {
    perfilButtonClick++;
    perfilButtonClick === 1 ? buttonArrowIcon(true, true) : buttonArrowIcon(true, false);
});
perfilNavbarEsquerdoPerfilButton02.addEventListener('click', function() {
    perfilButtonClick02++;
    perfilButtonClick02 === 1 ? buttonArrowIcon(false, true) : buttonArrowIcon(false, false);
});



function buttonArrowIcon(frist, fa_angle_up) {
    if (frist) {
        if (fa_angle_up) {
            perfilNavbarEsquerdoButtonArrowIcon.classList.remove("fa-angle-up");
            perfilNavbarEsquerdoButtonArrowIcon.classList.add("fa-angle-down");
            perfilNavbarEsquerdoPerfilUl.style.display = 'none';
        }
        else {
            perfilNavbarEsquerdoPerfilUl.style.display = 'grid'; 
            perfilNavbarEsquerdoButtonArrowIcon.classList.remove("fa-angle-down");
            perfilNavbarEsquerdoButtonArrowIcon.classList.add("fa-angle-up");
            perfilButtonClick = 0;
        }
    }

    else {
        if (fa_angle_up) {
            perfilNavbarEsquerdoButtonArrowIcon02.classList.remove("fa-angle-up");
            perfilNavbarEsquerdoButtonArrowIcon02.classList.add("fa-angle-down");
            perfilNavbarEsquerdoPerfilUl02.style.display = 'none';
        }
        else {
            perfilNavbarEsquerdoPerfilUl02.style.display = 'grid'; 
            perfilNavbarEsquerdoButtonArrowIcon02.classList.remove("fa-angle-down");
            perfilNavbarEsquerdoButtonArrowIcon02.classList.add("fa-angle-up");
            perfilButtonClick02 = 0;
        }        
    }
}





let perfilLiClick = 0;
perfilNavbarEsquerdoPerfilLi.forEach(li => {
    li.addEventListener('click', function() {
        perfilLiClick++;
        
        // Ocultar todas as seções
        document.querySelectorAll('.section-content').forEach(section => {
            section.style.display = 'none';
        });
        
        // Mostrar a seção correspondente
        const sectionId = li.getAttribute('data-section');
        const targetSection = document.getElementById(`section-${sectionId}`);
        if (targetSection) {
            targetSection.style.display = 'flex';
        }
        
        if (perfilLiClick === 1) {
            perfilDados.style.display = 'flex';
            if (window.innerWidth <= 500) {
                perfilNavbarEsquerdo.style.display = "none";
                voltarPerfilUl.addEventListener('click', function() {
                    nameIt();
                });
            }
        }
        else {
            perfilDados.style.display = 'none';
            perfilLiClick = 0;
        }
    });
});

window.addEventListener('resize', function() {
    if (window.innerHeight <= 500 && perfilNavbarEsquerdo.style.display === "none") {
        voltarPerfilUl.style.display = "flex";
        voltarPerfilUl.addEventListener('click', function() {
            nameIt();
        });
    }
});

function nameIt() {
    perfilDados.style.display = "none";
    perfilNavbarEsquerdo.style.display = "flex";
    perfilNavbarEsquerdoDiv.style.display = "grid";  
}