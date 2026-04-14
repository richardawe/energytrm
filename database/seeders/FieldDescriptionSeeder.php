<?php

namespace Database\Seeders;

use App\Models\FieldDescription;
use Illuminate\Database\Seeder;

class FieldDescriptionSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('ETRM_Data_Dictionary_Combined.csv');

        if (! file_exists($csvPath)) {
            $this->command->warn('ETRM_Data_Dictionary_Combined.csv not found — skipping field descriptions.');
            return;
        }

        $handle = fopen($csvPath, 'r');
        $headers = null;
        $inserted = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle)) !== false) {
            // Strip BOM from first header if present
            if ($headers === null) {
                $row[0] = ltrim($row[0], "\xEF\xBB\xBF\xFF\xFE");
                $headers = $row;
                continue;
            }

            $data = array_combine($headers, $row);

            $tab   = trim($data['Tab'] ?? '');
            $field = trim($data['Field'] ?? '');
            $desc  = trim($data['Short Description'] ?? '');

            if (! $tab || ! $field || ! $desc) {
                $skipped++;
                continue;
            }

            FieldDescription::updateOrCreate(
                ['tab' => $tab, 'field_name' => $field],
                [
                    'subtab'            => trim($data['Subtab'] ?? '') ?: null,
                    'source_type'       => trim($data['Source Type'] ?? '') ?: null,
                    'short_description' => $desc,
                ]
            );
            $inserted++;
        }

        fclose($handle);
        $this->command->info("Field descriptions: {$inserted} seeded, {$skipped} skipped.");
    }
}
