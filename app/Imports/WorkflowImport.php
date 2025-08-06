<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Workflow;
use App\Models\WorkflowField;
use App\Models\WorkflowFieldOption;
use App\Models\WorkflowProcessName;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WorkflowImport implements ToModel, WithHeadingRow
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function model(array $row)
    {
        if (!isset($row['client_name'], $row['workflow_name'])) {
            return;
        }

        $client = Client::firstOrCreate([
            'client_name' => (string)$row['client_name'],
        ]);

        if (!$client) {
            return; // Skip if the client cannot be resolved
        }

        $clientId = $client->id;

        // Insert Workflow Data with created_by and client_id fields
        $workflow = Workflow::firstOrCreate([
            'workflow_name' => (string)$row['workflow_name'],
            'client_id'     => $clientId,
        ], [
            'created_by' => $this->userId,
        ]);

        if (!isset($row['field_type'], $row['field_name'])) {
            return;
        }

        // Validate and Insert Workflow Field Data
        $fieldType = strtolower(trim((string)$row['field_type']));

        // Validate field type before inserting
        if (!in_array($fieldType, ['text', 'dropdown', 'date', 'checkbox'])) {
            return; // Skip invalid field types
        }

        $field = WorkflowField::updateOrCreate(
            [
                'workflow_id' => $workflow->id,
                'field_name'  => (string)$row['field_name'],
            ],
            [
                'field_type'  => $fieldType,
            ]
        );

        if (!$workflow->fields()->where('is_primary', 1)->exists()) {
            $field->is_primary = 1;
            $field->save();
        }

        // Check for 'option_name' before using it
        if (isset($row['option_name']) && !empty(trim($row['option_name']))) {
            // Insert Workflow Field Options (for dropdown fields)
            WorkflowFieldOption::create([
                'workflow_field_id' => $field->id,
                'option_value'       => (string)$row['option_name'],
            ]);
        }

        // Check if 'process_name' exists before using it
        if (isset($row['process_name']) && !empty($row['process_name'])) {
            WorkflowProcessName::firstOrCreate([
                'workflow_id'  => $workflow->id,
                'process_name' => (string)$row['process_name'],
            ]);
        }
    }
}
