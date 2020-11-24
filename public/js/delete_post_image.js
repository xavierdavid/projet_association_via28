// Identification des éléments du DOM
let link = document.querySelector("[data-delete]"); // Lien ayant l'attribut data-delete

// On ajoute un gestionnaire d'événement sur le lien
link.addEventListener('click', function(event) {
    // On désactive le lien pour empêcher la navigation
    event.preventDefault();
    // On demande confirmation
    if(confirm("Voulez-vous supprimer cette image ?")) {
        // On envoie une requête Ajax vers le href du lien avec la méthode 'DELETE'
        fetch(this.getAttribute("href"), { // La fonction fetch envoie une requête Ajax sous forme de promesse : 'this' est ici le lien sur lequel on a cliqué
            method: "DELETE", // Méthode utilisée
            headers: { // Informations à envoyer en 'en-tête'
                'X-Requested-With': 'XMLHttpRequest', // Type de requête : Ajax XMLHttpRequest
                'Content-Type': "application/json" // Type d'information à envoyer : du Json
            },
            body: JSON.stringify({"_token": this.dataset.token}) // Les données à envoyer (tableau que l'on convertit en Json) - 'this' est le lien sur lequel on a cliqué, dataset permet d'identifier les attributs de type 'data' et notamment celui qui est nommé 'token'. On envoie donc le token.
        }).then( // Alors, procédure à suivre si la promesse est tenue (s'il y a une réponse)
            // On récupère la réponse en Json, envoyée par notre contrôleur. Il s'agit une nouvelle fois ici d'une promesse
            response => response.json() 
        ).then(data => {
            // Alors on récupère les données sous forme d'un tableau qui contient soit 'success', soit 'error'. On stocke ces données dans la variable 'data'
            if(data.success) {
                // En cas de succès de la requête, on supprime l'élément parent de notre lien (balise 'figure')
                this.parentElement.remove();
            } else {
                // En cas d'échec,
                alert(data.error);
            }
        }).catch(e => alert(e)) // Si la promesse n'est pas tenue ...
    }
});