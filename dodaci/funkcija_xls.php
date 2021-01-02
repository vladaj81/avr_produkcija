<?php
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 600);

date_default_timezone_set('Europe/Belgrade');


//AKO POSTOJI POST ZAHTEV
if (isset($_POST)) {

	//AKO SU PROSLEDJENI PODACI ZA POCETNU GODINU
	if (isset($_POST['troskovi_pocetna'])) {

		if (PHP_SAPI == 'cli')
			die('This example should only be run from a Web Browser');

		require_once '../../../php_excel_master/Classes/PHPExcel.php';

	

		// Ukoliko je uklju?en 'magic_quotes', onda poskidaj vi?ak karaktere iz dolaznih promenljivih 
		if (get_magic_quotes_gpc()) 
		{
			function undoMagicQuotes($array, $topLevel=true) 
			{
				$newArray = array();
				foreach($array as $key => $value) {
					if (!$topLevel) {
						$key = stripslashes($key);
					}
					if (is_array($value)) {
						$newArray[$key] = undoMagicQuotes($value, false);
					}
					else {
						$newArray[$key] = stripslashes($value);
					}
				}
				return $newArray;
			}
			$_GET = undoMagicQuotes($_GET);
			$_POST = undoMagicQuotes($_POST);
			$_COOKIE = undoMagicQuotes($_COOKIE);
			$_REQUEST = undoMagicQuotes($_REQUEST);
		}


		//KREIRANJE PHPExcel OBJEKTA
		$objPHPExcel = new PHPExcel();

		//PODESAVANJE PROPERTIJA DOKUMENTA
		$objPHPExcel->getProperties()->setCreator("AMS Osiguranje a.d.o.")
									->setLastModifiedBy("AMS Osiguranje a.d.o.")
									->setTitle("XLS")
									->setSubject(utf8_encode("XLS tabela"))
									->setDescription("Opis")
									->setKeywords("XLs")
									->setCategory("Test");

		//PODESAVANJE FONTA
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11)->setBold(false);
		//$objPHPExcel->getDefaultStyle()->getFont()->setName('Times')->setSize(11)->setBold(false);


		// $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory;
		// $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
		//$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;

		//PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

		//RASTAVLJANJE POCETNOG DATUMA U NIZ
		$datum_pocetni = explode('-', $_POST['datum_pocetna_godina']);

		//DINAMICKO KREIRANJE NASLOVA ZA PRVI TAB
		$naslov_prvi_tab = 'Troškovi ' .substr($datum_pocetni[0], 0, 6) .' -'. $datum_pocetni[1];

		//KREIRANJE DATUMA ZA TAB PRIBAVA PRVE GODINE
		$datum_pribava_pocetna = 'Pribava ' .substr($datum_pocetni[1], 7, 4);

		//PROMENA IMENA PRVOM STYLESHEETU
		$objPHPExcel->getActiveSheet()->setTitle($naslov_prvi_tab);



		//IZBACIVANJE SLASH KARAKTERA IZ DOBIJENOG NIZA
		$niz_xls = stripslashes($_POST['troskovi_pocetna']);
	
		//KREIRANJE NIZA ZA GENERISANJE TABELE SA TROSKOVIMA ZA POCETNU GODINU
		$niz_xls = json_decode($niz_xls,true);

		//KREIRANJE KOLONA ZA PRVU TABELU
		$kolone_za_xls = array('Grupa', 'Sektor', 'Konto','Opis', 'Iznos');
		$broj_kolona = count($kolone_za_xls);

		//KREIRANJE NIZA ZA GENERISANJE TABELE SA OBRACUNATIM TROSKOVIMA ZA POCETNU GODDINU
		$niz_xls2 = $_POST['obracun_pocetni_niz'];

		//KREIRANJE KOLONA ZA DRUGU TABELU
		$kolone_za_xls2 = array('GRUPA', '1', '2', '3', '4', '6', 'SUMA PO GRUPAMA');
		$broj_kolona2 = count($kolone_za_xls2);

		//KREIRANJE NIZA ZA GENERISANJE TABELE SA KOEFICIJENTIMA ZA POCETNU GODDINU
		$niz_xls3 = $_POST['koeficijenti_pocetni_niz'];

		//KREIRANJE KOLONA ZA TRECU TABELU
		$kolone_za_xls3 = array('Koeficijent', 'Čist trošak', 'Deo ključ', 'Ukupno');
		$broj_kolona3 = count($kolone_za_xls3);

		//DOBIJANJE BROJA REDOVA ZA SVE NIZOVE
		$broj_redova_svih = count($niz_xls);
		$broj_redova_svih2 = count($niz_xls2);
		$broj_redova_svih3 = count($niz_xls3);


		//PRVI TAB - OBRACUN TROSKOVA ZA POCETNU GODINU

		//DOK IMA REDOVA U NIZU SA TROSKOVIMA
		for ($j = -1; $j < $broj_redova_svih; $j++) 
		{
			
			if ($j ==-1) 
			{
				$i = 0;

				//UNESI NASLOV ZA SVAKU KOLONU PRVE TABELE
				foreach ($kolone_za_xls as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}
				
				//ISKLJUCIVANJE AUTOSIZE NAD KOLONOM D I ZADAVANJE SIRINE
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(false);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth("100");

				//PORAVNANJE SADRZAJA E KOLONE U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("E")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//SETOVANJE TEKSTA U ZAGLAVLJU DA BUDE BOLD
				$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
			}
			else
			{
				$i = 0;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_za_xls as $key => $value)
				{
					
					$vrednost_za_celiju = $niz_xls[$j][$key];
				

					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(0);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(0);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(0);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$poslednja_pozicija = $broj_redova_svih + 1;

				//PREBACIVANJE SVIH VREDNOSTI U KOLONI E U DOUBLE FORMAT
				$objPHPExcel->getActiveSheet()->getStyle('E1:'.'E'.$poslednja_pozicija)->getNumberFormat()->setFormatCode(
					PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
				);

				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls)+1)."1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU REDOVA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls)-1).(count($niz_xls)+1))->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//OBOJ CELIJE
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls)-1)."1")->applyFromArray(
						array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('argb' => 'FFCCFFCC')
								)
						)
				);
			}
		}


		//DOK IMA REDOVA U NIZU SA OBRACUNOM TROSKOVA
		for ($j = -1; $j < $broj_redova_svih2; $j++) 
		{

			if ($j == -1) 
			{
				$i = 6;

				//UNESI NASLOV ZA SVAKU KOLONU TABELE SA OBRACUNOM TROSKOVA
				foreach ($kolone_za_xls2 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}
				
				//ISKLJUCIVANJE AUTOSIZE NAD KOLONOM M I ZADAVANJE SIRINE
				$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(false);
				$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth("20");

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('G1:M1')->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getStyle('M')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('G1:M1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}

			else
			{
				$i = 6;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_za_xls2 as $key => $value)
				{

					$vrednost_za_celiju = $niz_xls2[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(0);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(0);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(0);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('G1:M1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('G1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls2)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija = $broj_redova_svih2 + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("H2:M".$krajnja_pozicija)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//SETUJ TEKST U CELIJI DA BUDE BOLD
				$objPHPExcel->getActiveSheet()->getStyle('G'.$krajnja_pozicija.':L'.$krajnja_pozicija)->getFont()->setBold(true);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('G1:M'.$krajnja_pozicija)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);
			}
		}

		
		//DOK IMA REDOVA U NIZU SA KOEFICIJENTIMA
		for ($j = -1; $j < $broj_redova_svih3; $j++) 
		{
			
			if ($j ==-1) 
			{
				$i = 6;

				//UNESI NASLOV ZA SVAKU KOLONU TABELE SA KOEFICIJENTIMA
				foreach ($kolone_za_xls3 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i, $j+2+25, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE TEKSTA U ZAGLAVLJU DA BUDE BOLD
				$objPHPExcel->getActiveSheet()->getStyle('G26:J26')->getFont()->setBold(true);
			}
			
			else
			{
				$i = 6;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_za_xls3 as $key => $value)
				{

					$vrednost_za_celiju = $niz_xls3[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(0);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(0);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(0);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE
					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValueByColumnAndRow($i, $j+2+25, $vrednost_za_celiju);
					$i++;

					//KREIRAJ BORDERE
					$objPHPExcel->getActiveSheet()->getStyle('G26:J31')->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
					);

					//SETOVANJE TEKSTA U ZAGLAVLJU DA BUDE BOLD I CENTRIRANJE
					$objPHPExcel->getActiveSheet()->getStyle('G26:J26')->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getStyle('J27:J31')->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getStyle('G26:J26')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

					//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
					$objPHPExcel->getActiveSheet()->getStyle("H27:J31")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				}

				//OBOJ CELIJE ZAGLAVLJA TABELE
				$objPHPExcel->getActiveSheet()->getStyle('G26:J26')->applyFromArray(
						array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('argb' => 'FFCCFFCC')
								)
						)
				);
			}
		}
	

		//DRUGI TAB - OBRACUN PRIBAVE ZA POCETNU GODINU

		//UKLANJANJE SLASH-EVA IZ DOBIJENOG NIZA
		$troskovi_pribava_xls = stripslashes($_POST['troskovi_pribava_pocetna']);

		//KREIRANJE NIZA ZA GENERISANJE TABELE SA TROSKOVIMA ZA POCETNU GODINU
		$troskovi_pribava_xls = json_decode($troskovi_pribava_xls, true);

		//UKLANJANJE SLASH-EVA IZ DOBIJENOG NIZA
		$obracun_pribava_xls = stripslashes($_POST['obracun_pribava_pocetna']);

		//KREIRANJE NIZA ZA GENERISANJE TABELE SA OBRACUNOM PRIBAVE ZA POCETNU GODINU
		$obracun_pribava_xls = json_decode($obracun_pribava_xls, true);

		//KREIRANJE KOLONA ZA PRVU TABELU
		$kolone_troskovi_xls = array('Grupa', 'Sektor', 'Konto','Opis', 'Iznos');
		$broj_kolona_troskovi = count($kolone_troskovi_xls);

		//KREIRANJE KOLONA ZA DRUGU TABELU
		$kolone_obracun_xls = array('VO', 'Konto', 'Opis', 'Čisti Trošak');
		$broj_kolona_obracun = count($kolone_obracun_xls);

		//KREIRANJE NIZOVA ZA GENERISANJE OSTALIH TABELA U TABU ZA POCETNU GODINU
		$suma_pocetna_xls = $_POST['suma_pocetna'];
		$koef_pocetna_xls = $_POST['koef_pocetna'];
		$kljuc_pocetna_xls = $_POST['kljuc_pocetna'];
		$zbir_pocetna_xls = $_POST['pribava_kljuc_pocetna'];

		//KREIRANJE KOLONA ZA TRECU TABELU
		$kolone_suma_xls = array('VO', 'Suma po VO');
		$broj_kolona_suma = count($kolone_suma_xls);

		//KREIRANJE KOLONA ZA TABELU SA KOEFICIJENTIMA
		$kolone_koef_xls = array('Sektor', 'Koeficijent');
		$broj_kolona_koef = count($kolone_koef_xls);
		
		//KREIRANJE KOLONA ZA TABELU SA TROSKOVIMA SA KLJUCA
		$kolone_kljuc_xls = array('Konto', 'Opis', 'Iznos');
		$broj_kolona_kljuc = count($kolone_kljuc_xls);

		//KREIRANJE KOLONA ZA TABELU SA TROSKOVIMA PRIBAVA + KLJUC
		$kolone_zbir_xls = array('Šifra VO', 'Koeficijent Pribava', 'Ključ pribava', 'Direktno pribava', 'Direktno + Ključ');
		$broj_kolona_zbir = count($kolone_zbir_xls);

		//DOBIJANJE BROJA REDOVA ZA SVE NIZOVE
		$broj_redova_troskovi = count($troskovi_pribava_xls);
		$broj_redova_obracun = count($obracun_pribava_xls);
		$broj_redova_suma = count($suma_pocetna_xls);
		$broj_redova_koef = count($koef_pocetna_xls);
		$broj_redova_kljuc = count($kljuc_pocetna_xls);
		$broj_redova_zbir = count($zbir_pocetna_xls);

		//KREIRANJE NOVOG STYLESHEETA
		$objWorkSheet = $objPHPExcel->createSheet(1);
		$objWorkSheet->setTitle($datum_pribava_pocetna);
		

		//DOK IMA REDOVA U NIZU SA TROSKOVIMA
		for ($j = -1; $j < $broj_redova_troskovi; $j++) 
		{
			
			if ($j ==-1) 
			{
				$i = 0;

				//UNESI NASLOV ZA SVAKU KOLONU PRVOG SHEET-A
				foreach ($kolone_troskovi_xls as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}
				
				//ISKLJUCIVANJE AUTOSIZE NAD KOLONOM D I ZADAVANJE SIRINE
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(false);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth("100");

				//PORAVNANJE SADRZAJA E KOLONE U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("E")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//SETOVANJE TEKSTA U ZAGLAVLJU DA BUDE BOLD
				$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
			}
			else
			{
				$i = 0;

				//FORMATIRAJ VREDNOSTI ZA CELIJE PRVOG SHEET-A
				foreach ($kolone_troskovi_xls as $key => $value)
				{

					$vrednost_za_celiju = $troskovi_pribava_xls[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_troskovi_xls)+1)."1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_troskovi_xls)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU REDOVA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_troskovi_xls)-1).(count($troskovi_pribava_xls)+1))->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//OBOJ CELIJE
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_troskovi_xls)-1)."1")->applyFromArray(
						array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('argb' => 'FFCCFFCC')
								)
						)
				);
			}
		}


		//DOK IMA REDOVA U NIZU SA OBRACUNOM PRIBAVE
		for ($j = -1; $j < $broj_redova_obracun; $j++) 
		{

			if ($j == -1) 
			{
				$i = 6;

				//UNESI NASLOV ZA SVAKU KOLONU TABELE SA OBRACUNOM TROSKOVA
				foreach ($kolone_obracun_xls as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}
				
				//ISKLJUCIVANJE AUTOSIZE NAD KOLONOM M I ZADAVANJE SIRINE
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(false);
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth("100");

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('G1:J1')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('G1:J1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}

			else
			{
				$i = 6;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_obracun_xls as $key => $value)
				{

					$vrednost_za_celiju = $obracun_pribava_xls[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('G1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('G1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_obracun_xls)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_obracun = $broj_redova_obracun + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("J2:J".$krajnja_pozicija_obracun)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('G1:J'.$krajnja_pozicija_obracun)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);
			}
		}


		//DOK IMA REDOVA U NIZU SA SUMOM PO VO
		for ($j = -1; $j < $broj_redova_suma; $j++) 
		{

			if ($j == -1) 
			{
				$i = 11;

				//UNESI NASLOV ZA SVAKU KOLONU TABELE SA SUMOM PO VO
				foreach ($kolone_suma_xls as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('L1:M1')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('L1:M1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}

			else
			{
				$i = 11;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_suma_xls as $key => $value)
				{

					$vrednost_za_celiju = $suma_pocetna_xls[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('L1:M1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('L1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_suma_xls)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_suma = $broj_redova_suma + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("M2:M".$krajnja_pozicija_suma)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('L1:M'.$krajnja_pozicija_suma)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('L'.$krajnja_pozicija_suma.':M'.$krajnja_pozicija_suma)->getFont()->setBold(true);
			}
		}


		//DOK IMA REDOVA U NIZU SA KOEFICIJENTIMA
		for ($j = -1; $j < $broj_redova_koef; $j++) 
		{

			if ($j == -1) 
			{
				$i = 11;
				
				//UNESI NASLOV ZA SVAKU KOLONU TABELE SA SUMOM PO VO
				foreach ($kolone_koef_xls as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow($i, $j+2+19, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('L20:M20')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('L20:M20')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}
			
			else
			{
				$i = 11;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_koef_xls as $key => $value)
				{

					$vrednost_za_celiju = $koef_pocetna_xls[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValueByColumnAndRow($i, $j+2+19, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('L20:M20')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('L20:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_suma_xls)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_koef = $broj_redova_koef + 1 +19;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("M21:M".$krajnja_pozicija_koef)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('L20:M'.$krajnja_pozicija_koef)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('L'.$krajnja_pozicija_koef.':M'.$krajnja_pozicija_koef)->getFont()->setBold(true);
				
			}
			
		}


		//DOK IMA REDOVA U NIZU SA TROSKOVIMA SA KLJUCA
		for ($j = -1; $j < $broj_redova_kljuc; $j++) 
		{
		
			if ($j == -1) 
			{
				$i = 14;

				//UNESI NASLOV ZA SVAKU KOLONU
				foreach ($kolone_kljuc_xls as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('O1:Q1')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('O1:Q1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}
		
			
			else
			{
				$i = 14;

				//IZMENA POSLEDNJEG NIZA,DA IMA 2 UMESTO 3 CLANA,ZBOG BROJA KOLONA U EXCELU
				if ($j == $broj_redova_kljuc - 1) {

					$iznos = $kljuc_pocetna_xls[$j][1];
					$kljuc_pocetna_xls[$j][0] = 'SUMA KLJUČ';
					$kljuc_pocetna_xls[$j][1] = '';
					$kljuc_pocetna_xls[$j][2] = $iznos;
				}

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_kljuc_xls as $key => $value)
				{
					$vrednost_za_celiju = $kljuc_pocetna_xls[$j][$key];

					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('O1:Q1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('O1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_kljuc_xls)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
				
				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_kljuc = $broj_redova_kljuc + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("Q2:Q".$krajnja_pozicija_kljuc)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('O1:Q'.$krajnja_pozicija_kljuc)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('O'.$krajnja_pozicija_kljuc.':Q'.$krajnja_pozicija_kljuc)->getFont()->setBold(true);

				$objPHPExcel->getActiveSheet()->mergeCells('O'.$krajnja_pozicija_kljuc.':P'.$krajnja_pozicija_kljuc);
				
			}
			
		}	


		//DOK IMA REDOVA U NIZU DIREKTNO + KLJUC
		for ($j = -1; $j < $broj_redova_zbir; $j++) 
		{

			if ($j == -1) 
			{
				$i = 18;

				//UNESI NASLOV ZA SVAKU KOLONU
				foreach ($kolone_zbir_xls as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}
				
				//ISKLJUCIVANJE AUTOSIZE NAD KOLONOM M I ZADAVANJE SIRINE
				//$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(false);
				//$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth("100");

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('S1:W1')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('S1:W1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}

			else
			{
				$i = 18;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_zbir_xls as $key => $value)
				{

					$vrednost_za_celiju = $zbir_pocetna_xls[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(1);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(1)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('S1:W1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('S1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_zbir_xls)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_zbir = $broj_redova_zbir + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("T2:W".$krajnja_pozicija_zbir)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('S1:W'.$krajnja_pozicija_zbir)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('S'.$krajnja_pozicija_zbir.':W'.$krajnja_pozicija_zbir)->getFont()->setBold(true);
			}
		}

		//AKO GODINA NIJE KLIZNA,GENERISI TABELU SA OBRACUNOM AVR-A SAMO ZA JEDNU GODINU
		if ($_POST['godina_klizna'] == 'false') {

			//KREIRANJE NIZA SA OBRACUNOM AVR-A ZA POCETNU GODINU
			$avr_xls = $_POST['avr_pocetna'];

			//DINAMICKO KREIRANJE NASLOVA KOLONE
			$naslov_cetvrta_kolona = 'AVR  ' .$datum_pocetni[1];

			//KREIRANJE KOLONA ZA TABELU SA AVR-OM
			$kolone_avr_xls = array('Šifra VO', 'Ukupno troškovi bez AVR','Prenosna/Premija', $naslov_cetvrta_kolona);
			$broj_kolona_avr = count($kolone_avr_xls);

			//DOBIJANJE BROJA REDOVA ZA NIZ SA OBRACUNOM AVR-A
			$broj_redova_avr = count($avr_xls);

			//KREIRANJE NOVOG STYLESHEETA
			$objWorkSheet = $objPHPExcel->createSheet(2);
			$objWorkSheet->setTitle('Obračun AVR');



			//TRECI TAB - OBRACUN AVR-A
			
			//DOK IMA REDOVA U NIZU SA OBRACUNOM AVR-A
			for ($j = -1; $j < $broj_redova_avr; $j++) 
			{

				if ($j == -1) 
				{
					$i = 0;

					//UNESI NASLOV ZA SVAKU KOLONU
					foreach ($kolone_avr_xls as $key => $value) 
					{
						//FORMATIRANJE CELIJA
						$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow($i, $j+2, $value);
						$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
						$i++;
					}

					//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
					$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getFont()->setBold(true);

					//OBOJ CELIJE ZAGLAVLJA
					$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray(
						array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('argb' => 'FFCCFFCC')
								)
						)
					);
				}
				
				else
				{
					$i = 0;

					//FORMATIRAJ VREDNOSTI ZA CELIJE
					foreach ($kolone_avr_xls as $key => $value)
					{

						$vrednost_za_celiju = $avr_xls[$j][$key];
						
						if (gettype($vrednost_za_celiju) == 'integer') {

							$objPHPExcel->setActiveSheetIndex(2);   
							$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
							$objPHPExcel->getActiveSheet()
							->getStyle($kolona_i_red)
							->getNumberFormat()
							->setFormatCode(
								PHPExcel_Style_NumberFormat::FORMAT_NUMBER
							);
						}
						elseif (gettype($vrednost_za_celiju) == 'double') {

							$objPHPExcel->setActiveSheetIndex(2);   
							$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
							$objPHPExcel->getActiveSheet()
							->getStyle($kolona_i_red)
							->getNumberFormat()
							->setFormatCode(
								PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
							);
						}
						else
						{
							$objPHPExcel->setActiveSheetIndex(2);   
							$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
							$objPHPExcel->getActiveSheet()
							->getStyle($kolona_i_red)
							->getNumberFormat()
							->setFormatCode(
								PHPExcel_Style_NumberFormat::FORMAT_TEXT
							);
						}
						
						//UPISI VREDNOSTI U CELIJE
						$objPHPExcel->setActiveSheetIndex(2)
						->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
						$i++;
					}
					
					//PORAVNAJ SADRZAJ CELIJA
					$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_avr_xls)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

					//PODESI VISINU CELIJA
					$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
					$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

					//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
					$krajnja_pozicija_avr = $broj_redova_avr + 1;

					//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
					$objPHPExcel->getActiveSheet()->getStyle("B2:D".$krajnja_pozicija_avr)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

					//KREIRAJ BORDERE
					$objPHPExcel->getActiveSheet()->getStyle('A1:D'.$krajnja_pozicija_avr)->applyFromArray(
							array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
					);

					//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
					$objPHPExcel->getActiveSheet()->getStyle('A'.$krajnja_pozicija_avr.':D'.$krajnja_pozicija_avr)->getFont()->setBold(true);
				}
			}
		}
	}
	


	//AKO JE GODINA KLIZNA
	if (isset($_POST['troskovi_krajnja'])) {


		//RASTAVLJANJE KRAJNJEG DATUMA U NIZ
		$datum_krajnji = explode('-', $_POST['datum_krajnja_godina']);

		//DINAMICKO KREIRANJE NASLOVA ZA DRUGI TAB
		$naslov_drugi_tab = 'Troškovi ' .substr($datum_krajnji[0], 0, 6) .' -'. $datum_krajnji[1];

		//KREIRANJE DATUMA ZA TAB PRIBAVA KRAJNJE GODINE
		$datum_pribava_krajnja = 'Pribava ' .substr($datum_krajnji[1], 7, 4);

		//KREIRANJE NOVOG STYLESHEETA
		$objWorkSheet = $objPHPExcel->createSheet(2);
		$objWorkSheet->setTitle($naslov_drugi_tab);

		//UKLANJANJE SLASH-EVA IZ DOBIJENOG NIZA
		$niz_xls4 = stripslashes($_POST['troskovi_krajnja']);
		
		//KREIRANJE NIZA ZA GENERISANJE TABELE SA TROSKOVIMA ZA KRAJNJU GODINU
		$niz_xls4 = json_decode($niz_xls4,true);

		//KREIRANJE KOLONA ZA PRVU TABELU
		$kolone_za_xls4 = array('Grupa', 'Sektor', 'Konto', 'Opis', 'Iznos');
		$broj_kolona4 = count($kolone_za_xls4);

		//KREIRANJE NIZA ZA GENERISANJE TABELE SA OBRACUNATIM TROSKOVIMA ZA KRAJNJU GODDINU
		$niz_xls5 = $_POST['obracun_krajnji_niz'];

		//KREIRANJE KOLONA ZA DRUGU TABELU
		$kolone_za_xls5 = array('GRUPA', '1', '2', '3', '4', '6', 'SUMA PO GRUPAMA');
		$broj_kolona5 = count($kolone_za_xls5);

		//KREIRANJE NIZA ZA GENERISANJE TABELE SA KOEFICIJENTIMA ZA KRAJNJU GODDINU
		$niz_xls6 = $_POST['koeficijenti_krajnji_niz'];

		//KREIRANJE KOLONA ZA TRECU TABELU
		$kolone_za_xls6 = array('Koeficijent', 'Čist trošak', 'Deo ključ', 'Ukupno');
		$broj_kolona6 = count($kolone_za_xls6);

		//DOBIJANJE BROJA REDOVA ZA SVE NIZOVE
		$broj_redova_svih4 = count($niz_xls4);
		$broj_redova_svih5 = count($niz_xls5);
		$broj_redova_svih6 = count($niz_xls6);


		//DOK IMA REDOVA U NIZU SA TROSKOVIMA
		for ($j = -1; $j < $broj_redova_svih4; $j++) 
		{
			
			if ($j ==-1) 
			{
				$i = 0;

				//UNESI NASLOV ZA SVAKU KOLONU
				foreach ($kolone_za_xls4 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}
				
				//ISKLJUCIVANJE AUTOSIZE NAD KOLONOM D I ZADAVANJE SIRINE
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(false);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth("100");

				//PORAVNANJE SADRZAJA E KOLONE U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("E")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//SETOVANJE TEKSTA U ZAGLAVLJU DA BUDE BOLD
				$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
			}
			else
			{
				$i = 0;

				//FORMATIRAJ VREDNOSTI ZA CELIJE PRVOG SHEET-A
				foreach ($kolone_za_xls4 as $key => $value)
				{

					$vrednost_za_celiju = $niz_xls4[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(2);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(2);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(2);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(2)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$poslednja_pozicija = $broj_redova_svih4 + 1;

				//PREBACIVANJE SVIH VREDNOSTI U KOLONI E U DOUBLE FORMAT
				$objPHPExcel->getActiveSheet()->getStyle('E1:'.'E'.$poslednja_pozicija)->getNumberFormat()->setFormatCode(
					PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
				);
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls4)+1)."1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls4)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU REDOVA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls4)-1).(count($niz_xls4)+1))->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//OBOJ CELIJE
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls4)-1)."1")->applyFromArray(
						array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('argb' => 'FFCCFFCC')
								)
						)
				);
			}
		}
		

		//DOK IMA REDOVA U NIZU SA OBRACUNOM TROSKOVA
		for ($j = -1; $j < $broj_redova_svih5; $j++) 
		{
			if ($j == -1) 
			{
				$i = 6;

				//UNESI NASLOV ZA SVAKU KOLONU
				foreach ($kolone_za_xls5 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}
				
				//ISKLJUCIVANJE AUTOSIZE NAD KOLONOM M I ZADAVANJE SIRINE
				$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(false);
				$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth("20");

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('G1:M1')->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getStyle('M')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('G1:M1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}

			else
			{
				$i = 6;

				//FORMATIRAJ VREDNOSTI ZA CELIJE PRVOG SHEET-A
				foreach ($kolone_za_xls5 as $key => $value)
				{

					$vrednost_za_celiju = $niz_xls5[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(2);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(2);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(2);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(2)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('G1:M1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('G1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_za_xls5)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
				
				//DINAMICKO KREIRANJE POZICIJE
				$krajnja_pozicija5 = $broj_redova_svih5 + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("H2:M".$krajnja_pozicija5)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//SETUJ TEKST U CELIJI DA BUDE BOLD
				$objPHPExcel->getActiveSheet()->getStyle('G'.$krajnja_pozicija5.':L21'.$krajnja_pozicija5)->getFont()->setBold(true);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('G1:M'.$krajnja_pozicija5)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);
			}
		}


		//DOK IMA REDOVA U NIZU SA KOEFICIJENTIMA
		for ($j = -1; $j < $broj_redova_svih6; $j++) 
		{
			
			if ($j ==-1) 
			{
				$i = 6;

				//UNESI NASLOV ZA SVAKU KOLONU TABELE SA KOEFICIJENTIMA
				foreach ($kolone_za_xls6 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow($i, $j+2+25, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE TEKSTA U ZAGLAVLJU DA BUDE BOLD
				$objPHPExcel->getActiveSheet()->getStyle('G26:J26')->getFont()->setBold(true);
			}
			
			else
			{
				$i = 6;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_za_xls6 as $key => $value)
				{

					$vrednost_za_celiju = $niz_xls6[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(2);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(2);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(2);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE
					$objPHPExcel->setActiveSheetIndex(2)
					->setCellValueByColumnAndRow($i, $j+2+25, $vrednost_za_celiju);
					$i++;

					//KREIRAJ BORDERE
					$objPHPExcel->getActiveSheet()->getStyle('G26:J31')->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
					);

					//SETOVANJE TEKSTA U ZAGLAVLJU DA BUDE BOLD I CENTRIRANJE
					$objPHPExcel->getActiveSheet()->getStyle('G26:J26')->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getStyle('J27:J31')->getFont()->setBold(true);
					$objPHPExcel->getActiveSheet()->getStyle('G27:I31')->getFont()->setBold(false);
					$objPHPExcel->getActiveSheet()->getStyle('G26:J26')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

					//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
					$objPHPExcel->getActiveSheet()->getStyle("H27:J31")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
				}

				//OBOJ CELIJE ZAGLAVLJA TABELE
				$objPHPExcel->getActiveSheet()->getStyle('G26:J26')->applyFromArray(
						array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('argb' => 'FFCCFFCC')
								)
						)
				);
			}
		}

		
		//CETVRTI TAB - OBRACUN PRIBAVE ZA POCETNU GODINU	

		//UKLANJANJE SLASH-EVA IZ DOBIJENOG NIZA
		$troskovi_pribava_xls2 = stripslashes($_POST['troskovi_pribava_krajnja']);

		//KREIRANJE NIZA ZA GENERISANJE TABELE SA TROSKOVIMA ZA KRAJNJU GODINU
		$troskovi_pribava_xls2 = json_decode($troskovi_pribava_xls2, true);

		//UKLANJANJE SLASH-EVA IZ DOBIJENOG NIZA
		$obracun_pribava_xls2 = stripslashes($_POST['obracun_pribava_krajnja']);

		//KREIRANJE NIZA ZA GENERISANJE TABELE SA OBRACUNOM PRIBAVE ZA KRAJNJU GODINU
		$obracun_pribava_xls2 = json_decode($obracun_pribava_xls2, true);

		//KREIRANJE KOLONA ZA PRVU TABELU
		$kolone_troskovi_xls2 = array('Grupa', 'Sektor', 'Konto','Opis', 'Iznos');
		$broj_kolona_troskovi2 = count($kolone_troskovi_xls2);

		//KREIRANJE KOLONA ZA DRUGU TABELU
		$kolone_obracun_xls2 = array('VO', 'Konto', 'Opis', 'Čisti Trošak');
		$broj_kolona_obracun2 = count($kolone_obracun_xls2);

		//KREIRANJE NIZOVA ZA GENERISANJE OSTALIH TABELA U TABU ZA KRAJNJU GODINU
		$suma_krajnja_xls = $_POST['suma_krajnja'];
		$koef_krajnja_xls = $_POST['koef_krajnja'];
		$kljuc_krajnja_xls = $_POST['kljuc_krajnja'];
		$zbir_krajnja_xls = $_POST['pribava_kljuc_krajnja'];

		//KREIRANJE KOLONA ZA TRECU TABELU
		$kolone_suma_xls2 = array('VO', 'Suma po VO');
		$broj_kolona_suma2 = count($kolone_suma_xls2);

		//KREIRANJE KOLONA ZA TABELU SA KOEFICIJENTIMA
		$kolone_koef_xls2 = array('Sektor', 'Koeficijent');
		$broj_kolona_koef2 = count($kolone_koef_xls2);
		
		//KREIRANJE KOLONA ZA TABELU SA TROSKOVIMA SA KLJUCA
		$kolone_kljuc_xls2 = array('Konto', 'Opis', 'Iznos');
		$broj_kolona_kljuc2 = count($kolone_kljuc_xls2);

		//KREIRANJE KOLONA ZA TABELU SA TROSKOVIMA PRIBAVA + KLJUC
		$kolone_zbir_xls2 = array('Šifra VO', 'Koeficijent Pribava', 'Ključ pribava', 'Direktno pribava', 'Direktno + Ključ');
		$broj_kolona_zbir2 = count($kolone_zbir_xls2);

		//DOBIJANJE BROJA REDOVA ZA SVE NIZOVE
		$broj_redova_troskovi2 = count($troskovi_pribava_xls2);
		$broj_redova_obracun2 = count($obracun_pribava_xls2);
		$broj_redova_suma2 = count($suma_krajnja_xls);
		$broj_redova_koef2 = count($koef_krajnja_xls);
		$broj_redova_kljuc2 = count($kljuc_krajnja_xls);
		$broj_redova_zbir2 = count($zbir_krajnja_xls);
		
		//KREIRANJE NOVOG STYLESHEETA
		$objWorkSheet = $objPHPExcel->createSheet(3);
		$objWorkSheet->setTitle($datum_pribava_krajnja);



		//DOK IMA REDOVA U NIZU SA TROSKOVIMA
		for ($j = -1; $j < $broj_redova_troskovi2; $j++) 
		{
			
			if ($j ==-1) 
			{
				$i = 0;

				//UNESI NASLOV ZA SVAKU KOLONU PRVOG SHEET-A
				foreach ($kolone_troskovi_xls2 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}
				
				//ISKLJUCIVANJE AUTOSIZE NAD KOLONOM D I ZADAVANJE SIRINE
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(false);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth("100");

				//PORAVNANJE SADRZAJA E KOLONE U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("E")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//SETOVANJE TEKSTA U ZAGLAVLJU DA BUDE BOLD
				$objPHPExcel->getActiveSheet()->getStyle('A1:E1')->getFont()->setBold(true);
			}
			else
			{
				$i = 0;

				//FORMATIRAJ VREDNOSTI ZA CELIJE PRVOG SHEET-A
				foreach ($kolone_troskovi_xls2 as $key => $value)
				{

					$vrednost_za_celiju = $troskovi_pribava_xls2[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(3)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_troskovi_xls2)+1)."1")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_troskovi_xls2)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU REDOVA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_troskovi_xls2)-1).(count($troskovi_pribava_xls2)+1))->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//OBOJ CELIJE
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_troskovi_xls2)-1)."1")->applyFromArray(
						array('fill' 	=> array(
								'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
								'color'		=> array('argb' => 'FFCCFFCC')
								)
						)
				);
			}
		}

		
		
		//DOK IMA REDOVA U NIZU SA OBRACUNOM PRIBAVE
		for ($j = -1; $j < $broj_redova_obracun2; $j++) 
		{

			if ($j == -1) 
			{
				$i = 6;

				//UNESI NASLOV ZA SVAKU KOLONU TABELE SA OBRACUNOM TROSKOVA
				foreach ($kolone_obracun_xls2 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}
				
				//ISKLJUCIVANJE AUTOSIZE NAD KOLONOM M I ZADAVANJE SIRINE
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(false);
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth("100");

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('G1:J1')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('G1:J1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}

			else
			{
				$i = 6;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_obracun_xls2 as $key => $value)
				{

					$vrednost_za_celiju = $obracun_pribava_xls2[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(3)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('G1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('G1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_obracun_xls2)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
				
				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_obracun3 = $broj_redova_obracun2 + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("J2:J".$krajnja_pozicija_obracun3)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('G1:J'.$krajnja_pozicija_obracun3)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);
				
			}
			
		}
		
		
		
		//DOK IMA REDOVA U NIZU SA SUMOM PO VO
		for ($j = -1; $j < $broj_redova_suma2; $j++) 
		{

			if ($j == -1) 
			{
				$i = 11;

				//UNESI NASLOV ZA SVAKU KOLONU TABELE SA SUMOM PO VO
				foreach ($kolone_suma_xls2 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('L1:M1')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('L1:M1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}

			else
			{
				$i = 11;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_suma_xls2 as $key => $value)
				{

					$vrednost_za_celiju = $suma_krajnja_xls[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(3)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('L1:M1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('L1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_suma_xls2)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_suma = $broj_redova_suma2 + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("M2:M".$krajnja_pozicija_suma)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('L1:M'.$krajnja_pozicija_suma)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('L'.$krajnja_pozicija_suma.':M'.$krajnja_pozicija_suma)->getFont()->setBold(true);
			}
		}
		
		
		
		//DOK IMA REDOVA U NIZU SA KOEFICIJENTIMA
		for ($j = -1; $j < $broj_redova_koef2; $j++) 
		{

			if ($j == -1) 
			{
				$i = 11;
				
				//UNESI NASLOV ZA SVAKU KOLONU TABELE SA SUMOM PO VO
				foreach ($kolone_koef_xls2 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow($i, $j+2+19, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('L20:M20')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('L20:M20')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}
			
			else
			{
				$i = 11;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_koef_xls2 as $key => $value)
				{

					$vrednost_za_celiju = $koef_krajnja_xls[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(3)
					->setCellValueByColumnAndRow($i, $j+2+19, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('L20:M20')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('L20:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_suma_xls2)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_koef = $broj_redova_koef2 + 1 + 19;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("M21:M".$krajnja_pozicija_koef)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('L20:M'.$krajnja_pozicija_koef)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('L'.$krajnja_pozicija_koef.':M'.$krajnja_pozicija_koef)->getFont()->setBold(true);
				
			}
			
		}
		
		
		//DOK IMA REDOVA U NIZU SA TROSKOVIMA SA KLJUCA
		for ($j = -1; $j < $broj_redova_kljuc2; $j++) 
		{
		
			if ($j == -1) 
			{
				$i = 14;

				//UNESI NASLOV ZA SVAKU KOLONU
				foreach ($kolone_kljuc_xls2 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('O1:Q1')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('O1:Q1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}
		
			
			else
			{
				$i = 14;

				//IZMENA POSLEDNJEG NIZA,DA IMA 2 UMESTO 3 CLANA,ZBOG BROJA KOLONA U EXCELU
				if ($j == $broj_redova_kljuc2 - 1) {

					$iznos = $kljuc_krajnja_xls[$j][1];
					$kljuc_krajnja_xls[$j][0] = 'SUMA KLJUČ';
					$kljuc_krajnja_xls[$j][1] = '';
					$kljuc_krajnja_xls[$j][2] = $iznos;
				}

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_kljuc_xls2 as $key => $value)
				{
					$vrednost_za_celiju = $kljuc_krajnja_xls[$j][$key];

					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(3)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('O1:Q1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('O1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_kljuc_xls2)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);
				
				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_kljuc = $broj_redova_kljuc2 + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("Q2:Q".$krajnja_pozicija_kljuc)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('O1:Q'.$krajnja_pozicija_kljuc)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('O'.$krajnja_pozicija_kljuc.':Q'.$krajnja_pozicija_kljuc)->getFont()->setBold(true);

				$objPHPExcel->getActiveSheet()->mergeCells('O'.$krajnja_pozicija_kljuc.':P'.$krajnja_pozicija_kljuc);
				
			}
			
		}	
		
		
		
		//DOK IMA REDOVA U NIZU DIREKTNO + KLJUC
		for ($j = -1; $j < $broj_redova_zbir2; $j++) 
		{

			if ($j == -1) 
			{
				$i = 18;

				//UNESI NASLOV ZA SVAKU KOLONU
				foreach ($kolone_zbir_xls2 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('S1:W1')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('S1:W1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}

			else
			{
				$i = 18;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_zbir_xls2 as $key => $value)
				{

					$vrednost_za_celiju = $zbir_krajnja_xls[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(3);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(3)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('S1:W1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('S1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_zbir_xls2)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_zbir = $broj_redova_zbir2 + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("T2:W".$krajnja_pozicija_zbir)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('S1:W'.$krajnja_pozicija_zbir)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('S'.$krajnja_pozicija_zbir.':W'.$krajnja_pozicija_zbir)->getFont()->setBold(true);
			}
		}



		//PETI TAB - OBRACUN AVR-A ZA OBE GODINE

		//UKLANJANJE SLASH-EVA IZ DOBIJENOG NIZA
		$avr_xls2 = stripslashes($_POST['avr_obe']);

		//KREIRANJE NIZA SA OBRACUNOM AVR-A ZA OBE GODINE
		$avr_xls2 = json_decode($avr_xls2, true);

		//DINAMICKO KREIRANJE NASLOVA ZA KOLONE U TABELI SA OBRACUNOM AVR-A
		$naslov_druga_kolona = 'Direktno + Ključ  ' .substr($datum_pocetni[0], 0, 6) .' -'. $datum_pocetni[1];
		$naslov_treca_kolona = 'Direktno + Ključ  ' .substr($datum_krajnji[0], 0, 6) .' -'. $datum_krajnji[1];
		$naslov_sesta_kolona = 'AVR  ' .$datum_krajnji[1];
		$naslov_sedma_kolona = 'AVR stanje  ' .$datum_pocetni[1];
		$naslov_osma_kolona = 'Razlika-Doknjižavanje  ' .$datum_krajnji[1];

		//KREIRANJE KOLONA ZA TABELU SA AVR-OM
		$kolone_avr_xls2 = array('Šifra VO', $naslov_druga_kolona, $naslov_treca_kolona, 'Ukupno troškovi bez AVR', 'Prenosna/Premija', $naslov_sesta_kolona, $naslov_sedma_kolona, $naslov_osma_kolona);
		$broj_kolona_avr2 = count($kolone_avr_xls2);

		//DOBIJANJE BROJA REDOVA ZA NIZ SA OBRACUNOM AVR-A
		$broj_redova_avr2 = count($avr_xls2);
		
		//KREIRANJE NOVOG STYLESHEETA
		$objWorkSheet = $objPHPExcel->createSheet(4);
		$objWorkSheet->setTitle('Obračun AVR');


			
		//DOK IMA REDOVA U NIZU SA OBRACUNOM AVR-A
		for ($j = -1; $j < $broj_redova_avr2; $j++) 
		{

			if ($j == -1) 
			{
				$i = 0;

				//UNESI NASLOV ZA SVAKU KOLONU
				foreach ($kolone_avr_xls2 as $key => $value) 
				{
					//FORMATIRANJE CELIJA
					$objPHPExcel->setActiveSheetIndex(4)->setCellValueByColumnAndRow($i, $j+2, $value);
					$objPHPExcel->getActiveSheet()->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($i))->setAutoSize(true);
					$i++;
				}

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getFont()->setBold(true);

				//OBOJ CELIJE ZAGLAVLJA
				$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->applyFromArray(
					array('fill' 	=> array(
							'type'		=> PHPExcel_Style_Fill::FILL_SOLID,
							'color'		=> array('argb' => 'FFCCFFCC')
							)
					)
				);
			}

			else
			{
				$i = 0;

				//FORMATIRAJ VREDNOSTI ZA CELIJE
				foreach ($kolone_avr_xls2 as $key => $value)
				{

					$vrednost_za_celiju = $avr_xls2[$j][$key];
					
					if (gettype($vrednost_za_celiju) == 'integer') {

						$objPHPExcel->setActiveSheetIndex(4);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER
						);
					}
					elseif (gettype($vrednost_za_celiju) == 'double') {

						$objPHPExcel->setActiveSheetIndex(4);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
						);
					}
					else
					{
						$objPHPExcel->setActiveSheetIndex(4);   
						$kolona_i_red = PHPExcel_Cell::stringFromColumnIndex($i).($j+2);
						$objPHPExcel->getActiveSheet()
						->getStyle($kolona_i_red)
						->getNumberFormat()
						->setFormatCode(
							PHPExcel_Style_NumberFormat::FORMAT_TEXT
						);
					}
					
					//UPISI VREDNOSTI U CELIJE PRVOG SHEET-A
					$objPHPExcel->setActiveSheetIndex(4)
					->setCellValueByColumnAndRow($i, $j+2, $vrednost_za_celiju);
					$i++;
				}
				
				//PORAVNAJ SADRZAJ CELIJA
				$objPHPExcel->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$objPHPExcel->getActiveSheet()->getStyle('A1:'.PHPExcel_Cell::stringFromColumnIndex(count($kolone_avr_xls2)+1)."1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

				//PODESI VISINU CELIJA
				$objPHPExcel->getActiveSheet()->getRowDimension($j+2)->setRowHeight(15);
				$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(15);

				//DINAMICKO KREIRANJE KRAJNJE POZICIJE 
				$krajnja_pozicija_avr = $broj_redova_avr2 + 1;

				//PORAVNANJE SADRZAJA KOLONA U DESNU STRANU
				$objPHPExcel->getActiveSheet()->getStyle("B2:H".$krajnja_pozicija_avr)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				//KREIRAJ BORDERE
				$objPHPExcel->getActiveSheet()->getStyle('A1:H'.$krajnja_pozicija_avr)->applyFromArray(
						array('borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)))
				);

				//SETOVANJE ZELJENIH POLJA DA BUDU BOLD
				$objPHPExcel->getActiveSheet()->getStyle('A'.$krajnja_pozicija_avr.':H'.$krajnja_pozicija_avr)->getFont()->setBold(true);
			}
		}
		
		
	}

	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
	/*
	// Redirect output to a client's web browser (Excel5)
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="Obračun AVR-a.xls"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');
	*/
	// If you're serving to IE over SSL, then the following may be needed
	// header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	// header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	// header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	// header ('Pragma: public'); // HTTP/1.0

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	//$objWriter->save('php://output');

	//$putanja_excel_fajla = 'AVR.xls';
	$putanja_excel_fajla = str_replace(__FILE__,$_SERVER['DOCUMENT_ROOT'] .'/acc/avr/dodaci/Obračun AVR-A.xls',__FILE__);

	$objWriter->save($putanja_excel_fajla);

	//PROMENA PERMISIJA EXCEL FAJLU
	chmod($putanja_excel_fajla, 0777);

	$objPHPExcel->disconnectWorksheets();
	unset($objPHPExcel);

	ini_restore('display_errors');
	ini_restore('display_startup_errors');
	ini_restore('memory_limit');
	ini_restore('max_execution_time');

	echo json_encode('http://10.101.50.12/acc/avr/dodaci/Obračun AVR-A.xls');
	
	exit;
}
?>