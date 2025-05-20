<script type="module">
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/11.7.3/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.7.3/firebase-analytics.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyBuSSGf_iG9Rzyilo7TG8qr-4dFEN_YWI4",
    authDomain: "ecoride-ecf.firebaseapp.com",
    projectId: "ecoride-ecf",
    storageBucket: "ecoride-ecf.firebasestorage.app",
    messagingSenderId: "992500631661",
    appId: "1:992500631661:web:4cf327893a8aa1ad8a9764",
    measurementId: "G-EW1PZE72ZR"
  };

  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);
</script>