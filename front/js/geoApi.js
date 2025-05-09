function autoCompleteVille(inputId, listId) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);
  
    input.addEventListener("input", function () {
      const query = input.value.trim();
      if (query.length < 2) return;
  
      fetch(`https://geo.api.gouv.fr/communes?nom=${query}&fields=nom&boost=population&limit=5`)
        .then(response => response.json())
        .then(data => {
          list.innerHTML = '';
          data.forEach(ville => {
            const option = document.createElement("option");
            option.value = ville.nom;
            list.appendChild(option);
          });
        })
        .catch(error => {
          console.error("Erreur GeoAPI :", error);
        });
    });
  }
  
  autoCompleteVille("depart", "depart-list");
  autoCompleteVille("arrivee", "arrivee-list");