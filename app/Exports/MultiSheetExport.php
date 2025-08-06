<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiSheetExport implements WithMultipleSheets
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->data as $title => $rows) {
            if (isset($rows['is_chart']) && $rows['is_chart']) {
                $sheets[] = new ChartSheetExport();
            } else {
                $sheets[] = new SheetExport($title, $rows['data']);
            }
        }

        return $sheets;
    }
}
