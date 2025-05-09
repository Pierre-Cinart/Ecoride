<?php
/**
 * Composant de pagination réutilisable
 *
 * @param int $totalItems     Nombre total d'éléments (ex: 125 messages)
 * @param int $itemsPerPage   Nombre d'éléments par page (ex: 10)
 * @param int $currentPage    Page actuelle (via $_GET['page'])
 * @param string $baseUrl     URL de base SANS le paramètre `page`, ex : "tripList.php?search=paris"
 * @param int $maxLinks       (optionnel) Nombre de liens max visibles autour de la page actuelle (défaut : 5)
 */
function renderPagination($totalItems, $itemsPerPage, $currentPage, $baseUrl, $maxLinks = 5) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    if ($totalPages <= 1) return; // pas besoin de pagination

    echo '<div class="pagination">';

    // Lien précédent
    if ($currentPage > 1) {
        echo '<a href="' . $baseUrl . '?page=' . ($currentPage - 1) . '">←</a> ';
    }

    // Définir la plage de pages à afficher
    $start = max(1, $currentPage - $maxLinks);
    $end = min($totalPages, $currentPage + $maxLinks);

    if ($start > 1) {
        echo '<a href="' . $baseUrl . '?page=1">1</a> ';
        if ($start > 2) echo '... ';
    }

    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            echo '<span class="active">' . $i . '</span> ';
        } else {
            echo '<a href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a> ';
        }
    }

    if ($end < $totalPages) {
        if ($end < $totalPages - 1) echo '... ';
        echo '<a href="' . $baseUrl . '?page=' . $totalPages . '">' . $totalPages . '</a> ';
    }

    // Lien suivant
    if ($currentPage < $totalPages) {
        echo '<a href="' . $baseUrl . '?page=' . ($currentPage + 1) . '">→</a>';
    }

    echo '</div>';
}
?>
