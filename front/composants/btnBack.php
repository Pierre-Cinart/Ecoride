<?php

function btnBack(string $url = 'home.php', string $label = '← Retour'): void {
    echo '<button class="blue btn-back" onclick="location.href=\'' . htmlspecialchars($url) . '\'">';
    echo htmlspecialchars($label);
    echo '</button>';
}
?>