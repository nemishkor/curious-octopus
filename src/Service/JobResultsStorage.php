<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Query;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class JobResultsStorage {

    public function __construct(
        private string $resultsDir,
        private Filesystem $filesystem,
        private SerializerInterface $serializer,
    ) {
    }

    public function saveAsJson(Query $query, array $results): void {
        $this->filesystem->dumpFile(
            $this->getJsonFilename($query),
            $this->serializer->serialize($results, JsonEncoder::FORMAT),
        );
    }

    public function getJsonFilename(Query $query): string {
        return sprintf('%s/%s.json', $this->resultsDir, $query->getId());
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function saveAsXlsx(Query $query, array $queryOutput): void {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->setupStaticHeaderTitles($sheet);

        /** @var array|null $sqlColumns */
        $sqlColumns = null;

        $rowIndex = 3;
        foreach ($queryOutput as $jobOutput) {
            $jobOutputRowIndexStart = $rowIndex;

            $databaseHostCell = $sheet->getCellByColumnAndRow(1, $rowIndex);
            $databaseHostCell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $databaseHostCell->setValue($jobOutput['database']['host']);

            $databaseNameCell = $sheet->getCellByColumnAndRow(2, $rowIndex);
            $databaseNameCell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $databaseNameCell->setValue($jobOutput['database']['name']);

            $errorCell = $sheet->getCellByColumnAndRow(3, $rowIndex);
            $errorCell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $errorCell->setValue($jobOutput['error']);

            foreach ($jobOutput['result'] as $row) {
                $dataColumnIndex = 0;
                if ($sqlColumns === null && count($row) > 0) {
                    $sqlColumns = array_keys($row);
                }
                foreach ($row as $value) {
                    $sheet->setCellValueByColumnAndRow(
                        4 + $dataColumnIndex,
                        $rowIndex,
                        is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'UTF-8') : 'null'
                    );
                    $dataColumnIndex++;
                }
                $rowIndex++;
            }
            $sheet->mergeCellsByColumnAndRow(1, $jobOutputRowIndexStart, 1, $rowIndex - 1);
            $sheet->mergeCellsByColumnAndRow(2, $jobOutputRowIndexStart, 2, $rowIndex - 1);
            $sheet->mergeCellsByColumnAndRow(3, $jobOutputRowIndexStart, 3, $rowIndex - 1);
        }

        if ($sqlColumns !== null) {
            foreach ($sqlColumns as $sqlColumnIndex => $sqlColumn) {
                $sheet->setCellValueByColumnAndRow(
                    4 + $sqlColumnIndex,
                    2,
                    is_string($sqlColumn) ? mb_convert_encoding($sqlColumn, 'UTF-8', 'UTF-8') : ''
                );
                $sheet->getColumnDimensionByColumn(4 + $sqlColumnIndex)->setWidth(24);
            }
            $sheet->mergeCellsByColumnAndRow(4, 1, 4 + count($sqlColumns), 1);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($this->getXlsxFilename($query));
    }

    private function setupStaticHeaderTitles(Worksheet $sheet): void {
        $sheet->getColumnDimensionByColumn(1)->setWidth(24);
        $databaseHostCell = $sheet->getCellByColumnAndRow(1, 1);
        $databaseHostCell->setValue('Database host');
        $databaseHostCell->getStyle()->getFont()->setBold(true)->setSize(12);
        $databaseHostCell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimensionByColumn(2)->setWidth(24);
        $databaseNameCell = $sheet->getCellByColumnAndRow(2, 1);
        $databaseNameCell->setValue('Database name');
        $databaseNameCell->getStyle()->getFont()->setBold(true)->setSize(12);
        $databaseNameCell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getColumnDimensionByColumn(3)->setWidth(24);
        $errorCell = $sheet->getCellByColumnAndRow(3, 1);
        $errorCell->setValue('Error');
        $errorCell->getStyle()->getFont()->setBold(true)->setSize(12);
        $errorCell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $resultsCell = $sheet->getCellByColumnAndRow(4, 1);
        $resultsCell->setValue('Results');
        $resultsCell->getStyle()->getFont()->setBold(true)->setSize(12);
        $resultsCell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    public function getXlsxFilename(Query $query): string {
        return sprintf('%s/%s.xlsx', $this->resultsDir, $query->getId());
    }

}
