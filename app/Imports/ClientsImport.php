<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientsImport implements ToModel, WithHeadingRow
{
    protected $userId;

    public function __construct()
    {
        $this->userId = auth()->id();
    }
    /**
     * Convert the row data into a Client model instance.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Client([
            'client_name' => $row['client_name'],
            'created_by' => $this->userId
        ]);
    }
}
