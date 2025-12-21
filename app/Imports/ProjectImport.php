<?php

namespace App\Imports;

use App\Models\Projects;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Throwable;

class ProjectImport implements SkipsOnError, ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsErrors;

    public function model(array $row)
    {
        $project = new Projects([
            'name' => $row['projecttitle'],
        ]);

        return $project;
    }

    public function onError(Throwable $error) {}

    public function rules(): array
    {
        return [
            '*.projecttitle' => ['required', 'string', 'unique:projects,name'],
        ];

    }
}
