<?php
$amso_konekcija = pg_connect("host=localhost dbname=a user=z");

if (!$amso_konekcija) {
    exit('Greška otvaranja konekcije prema SQL serveru.');
}
?>
