<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;


class ChartSheetExport
{
    public function generate($dynamicData)
    {
        $spreadsheet = new Spreadsheet();
        foreach ($dynamicData as $title => $value) {
            if (isset($value['is_chart']) && $value['is_chart']) {
                $chart_data_sheet = 'Chart Data';
                    // if (isset($value['multi_chart']) && count($value['multi_chart']) > 0) {
                    //     foreach ($value['multi_chart'] as $multi_key => $multi_value) {
                    //         dd($multi_value);
                    //         // code...
                    //     }
                    // }
                // $sheet1 = $spreadsheet->getActiveSheet();
                $dataSheet = new Worksheet($spreadsheet, 'Chart Data');
                // $spreadsheet->addSheet($dataSheet);
                $sheet = new Worksheet($spreadsheet, $title);
                
                $pieChart = $this->barChart($value['data'], $dataSheet);
                // $pieChart = $this->pieChart($value['data']);
                // $sheet->addChart($pieChart);
                $spreadsheet->addSheet($sheet);

               
               } else {
                    $sheet2 = new Worksheet($spreadsheet, $title);
                    // $sheet2->setTitle('Saless');

                    // Sample data
                    $sheet2->fromArray($value['data']);

                    $spreadsheet->addSheet($sheet2);

               }
        }
        if ($spreadsheet->getSheetCount() > 1) {
            $spreadsheet->removeSheetByIndex(0);
            $spreadsheet->setActiveSheetIndex(0);
        }
        // File path
        $fileName = 'sales_chart_export.xlsx';
        $filePath = storage_path("app/public/{$fileName}");

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->save($filePath);



        // Sheet 2
        // $sheet2 = $spreadsheet->getActiveSheet();

        return $filePath;
    }

    public function barChart($data, $dataSheet) {
        $highestRow = $dataSheet->getHighestRow();
        $highestColumn = $dataSheet->getHighestColumn();
        $dataSheet->fromArray($data, null, $highestColumn.$highestRow);
        $highestRow = $dataSheet->getHighestRow();
        $highestColumn = $dataSheet->getHighestColumn();
        dd($highestRow, $highestColumn);
        $dataSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
        $sheet1 = new Worksheet($spreadsheet, $title);
        $sheet1->fromArray($data);
        $dataSeriesLabels = [
            new DataSeriesValues('String', $title.'!$B$1', null, 1),
            new DataSeriesValues('String', $title.'!$C$1', null, 1),
            new DataSeriesValues('String', $title.'!$D$1', null, 1),
        ];
        $dataSeriesValues = [
            new DataSeriesValues('Number', $title.'!$B$2:$B$3', null, 2),
            new DataSeriesValues('Number', $title.'!$C$2:$C$3', null, 2),
            new DataSeriesValues('Number', $title.'!$D$2:$D$3', null, 2),
        ];
        $dataSeriesValues = [
            new DataSeriesValues('Number', $title.'!$B$2:$B$3', null, 2),
            new DataSeriesValues('Number', $title.'!$C$2:$C$3', null, 2),
            new DataSeriesValues('Number', $title.'!$D$2:$D$3', null, 2),
        ];
        $xAxisTickValues = [
            new DataSeriesValues('String', $title.'!$A$2:$A$3', null, 2),
        ];


        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );

        $series->setPlotDirection(DataSeries::DIRECTION_COL);
        $plotArea = new PlotArea(null, [$series]);
        $chart = new Chart(
            'Sales Chart',
            new Title('Monthly Sales Chart'),
            new Legend(Legend::POSITION_RIGHT, null, false),
            $plotArea
        );

        $chart->setTopLeftPosition('F2');
        $chart->setBottomRightPosition('L20');
        $sheet1->addChart($chart);
        return $sheet1;
    }

    public function pieChart($data) {
        // $pieLabels = [new DataSeriesValues('String', "'{$title}'!\$B\$1", null, 1)];
        // $pieCategories = [new DataSeriesValues('String', "'{$title}'!\$A\$2:\$A\$3", null, 2)];
        // $pieValues = [new DataSeriesValues('Number', "'{$title}'!\$B\$2:\$B\$3", null, 2)];

        // $pieSeries = new DataSeries(
        //     DataSeries::TYPE_PIECHART,
        //     null,
        //     range(0, count($pieValues) - 1),
        //     $pieLabels,
        //     $pieCategories,
        //     $pieValues
        // );
        // $pieLayout = new \PhpOffice\PhpSpreadsheet\Chart\Layout();
        // $pieLayout->setShowPercent(true);

        // $pieChart = new Chart(
        //     'pie_chart',
        //     new Title('Product Distribution (Pie Chart)'),
        //     new Legend(Legend::POSITION_RIGHT, null, false),
        //     new PlotArea($pieLayout, [$pieSeries])
        // );
        // $pieChart->setTopLeftPosition('F22');
        // $pieChart->setBottomRightPosition('N40');
        // return $pieChart;
    }


}
