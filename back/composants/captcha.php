<?php
    require_once __DIR__ . '/../config/configCaptcha.php';


    /**
     * Fonction front : injecte le script de Google reCAPTCHA v3 avec l’action souhaitée
     */
    function renderRecaptcha(string $action = 'submit') {
    global $RECAPTCHA_PUBLIC_KEY;

    echo <<<HTML
<script src="https://www.google.com/recaptcha/api.js?render={$RECAPTCHA_PUBLIC_KEY}"></script>
<script>
  grecaptcha.ready(function() {
    grecaptcha.execute('{$RECAPTCHA_PUBLIC_KEY}', {action: '{$action}'}).then(function(token) {
      const responseField = document.getElementById('g-recaptcha-response');
      if (responseField) responseField.value = token;
    });
  });
</script>
<!-- Badge reCAPTCHA v3 -->
<style>
  .grecaptcha-badge { visibility: visible !important; }
</style>
HTML;
}


    /**
     * Fonction back : vérifie que le token reCAPTCHA reçu est valide
     */
    function verifyCaptcha(string $expectedAction = 'submit', string $redirectOnFail = '../../index.php'): void {
        global $RECAPTCHA_PRIVATE_KEY;

        if (!isset($_POST['g-recaptcha-response'])) {
            $_SESSION['error'] = "Le champ reCAPTCHA est manquant.";
            header("Location: $redirectOnFail");
            exit();
        }

        $token = $_POST['g-recaptcha-response'];
        $url = "https://www.google.com/recaptcha/api/siteverify";

        $params = [
            'secret'   => $RECAPTCHA_PRIVATE_KEY,
            'response' => $token
        ];

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($params)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $result = json_decode($response, true);

        if (
            !$result['success'] ||
            ($result['action'] ?? '') !== $expectedAction ||
            ($result['score'] ?? 0) < 0.5
        ) {
            $_SESSION['error'] = "Échec de la vérification reCAPTCHA.";
            header("Location: $redirectOnFail");
            exit();
        }
    } ?>
