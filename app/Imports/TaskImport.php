<?php

namespace App\Imports;

use App\Models\ImportedTask;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TaskImport implements ToModel, WithHeadingRow
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function model(array $row)
    {
    	 return new ImportedTask([
    	 	'imported_by' => $this->userId,
            'data'        => json_encode($row),
        ]);
    }
}
