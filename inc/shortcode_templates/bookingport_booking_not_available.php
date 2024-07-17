<?php
$option_table = get_option(BOOKINGPORT_Settings::$option_table); ?>

<div class="wrapper">
    <p>Leider sind derzeit keine Buchungen für Sie möglich. Momentan haben unsere gewerblichen Kunden wie z.B. Schausteller oder Getränke- und Imbissstände ein
        Vorbuchungsrecht. Wir bitten Sie um etwas Geduld.</p>
    <p class="btn-primary"><a
                href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_dashboard] ?>">Übersicht</a></p>
</div>
