 <!-- Google reCAPTCHA v3 -->
 <script src="https://www.google.com/recaptcha/api.js?render=<?= $RECAPTCHA_PUBLIC_KEY ?>"></script>
  <script>
    grecaptcha.ready(function() {
      grecaptcha.execute('<?= $RECAPTCHA_PUBLIC_KEY ?>', {action: 'login'}).then(function(token) {
        document.getElementById('g-recaptcha-response').value = token;
      });
    });
    </script>