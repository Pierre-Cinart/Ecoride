// Fonction pour activer l'autocomplétion des villes dans un champ input
function autoCompleteVille(inputId, listId) {
  const input = document.getElementById(inputId); // Récupère l'élément input par son ID
  const list = document.getElementById(listId);   // Récupère la datalist associée (pour suggestions)

  // À chaque saisie dans le champ input
  input.addEventListener("input", function () {
    const query = input.value.trim(); // Supprime les espaces inutiles autour
    if (query.length < 2) return;     // On ne lance pas de requête si la saisie est trop courte

    // Appel à l'API Geo (géo.api.gouv.fr) pour chercher les villes correspondant à la saisie
    fetch(`https://geo.api.gouv.fr/communes?nom=${query}&fields=nom&boost=population&limit=5`)
      .then(response => response.json()) // Convertit la réponse en JSON
      .then(data => {
        list.innerHTML = ''; // Vide les suggestions précédentes

        // Pour chaque ville retournée par l’API
        data.forEach(ville => {
          const option = document.createElement("option"); // Crée une nouvelle option HTML
          option.value = ville.nom; // Insère le nom de la ville dans l’attribut value
          list.appendChild(option); // Ajoute cette option à la datalist liée au champ input
        });
      })
      .catch(error => {
        // Si une erreur survient pendant la requête (ex : pas de connexion)
        console.error("Erreur GeoAPI :", error);
      });
  });
}

// Active l'autocomplétion sur les deux champs "ville de départ" et "ville d'arrivée"
autoCompleteVille("depart", "depart-list");
autoCompleteVille("arrivee", "arrivee-list");
