// Gestion de l'ajout et de la suppression dynamique de prototypes de sous-formulaires d'upload de documents

// Récupération des éléments du DOM
let addDocument = document.querySelector('#add-document');
let associationFormDocument = document.querySelector('#association_form_document');

// Définition d'un gestionnaire d'événement
addDocument.addEventListener('click', function(event){
    // Annulation du comportement par défaut du lien
    event.preventDefault();
    // Récupération de l'index du sous-formulaire de documents, défini dans le champ 'input' de type 'hidden' ayant pour identifiant 'widget-document-counter' - Renvoie une chaîne caractères
    let documentIndex = document.querySelector('#widget-document-counter').getAttribute("value");
    // Transformation de la chaîne de caractère en nombre pour permettre l'incrémentation de l'index
    documentIndex = Number(documentIndex);
    console.log(documentIndex);
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
    // Incrémentation de l'index de spous-formulaire 'document'
    document.querySelector('#widget-document-counter').setAttribute("value", documentIndex+1 ); 
    // Appel de la fonction handleDeleteButtons pour supprimer les sous-formulaires
    documentDeleteButtons();
})

// Initialisation du compteur d'index de sous-formulaire
function updateIndexCounter() {
    // Récupération du nombre de sous-formulaires de documents ayant pour identifiant 'form-group' présents dans l'élément parent 'association_form_document' - Renvoie une chaîne de caractères
    let counterIndex = associationFormDocument.children.length;
    // Transformation en nombre
    counterIndex = Number(counterIndex);
    // Actualisation du compteur d'index de sous-formulaire
    document.querySelector('#widget-document-counter').setAttribute("value", counterIndex);
}

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
            // On récupère le sous-formulaire dont l'identifiant possède cette valeur cible
            let documentFormTarget = document.querySelector(target);
            // Si un sous-formulaire existe
            if(documentFormTarget != null) {
                // On supprime le sous-formulaire
                documentFormTarget.remove();  
            } 
        });
    }
}

// Appel des fonctions
updateIndexCounter();
documentDeleteButtons();