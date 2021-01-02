<?php
$amso_konekcija = pg_connect("host=localhost dbname=amso user=zoranp");

if (!$amso_konekcija) {
    exit('Greška otvaranja konekcije prema SQL serveru.');
}
?>
