<?php

use App\Http\Controllers\PortalController;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

test('the teacher import recognizes the standard spreadsheet columns', function () {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A2', 'Name');
    $sheet->setCellValue('B2', 'Nachname');
    $sheet->setCellValue('C2', 'Vorname');
    $sheet->setCellValue('D2', 'Klassen');
    $sheet->setCellValue('E2', 'Titel');
    $sheet->setCellValue('F2', 'Anzahl Kl-Leh');
    $sheet->setCellValue('G2', 'Liste Kl-Leh');
    $sheet->setCellValue('A3', 'AP');
    $sheet->setCellValue('B3', 'AIGNER');
    $sheet->setCellValue('C3', 'Paul');
    $sheet->setCellValue('D3', '4AHWIM,4BHWIM');

    $path = tempnam(sys_get_temp_dir(), 'press-import-').'.xlsx';
    (new Xlsx($spreadsheet))->save($path);

    try {
        $rows = IOFactory::load($path)
            ->getActiveSheet()
            ->toArray(null, true, true, true);

        $controller = new PortalController();
        $locateHeader = new ReflectionMethod($controller, 'locateImportHeaderRow');
        $mapHeaders = new ReflectionMethod($controller, 'mapImportHeaders');
        $resolveColumn = new ReflectionMethod($controller, 'resolveImportColumn');

        [$header, $dataRows] = $locateHeader->invoke($controller, $rows);
        $headers = $mapHeaders->invoke($controller, $header);
        $shortCodeColumn = $resolveColumn->invoke($controller, $headers, [
            'kuerzel', 'kürzel', 'name', 'short', 'code',
        ]);

        expect($headers)
            ->toHaveKey('name', 'A')
            ->toHaveKey('nachname', 'B')
            ->toHaveKey('vorname', 'C')
            ->toHaveKey('klassen', 'D')
            ->and($shortCodeColumn)->toBe('A')
            ->and($dataRows)->toHaveCount(1)
            ->and(array_key_first($dataRows))->toBe(3);
    } finally {
        @unlink($path);
    }
});
