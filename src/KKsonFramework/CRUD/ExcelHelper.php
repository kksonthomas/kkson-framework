<?php

namespace KKsonFramework\CRUD;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Font;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Stringy\Stringy;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use RedBeanPHP\BeanCollection;
use RedBeanPHP\R;

class ExcelHelper
{

    private $headerClosure;

    public function __construct() {
        $this->headerClosure= function ($key, $value) {
            header("$key: $value");
        };
    }
    /**
     * @param KKsonCRUD $crud
     * @param array|BeanCollection $list
     * @param string|null $filename
     */
    public function genExcel(KKsonCRUD $crud, $list, $filename = null) {
        Font::setAutoSizeMethod(Font::AUTOSIZE_METHOD_EXACT);
        $excel = new Spreadsheet();

        $sheet = $excel->getActiveSheet();

        $fields = $crud->getShowFields();

        // Header
        $colIndex = 1;
        foreach($fields as $field) {
            $coord = Coordinate::stringFromColumnIndex($colIndex) . "1";
            $sheet->getCell($coord)->setValue($field->getDisplayName());
            $sheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);

            $colIndex++;
        }

        // Data
        $rowIndex = 2;

        $rowHandler = function ($bean, $rowIndex) use ($sheet, $fields) {
            $colIndex = 1;
            foreach($fields as $field) {
                $coord = Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;
                $value = strip_tags($field->cellValue($bean, true));
                
                if (Stringy::create($value)->startsWith("=")) {
                    $value = " $value";
                }
                
                $cell = $sheet->getCell($coord);
                $cell->setValueExplicit($value, DataType::TYPE_STRING);
                if(preg_match('/\n/', $value)) {
                    $cell->getStyle()->getAlignment()->setWrapText(true);
                }
                $colIndex++;
            }
        };

        if($list instanceof BeanCollection) {
            $bean = $list->next();
            while($bean !== null) {
                $rowHandler($bean, $rowIndex++);
                $bean = $list->next();
            }
            $list->close();
        } else {
            foreach ($list as $bean) {
                $rowHandler($bean, $rowIndex++);
            }
        }

        // Save
        $objWriter = new Xlsx($excel);

        //$rand = dechex(rand(0, 99999999));

        if ($filename == null) {
            $name = $crud->getTableName();
            $date = date("Y-m-d", time());
            $filename = "$name-$date.xlsx";
        }

        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0'
        ];

        foreach ($headers as $key => $value) {
            $h = $this->headerClosure;
            $h($key, $value);
        }

        $objWriter->save("php://output");

    }

    /**
     * @param \Closure $headerClosure
     */
    public function setHeaderClosure($headerClosure)
    {
        $this->headerClosure = $headerClosure;
    }

    public static function xlsToXlsx($srcFilePath, $destFilePath = null) {
        if($destFilePath == null) {
            $destFilePath = tempnam(sys_get_temp_dir(), 'xls2Xlsx') . ".xlsx";
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $srcFilePath);
        if($mimeType == "application/vnd.ms-office" || $mimeType == "application/vnd.ms-excel" || $mimeType == 'application/CDFV2') {
            $spreadsheet = IOFactory::load($srcFilePath);
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save($destFilePath);
            return $destFilePath;
        }
        return $srcFilePath;
    }

    public static function colCodeToIndex($col) {

        $list = str_split(trim(strtoupper($col)));

        $result = 0;
        foreach ($list as $i => $chr) {
            $result += (ord($chr) - ord('A') + 1) * pow(26, count($list)-$i-1);
        }

        return $result;
    }

}