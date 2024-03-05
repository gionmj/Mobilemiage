document.addEventListener("DOMContentLoaded", function() {
    var btnTraiter = document.getElementById("btnTraiter");

    btnTraiter.addEventListener("click", function() {
        // Récupérer l'ID de l'incident à traiter (c'est un exemple, vous pouvez obtenir cette valeur de différentes manières dans votre application)
        var idIncident = 123; // Remplacer par la méthode réelle pour obtenir l'ID de l'incident

        fetch('TraiterIncident.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                idIncident: idIncident
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('La requête a échoué');
                }
                return response.json();
            })
            .then(data => {
                console.log(data); // Afficher les données de la réponse dans la console
                var idIncident = data.incident.idIncident;
                var idAgent = data.incident.idAgent;
                var newStatus = data.incident.newStatus;

                // Utiliser ces valeurs pour effectuer d'autres actions, si nécessaire
                console.log("ID de l'incident :", idIncident);
                console.log("ID de l'agent :", idAgent);
                console.log("Nouveau statut :", newStatus);
            })
            .catch(error => console.error('Erreur :', error));
    });
});
