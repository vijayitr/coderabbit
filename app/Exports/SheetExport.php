<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class SheetExport implements FromCollection, WithTitle
{
    protected $data;
    protected $title;

    public function __construct($title, $rows)
    {
        $this->data = $rows;
        $this->title = $title;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function title(): string
    {
        return $this->title;
    }
}