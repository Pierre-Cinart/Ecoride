<?php // faire la requette en fonction du get ?>
<div class="user-card">
  <p><strong>Pseudo :</strong> exemple_user</p>
  <p><strong>Email :</strong> user@email.com</p>
  <p><strong>Rôle :</strong> Conducteur (en attente)</p>
  <div id="showPermit">
    <h2>permis</h2>
    <!-- injecter le permis en miniaturisé ouverture about_blank en grand format -->

     <button class = 'green' onclick = 'validatePermit(true)'>Valider</button>
     <button class = 'red' onclick ='validatePermit(false)'>Refuser</button>
  </div>
  <!-- faire un foreach pour tout recupérer -->
  <div id="vehicles_documents(+id du vehicule)">
    <!-- injecter les documents en attentes trier par vehicule enregistré en miniaturisé ouverture about_blank en grand format -->
      <div id="registration+id du vehicule">
        <button class = 'green' onclick = 'validatePermit(true)'>Valider</button>
        <button class = 'red' onclick ='validatePermit(false)'>Refuser</button>
      </div>
       <div id="insurance+id du vehicule">
        <button class = 'green' onclick = 'validatePermit(true)'>Valider</button>
        <button class = 'red' onclick ='validatePermit(false)'>Refuser</button>
      </div>
     
  </div>
</div>
     

     
    

