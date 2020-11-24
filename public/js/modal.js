// Permet de copier le lien de suppression contenant le chemin et l'identifiant dans la modale de suppression 
// Ce lien est stocké dans l'attribut "data-suppression"

// Récupération des éléments du DOM ayant pour classe "deleteButton" dans un tableau
let deleteButtons = document.querySelectorAll(".deleteButton");

// On parcourt le tableau des éléments 'deleteButtons'
for(let i=0; i < deleteButtons.length; i++) {
    // Sur chaque élément on ajoute un gestionnaire d'événement
    deleteButtons[i].addEventListener('click', function(event){
        // On bloque l'envoi du formulaire
        event.preventDefault();
        // On récupère la valeur de l'attribut 'data-suppression' du bouton contenant la route permettant au contrôleur de traiter la suppression, ainsi que l'identifiant du contenu à supprimer 
        let deleteRoute = deleteButtons[i].getAttribute('delete-route');
        // On récupère l'élément 'form' de la modale ayant pour identifiant 'deleteForm' 
        let deleteForm = document.querySelector('#deleteForm');
        // On copie la valeur de 'deleteRoute' dans le champ 'action' du formulaire de suppression de la modale
        deleteForm.setAttribute('action',deleteRoute);
        // On récupère le token de suppression du contenu à supprimer 
        let deleteToken = deleteButtons[i].getAttribute('delete-token');
        console.log(deleteToken);
        // On récupère l'élément 'input' de la modale ayant pour identifiant 'tokenInput'
        let inputToken = document.querySelector('#inputToken');
        // On copie la valeur de 'deleteToken' dans l'attribut 'value' de 'inputToken' 
        inputToken.setAttribute('value',deleteToken);
        // On récupère le message stocké dans l'attribut 'data-message'
        let message = deleteButtons[i].getAttribute('delete-message');
        // On récupère l'élément '.modal-body' de la modale
        let modalBody = document.querySelector(".modal-body");
        // On copie le message dans le texte à afficher dans la modale 
        modalBody.textContent = message;
    });
}