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

    // 2. Déterminer si on utilise ? ou & pour ajouter le paramètre page
    $separator = (strpos($baseUrl, '?') !== false) ? '&' : '?';

    // 3. Ajouter la classe la div  si plus de une page 
    if ($totalPages > 1){
            echo '<div class="pagination">';

        // 4. Page précédente
        if ($currentPage > 1) {
            echo '<a href="' . $baseUrl . $separator . 'page=' . ($currentPage - 1) . '">←</a> ';
        }

        // 5. Pages autour
        $start = max(1, $currentPage - $maxLinks);
        $end = min($totalPages, $currentPage + $maxLinks);

        if ($start > 1) {
            echo '<a href="' . $baseUrl . $separator . 'page=1">1</a> ';
            if ($start > 2) echo '... ';
        }

        for ($i = $start; $i <= $end; $i++) {
            if ($i == $currentPage) {
                echo '<span class="active">' . $i . '</span> ';
            } else {
                echo '<a href="' . $baseUrl . $separator . 'page=' . $i . '">' . $i . '</a> ';
            }
        }

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) echo '... ';
            echo '<a href="' . $baseUrl . $separator . 'page=' . $totalPages . '">' . $totalPages . '</a> ';
        }

        // 6. Page suivante
        if ($currentPage < $totalPages) {
            echo '<a href="' . $baseUrl . $separator . 'page=' . ($currentPage + 1) . '">→</a>';
        }

        echo '</div>';
        }
   

  
}

?>
