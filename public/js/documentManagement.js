// Gestion de l'ajout et de la suppression dynamique de prototypes de sous-formulaires d'upload de documents

// Récupération des éléments du DOM
let addDocument = document.querySelector('#add-document');
let associationFormDocument = document.querySelector('#association_form_document');

// Définition d'un gestionnaire d'événement
addDocument.addEventListener('click', function(event){
    // Annulation du comportement par défaut du lien
    event.preventDefault();
    // Récupération du nombre de sous-formulaires de documents ayant pour identifiant 'form-group' présents dans l'élément parent 'association_form_document'
    const documentIndex = associationFormDocument.children.length;
    // Récupération du prototype des entrées représentant le 'template' des sous-formulaires
    let formDocumentTemplate = associationFormDocument.getAttribute('data-prototype');
    // On remplace l'élément '__name__' présent dans le template par l'index préalablement récupéré
    const regex = /__name__/gi;
    documentIndexTemplate = formDocumentTemplate.replace(regex, documentIndex);
    // On crée un nouvel élément 'div' dans le DOM
    let newDocumentFormGroup = document.createElement("div");
    // Affectation d'une classe 'form-group' à ce nouvel élément
    newDocumentFormGroup.className='form-group';
    // Insertion du contenu 'indexTemplate' dans 'newFormGroup'
    newDocumentFormGroup.innerHTML = documentIndexTemplate;
    // On injecte ce 'newFormGroup' dans son parent 'associationFormImage'
    associationFormDocument.appendChild(newDocumentFormGroup); 
    // Appel de la fonction handleDeleteButtons pour supprimer les sous-formulaires
    documentDeleteButtons();
})

// Gestion de la suppression des sous-formulaires de documents
function documentDeleteButtons() {
    // Récupération dans un tableau des éléments du DOM les éléments de type 'button' possédant l'attribut 'data-action' avec la valeur 'delete' 
    let deleteDocumentButtons = document.querySelectorAll("button[data-action=delete]");
    // On parcourt le tableau des éléments 'deleteImageButtons'
    for(let i=0; i < deleteDocumentButtons.length; i++) {
        // Sur chaque élément on ajoute un gestionnaire d'événement
        deleteDocumentButtons[i].addEventListener('click', function() {
            // On récupère la valeur de la cible 'data-target'
            let target = this.dataset.target;
            console.log(target);
            // On récupère le sous-formulaire dont l'identifiant possède cette valeur cible
            let documentFormTarget = document.querySelector(target);
            //console.log(imageFormTarget);
            // On supprime le sous-formulaire 
            documentFormTarget.remove();
        });
    }
}

documentDeleteButtons();