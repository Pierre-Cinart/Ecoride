 <!-- Google reCAPTCHA v3 -->
 <?php 
require_once '../../back/config/configCaptcha.php';//clés de config
 $captchaAction = $captchaAction ?? 'submit'; ?>

 <script src="https://www.google.com/recaptcha/api.js?render=<?= $RECAPTCHA_PUBLIC_KEY ?>"></script>
  <script>
    grecaptcha.ready(function() {
      grecaptcha.execute('<?= $RECAPTCHA_PUBLIC_KEY ?>', {action: '<?= $captchaAction ?>' }).then(function(token) {
        document.getElementById('g-recaptcha-response').value = token;
      });
    });
    </script>