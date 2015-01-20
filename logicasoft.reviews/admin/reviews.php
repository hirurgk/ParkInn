<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
CModule::IncludeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle('Выгрузка отзывов');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CJSCore::Init(array("jquery"));
?>

<?
if ($_REQUEST['save_csv']) {
	$APPLICATION->RestartBuffer();
	
	
	function download_send_headers($filename){
		$now = gmdate("D, d M Y H:i:s");
		header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
		header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
		header("Last-Modified: {$now} GMT");
	
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
	
		header("Content-Disposition: attachment;filename={$filename}");
		header("Content-Transfer-Encoding: binary");
	}
	
	function array2csv(array &$array){
		if (count($array) == 0) {
			return null;
		}
		ob_start();
		$df = fopen("php://output", 'w');
		foreach ($array as $row) {
			fputcsv($df, $row, ';');
		}
		fclose($df);
		return ob_get_clean();
	}
	
	$reviews = array();
	$rent_ids = array();
	$dbReviews = CIBlockElement::GetList(array(), array('IBLOCK_ID' => 30));
	while ($arReview = $dbReviews->GetNextElement()) {
		$arFields = $arReview->GetFields();
		$arFields['PROPERTIES'] = $arReview->GetProperties();
		$reviews[] = $arFields;
		$rent_ids[] = $arFields['PROPERTIES']['RENT']['VALUE'];
	}
	
	if (sizeof($reviews)) {
		$rents = array();
		$dbRents = CIBlockElement::GetList(array(), array('IBLOCK_ID' => 13, 'ID' => $rent_ids), false, false, array('ID', 'PROPERTY_RACE', 'PROPERTY_DEPARTURE'));
		while ($arRent = $dbRents->Fetch()) {
			$rents[$arRent['ID']]['RACE'] = $arRent['PROPERTY_RACE_VALUE'];
			$rents[$arRent['ID']]['DEPARTURE'] = $arRent['PROPERTY_DEPARTURE_VALUE'];
		}
	}
	
	
	$data = array();
	$data[] = array(
			iconv('utf-8', 'windows-1251', 'Ид'),
			iconv('utf-8', 'windows-1251', 'Дата заселения'),
			iconv('utf-8', 'windows-1251', 'Дата отъезда'),
			iconv('utf-8', 'windows-1251', 'Дата отзыва'),
			iconv('utf-8', 'windows-1251', 'Работа стойки размещения'),
			iconv('utf-8', 'windows-1251', 'Работа стойки размещения (ОЦЕНКА)'),
			iconv('utf-8', 'windows-1251', 'Качество уборки номеров'),
			iconv('utf-8', 'windows-1251', 'Качество уборки номеров (ОЦЕНКА)'),
			iconv('utf-8', 'windows-1251', 'Комфортабельность номера'),
			iconv('utf-8', 'windows-1251', 'Комфортабельность номера (ОЦЕНКА)'),
			iconv('utf-8', 'windows-1251', 'Уровень сервиса в ресторане «Галерея»'),
			iconv('utf-8', 'windows-1251', 'Уровень сервиса в ресторане «Галерея» (ОЦЕНКА)'),
			iconv('utf-8', 'windows-1251', 'Качество и ассортимент завтрака'),
			iconv('utf-8', 'windows-1251', 'Качество и ассортимент завтрака (ОЦЕНКА)'),
			iconv('utf-8', 'windows-1251', 'Beresta SPA-центр'),
			iconv('utf-8', 'windows-1251', 'Beresta SPA-центр (ОЦЕНКА)'),
			iconv('utf-8', 'windows-1251', 'Общее впечатление об отеле'),
			iconv('utf-8', 'windows-1251', 'Общее впечатление об отеле (ОЦЕНКА)'),
			iconv('utf-8', 'windows-1251', 'Ваши пожелания'),
			iconv('utf-8', 'windows-1251', 'Ваши пожелания (ОЦЕНКА)')
	);
	
	foreach($reviews as $review){
		$data[] = array(
				$review['ID'],
				$rents[$review['PROPERTIES']['RENT']['VALUE']]['RACE'],
				$rents[$review['PROPERTIES']['RENT']['VALUE']]['DEPARTURE'],
				$review['DATE_CREATE'],
				iconv('utf-8', 'windows-1251', $review['PROPERTIES']['RECEPTION']['VALUE']['TEXT']),
				$review['PROPERTIES']['RECEPTION_RAITING']['VALUE'],
				iconv('utf-8', 'windows-1251', $review['PROPERTIES']['CLEANING']['VALUE']['TEXT']),
				$review['PROPERTIES']['CLEANING_RAITING']['VALUE'],
				iconv('utf-8', 'windows-1251', $review['PROPERTIES']['COMFORT']['VALUE']['TEXT']),
				$review['PROPERTIES']['COMFORT_RAITING']['VALUE'],
				iconv('utf-8', 'windows-1251', $review['PROPERTIES']['SERVICE']['VALUE']['TEXT']),
				$review['PROPERTIES']['SERVICE_RAITING']['VALUE'],
				iconv('utf-8', 'windows-1251', $review['PROPERTIES']['BREAKFAST']['VALUE']['TEXT']),
				$review['PROPERTIES']['BREAKFAST_RAITING']['VALUE'],
				iconv('utf-8', 'windows-1251', $review['PROPERTIES']['SPA']['VALUE']['TEXT']),
				$review['PROPERTIES']['SPA_RAITING']['VALUE'],
				iconv('utf-8', 'windows-1251', $review['PROPERTIES']['IMPRESSION']['VALUE']['TEXT']),
				$review['PROPERTIES']['IMPRESSION_RAITING']['VALUE'],
				iconv('utf-8', 'windows-1251', $review['PROPERTIES']['WISH']['VALUE']['TEXT']),
				$review['PROPERTIES']['WISH_RAITING']['VALUE']
		);
	}
	
	$d = date('d.m.Y H:i');
	download_send_headers("Экспорт отзывов ({$d}).csv");
	echo array2csv($data);
	
	exit;
}
?>

<form method="POST" target="_blank">
	<input type="submit" name="save_csv" value="Сохранить CSV-файл с отзывами">
</form>

<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>