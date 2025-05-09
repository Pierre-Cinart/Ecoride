<?php
/**
 * Composant de pagination réutilisable
 *
 * @param int $totalItems     Nombre total d'éléments (ex: 125 messages)
 * @param int $itemsPerPage   Nombre d'éléments par page (ex: 10)
 * @param int $currentPage    Page actuelle (via $_GET['page'])
 * @param string $baseUrl     URL de base (ex : "tripList.php", ou avec paramètres : "tripList.php?search=paris")
 * @param int $maxLinks       Nombre de liens max visibles autour de la page actuelle (défaut : 5)
 */
function renderPagination($totalItems, $itemsPerPage, $currentPage, $baseUrl, $maxLinks = 5) {
    // 1. Calcul du nombre total de pages
    $totalPages = ceil($totalItems / $itemsPerPage);
    if ($totalPages <= 1) return; // Aucune pagination nécessaire

    // 2. Déterminer si on utilise ? ou & pour ajouter le paramètre page
    $separator = (strpos($baseUrl, '?') !== false) ? '&' : '?';

    echo '<div class="pagination">';

    // 3. Lien vers la page précédente
    if ($currentPage > 1) {
        echo '<a href="' . $baseUrl . $separator . 'page=' . ($currentPage - 1) . '">←</a> ';
    }

    // 4. Définir les bornes gauche/droite autour de la page actuelle
    $start = max(1, $currentPage - $maxLinks);
    $end = min($totalPages, $currentPage + $maxLinks);

    // 5. Affichage du lien vers la première page si nécessaire
    if ($start > 1) {
        echo '<a href="' . $baseUrl . $separator . 'page=1">1</a> ';
        if ($start > 2) echo '... ';
    }

    // 6. Affichage des pages autour de la page actuelle
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            echo '<span class="active">' . $i . '</span> ';
        } else {
            echo '<a href="' . $baseUrl . $separator . 'page=' . $i . '">' . $i . '</a> ';
        }
    }

    // 7. Affichage du lien vers la dernière page si nécessaire
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) echo '... ';
        echo '<a href="' . $baseUrl . $separator . 'page=' . $totalPages . '">' . $totalPages . '</a> ';
    }

    // 8. Lien vers la page suivante
    if ($currentPage < $totalPages) {
        echo '<a href="' . $baseUrl . $separator . 'page=' . ($currentPage + 1) . '">→</a>';
    }

    echo '</div>';
}
?>
