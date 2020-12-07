// Gestion de l'ajout et de la suppression dynamique de prototypes de sous-formulaires d'upload d'images

// Récupération des éléments du DOM
let addImage = document.querySelector('#add-image');
let associationFormImage = document.querySelector('#association_form_image');


// Définition d'un gestionnaire d'événement
addImage.addEventListener('click', function(event){
    // Annulation du comportement par défaut du lien
    event.preventDefault();
    // Récupération du nombre de sous-formulaires d'images ayant pour identifiant 'form-group' présents dans l'élément parent 'association_form_image'
    const imageIndex = associationFormImage.children.length;
    // Récupération du prototype des entrées représentant le 'template' des sous-formulaires
    let formImageTemplate = associationFormImage.getAttribute('data-prototype');
    // On remplace l'élément '__name__' présent dans le template par l'index préalablement récupéré
    const regex = /__name__/gi;
    imageIndexTemplate = formImageTemplate.replace(regex, imageIndex);
    // On crée un nouvel élément 'div' dans le DOM
    let newImageFormGroup = document.createElement("div");
    // Affectation d'une classe 'form-group' à ce nouvel élément
    newImageFormGroup.className='form-group';
    // Insertion du contenu 'indexTemplate' dans 'newFormGroup'
    newImageFormGroup.innerHTML = imageIndexTemplate;
    // On injecte ce 'newFormGroup' dans son parent 'associationFormImage'
    associationFormImage.appendChild(newImageFormGroup); 
    // Appel de la fonction handleDeleteButtons pour supprimer les sous-formulaires
    imageDeleteButtons();
})

// Gestion de la suppression des sous-formulaires d'images
function imageDeleteButtons() {
    // Récupération dans un tableau des éléments du DOM les éléments de type 'button' possédant l'attribut 'data-action' avec la valeur 'delete' 
    let deleteImageButtons = document.querySelectorAll("button[data-action=delete]");
    // On parcourt le tableau des éléments 'deleteImageButtons'
    for(let i=0; i < deleteImageButtons.length; i++) {
        // Sur chaque élément on ajoute un gestionnaire d'événement
        deleteImageButtons[i].addEventListener('click', function() {
            // On récupère la valeur de la cible 'data-target'
            let target = this.dataset.target;
            //console.log(target);
            // On récupère le sous-formulaire dont l'identifiant possède cette valeur cible
            let imageFormTarget = document.querySelector(target);
            //console.log(imageFormTarget);
            // On supprime le sous-formulaire 
            imageFormTarget.remove();
        });
    }
}

imageDeleteButtons();
