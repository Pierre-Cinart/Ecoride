// =============================================================================
// Script JS pour gérer les filtres d'affichage de la gestion des utilisateurs
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {
  // Sélection du menu déroulant pour filtrer les utilisateurs
  const filter = document.getElementById('userFilter');

  // Sélection de toutes les sections de type user-cards (divs contenant les listes)
  const sections = document.querySelectorAll('.user-cards');

  // Fonction pour afficher uniquement la section sélectionnée
  const updateVisibleSection = (selectedId) => {
    sections.forEach(section => {
      // On montre uniquement la section dont l'ID correspond à l'option sélectionnée
      section.classList.toggle('hidden', section.id !== selectedId);
    });

    // On remet la pagination à la page 1 sans recharger la page
    const url = new URL(window.location);
    url.searchParams.set('page', '1');
    history.replaceState(null, '', url);
  };

  // Lors du changement de valeur dans le menu déroulant
  filter.addEventListener('change', (e) => {
    const selected = e.target.value;
    console.log("Option sélectionnée :", selected); 
    updateVisibleSection(selected);
  });
});
