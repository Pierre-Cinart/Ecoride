document.getElementById('creditForm').addEventListener('submit', function(e) {
    const amount = document.getElementById('creditAmount').value;
    if (!amount || isNaN(amount) || amount <= 0) return;

    const confirmMsg = `⚠️ Vous êtes sur le point d'ajouter ${amount} crédit(s) à votre compte.\n\nSouhaitez-vous continuer ?`;

    if (!confirm(confirmMsg)) {
      e.preventDefault();
    }
  });