<?php

/******
FUNKCIJA ZA OBRACUN TROSKOVA PO GRUPI I SEKTORU
PARAMETRI SU: NIZ SA TROSKOVIMA, DATUM ZA KOEFICIJENTE I PERIOD,TJ. KOJA JE GODINA
*******/
function obracun_troskova(array $niz_troskovi, $datum_za_koeficijente, $period) {

    //UKLJUCIVANJE KONEKCIJE KA AMSO BAZI
    require_once '../dodaci/konekcija_amso.php';

    //PROLAZAK KROZ NIZ SA SVIM TROSKOVIMA
    for ($i = 0; $i < count($niz_troskovi); $i++) {

        //KREIRANJE NIZOVA SA GRUPAMA I GRUPAMA+SEKTOR KONTA
        $mesta_troskova[] = $niz_troskovi[$i][2];
        $grupe_troskova[] = $niz_troskovi[$i][0];
    }
   
    //KREIRANJE NIZA SA JEDINSTVENIM GRUPAMA+SEKTOR I SETOVANJE INDEKSA DA KRECU OD NULE
    $mesta_troskova = array_unique($mesta_troskova);
    $mesta_troskova = array_values($mesta_troskova);



    //KREIRANJE NIZA SA JEDINSTVENIM GRUPAMA I SETOVANJE INDEKSA DA KRECU OD NULE
    $grupe_troskova = array_unique($grupe_troskova);
    $grupe_troskova = array_values($grupe_troskova);


    //KREIRANJE PRAZNOG NIZA CIJI SU INDEKSI JEDNAKI PUNOM MESTU TROSKA(GRUPA+SEKTOR)
    foreach ($mesta_troskova as $mesto_troska) {

        $sume_po_grupama[$mesto_troska] = array(

            
        );
    }


    //PROLAZAK KROZ NIZ SA SVIM TROSKOVIMA SA FRONTENDA
    foreach ($niz_troskovi as $stavke) {

        //PROLAZAK KROZ NIZ SA PUNIM MESTOM TROSKA(GRUPA+SEKTOR)
        foreach ($sume_po_grupama as $mesto => $grupa) {

            //AKO SU PUNA MESTA TROSKA(GRUPA+SEKTOR) JEDNAKA U OBA NIZA,UPISI VREDNOST STAVKE U NIZ ZA SUMIRANJE PO GRUPI+SEKTOR
            if ($stavke[2] == $mesto) {

                $sume_po_grupama[$mesto][] = $stavke[3];
            }
        }
    }

    //DODAVANJE NIZOVA SA SUMOM PO PUNOM MESTU TROSKA(GRUPA+SEKTOR) U NIZ KRAJNJA SUMA
    foreach ($sume_po_grupama as $mesto => $grupa) {

        //DOBIJANJE GRUPE I SEKTORA KONTA
        $grupa_konta = substr($mesto, 0, 3);
        $sektor = substr($mesto, 3, 1);

        $suma_po_grupi = array_sum($grupa);

        $krajnja_suma[] = array($mesto, $grupa_konta, $sektor,$suma_po_grupi);
        
    }

 


    //GRUPISANJE NIZOVA SA SUMOM ZA PUNA MESTA TROSKOVA(GRUPA+SEKTOR) PO GRUPAMA
    foreach ($krajnja_suma as $stavka) {

        foreach ($grupe_troskova as $grupa) {

            if ($stavka[1] == $grupa) {

                $krajnji_niz[$grupa][] = $stavka;
            }
        }
    }

   
    //AKO SE RADI OBRACUN TROSKOVA ZA POCETNU GODINU,SETUJ KLASU TABELE NA POCETNU GODINU
    if ($period == 'pocetna godina') {

        $tabela = '<table class="tabela_konta obracun_pocetna_godina" id="obracun_pocetna">'; 

        //OTVARANJE TABELE SA OBRACUNOM PO KOEFICIJENTIMA
        $tabela2 = '<table class="tabela_koeficijenti" id="koeficijenti_pocetna">
                        <tbody>';
    }

    //AKO SE RADI OBRACUN TROSKOVA ZA KRAJNJU GODINU,SETUJ KLASU TABELE NA KRAJNJU GODINU
    if ($period == 'krajnja godina') {

        $tabela = '<table class="tabela_konta obracun_krajnja_godina" id="obracun_krajnja">'; 

        //OTVARANJE TABELE SA OBRACUNOM PO KOEFICIJENTIMA
        $tabela2 = '<table class="tabela_koeficijenti" id="koeficijenti_krajnja">
                        <tbody>';
    }

    //OTVARANJE TABELE SA TROSKOVIMA PO GRUPAMA I SEKTORIMA
    $tabela .= '<thead>
                    <tr>
                        <th>Grupa</th>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>6</th>
                        <th>Suma po grupama</th>
                    </tr>
                </thead>
                <tbody>';

    //KREIRANJE PROMENJIVIH ZA UPIS SUME PO SEKTORIMA
    $suma_sektor_1 = 0;
    $suma_sektor_2 = 0;
    $suma_sektor_3 = 0;
    $suma_sektor_4 = 0;
    $suma_sektor_6 = 0;

    //KREIRANJE PROMENJIVE ZA UPIS KRAJNJE SUME
    $ukupna_suma = 0;

    //PROLAZAK KROZ NIZ SA JEDINSTVENIM GRUPAMA TROSKOVA
    foreach ($grupe_troskova as $grupa) {
                        
        //PROLAZAK KROZ NIZ SA SUMOM TROSKOVA PO GRUPAMA
        foreach ($krajnji_niz as $indeks => $stavka) {

            //AKO SU GRUPE U OBA NIZA JEDNAKE
            if ($indeks == $grupa) {

                //OTVORI RED ZA SVAKU GRUPU KOJA POSTOJI U NIZU SA TROSKOVIMA PO GRUPAMA
                $tabela .= '<tr>
                                <td class="naziv_grupe">' .$grupa. '</td>';

                //SETOVANJE POLJA TABELE DA BUDU PRAZNA PO DEFALT-U
                $polje_1 = '<td></td>';
                $polje_2 = '<td></td>';
                $polje_3 = '<td></td>';
                $polje_4 = '<td></td>';
                $polje_6 = '<td></td>';
                
                //INICIJALIZACIJA SUME PO GRUPAMA TROSKOVA
                $suma_grupe = 0;
                
                //PROLAZAK KROZ SVAKI PODNIZ U NIZU SA GRUPAMA TROSKOVA
                foreach ($stavka as $podniz) {

                    //SABIRANJE SUME ZA SVAKU GRUPU TROSKOVA
                    $suma_grupe += $podniz[3];

                    //AKO JE SEKTOR TROSKA 1,UVECAJ SUMU I UPISI IZNOS U ODGOVARAJUCE POLJE
                    if ($podniz[2] == '1') {

                        $suma_sektor_1 += $podniz[3];

                        $polje_1 = '<td class="centriraj_brojeve">' .number_format($podniz[3], 2). '</td>';
                    }

                    //AKO JE SEKTOR TROSKA 2,UVECAJ SUMU I UPISI IZNOS U ODGOVARAJUCE POLJE
                    if ($podniz[2] == '2') {

                        $suma_sektor_2 += $podniz[3];

                        $polje_2 = '<td class="centriraj_brojeve">' .number_format($podniz[3], 2). '</td>';
                    }

                    //AKO JE SEKTOR TROSKA 3,UVECAJ SUMU I UPISI IZNOS U ODGOVARAJUCE POLJE
                    if ($podniz[2] == '3') {

                        $suma_sektor_3 += $podniz[3];

                        $polje_3 = '<td class="centriraj_brojeve">' .number_format($podniz[3], 2). '</td>';
                    }

                    //AKO JE SEKTOR TROSKA 4,UVECAJ SUMU I UPISI IZNOS U ODGOVARAJUCE POLJE
                    if ($podniz[2] == '4') {

                        $suma_sektor_4 += $podniz[3];

                        $polje_4 = '<td class="centriraj_brojeve">' .number_format($podniz[3], 2). '</td>';
                    }

                    //AKO JE SEKTOR TROSKA 6,UVECAJ SUMU I UPISI IZNOS U ODGOVARAJUCE POLJE
                    if ($podniz[2] == '6') {

                        $suma_sektor_6 += $podniz[3];

                        $polje_6 = '<td class="centriraj_brojeve">' .number_format($podniz[3], 2). '</td>';
                    }
                }

                //DODAVANJE SUME GRUPE U KRAJNJU SUMU
                $ukupna_suma += $suma_grupe;

                //KREIRANJE POLJA SA SUMOM GRUPE
                $polje_suma = '<td class="centriraj_brojeve grupe_pozadina">' .number_format($suma_grupe, 2). '</td>';

                //DODAVANJE POLJA U TABELU
                $tabela .= $polje_1.$polje_2.$polje_3.$polje_4.$polje_6.$polje_suma;
                
                //ZATVARANJE REDA TABELE
                $tabela .= '</tr>';
            }
        }              
    }

    //DODAVANJE REDA TABELE SA SUMOM PO GRUPAMA I KRAJNJOM SUMOM
    $tabela .= '<tr>
                    <td class="suma_sektori">Suma po sektorima</td>
                    <td class="centriraj_brojeve siva_pozadina">' .number_format($suma_sektor_1, 2). '</td>
                    <td class="centriraj_brojeve siva_pozadina">' .number_format($suma_sektor_2, 2). '</td>
                    <td class="centriraj_brojeve siva_pozadina">' .number_format($suma_sektor_3, 2). '</td>
                    <td class="centriraj_brojeve siva_pozadina">' .number_format($suma_sektor_4, 2). '</td>
                    <td class="centriraj_brojeve siva_pozadina">' .number_format($suma_sektor_6, 2). '</td>
                    <td class="centriraj_brojeve total_pozadina">' .number_format($ukupna_suma, 2). '</td>
                </tr>';
     
    //ZATVARANJE BODY SEKCIJE I TABELE
    $tabela .= '</tbody>
            </table>';

 

    //INICIJALIZACIJA UKUPNOG KOEFICIJENTA
    $ukupan_koeficijent = 0;
    $suma_kljuc_puta_koeficijent = 0;
    $konacna_suma = 0;

    //UPIS DATUMA ZA KOEFICIJENTE U PROMENJIVU
    $datum_koef = $datum_za_koeficijente;


    //UPIT ZA DOBIJANJE KOEFICIJENATA PO SEKTORIMA
    $upit_koef = "SELECT koef_kljuc, ssektor FROM dev_kljuc WHERE dan = '$datum_koef'";

    //IZVRSAVANJE UPITA
    $rezultat_koef = pg_query($amso_konekcija, $upit_koef);

    //AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA,OBAVESTI KORISNIKA I PREKINI IZVRSAVANJE SKRIPTE
    if (!$rezultat_koef) {

        echo json_encode('Greška pri izvršavanju upita. Pokušajte ponovo.');
        die();
    } 

    //AKO UPIT VRATI REZULTATE
    if (pg_num_rows($rezultat_koef) > 0) {

        //DOK IMA REDOVA U BAZI,UPISI IH U NIZ ZA VRSTE OSIGURANJA
        while($red_koef = pg_fetch_array($rezultat_koef)) {

            //AKO JE SEKTOR UPRAVA
            if ($red_koef['ssektor'] == 'U') {

                //DODAVANJE KOEFICIJENTA PO KLJUCU U UKUPAN KOEFICIJENT
                $ukupan_koeficijent += $red_koef['koef_kljuc'];

                $suma_kljuc_puta_koeficijent += $suma_sektor_6 * $red_koef['koef_kljuc'];

                $konacna_suma += $suma_sektor_1 + $suma_sektor_6 * $red_koef['koef_kljuc'];

                $tabela2 .= '<tr>
                                <td class="bold">Uprava</td>
                                <td>'. number_format($red_koef['koef_kljuc'], 6) .'</td>
                                <td>'. number_format($suma_sektor_6 * $red_koef['koef_kljuc'], 2) .'</td>
                                <td>'. number_format($suma_sektor_1 + $suma_sektor_6 * $red_koef['koef_kljuc'], 2) .'</td>
                            </tr>';
            }

            //AKO JE SEKTOR PRIBAVA
            if ($red_koef['ssektor'] == 'P') {

                //DODAVANJE KOEFICIJENTA PO KLJUCU U UKUPAN KOEFICIJENT
                $ukupan_koeficijent += $red_koef['koef_kljuc'];

                $suma_kljuc_puta_koeficijent += $suma_sektor_6 * $red_koef['koef_kljuc'];

                $konacna_suma += $suma_sektor_2 + $suma_sektor_6 * $red_koef['koef_kljuc'];

                $tabela2 .= '<tr>
                                <td class="bold">Pribava</td>
                                <td>'. number_format($red_koef['koef_kljuc'], 6) .'</td>
                                <td>'. number_format($suma_sektor_6 * $red_koef['koef_kljuc'], 2) .'</td>
                                <td>'. number_format($suma_sektor_2 + $suma_sektor_6 * $red_koef['koef_kljuc'], 2) .'</td>
                            </tr>';
            }

            //AKO JE SEKTOR LIKVIDACIJA
            if ($red_koef['ssektor'] == 'L') {

                //DODAVANJE KOEFICIJENTA PO KLJUCU U UKUPAN KOEFICIJENT
                $ukupan_koeficijent += $red_koef['koef_kljuc'];

                $suma_kljuc_puta_koeficijent += $suma_sektor_6 * $red_koef['koef_kljuc'];

                $konacna_suma += $suma_sektor_3 + $suma_sektor_6 * $red_koef['koef_kljuc'];

                $tabela2 .= '<tr>
                                <td class="bold">Likvidacija</td>
                                <td>'. number_format($red_koef['koef_kljuc'], 6) .'</td>
                                <td>'. number_format($suma_sektor_6 * $red_koef['koef_kljuc'], 2) .'</td>
                                <td>'. number_format($suma_sektor_3 + $suma_sektor_6 * $red_koef['koef_kljuc'], 2) .'</td>
                            </tr>';
            }

            //AKO JE SEKTOR DEPONOVANJE
            if ($red_koef['ssektor'] == 'D') {

                //DODAVANJE KOEFICIJENTA PO KLJUCU U UKUPAN KOEFICIJENT
                $ukupan_koeficijent += $red_koef['koef_kljuc'];

                $suma_kljuc_puta_koeficijent += $suma_sektor_6 * $red_koef['koef_kljuc'];

                $konacna_suma += $suma_sektor_4 + $suma_sektor_6 * $red_koef['koef_kljuc'];

                $tabela2 .= '<tr>
                                <td class="bold">Deponovanje</td>
                                <td>'. number_format($red_koef['koef_kljuc'], 6) .'</td>
                                <td>'. number_format($suma_sektor_6 * $red_koef['koef_kljuc'], 2) .'</td>
                                <td>'. number_format($suma_sektor_4 + $suma_sektor_6 * $red_koef['koef_kljuc'], 2) .'</td>
                            </tr>';
            }    
        }
    }


    //DODAVANJE REDA SA SUMOM U TABELU
    $tabela2 .= '<tr class="poslednji_red">
                    <td class="suma_koef">Suma</td>
                    <td class="podebljaj">'. number_format($ukupan_koeficijent, 2) .'</td>
                    <td class="podebljaj">'. number_format($suma_kljuc_puta_koeficijent, 2) .'</td>
                    <td class=" podebljaj konacna_suma centriraj_iznos">'. number_format($konacna_suma, 2) .'</td>
                </tr>';
    

    //ZATVARANJE BODY SEKCIJE I TABELE
    $tabela2 .= '</tbody>
            </table>';
     
    //DODAVANJE TABELA U HTML OUTPUT        
    $html = $tabela.$tabela2;
    
    echo json_encode($html);
}

//AKO JE POSLAT NIZ SA TROSKOVIMA SA FRONTENDA
if (isset($_POST['niz_slanje'])) {

    //IZBACIVANJE SLASH KARAKTERA IZ DOBIJENOG NIZA
    $niz_troskovi = stripslashes($_POST['niz_slanje']);

    //PRETVARANJE DOBIJENOG NIZA U PHP ASOCIJATIVNI NIZ
    $niz_troskovi = json_decode($niz_troskovi);

    //POZIVANJE FUNKCIJE ZA OBRACUN TROSKOVA PO GRUPI I SEKTORU I PROSLEDJIVANJE PARAMETARA
    obracun_troskova($niz_troskovi, $_POST['datum_za_koeficijente'], $_POST['period']);
}


/******
FUNKCIJA ZA OBRACUN TROSKOVA PRIBAVE
PARAMETRI SU: NIZ SA TROSKOVIMA PRIBAVE, NIZ SA TROSKOVIMA KLJUCA, KRAJNJI DATUM POCETNE GODINE, KRAJNJI DATUM DRUGE GODINE
*******/
function obracun_pribave(array $niz_sektor_2, array $niz_sektor_6, $period_pocetna_godina = null, $period_krajnja_godina = null) 
{
    //UKLJUCIVANJE KONEKCIJE KA AMSO BAZI
    require_once '../dodaci/konekcija_amso.php';

    //DEKLARISANJE PROMENJIVE ZA CUVANJE DATUMA ZA KOEFICIJENTE
    $datum_koeficijenti = '';

    //UPIT ZA DOBIJANJE VRSTE OSIGURANJA ZA SVA KONTA KOJA POCINJU NA 53,54,55 I GDE JE SEKTOR,TJ. MESTO TROSKA 2
    $upit_vrste_osiguranja = "SELECT DISTINCT konto, vrsta_osiguranja FROM bu_noviji";

    //AKO JE NA FRONTENDU KLIKNUTO NA PRIBAVU ZA POCETNU GODINU
    if (isset($period_pocetna_godina)) {

        //OTVARANJE TABELE SA TROSKOVIMA PRIBAVE
        $tabela = '<table class="tabela_pribava izvoz_excel" id="tabela_pribava_pocetna">
                    <thead>
                        <tr>
                            <th>VO</th>
                            <th>Konto</th>
                            <th>Opis</th>
                            <th width="20%">Čisti trošak</th>
                        </tr>
                    </thead>';
        
        //DOBIJANJE GODINE IZ DATUMA
        $krajnji_period = explode('.', $period_pocetna_godina);
        $krajnja_godina = $krajnji_period[2];

        //KREIRANJE NIZA SA KVARTALIMA I DOBIJANJE MESECA IZ DATUMA
        $kvartali = array("1"=> array($krajnja_godina."-01-01", $krajnja_godina."-03-31"), "2"=>array($krajnja_godina."-04-01", $krajnja_godina."-06-30"), "3"=>array($krajnja_godina."-07-01", $krajnja_godina."-09-30"), "4"=>array($krajnja_godina."-10-01", $krajnja_godina."-12-31"));
        $mesec = $krajnji_period[1];

        //DOBIJANJE POCETNOG DATUMA KVARTALA
        $pocetak_kvartala = date($kvartali[ceil($mesec/3)][0]);

        //FORMATIRANJE DATUMA ZA UPIT NAD TABELOM DEV_KOEF
        $datum_koeficijenti = $krajnji_period[2] .'-'. $krajnji_period[1] .'-'. $krajnji_period[0];

        //NASTAVLJANJE UPITA
        $upit_vrste_osiguranja .= " WHERE datum = '$pocetak_kvartala'";

        //OTVARANJE BODY SEKCIJE SA ODGOVARAJUCIM ID-JEM
        $tabela .= '<tbody id="pribava_pocetna_godina">';
    }

    //AKO JE NA FRONTENDU KLIKNUTO NA PRIBAVU ZA KRAJNJU GODINU
    if (isset($period_krajnja_godina)) {

        //OTVARANJE TABELE SA TROSKOVIMA PRIBAVE
        $tabela = '<table class="tabela_pribava izvoz_excel" id="tabela_pribava_krajnja">
                    <thead>
                        <tr>
                            <th>VO</th>
                            <th>Konto</th>
                            <th>Opis</th>
                            <th width="20%">Čisti trošak</th>
                        </tr>
                    </thead>';

        //DOBIJANJE GODINE IZ DATUMA
        $krajnji_period2 = explode('.', $period_krajnja_godina);
        $krajnja_godina2 = $krajnji_period2[2];

        //KREIRANJE NIZA SA KVARTALIMA I DOBIJANJE MESECA IZ DATUMA
        $kvartali2 = array("1"=> array($krajnja_godina2."-01-01", $krajnja_godina2."-03-31"), "2"=>array($krajnja_godina2."-04-01", $krajnja_godina2."-06-30"), "3"=>array($krajnja_godina2."-07-01", $krajnja_godina2."-09-30"), "4"=>array($krajnja_godina2."-10-01", $krajnja_godina2."-12-31"));
        $mesec2 = $krajnji_period2[1];

        //DOBIJANJE POCETNOG DATUMA KVARTALA
        $pocetak_kvartala2 = date($kvartali2[ceil($mesec2/3)][0]);

        //FORMATIRANJE DATUMA ZA UPIT NAD TABELOM DEV_KOEF
        $datum_koeficijenti = $krajnji_period2[2] .'-'. $krajnji_period2[1] .'-'. $krajnji_period2[0];

        //NASTAVLJANJE UPITA
        $upit_vrste_osiguranja .= " WHERE datum = '$pocetak_kvartala2'";

        //OTVARANJE BODY SEKCIJE SA ODGOVARAJUCIM ID-JEM
        $tabela .= '<tbody id="pribava_krajnja_godina">';
    }

    //ZAVRSETAK UPITA
    $upit_vrste_osiguranja .= " AND konto LIKE ANY (array['53_2%', '54_2%', '55_2%']) ORDER BY konto";

    //IZVRSAVANJE UPITA
    $rezultat_vo = pg_query($amso_konekcija, $upit_vrste_osiguranja);

    //AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA,OBAVESTI KORISNIKA I PREKINI IZVRSAVANJE SKRIPTE
    if (!$rezultat_vo) {

        echo json_encode('Greška pri izvršavanju upita. Pokušajte ponovo.');
        die();
    } 

    //AKO UPIT VRATI REZULTATE
    if (pg_num_rows($rezultat_vo) > 0) {

        //DOK IMA REDOVA U BAZI,UPISI IH U NIZ ZA VRSTE OSIGURANJA
        while($red_vo = pg_fetch_array($rezultat_vo)) {

            $niz_vo[] = $red_vo;
        }
    }

    $suma_pribava = 0;

    //PROLAZAK KROZ NIZ SA KONTIMA GDE JE SEKTOR 2
    foreach ($niz_sektor_2 as $stavka) {

        //PROLAZAK KROZ NIZ SA VRSTIMA OSIGURANJA
        foreach ($niz_vo as $vo_stavka) {
            
            //AKO JE KONTO U NIZU ZA SEKTOR 2 JEDNAK KONTU U NIZU SA VRSTAMA OSIGURANJA
            if ($vo_stavka['konto'] == $stavka[2]) {

                //UPISIVANJE VRSTE OSIGURANJA U PROMENJIVU
                $vrsta_osiguranja = $vo_stavka['vrsta_osiguranja'];

                //KREIRANJE NIZA SA TROSKOVIMA PO VRSTI OSIGURANJA
                $niz_troskovi_po_vo[] = array($stavka[4], $vrsta_osiguranja);
            }
        }

        $suma_pribava += $stavka[4];

        //UPIS PODATAKA U CELIJE TABELE
        $tabela .= '<tr>
                        <td>' .$vrsta_osiguranja. '</td>
                        <td>' .$stavka[2]. '</td>
                        <td>' .$stavka[3]. '</td>
                        <td class="pribava_iznos">' .number_format($stavka[4], 2). '</td>
                    </tr>';
    }

    //ZATVARANJE BODY SEKCIJE I TABELE
    $tabela .= '</tbody>
            </table>';

    //UPIT ZA DINAMICKO DOBIJANJE VRSTA OSIGURANJA IZ BAZE
    $upit_vrste = "SELECT DISTINCT a.vrsta_osiguranja FROM dev_koef as a 
                    INNER JOIN sifarnici.vrste_osiguranja_nbs AS b ON (a.vrsta_osiguranja = b.cela_sifra) WHERE dan = (SELECT MAX(dan) FROM dev_koef) ORDER BY vrsta_osiguranja";
    
    //IZVRSAVANJE UPITA
    $rezultat_vrste = pg_query($amso_konekcija, $upit_vrste);

    //AKO UPIT VRATI REZULTATE
    if (pg_num_rows($rezultat_vrste) > 0) {

        //DOK IMA REDOVA U BAZI,UPISI IH U NIZ ZA VRSTE OSIGURANJA
        while($red_vrste = pg_fetch_array($rezultat_vrste)) {

            $jedinstvene_vo[] = $red_vrste['vrsta_osiguranja'];
        }
    }


    //PROLAZAK KROZ NIZ SA JEDINSTVENIM VRSTAMA OSIGURANJA
    foreach ($jedinstvene_vo as $vo) {
    
        //PROLAZAK KROZ NIZ SA TROSKOVIMA PO VRSTI OSIGURANJA
        foreach ($niz_troskovi_po_vo as $clan) {
        
            //AKO SU VRSTE OSIGURANJA JEDNAKE
            if ($clan[1] == $vo) {

                //KREIRAJ NIZ SA TROSKOVIMA PO VRSTI OSIGURANJA(INDEKSI NIZA SU VO)
                $troskovi_po_vo[$vo][] = $clan[0];
            }
            else {
                $troskovi_po_vo[$vo][] = 0;
            }

        }
    }

    //PROLAZAK KROZ NIZ SA TROSKOVIMA PO VRSTI OSIGURANJA
    foreach ($troskovi_po_vo as $vo => $trosak) {

        //IZRACUNAVANJE SUME ZA SVAKU VRSTU OSIGURANJA
        $suma_po_vo = array_sum($trosak);

        //KREIRANJE NIZA SA SUMAMA PO VRSTI OSIGURANJA(INDEKSI NIZA SU VO)
        $niz_suma_po_vo[$vo] = array($suma_po_vo);
    }
   
    //KREIRANJE TABELE SA SUMAMA PO VRSTAMA OSIGURANJA
    $tabela_suma = '<table class="tabela_suma izvoz_excel" id="tabela_suma">
                        <thead>
                            <tr>
                                <th>VO</th>
                                <th>Suma po VO</th>
                            </tr>
                        </thead>
                        <tbody>';

    //INICIJALIZACIJA TOTALNE SUME PO VO
    $total_vo = 0;

    //PROLAZAK KROZ NIZ SA JEDINSTVENIM VRSTAMA OSIGURANJA
    foreach ($jedinstvene_vo as $vrsta_osig) {

        //PROLAZAK KROZ NIZ SA SUMAMA PO VRSTAMA OSIGURANJA
        foreach ($niz_suma_po_vo as $vo_indeks => $suma_vo) {

            //AKO SU VRSTE OSIGURANJA JEDNAKE
            if($vo_indeks == $vrsta_osig) {

                //UPIS SUME PO VO U PROMENJIVU
                $vo_iznos = $suma_vo[0];

                //DODAVANJE SUME PO VO U TOTALNU SUMU PO VO
                $total_vo += $suma_vo[0];
            }       
        }

        //UPIS PODATAKA U CELIJE TABELE
        $tabela_suma .= '<tr>
                            <td>' .$vrsta_osig. '</td>
                            <td class="druga_kolona">' .number_format($vo_iznos, 2). '</td>
                        </tr>';
    }

    //DODAVANJE POSLEDNJEG REDA U TABELU
    $tabela_suma .= '<tr>
                        <td class="total_vo">TOTAL</td>
                        <td class="total">' .number_format($total_vo, 2). '</td>
                    </tr>';

    
    //ZATVARANJE BODY SEKCIJE I TABELE
    $tabela_suma .= '</tbody>
                </table>';

    //KREIRANJE NIZA SA HTML OUTPUTOM I DODAVANJE TABELA U NJEGA
    $html = array();
   


    //KREIRANJE TABELE SA TROSKOVIMA PO KLJUCU
    $tabela_kljuc = '<table class="tabela_kljuc izvoz_excel" id="tabela_kljuc">
                        <thead>
                            <tr>
                                <th>Konto</th>
                                <th>Opis</th>
                                <th>Iznos</th>
                            </tr>
                        </thead>';

    if (isset($period_pocetna_godina)) {

        $tabela_kljuc .= '<tbody id="kljuc_pocetna_godina">';
    }

    if (isset($period_krajnja_godina)) {
        
        $tabela_kljuc .= '<tbody id="kljuc_krajnja_godina">';
    }

    //INICIJALIZACIJA SUME PO KLJUCU
    $suma_kljuc = 0;

    //PROLAZAK KROZ NIZ SA TROSKOVIMA PO KLJUCU
    foreach ($niz_sektor_6 as $trosak6) {

        //DODAVANJE IZNOSA TROSKA U SUMU
        $suma_kljuc += $trosak6[2];

        //UPIS PODATAKA U CELIJE TABELE
        $tabela_kljuc .= '<tr>
                            <td>' .$trosak6[0]. '</td>
                            <td>' .$trosak6[1]. '</td>
                            <td class="kljuc_iznos">' .number_format($trosak6[2], 2). '</td>
                        </tr>';

    }

    $tabela_kljuc .= '<tr>
                        <td colspan="2" class="pozadina_kljuc">SUMA KLJUČ</td>
                        <td id="suma_kljuc" class="kljuc_iznos">' .number_format($suma_kljuc, 2). '</td>
                    </tr>';

    //ZATVARANJE BODY SEKCIJE I TABELE
    $tabela_kljuc .= '</tbody>
                </table>';



    //KREIRANJE TABELE SA TROSKOVIMA PO KLJUCU
    $tabela_obracun_pribave = '<table class="tabela_obracun_pribave izvoz_excel" id="pribava_plus_kljuc">
                                <thead>
                                    <tr>
                                        <th>Šifra VO</th>
                                        <th>Koeficijent pribava</th>
                                        <th>Ključ pribava</th>
                                        <th>Direktno pribava</th>
                                        <th>Direktno + Ključ</th>
                                    </tr>
                                </thead>
                                <tbody>';

    //UPIT ZA DOBIJANJE KOEFICIJENTA PRIBAVE
    $upit_tabela_koef = "SELECT koef_kljuc, ssektor FROM dev_kljuc WHERE dan = '$datum_koeficijenti'";
    $rezultat_tabela_koef = pg_query($amso_konekcija, $upit_tabela_koef);

    //AKO UPIT VRATI REZULTATE
    if (pg_num_rows($rezultat_tabela_koef) > 0) {

        //INICIJALIZACIJA SUME KOEFICIJENATA
        $ukupan_koef = 0;

        //OTVARANJE TABELE SA KOEFICIJENTIMA
        $tabela_koef = '<table class="tabela_koef" id="tabela_koef">
                            <body>';

        //DOK IMA REDOVA U BAZI
        while ($red_tabela_koef = pg_fetch_array($rezultat_tabela_koef)) {

            //DODAVANJE VREDNOSTI KOEFICIJENATA U SUMU
            $ukupan_koef += $red_tabela_koef['koef_kljuc'];

            //AKO JE SEKTOR UPRAVA
            if ($red_tabela_koef['ssektor'] == 'U') {

                $tabela_koef .= '<tr>
                                    <td class="sektor">Uprava</td>
                                    <td class="koef_desno">' .number_format($red_tabela_koef['koef_kljuc'], 6). '</td>
                                </tr>';
            }

            //AKO JE SEKTOR PRIBAVA
            if ($red_tabela_koef['ssektor'] == 'P') {

                //UPIS KOEFICIJENTA PRIBAVE U PROMENJIVU
                $koef_pribava = number_format($red_tabela_koef['koef_kljuc'], 6);

                $tabela_koef .= '<tr>
                                    <td class="sektor">Pribava</td>
                                    <td class="koef_desno">' .$koef_pribava. '</td>
                                </tr>';
            }

            //AKO JE SEKTOR LIKVIDACIJA
            if ($red_tabela_koef['ssektor'] == 'L') {

                $tabela_koef .= '<tr>
                                    <td class="sektor">Likvidacija</td>
                                    <td class="koef_desno">' .number_format($red_tabela_koef['koef_kljuc'], 6). '</td>
                                </tr>';
            }

            //AKO JE SEKTOR DEPONOVANJE
            if ($red_tabela_koef['ssektor'] == 'D') {

                $tabela_koef .= '<tr>
                                    <td class="sektor">Deponovanje</td>
                                    <td class="koef_desno">' .number_format($red_tabela_koef['koef_kljuc'], 6). '</td>
                                </tr>';
            }
        }

        //DODAVANJE POSLEDNJEG REDA U TABELU
        $tabela_koef .= '<tr>
                            <td class="suma_sektor">SUMA</td>
                            <td class="total koef_desno">' .number_format($ukupan_koef, 6). '</td>
                        </tr>';

        //ZATVARANJE BODY SEKCIJE I TABELE
        $tabela_koef .= '</body>
                    </table>';
    }

    $tabela_suma .= '<br>' .$tabela_koef;


    //UPIT ZA DOBIJANJE KOEFICIJENATA PRIBAVE ZA KRAJ ODABRANOG PERIODA
    $upit_koeficijenti_pribave_po_vo = "SELECT vrsta_osiguranja, koeficijent FROM dev_koef WHERE dan = '$datum_koeficijenti' AND sektor = 'P' ORDER BY vrsta_osiguranja";

    //IZVRSAVANJE UPITA
    $rezultat_koeficijenti_po_vo = pg_query($amso_konekcija, $upit_koeficijenti_pribave_po_vo);

    //AKO DODJE DO GRESKE PRI IZVRSAVANJU UPITA,OBAVESTI KORISNIKA I PREKINI IZVRSAVANJE SKRIPTE
    if (!$rezultat_koeficijenti_po_vo) {

        echo json_encode('Greška pri izvršavanju upita. Pokušajte ponovo.');
        die();
    } 

    //AKO UPIT VRATI REZULTATE
    if (pg_num_rows($rezultat_koeficijenti_po_vo) > 0) {

        //INICIJALIZACIJA PROMENJIVE ZA ZBIR KOEFICIJENATA
        $zbir_koeficijenti_po_vo = 0;
        $suma_kljuc_pribava = 0;
        $suma_direktno_pribava = 0;
        $suma_direktno_plus_kljuc = 0;

        //DOK IMA REDOVA U BAZI,UPISI IH U NIZ ZA VRSTE OSIGURANJA
        while ($red_koeficijenti_po_vo = pg_fetch_array($rezultat_koeficijenti_po_vo)) {

            foreach ($niz_suma_po_vo as $vo_indeks => $suma_vo) {

                if ($vo_indeks == $red_koeficijenti_po_vo['vrsta_osiguranja']) {

                    $direktno_pribava = $suma_vo[0];
                }
                
            }

            //IZRACUNAVANJE KLJUCA PRIBAVE
            $kljuc_pribava = $suma_kljuc * $koef_pribava * $red_koeficijenti_po_vo['koeficijent'];

            $direktno_plus_kljuc = number_format($kljuc_pribava + $direktno_pribava, 2);
            
            //UPIS PODATAKA U CELIJE TABELE
            $tabela_obracun_pribave .= '<tr>
                                            <td class="pribava_centriraj vrsta_osiguranja_pocetna">' .$red_koeficijenti_po_vo[0]. '</td>
                                            <td class="pribava_centriraj">' .number_format($red_koeficijenti_po_vo['koeficijent'], 9). '</td>
                                            <td class="pribava_iznosi">' .number_format($kljuc_pribava, 2). '</td>
                                            <td class="pribava_iznosi">' .number_format($direktno_pribava, 2). '</td>
                                            <td class="pribava_iznosi direktno_plus_kljuc_pocetna">' .$direktno_plus_kljuc. '</td>
                                        </tr>';

            $kljuc_pribava2 = number_format($suma_kljuc * $koef_pribava * $red_koeficijenti_po_vo['koeficijent'], 2);


            $zbir_koeficijenti_po_vo += $red_koeficijenti_po_vo['koeficijent'];
            $suma_kljuc_pribava += str_replace(',', '', $kljuc_pribava2);
            $suma_direktno_pribava += $direktno_pribava;
            $suma_direktno_plus_kljuc += str_replace(',', '', $direktno_plus_kljuc);
        }
    }

    $tabela_obracun_pribave .= '<tr>
                                    <td class="total_vo vrsta_osiguranja_pocetna">UKUPNO</td>
                                    <td class="pribava_centriraj total">' .number_format($zbir_koeficijenti_po_vo, 9). '</td>
                                    <td class="pribava_iznosi total">' .number_format($suma_kljuc_pribava, 2). '</td>
                                    <td class="pribava_iznosi total">' .number_format($suma_direktno_pribava, 2). '</td>
                                    <td class="pribava_iznosi total direktno_plus_kljuc_pocetna">' .number_format($suma_direktno_plus_kljuc, 2). '</td>
                                </tr>';
    
    //ZATVARANJE BODY SEKCIJE I TABELE
    $tabela_obracun_pribave .= '</tbody>
                             </table>';

    array_push($html, $tabela, $tabela_suma, $tabela_kljuc, $tabela_obracun_pribave);
    
    echo json_encode($html);
}


//AKO JE U POST ZAHTEVU PROSLEDJEN PARAMETAR ZA TROSKOVE PRIBAVE
if (isset($_POST['funkcija']) && $_POST['funkcija'] == 'troskovi pribave') {

    //IZBACIVANJE SLASH KARAKTERA IZ DOBIJENIH NIZOVA
    $niz_sektor_2 = stripslashes($_POST['niz_sektor2']);
    $niz_sektor_6 = stripslashes($_POST['niz_sektor6']);

    //PRETVARANJE DOBIJENIH NIZOVA U PHP ASOCIJATIVNE NIZOVE
    $niz_sektor_2 = json_decode($niz_sektor_2,true);
    $niz_sektor_6 = json_decode($niz_sektor_6,true);

    //AKO SE RADI OBRACUN ZA POCETNU GODINU
    if (isset($_POST['period_pocetna_godina'])) {

        //POZIVANJE FUNKCIJE ZA OBRACUN PRIBAVE
        obracun_pribave($niz_sektor_2, $niz_sektor_6, $_POST['period_pocetna_godina']);
    }

    //AKO SE RADI OBRACUN ZA KRAJNJU GODINU
    if (isset($_POST['period_krajnja_godina'])) {

        //POZIVANJE FUNKCIJE ZA OBRACUN PRIBAVE
        obracun_pribave($niz_sektor_2, $niz_sektor_6, null, $_POST['period_krajnja_godina']);
    }
    
}

/******
FUNKCIJA ZA OBRACUN AVR-A
PARAMETRI SU: 
-ZBIR POCETNA GODINA (TROSKOVI DIREKTNO PRIBAVA + KLJUC)
-ZBIR KRAJNJA GODINA (TROSKOVI DIREKTNO PRIBAVA + KLJUC)
-DATUM ZA UPIT,KOJIM SE DOBIJAJU SUME PRENOSNE PREMIJE PO VO I SUME PREMIJE,KOJE IMAJU PRENOSNU PO VO,
-GODINA AVR STANJA,ZA DINAMICKO ODREDJIVANJE PRETHODNE GODINE GLAVNE KNJIGE ZA DOBIJANJE AVR-A
-TABELA POCETNI DATUM,ZA DINAMICKI UPIS DATUMA U POLJE DIREKTNO + KLJUC POCETNE GODINE U ZAGLAVLJU TABELE SA OBRACUNOM AVR-A
-TABELA KRAJNJI DATUM,ZA DINAMICKI UPIS DATUMA U POLJE DIREKTNO + KLJUC KRAJNJE GODINE U ZAGLAVLJU TABELE SA OBRACUNOM AVR-A
*******/
function obracunaj_avr(array $zbir_pocetna_god, array $zbir_krajnja_god = array(), $datum_za_upit, $godina_avr_stanja = null, $tabela_pocetni_datum, $tabela_krajnji_datum = null)
{
    ///UKLJUCIVANJE KONEKCIJE KA AMSO BAZI
    require_once '../dodaci/konekcija_amso.php';

    //UPIS KRAJNJEG DATUMA SA FRONTENDA U PROMENJIVU
    $datum_za_upit = $datum_za_upit;

    //UPIT ZA DOBIJANJE SUME PRENOSNIH PREMIJA PO VRSTAMA OSIGURANJA
    $upit_suma_prenosna = "WITH jedinstvene_vrste_osiguranja AS (

                                    SELECT DISTINCT a.vrsta_osiguranja FROM dev_koef as a 
                                    INNER JOIN sifarnici.vrste_osiguranja_nbs AS b ON (a.vrsta_osiguranja = b.cela_sifra) 
                                    WHERE dan = (SELECT MAX(dan) FROM dev_koef) ORDER BY vrsta_osiguranja

                            ),
                            obracun AS(

                                SELECT DISTINCT substring(konto FROM 4 FOR 2) AS vrsta_osiguranja, SUM(prenosna) as suma_prenosna FROM prenosna.pp_$datum_za_upit
                                GROUP BY vrsta_osiguranja
                                ORDER BY vrsta_osiguranja
                            )
                            SELECT jvo.vrsta_osiguranja, 

                            CASE 
                                WHEN suma_prenosna IS NULL THEN 0.00 
                                ELSE suma_prenosna 
                            END AS iznos

                            FROM jedinstvene_vrste_osiguranja AS jvo
                            LEFT OUTER JOIN obracun AS o
                            ON jvo.vrsta_osiguranja = o.vrsta_osiguranja 
                            ORDER BY jvo.vrsta_osiguranja";


    //UPIT ZA DOBIJANJE SUME PREMIJA KOJE IMAJU PRENOSNU PO VRSTAMA OSIGURANJA
    $upit_premija_sa_prenosnom = "WITH sve_vrste_osiguranja AS (

                                        SELECT DISTINCT a.vrsta_osiguranja FROM dev_koef as a 
                                        INNER JOIN sifarnici.vrste_osiguranja_nbs AS b ON (a.vrsta_osiguranja = b.cela_sifra) 
                                        WHERE dan = (SELECT MAX(dan) FROM dev_koef) ORDER BY vrsta_osiguranja

                                    ),
                                    obracun_premije_sa_prenosnom AS(

                                        SELECT DISTINCT substring(konto FROM 4 FOR 2) AS vrsta_osiguranja, SUM(premija) AS suma_premija FROM prenosna.pp_$datum_za_upit
                                        WHERE prenosna > 0
                                        GROUP BY vrsta_osiguranja
                                        ORDER BY vrsta_osiguranja
                                    )
    
                                    SELECT vo.vrsta_osiguranja, 
                                    CASE WHEN suma_premija IS NULL THEN 0.00 ELSE suma_premija END AS iznos_prenosna
                                    FROM sve_vrste_osiguranja AS vo
                                    LEFT OUTER JOIN obracun_premije_sa_prenosnom AS op
                                    ON vo.vrsta_osiguranja = op.vrsta_osiguranja 
                                    ORDER BY vo.vrsta_osiguranja ";

    //IZVRSAVANJE UPITA
    $rezultat_suma_prenosna = pg_query($amso_konekcija, $upit_suma_prenosna);
    $rezultat_premija_sa_prenosnom = pg_query($amso_konekcija, $upit_premija_sa_prenosnom);
    
    //AKO UPIT VRATI REZULTATE
    if (pg_num_rows($rezultat_suma_prenosna) > 0) {

        //DOK IMA REDOVA U BAZI,UPISI IH U NIZ ZA SUMU PRENOSNIH PREMIJA PO VO
        while($red_suma_prenosna = pg_fetch_array($rezultat_suma_prenosna)) {

            $niz_prenosna_premija[$red_suma_prenosna['vrsta_osiguranja']] = $red_suma_prenosna['iznos'];
        }
    }

    //AKO UPIT VRATI REZULTATE
    if (pg_num_rows($rezultat_premija_sa_prenosnom) > 0) {

        //DOK IMA REDOVA U BAZI,UPISI IH U NIZ ZA SUMU PREMIJA KOJE IMAJU PRENOSNU PO VO
        while($red_premija_sa_prenosnom = pg_fetch_array($rezultat_premija_sa_prenosnom)) {

            $niz_premija_sa_prenosnom[$red_premija_sa_prenosnom['vrsta_osiguranja']] = $red_premija_sa_prenosnom['iznos_prenosna'];
        }
    }


    //PROLAZAK KROZ PRVI NIZ
    foreach ($niz_prenosna_premija as $vo1 => $stavka) {

        //PROLAZAK KROZ DRUGI NIZ
        foreach ($niz_premija_sa_prenosnom as $vo2 => $stavka2) {

            //AKO SU INDEKSI,TJ. VRSTE OSIGURANJA JEDNAKI
            if ($vo1 == $vo2) {

                //AKO JE REZULTAT DELJENJA NULA,UPISI NULU KAO KOEFICIJENT ZA VRSTU OSIGURANJA
                if ($stavka == 0 || $stavka2 == 0) {

                    $niz_koeficijenti[$vo1] = number_format(0, 15);
                }
                //U SUPROTNOM UPISI VREDNOST KOEFICIJENTA ZA VRSTU OSIGURANJA I FORMATIRAJ NA 15 CIFARA
                else {

                    $niz_koeficijenti[$vo1] = number_format($stavka / $stavka2, 15);
                }
            }
        }
    }

    //OTVARANJE TABELE ZA OBRACUN AVR-A
    $tabela_obracun_avr = '<table class="ukupni_obracun_avr" id="ukupni_obracun_avr">
                            <thead>
                                <tr>
                                    <th>Šifra VO</th>';
    
    //UPIS POCETNOG DATUMA I IZDVOJENOG KRAJNJEG DELA DATUMA U PROMENJIVE
    $pocetni_datum_zaglavlje = $tabela_pocetni_datum;
    $poslednji_deo_datuma = substr($pocetni_datum_zaglavlje, 14);

    //AKO JE GODINA KLIZNA,TJ. POSTOJI I KRAJNJA GODINA
    if(count($zbir_krajnja_god)) {

        //UPIS KRAJNJEG DATUMA I IZDVOJENOG KRAJNJEG DELA DATUMA U PROMENJIVE
        $krajnji_datum_zaglavlje = $tabela_krajnji_datum;
        $krajnji_deo_datuma = substr($krajnji_datum_zaglavlje, 14);

        //DODAVANJE KOLONA U ZAGLAVLJE TABELE 
        $tabela_obracun_avr .= '<th>Direktno + Ključ ' .$pocetni_datum_zaglavlje. '</th>
                                <th>Direktno + Ključ ' .$krajnji_datum_zaglavlje. '</th>
                                <th>Ukupno troškovi bez AVR</th>
                                <th>Prenosna / Premija</th>
                                <th>AVR ' .$krajnji_deo_datuma. '</th>
                                <th>AVR stanje ' .$poslednji_deo_datuma. '</th>
                                <th>Razlika-doknjižavanje ' .$krajnji_deo_datuma. '</th>
                                </tr>
                            </thead>
                        <tbody>';
    }
    //AKO GODINA NIJE KLIZNA
    else {

        //DODAVANJE KOLONA U ZAGLAVLJE TABELE 
        $tabela_obracun_avr .= '<th>Ukupno troškovi bez AVR</th>
                                <th>Prenosna / Premija</th>
                                <th>AVR ' .$poslednji_deo_datuma. '</th>
                                </tr>
                            </thead>
                        <tbody>';
    }


    //INICIJALIZACIJA ZBIRA DIREKTNO + KLJUC OBE GODINE
    $ukupno_troskovi_bez_avr = 0;

    //AKO POSTOJI SAMO JEDNA GODINA,TJ. GODINA NIJE KLIZNA
    if(count($zbir_pocetna_god) && !count($zbir_krajnja_god)) {

        //INICIJALIZACIJA PROMENJIVIH ZA SUME
        $suma_prva_god = 0;
        $suma_avr_prva_god = 0;

        //PROLAZAK KROZ NIZ SA IZNOSIMA IZ POCETNE GODINE
        foreach ($zbir_pocetna_god as $indeks_pocetna => $iznos_pocetna) {

            //DODAVANJE POLJA SA VRSTOM OSIGURANJA U TABELU
            $tabela_obracun_avr .= '<tr>
                                        <td class="prva_kolona">' .$iznos_pocetna[0]. '</td>';   

                //DODAVANJE IZNOSA U SUMU PRVE GODINE
                $suma_prva_god += str_replace(',', '', $iznos_pocetna[1]);

                //UPIS IZNOSA POCETNE GODINE U POLJE TABELE
                $tabela_obracun_avr .= '<td class="centriraj_desno">' .$iznos_pocetna[1]. '</td>';       
    
            //PROLAZAK KROZ NIZ SA KOEFICIJENTIMA PO VRSTI OSIGURANJA
            foreach ($niz_koeficijenti as $indeks_koef => $koef) {

                //AKO SU INDEKSI JEDNAKI
                if ($indeks_koef == $iznos_pocetna[0]) {


                    //FORMATIRANJE IZNOSA I IZRACUNAVANJE AVR-A ZA KRAJNJU GODINU
                    $iznos_pocetna[1] = str_replace(',', '', $iznos_pocetna[1]);
                    $avr = number_format($iznos_pocetna[1] * $koef, 2);

                    //UPIS KOEFICIJENTA I IZNOSA AVR-A PO VO U TABELU
                    $tabela_obracun_avr .= '<td class="centriraj_desno">' .$koef. '</td>
                                            <td class="centriraj_desno">' .$avr. '</td>'; 
                    
                    
                    //DODAVANJE IZNOSA AVR-A U UKUPNU SUMU AVR-A
                    $suma_avr_prva_god += str_replace(',', '', $avr);
                }
            }
            
            //ZATVARANJE REDA TABELE
            $tabela_obracun_avr .= '</tr>';
        }

           //DODAVANJE POSLEDNJEG REDA SA SUMAMA U TABELU
           $tabela_obracun_avr .= '<tr>
                                        <td class="ukupno_avr">UKUPNO</td>
                                        <td class="centriraj_desno total_avr">' .number_format($suma_prva_god, 2). '</td>
                                        <td class="centriraj_desno total_avr"></td>
                                        <td class="centriraj_desno total_avr">' .number_format($suma_avr_prva_god, 2). '</td>
                                    </tr>';
    }



    //AKO JE GODINA KLIZNA,TJ. POSTOJI I KRAJNJA GODINA
    if(count($zbir_pocetna_god) && count($zbir_krajnja_god)) {

        //UPIS POCETNE GODINE U PROMENJIVU ZA GODINU AVR-A
        //$godina_avr_stanja = $_POST['godina_avr_stanja'];

        //UPIT ZA DOBIJANJE AVR-A PRETHODNE GODINE PO VRSTAMA OSIGURANJA
        $upit_avr_prethodna_godina = "SELECT substring(konto FROM 5 FOR 2) AS vrsta_osiguranja, SUM(duguje) FROM g$godina_avr_stanja WHERE konto LIKE ANY (array['274%']) 
                                    GROUP BY vrsta_osiguranja
                                    ORDER BY vrsta_osiguranja";

        //IZVRSAVANJE UPITA
        $rezultat_avr_prethodna_godina = pg_query($amso_konekcija, $upit_avr_prethodna_godina);
            
        //AKO UPIT VRATI REZULTATE
        if (pg_num_rows($rezultat_avr_prethodna_godina) > 0) {

            //DOK IMA REDOVA U BAZI,UPISI IH U NIZ ZA SUMU AVR-A PO VO ZA PRETHODNU GODINU
            while($red_avr_prethodna_godina = pg_fetch_array($rezultat_avr_prethodna_godina)) {

                $niz_avr_prethodna_godina[$red_avr_prethodna_godina['vrsta_osiguranja']] = $red_avr_prethodna_godina['sum'];
            }
        }

        //AKO NEMA AVR-A ZA VRSTU OSIGURANJA 07, DODAJ INDEKS U NIZ SA VREDNOSCU NULA
        if (!array_key_exists('07', $niz_avr_prethodna_godina)) {

            $niz_avr_prethodna_godina['07'] = 0;
        }

        //INICIJALIZACIJA PROMENJIVIH ZA SUME
        $suma_prva = 0;
        $suma_krajnja = 0;
        $suma_troskovi_bez_avr = 0;
        $suma_avr = 0;
        $suma_avr_prethodna = 0;
        $razlika_doknjizavanje = 0;

        //PROLAZAK KROZ NIZ SA IZNOSIMA IZ POCETNE GODINE
        foreach ($zbir_pocetna_god as $indeks_pocetna => $iznos_pocetna) {

            //DODAVANJE POLJA SA VRSTOM OSIGURANJA U TABELU
            $tabela_obracun_avr .= '<tr>
                                        <td class="prva_kolona">' .$iznos_pocetna[0]. '</td>';   
        
    
            //DODAVANJE IZNOSA U ZBIR DIREKTNO + KLJUC OBE GODINE
            $ukupno_troskovi_bez_avr += str_replace(',', '', $iznos_pocetna[1]);

            //DODAVANJE IZNOSA U SUMU PRVE GODINE
            $suma_prva += str_replace(',', '', $iznos_pocetna[1]);

            //UPIS IZNOSA POCETNE GODINE U POLJE TABELE
            $tabela_obracun_avr .= '<td class="centriraj_desno">' .$iznos_pocetna[1]. '</td>';  

            
            //PROLAZAK KROZ NIZ SA IZNOSIMA IZ KRAJNJE GODINE
            foreach ($zbir_krajnja_god as $indeks_krajnja => $iznos_krajnja) {

                //AKO SU INDEKSI JEDNAKI
                if($iznos_pocetna[0] == $iznos_krajnja[0]) {

                    //DODAVANJE IZNOSA U ZBIR DIREKTNO + KLJUC OBE GODINE
                    $ukupno_troskovi_bez_avr += str_replace(',', '', $iznos_krajnja[1]);

                    //DODAVANJE IZNOSA U SUMU KRAJNJE GODINE
                    $suma_krajnja += str_replace(',', '', $iznos_krajnja[1]);

                    $zbir_obe_godine = str_replace(',', '', $iznos_pocetna[1]) + str_replace(',', '', $iznos_krajnja[1]);

                    $suma_troskovi_bez_avr += $zbir_obe_godine;

                    //UPIS IZNOSA POCETNE GODINE U POLJE TABELE
                    $tabela_obracun_avr .= '<td class="centriraj_desno">' .$iznos_krajnja[1]. '</td>
                                            <td class="centriraj_desno">' .number_format($zbir_obe_godine, 2). '</td>';
                }                                           
            }
            
            //PROLAZAK KROZ NIZ SA KOEFICIJENTIMA
            foreach ($niz_koeficijenti as $indeks_koef => $koef) {

                //AKO SU INDEKSI JEDNAKI
                if ($indeks_koef == $iznos_pocetna[0]) {

                    //MNOZENJE ZBIRA OBE GODINE SA KOEFICIJENTIMA I DOBIJANJE AVR-A
                    $zbir_obe_godine = str_replace(',', '', $zbir_obe_godine);
                    $avr = $zbir_obe_godine * $koef;

                    //DODAVANJE IZNOSA AVR-A U UKUPNU SUMU AVR-A
                    $suma_avr += $avr;

                    //UPIS KOEFICIJENATA I AVR-A U TABELU
                    $tabela_obracun_avr .= '<td class="centriraj_desno">' .$koef. '</td>
                                            <td class="centriraj_desno">' .number_format($avr, 2). '</td>'; 
                }
            }

            //PROLAZAK KROZ NIZ SA AVR-OM ZA PRETHODNU GODINU
            foreach ($niz_avr_prethodna_godina as $avr_vo => $iznos_avr) {

                //AKO SU INDEKSI JEDNAKI
                if ($iznos_pocetna[0] == $avr_vo) {

                    $iznos_avr_polje = number_format($iznos_avr, 2);

                    //UPIS KOEFICIJENATA I AVR-A U TABELU
                    $tabela_obracun_avr .= '<td class="centriraj_desno">' .$iznos_avr_polje. '</td>';

                    $suma_avr_prethodna += str_replace(',', '', $iznos_avr_polje);
                }
            }

            $iznos_avr_polje = str_replace(',', '', $iznos_avr_polje);
            $razlika_doknjizavanje = $avr - $iznos_avr_polje;

            $tabela_obracun_avr .= '<td class="centriraj_desno">' .number_format($razlika_doknjizavanje, 2). '</td>';
            
            //ZATVARANJE REDA TABELE
            $tabela_obracun_avr .= '</tr>';
        }

        //DODAVANJE POSLEDNJEG REDA SA SUMAMA U TABELU
        $tabela_obracun_avr .= '<tr>
                                    <td class="ukupno_avr">UKUPNO</td>
                                    <td class="centriraj_desno total_avr">' .number_format($suma_prva, 2). '</td>
                                    <td class="centriraj_desno total_avr">' .number_format($suma_krajnja, 2). '</td>
                                    <td class="centriraj_desno total_avr">' .number_format($suma_troskovi_bez_avr, 2). '</td>
                                    <td class="total_avr"></td>
                                    <td class="centriraj_desno total_avr">' .number_format($suma_avr, 2). '</td>
                                    <td class="centriraj_desno total_avr">' .number_format($suma_avr_prethodna, 2). '</td>
                                    <td class="centriraj_desno total_avr">' .number_format($suma_avr - $suma_avr_prethodna, 2). '</td>
                                </tr>';
    }

        
    //ZATVARANJE BODY SEKCIJE I TABELE
    $tabela_obracun_avr .= '</tbody>
                        </table>';

    echo json_encode($tabela_obracun_avr);  
}

//AKO JE U POST ZAHTEVU PROSLEDJEN PARAMETAR ZA UKUPNI OBRACUN AVR-A
if (isset($_POST['naziv_funkcije']) && $_POST['naziv_funkcije'] == 'ukupni_obracun_avr') {

    //AKO GODINA NIJE KLIZNA
    if (!isset($_POST['godina_avr_stanja'])) {

        //POZIVANJE FUNKCIJE I PROSLEDJIVANJE ODGOVARAJUCIH PARAMETARA
        obracunaj_avr($_POST['zbir_pocetna_god'], $zbir_krajnja_god = array(), $_POST['datum_za_upit'], $godina_avr_stanja = null, $_POST['tabela_pocetni_datum'], $tabela_krajnji_datum = null);
    }
    //AKO JE GODINA KLIZNA
    else {

        //POZIVANJE FUNKCIJE I PROSLEDJIVANJE ODGOVARAJUCIH PARAMETARA
        obracunaj_avr($_POST['zbir_pocetna_god'], $_POST['zbir_krajnja_god'], $_POST['datum_za_upit'], $_POST['godina_avr_stanja'], $_POST['tabela_pocetni_datum'], $_POST['tabela_krajnji_datum']);
    }
}
