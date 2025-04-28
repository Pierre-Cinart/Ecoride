document.addEventListener("DOMContentLoaded", function() {
    const popup = document.getElementById('popup');
    
    if (popup && popup.textContent.trim() !== '') {
      popup.style.display = 'block'; // Au cas où tu mets display:none au départ
      setTimeout(() => {
        popup.style.opacity = '0';
        setTimeout(() => {
          popup.remove(); // On enlève complètement le DOM après disparition
        }, 500); // Délai après l'opacité à 0
      }, 4000); // 4 secondes d'affichage
    }
  });
  