// Gestion de la dropdown du menu utilisateur

// Récupération des éléments du DOM
let dropdownLink = document.querySelector('.dropdown');
let dropdownContent = document.querySelector('.dropdown-content');

// Définition d'un gestionnaire d'événement
dropdownLink.addEventListener('click', function(event){
    // Annulation du comportement par défaut du lien
    event.preventDefault();
    // Affectation de la classe 'dropdown-active' à l'élément 'dropdownContent' 
    dropdownContent.classList.toggle('active-dropdown')
})