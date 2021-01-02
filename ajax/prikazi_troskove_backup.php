<?php
//AKO JE POTVRDJENA FORMA NA FRONTENDU I PROSLEDJEN DATUM
if (isset($_POST['pocetni_datum'])) {

    //UKLJUCIVANJE KONEKCIJE KA AMSO BAZI
    require_once '../dodaci/konekcija_amso.php';

    //UPIS POCETNOG I KRAJNJEG DATUMA ZA 4 KVARTALA U PROMENJIVE
    $pocetni_datum = $_POST['pocetni_datum'];
    $krajnji_datum = $_POST['krajnji_datum'];

    $niz_datum = explode('-', $pocetni_datum);
    $niz_datum2 = explode('-', $krajnji_datum);
  
    //IZDVAJANJE POCETNE I KRAJNJE GODINE I UPIS U PROMENJIVE
    $pocetna_godina = substr($_POST['pocetni_datum'], 0, 4);
    $krajnja_godina = substr($_POST['krajnji_datum'], 0, 4);

    //AKO SU POCETNA I KRAJNJA GODINA ISTE
    if ($pocetna_godina === $krajnja_godina) {

        //KREIRANJE PROMENJIVIH SA POCETNOM I KRAJNJOM GODINOM ZBOG PRIKAZIVANJA TABOVA
        $godina_pocetnog_taba = $pocetna_godina;
        $godina_krajnjeg_taba = '';

        //DINAMICKO ODREDJIVANJE GLAVNE KNJIGE I UPIS U PROMENJIVU
        $glavna_knjiga = 'g' .$krajnja_godina;

        //DEO UPITA ZA DOBIJANJE TROSKOVA PO KONTIMA I OSTALIH POTREBNIH PODATAKA
        $upit = "SELECT EXTRACT(YEAR FROM datknjiz) as god1,g1.konto,SUM(g1.duguje),k1.opis1 FROM $glavna_knjiga AS g1 
        INNER JOIN k_$krajnja_godina AS k1 ON g1.konto = k1.konto 
        WHERE g1.konto LIKE ANY (array['53%', '54%', '55%']) AND g1.datknjiz BETWEEN '$pocetni_datum' AND '$krajnji_datum' AND g1.opisdok NOT IN(SELECT opisdok FROM $glavna_knjiga WHERE vrstadok = 'PO' AND opisdok ILIKE '%avr%')
        GROUP BY g1.konto,k1.opis1,god1
        ORDER BY g1.konto";

        $tabovi = '<button class="dugme_tab active" id="prvi_tab">' .$niz_datum[2]. '.' .$niz_datum[1]. '.' .$pocetna_godina. '. - ' .$niz_datum2[2]. '.' .$niz_datum2[1]. '.'  .$krajnja_godina. '.' .'</button>
                    <button class="dugme_tab" id="drugi_tab" disabled>Pribava ' .$pocetna_godina. '</button>
                    <button class="dugme_tab" id="ukupno_tab">Ukupno</button>
                    <hr class="hr_tabovi">';
    }

    //AKO JE GODINA KLIZNA
    else {
        
        //DINAMICKO ODREDJIVANJE POCETNE I KRAJNJE GLAVNE KNJIGE I UPIS U PROMENJIVE
        $pocetna_glavna_knjiga = 'g' .$pocetna_godina;
        $krajnja_glavna_knjiga = 'g' .$krajnja_godina;

        //DEO UPITA ZA DOBIJANJE TROSKOVA PO KONTIMA I OSTALIH POTREBNIH PODATAKA KADA JE GODINA KLIZNA
        $upit = "SELECT EXTRACT(YEAR FROM datknjiz) as god1,g1.konto,SUM(g1.duguje),k1.opis1 FROM $pocetna_glavna_knjiga AS g1 
        INNER JOIN k_$pocetna_godina AS k1 ON g1.konto = k1.konto 
        WHERE g1.konto LIKE ANY (array['53%', '54%', '55%']) AND g1.datknjiz >= '$pocetni_datum' AND g1.opisdok NOT IN(SELECT opisdok FROM $pocetna_glavna_knjiga WHERE vrstadok = 'PO' AND opisdok ILIKE '%avr%')
        GROUP BY g1.konto,k1.opis1,god1

        UNION ALL

        SELECT EXTRACT(YEAR FROM datknjiz) as god2,g2.konto,SUM(g2.duguje),k2.opis1 FROM $krajnja_glavna_knjiga AS g2
        INNER JOIN k_$krajnja_godina AS k2 ON g2.konto = k2.konto 
        WHERE g2.konto LIKE ANY (array['53%', '54%', '55%']) AND g2.datknjiz <= '$krajnji_datum' AND g2.opisdok NOT IN(SELECT opisdok FROM $krajnja_glavna_knjiga WHERE vrstadok = 'PO' AND opisdok ILIKE '%avr%')
        GROUP BY g2.konto,k2.opis1,god2
        
        ORDER BY god1,konto";

        //GENERISANJE BUTTONA ZA PROMENU TABOVA
        $tabovi = '<button class="dugme_tab active" id="prvi_tab">' .$niz_datum[2]. '.' .$niz_datum[1]. '.' .$pocetna_godina. '.' .' - 31.12.' .$pocetna_godina. '.' .'</button>
                    <button class="dugme_tab" id="drugi_tab" disabled>Pribava ' .$pocetna_godina. '</button>
                    <button class="dugme_tab" id="cetvrti_tab">' .'01.01.' .$krajnja_godina. '.' .' - ' .$niz_datum2[2]. '.' .$niz_datum2[1]. '.' .$krajnja_godina. '.' .'</button>
                    <button class="dugme_tab" id="peti_tab" disabled>Pribava ' .$krajnja_godina. '</button>
                    <button class="dugme_tab" id="ukupno_tab">Ukupno</button>
                    <hr class="hr_tabovi">';
    }

    //IZVRSAVANJE UPITA
    $rezultat = pg_query($amso_konekcija, $upit);

    //AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA,OBAVESTI KORISNIKA I PREKINI IZVRSAVANJE SKRIPTE
    if (!$rezultat) {

        echo json_encode('Greška pri izvršavanju upita. Pokušajte ponovo.');
        die();
    }

        
    //AKO UPIT VRATI REZULTATE
    if (pg_num_rows($rezultat) > 0) {

        $tabela1 = '';
        $tabela2 = '';

        $brojac = 0;
        $brojac2 = 0;

        
        //AKO GODINA NIJE KLIZNA,POCNI DA KREIRAS TABELU ZA PRETHODNU GODINU                       
        if ($pocetna_godina === $krajnja_godina) {

            //KREIRANJE ZAGLAVLJA HTML TABELE
            $tabela1 = '<table class="tabela_konta tabela_stavke troskovi_pocetna" id="tabela_prva_god">
                            <thead>
                                <tr>
                                    <th width="">Grupa</th>
                                    <th width="">Sektor</th>
                                    <th width="">Konto</th>
                                    <th width="35%">Opis</th>
                                    <th width="">Iznos</th>
                                    <th width="">Iznos za izmenu</th>
                                    <th width="">Čekiraj</th>
                                </tr>
                            </thead>
                            <tbody id="troskovi_pocetna_godina">';

            //$tabela1 = $zaglavlje_tabele;
            //$tabela1 .= '<tbody id="troskovi_pocetna_godina">';
        }
        //U SUPROTNOM,KREIRAJ TABELE ZA TRENUTNU I PRETHODNU GODINU
        else {

            //KREIRANJE ZAGLAVLJA HTML TABELE
            $tabela1 = '<table class="tabela_konta tabela_stavke troskovi_pocetna" id="tabela_prva_god">
                            <thead>
                                <tr>
                                    <th width="">Grupa</th>
                                    <th width="">Sektor</th>
                                    <th width="">Konto</th>
                                    <th width="35%">Opis</th>
                                    <th width="">Iznos</th>
                                    <th width="">Iznos za izmenu</th>
                                    <th width="">Čekiraj</th>
                                </tr>
                            </thead>
                            <tbody id="troskovi_pocetna_godina">';

            $tabela2 = '<table class="tabela_konta tabela_stavke troskovi_krajnja" id="tabela_krajnja_god">
                        <thead>
                            <tr>
                                <th width="">Grupa</th>
                                <th width="">Sektor</th>
                                <th width="">Konto</th>
                                <th width="35%">Opis</th>
                                <th width="">Iznos</th>
                                <th width="">Iznos za izmenu</th>
                                <th width="">Čekiraj</th>
                            </tr>
                        </thead>
                        <tbody id="troskovi_krajnja_godina">';
            //$tabela2 .= '<tbody id="troskovi_krajnja_godina">';
        }
      
    
        //GENERISANJE REDOVA U HTML TABELI ZA DOBIJENE REDOVE IZ BAZE
        while ($red = pg_fetch_array($rezultat)) {

            $niz_proba[] = $red;

            //UPIS POTREBNIH VREDNOSTI U PROMENJIVE
            $grupa_konta = substr($red['konto'], 0, 3);
            $sektor_konta = substr($red['konto'], 3, 1);
            $suma = $red['sum'];
            $konto = $red['konto'];

            //FORMATIRANJE IZNOSA U ZELJENI FORMAT
            $suma = number_format($suma, 2);

            //KONVERTOVANJE NAZIVA KONTA U UTF-8 FORMAT
            $naziv = mb_convert_encoding($red['opis1'], 'UTF-8', 'ISO-8859-2');

            //AKO GODINA NIJE KLIZNA,TJ.VREDNOST GODINE IZ BAZE JEDNAKA POCETNOJ GODINI
            if ($red['god1'] == $pocetna_godina) {
                
                $brojac++;

                //UPIS PODATAKA U POLJA TABELE
                $tabela1 .= '<tr class="grupa_pocetna">
                                <td class="grupa_konta" id="grupa' .$brojac. '">' .$grupa_konta. '</td>
                                <td class="sektor_konta" id="sektor' .$brojac. '">' .$sektor_konta. '</td>
                                <td class="broj_konta" id="konto' .$brojac. '">' .$konto. '</td>
                                <td class="opis_konta" id="opis' .$brojac. '">' .$naziv. '</td>
                                <td class="polje_iznos iznos_konta" id="original_iznos' .$brojac.  '">' .$suma. '</td>
                                <td><input height="5" type="text" class="input_iznos pocetna_godina" value="' .$suma. '" id="iznos' .$brojac. '"/></td>
                                <td><input height="5" type="checkbox" class="konto_checkbox" id="pocetna' .$brojac. '" checked></td>
                            </tr>';
            }

            //AKO JE GODINA KLIZNA,TJ. POSTOJE DVE GODINE
            else {

                $brojac2++;

                //UPIS PODATAKA U POLJA TABELE
                $tabela2 .= '<tr>
                                <td id="grupa' .$brojac2. '">' .$grupa_konta. '</td>
                                <td id="sektor' .$brojac2. '">' .$sektor_konta. '</td>
                                <td id="konto' .$brojac2. '">' .$konto. '</td>
                                <td id="opis' .$brojac2. '">' .$naziv. '</td>
                                <td class="polje_iznos" id="original_iznos' .$brojac2.  '">' .$suma. '</td>
                                <td><input height="5" type="text" class="input_iznos krajnja_godina" value="' .$suma. '" id="iznos' .$brojac2. '"/></td>
                                <td><input height="5" type="checkbox" class="konto_checkbox" id="krajnja' .$brojac2. '" checked></td>
                            </tr>';
            }
        }

        //ZATVARANJE BODY SEKCIJE I TABELE 1
        $tabela1 .= '</tbody>
                </table>';

        //AKO POSTOJI TABELA 2
        if ($tabela2) {

            //ZATVARANJE BODY SEKCIJE I TABELE 2
            $tabela2 .= '</tbody>
            </table>';
        }

    
        //KREIRANJE NIZA U KOJI SE SMESTAJU TABELE I GODINE ZA TABOVE
        $niz_konta = array($tabela1, $tabela2, $tabovi);  

    } else {
        //KREIRANJE OBAVESTENJA,AKO NEMA REZULTATA ZA IZVRSEN UPIT
        $niz_konta['obavestenje'] = 'Nema rezultata za odabrani datum.';
    }
    echo json_encode($niz_konta);
}