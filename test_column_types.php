<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\Dataset;
use App\Models\DatasetRow;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Testing Column Types Functionality\n";
echo "==================================\n\n";

// Test 1: Create a dataset with column definitions
echo "Test 1: Creating dataset with column definitions\n";
try {
    $dataset = Dataset::create([
        'name' => 'Test Dataset with Types',
        'description' => 'Testing column type functionality',
        'columns' => [
            ['name' => 'name', 'type' => 'string'],
            ['name' => 'age', 'type' => 'integer'],
            ['name' => 'email', 'type' => 'string'],
            ['name' => 'birth_date', 'type' => 'date'],
            ['name' => 'is_active', 'type' => 'boolean']
        ],
        'user_id' => 1
    ]);

    echo "✓ Dataset created successfully\n";
    echo "  Dataset ID: {$dataset->id}\n";
    echo "  Columns: " . json_encode($dataset->columns) . "\n";
    echo "  Column Definitions: " . json_encode($dataset->column_definitions) . "\n\n";

} catch (Exception $e) {
    echo "✗ Failed to create dataset: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Add a row to the dataset
echo "Test 2: Adding a row to the dataset\n";
try {
    $row = DatasetRow::create([
        'dataset_id' => $dataset->id,
        'data' => [
            'name' => 'John Doe',
            'age' => 30,
            'email' => 'john@example.com',
            'birth_date' => '1993-01-15',
            'is_active' => true
        ]
    ]);

    echo "✓ Row created successfully\n";
    echo "  Row ID: {$row->id}\n";
    echo "  Data: " . json_encode($row->data) . "\n\n";

} catch (Exception $e) {
    echo "✗ Failed to create row: " . $e->getMessage() . "\n\n";
}

// Test 3: Test column type change
echo "Test 3: Testing column type change\n";
try {
    $originalColumns = $dataset->columns;
    echo "  Original columns: " . json_encode($originalColumns) . "\n";

    // Change 'age' column type from integer to string
    $updatedColumns = $originalColumns;
    foreach ($updatedColumns as &$col) {
        if ($col['name'] === 'age') {
            $col['type'] = 'string';
            break;
        }
    }

    $dataset->update(['columns' => $updatedColumns]);
    echo "✓ Column type changed successfully\n";
    echo "  Updated columns: " . json_encode($dataset->columns) . "\n\n";

} catch (Exception $e) {
    echo "✗ Failed to change column type: " . $e->getMessage() . "\n\n";
}

// Test 4: Test backward compatibility with string columns
echo "Test 4: Testing backward compatibility\n";
try {
    $legacyDataset = Dataset::create([
        'name' => 'Legacy Dataset',
        'description' => 'Testing backward compatibility',
        'columns' => ['name', 'email', 'phone'], // Old format
        'user_id' => 1
    ]);

    echo "✓ Legacy dataset created\n";
    echo "  Columns: " . json_encode($legacyDataset->columns) . "\n";
    echo "  Column Definitions: " . json_encode($legacyDataset->column_definitions) . "\n\n";

} catch (Exception $e) {
    echo "✗ Failed to create legacy dataset: " . $e->getMessage() . "\n\n";
}

// Test 5: Test column renaming
echo "Test 5: Testing column renaming\n";
try {
    $originalColumns = $dataset->columns;
    $updatedColumns = $originalColumns;

    // Rename 'name' to 'full_name'
    foreach ($updatedColumns as &$col) {
        if ($col['name'] === 'name') {
            $col['name'] = 'full_name';
            break;
        }
    }

    $dataset->update(['columns' => $updatedColumns]);

    // Update the row data to match new column name
    $row->update([
        'data' => [
            'full_name' => 'John Doe',
            'age' => '30',
            'email' => 'john@example.com',
            'birth_date' => '1993-01-15',
            'is_active' => true
        ]
    ]);

    echo "✓ Column renamed successfully\n";
    echo "  Updated columns: " . json_encode($dataset->columns) . "\n";
    echo "  Updated row data: " . json_encode($row->data) . "\n\n";

} catch (Exception $e) {
    echo "✗ Failed to rename column: " . $e->getMessage() . "\n\n";
}

// Cleanup
echo "Cleaning up test data...\n";
try {
    $row->delete();
    $dataset->delete();
    $legacyDataset->delete();
    echo "✓ Test data cleaned up\n\n";
} catch (Exception $e) {
    echo "✗ Failed to cleanup: " . $e->getMessage() . "\n\n";
}

echo "All tests completed!\n";
