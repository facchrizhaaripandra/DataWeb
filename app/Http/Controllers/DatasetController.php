<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\DatasetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataImport;
use App\Exports\DataExport;

class DatasetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get owned datasets
        $ownedDatasets = Dataset::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        // Get shared datasets with permissions
        $sharedDatasets = Dataset::whereHas('shares', function($subQuery) {
            $subQuery->where('user_id', auth()->id());
        })
        ->with(['user', 'shares' => function($query) {
            $query->where('user_id', auth()->id());
        }])
        ->where('user_id', '!=', auth()->id())
        ->orderBy('created_at', 'desc')
        ->get();

        return view('datasets.index', compact('ownedDatasets', 'sharedDatasets'));
    }

    public function create()
    {
        return view('datasets.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'columns' => 'required|array|min:1',
            'columns.*.name' => 'required|string|max:255',
            'columns.*.type' => 'required|string|in:string,text,integer,float,date,boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Convert columns to the new format
        $columns = array_map(function($column) {
            return [
                'name' => $column['name'],
                'type' => $column['type'] ?? 'string'
            ];
        }, $request->columns);

        $dataset = Dataset::create([
            'name' => $request->name,
            'description' => $request->description,
            'columns' => $columns,
            'user_id' => auth()->id()
        ]);

        return redirect()->route('datasets.show', $dataset->id)
            ->with('success', 'Dataset berhasil dibuat!');
    }

    public function addColumn(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit dataset structure (only owner and admin)
        if (!$dataset->canEditDataset()) {
            abort(403);
        }

        $request->validate([
            'column_name' => 'required|string|max:255',
            'column_type' => 'required|string|in:string,text,integer,float,date,boolean'
        ]);

        $columns = $dataset->columns;
        $columns[] = [
            'name' => $request->column_name,
            'type' => $request->column_type
        ];

        $dataset->update(['columns' => $columns]);

        // Add empty value for this column in all existing rows
        DatasetRow::where('dataset_id', $dataset->id)->chunk(100, function($rows) use ($request) {
            foreach ($rows as $row) {
                $data = $row->data;
                $data[$request->column_name] = null;
                $row->update(['data' => $data]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function renameColumn(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit dataset structure (only owner and admin)
        if (!$dataset->canEditDataset()) {
            abort(403);
        }

        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string|max:255'
        ]);

        $columns = $dataset->columns ?? [];

        // Find and update the column name
        $columnFound = false;
        foreach ($columns as &$column) {
            if (is_array($column) && $column['name'] === $request->old_name) {
                $column['name'] = $request->new_name;
                $columnFound = true;
                break;
            } elseif (is_string($column) && $column === $request->old_name) {
                // Handle backward compatibility - convert string to array format
                $column = [
                    'name' => $request->new_name,
                    'type' => 'string'
                ];
                $columnFound = true;
                break;
            }
        }

        if (!$columnFound) {
            return response()->json(['success' => false, 'message' => 'Column not found'], 404);
        }

        $dataset->update(['columns' => $columns]);

        // Update all rows with new column name
        DatasetRow::where('dataset_id', $dataset->id)->chunk(100, function($rows) use ($request) {
            foreach ($rows as $row) {
                $data = $row->data ?? [];
                if (isset($data[$request->old_name])) {
                    $data[$request->new_name] = $data[$request->old_name];
                    unset($data[$request->old_name]);
                    $row->update(['data' => $data]);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    public function deleteColumn(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit dataset structure (only owner and admin)
        if (!$dataset->canEditDataset()) {
            abort(403);
        }

        $request->validate([
            'column_name' => 'required|string'
        ]);

        $columns = $dataset->columns;
        $index = array_search($request->column_name, $columns);

        if ($index !== false) {
            unset($columns[$index]);
            $columns = array_values($columns); // Reindex array

            $dataset->update(['columns' => $columns]);

            // Remove column from all rows
            DatasetRow::where('dataset_id', $dataset->id)->chunk(100, function($rows) use ($request) {
                foreach ($rows as $row) {
                    $data = $row->data;
                    unset($data[$request->column_name]);
                    $row->update(['data' => $data]);
                }
            });

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function reorderColumns(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit dataset structure (only owner and admin)
        if (!$dataset->canEditDataset()) {
            abort(403);
        }

        $request->validate([
            'columns' => 'required|array'
        ]);

        // For reordering, we need to maintain the column definitions structure
        $currentColumns = $dataset->column_definitions ?? [];
        $newOrder = $request->columns;

        // Create new column order maintaining types
        $orderedColumns = [];
        foreach ($newOrder as $columnName) {
            $found = false;
            foreach ($currentColumns as $colDef) {
                if ($colDef['name'] === $columnName) {
                    $orderedColumns[] = $colDef;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // Fallback for backward compatibility
                $orderedColumns[] = ['name' => $columnName, 'type' => 'string'];
            }
        }

        $dataset->update(['columns' => $orderedColumns]);

        // Reorder data in all rows according to new column order
        DatasetRow::where('dataset_id', $dataset->id)->chunk(100, function($rows) use ($newOrder) {
            foreach ($rows as $row) {
                $newData = [];
                foreach ($newOrder as $column) {
                    $newData[$column] = $row->data[$column] ?? null;
                }
                $row->update(['data' => $newData]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function changeColumnType(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit dataset structure (owner, admin, or users with edit permission)
        if (!$dataset->canEdit()) {
            abort(403);
        }

        $request->validate([
            'column_name' => 'required|string',
            'new_type' => 'required|string|in:string,text,integer,float,date,boolean'
        ]);

        $columns = $dataset->columns;

        // Find the column and update its type
        $columnFound = false;
        foreach ($columns as &$column) {
            if (is_array($column) && $column['name'] === $request->column_name) {
                $column['type'] = $request->new_type;
                $columnFound = true;
                break;
            } elseif (is_string($column) && $column === $request->column_name) {
                // Handle backward compatibility - convert string to array format
                $column = [
                    'name' => $request->column_name,
                    'type' => $request->new_type
                ];
                $columnFound = true;
                break;
            }
        }

        if (!$columnFound) {
            return response()->json(['success' => false, 'message' => 'Column not found'], 404);
        }

        $dataset->update(['columns' => $columns]);

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        $rows = $dataset->rows()->paginate(50);

        // Check if user can edit this dataset
        $canEdit = $dataset->canEdit();

        // Get shared users for this dataset
        $sharedUsers = $dataset->shares()->with('user')->get()->map(function($share) {
            return [
                'user' => $share->user,
                'permission' => $share->permission
            ];
        });

        // Add owner to shared users list
        $sharedUsers->prepend([
            'user' => $dataset->user,
            'permission' => 'owner'
        ]);

        return view('datasets.show', compact('dataset', 'rows', 'canEdit', 'sharedUsers'));
    }

    public function edit($id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Only owner and admin can edit dataset metadata
        if (!$dataset->canEditDataset()) {
            abort(403);
        }

        return view('datasets.edit', compact('dataset'));
    }

    public function update(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Only owner and admin can edit dataset metadata
        if (!$dataset->canEditDataset()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dataset->update($request->only(['name', 'description']));

        return redirect()->route('datasets.show', $dataset->id)
            ->with('success', 'Dataset berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        $dataset->delete();

        return redirect()->route('datasets.index')
            ->with('success', 'Dataset berhasil dihapus!');
    }

    public function addRow(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit this dataset
        if (!$dataset->canEdit()) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DatasetRow::create([
            'dataset_id' => $dataset->id,
            'data' => $request->data
        ]);

        $dataset->increment('row_count');

        return redirect()->back()
            ->with('success', 'Data berhasil ditambahkan!');
    }

    public function editRow(Request $request, $datasetId, $rowId)
    {
        $dataset = Dataset::findOrFail($datasetId);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit this dataset
        if (!$dataset->canEdit()) {
            abort(403);
        }

        $row = DatasetRow::where('dataset_id', $datasetId)->findOrFail($rowId);

        // Jika request AJAX untuk inline editing (single column)
        if ($request->has('column') && $request->has('value')) {
            $data = $row->data;
            $data[$request->column] = $request->value;
            $row->update(['data' => $data]);

            return response()->json(['success' => true]);
        }
        // Jika request untuk edit full row (form)
        elseif ($request->has('data')) {
            $request->validate([
                'data' => 'required|array',
                'data.*' => 'nullable|string'
            ]);

            $row->update(['data' => $request->data]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
    }

    public function deleteRow($datasetId, $rowId)
    {
        $dataset = Dataset::findOrFail($datasetId);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit this dataset
        if (!$dataset->canEdit()) {
            abort(403);
        }

        $row = DatasetRow::where('dataset_id', $datasetId)->findOrFail($rowId);

        $row->delete();
        $dataset->decrement('row_count');

        return response()->json(['success' => true]);
    }

    public function editRowForm($datasetId, $rowId)
    {
        $dataset = Dataset::findOrFail($datasetId);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit this dataset
        if (!$dataset->canEdit()) {
            abort(403);
        }

        $row = DatasetRow::where('dataset_id', $datasetId)->findOrFail($rowId);

        $html = view('partials.edit-row-form', compact('dataset', 'row'))->render();

        return response()->json(['html' => $html]);
    }

    public function deleteSelectedRows(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit this dataset
        if (!$dataset->canEdit()) {
            abort(403);
        }

        $request->validate([
            'row_ids' => 'required|array'
        ]);

        $count = DatasetRow::where('dataset_id', $dataset->id)
            ->whereIn('id', $request->row_ids)
            ->delete();

        $dataset->decrement('row_count', $count);

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    public function duplicateRows(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit this dataset
        if (!$dataset->canEdit()) {
            abort(403);
        }

        $request->validate([
            'row_ids' => 'required|array'
        ]);

        $rows = DatasetRow::where('dataset_id', $dataset->id)
            ->whereIn('id', $request->row_ids)
            ->get();

        $count = 0;
        foreach ($rows as $row) {
            DatasetRow::create([
                'dataset_id' => $dataset->id,
                'data' => $row->data
            ]);
            $count++;
        }

        $dataset->increment('row_count', $count);

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    public function importExcel(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        // Check if user can edit this dataset
        if (!$dataset->canEdit()) {
            abort(403);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new DataImport($dataset), $request->file('file'));

            return redirect()->back()
                ->with('success', 'Data berhasil diimport!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function export($id, $format = 'excel')
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        switch ($format) {
            case 'excel':
                return Excel::download(new DataExport($dataset), $dataset->name . '.xlsx');

            case 'pdf':
                return $this->exportToPdf($dataset);

            case 'image':
                return $this->exportToImage($dataset);

            default:
                return Excel::download(new DataExport($dataset), $dataset->name . '.xlsx');
        }
    }

    private function exportToPdf($dataset)
    {
        // Generate HTML table for PDF
        $html = $this->generateTableHtml($dataset);

        // Use dompdf or similar library to generate PDF
        $pdf = \PDF::loadHTML($html);
        return $pdf->download($dataset->name . '.pdf');
    }

    private function exportToImage($dataset)
    {
        // Generate HTML table for image
        $html = $this->generateTableHtml($dataset);

        // Use a library like wkhtmltoimage or similar to generate image
        // For now, we'll use a simple approach with GD or Imagick if available
        // This is a placeholder - you might need to install additional packages

        $imagePath = storage_path('app/temp/' . $dataset->name . '.png');

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        // Use a simple GD approach to create an image
        $this->generateImageFromHtml($html, $imagePath);

        return response()->download($imagePath)->deleteFileAfterSend(true);
    }

    private function generateTableHtml($dataset)
    {
        $html = '<html><head><style>';
        $html .= 'table { border-collapse: collapse; width: 100%; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #f2f2f2; }';
        $html .= '</style></head><body>';

        $html .= '<h2>' . htmlspecialchars($dataset->name) . '</h2>';
        if ($dataset->description) {
            $html .= '<p>' . htmlspecialchars($dataset->description) . '</p>';
        }

        $html .= '<table>';
        $html .= '<thead><tr>';

        foreach ($dataset->column_definitions as $column) {
            $html .= '<th>' . htmlspecialchars($column['name']) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($dataset->rows as $row) {
            $html .= '<tr>';
            foreach ($dataset->column_definitions as $column) {
                $value = $row->data[$column['name']] ?? '';
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '</body></html>';

        return $html;
    }

    private function generateImageFromHtml($html, $outputPath)
    {
        // This is a basic implementation using GD
        // For production, consider using wkhtmltoimage or similar

        // Create a simple text-based image representation
        $width = 800;
        $height = 600;

        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        imagefill($image, 0, 0, $white);

        // Add some basic text
        imagestring($image, 5, 10, 10, 'Dataset Export (Image format)', $black);
        imagestring($image, 3, 10, 40, 'Note: Full HTML to image conversion requires additional setup', $black);

        imagepng($image, $outputPath);
        imagedestroy($image);
    }

    public function analyze($id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        $rows = $dataset->rows()->limit(1000)->get();

        $analysis = $this->performAnalysis($rows, $dataset->columns);

        return view('datasets.analysis', compact('dataset', 'analysis'));
    }

    private function performAnalysis($rows, $columns)
    {
        $analysis = [
            'total_rows' => count($rows),
            'column_stats' => []
        ];

        foreach ($columns as $column) {
            $values = $rows->pluck("data.$column")->filter();
            $analysis['column_stats'][$column] = [
                'count' => $values->count(),
                'unique' => $values->unique()->count(),
                'empty' => $rows->count() - $values->count()
            ];
        }

        return $analysis;
    }

    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);
        
        try {
            $data = Excel::toArray([], $request->file('file'));
            $firstRow = $data[0][0] ?? [];
            
            $columns = [];
            if (!is_numeric(key($firstRow))) {
                $columns = array_keys($firstRow);
                array_shift($data[0]); // Remove header
            } else {
                $columnCount = count($firstRow);
                for ($i = 1; $i <= $columnCount; $i++) {
                    $columns[] = 'Kolom ' . $i;
                }
            }
            
            $previewRows = array_slice($data[0], 0, 5); // First 5 rows
            
            return response()->json([
                'success' => true,
                'columns' => $columns,
                'total_rows' => count($data[0]),
                'preview' => $previewRows
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file: ' . $e->getMessage()
            ], 400);
        }
    }

    public function previewOcr(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif'
        ]);
        
        try {
            // Process OCR and return preview
            $imagePath = $request->file('image')->getPathname();
            
            $text = (new TesseractOCR($imagePath))
                ->lang('eng+ind')
                ->run();
                
            $parsedData = $this->parseTableFromText($text);
            
            return response()->json([
                'success' => true,
                'columns' => $parsedData['columns'],
                'total_rows' => count($parsedData['rows']),
                'preview' => array_slice($parsedData['rows'], 0, 5)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses OCR: ' . $e->getMessage()
            ], 400);
        }
    }

    private function parseTableFromText($text)
    {
        // Same logic as in OcrController
        $lines = explode("\n", trim($text));
        $data = ['columns' => [], 'rows' => []];
        
        $maxColumns = 0;
        $parsedRows = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $cells = preg_split('/\s{2,}|\t|\|/', $line);
            $cells = array_map('trim', $cells);
            $cells = array_filter($cells);
            
            if (!empty($cells)) {
                $parsedRows[] = array_values($cells);
                $maxColumns = max($maxColumns, count($cells));
            }
        }
        
        for ($i = 1; $i <= $maxColumns; $i++) {
            $data['columns'][] = 'Kolom ' . $i;
        }
        
        if (!empty($parsedRows[0]) && count($parsedRows[0]) <= 5) {
            $firstRow = $parsedRows[0];
            $looksLikeHeaders = true;
            
            foreach ($firstRow as $cell) {
                if (is_numeric($cell) || strlen($cell) > 50) {
                    $looksLikeHeaders = false;
                    break;
                }
            }
            
            if ($looksLikeHeaders) {
                $data['columns'] = $firstRow;
                array_shift($parsedRows);
            }
        }
        
        $data['rows'] = $parsedRows;
        return $data;
    }

    public function getStats($id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        $stats = [
            'total_rows' => $dataset->row_count,
            'total_columns' => count($dataset->columns),
            'created_at' => $dataset->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $dataset->updated_at->format('Y-m-d H:i:s'),
            'row_distribution' => $this->getRowDistribution($dataset),
        ];

        return response()->json($stats);
    }

    public function getRows($id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        $rows = $dataset->rows()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'data' => $rows->items(),
            'total' => $rows->total(),
            'per_page' => $rows->perPage(),
            'current_page' => $rows->currentPage(),
            'last_page' => $rows->lastPage(),
        ]);
    }

    public function search(Request $request, $id)
    {
        $dataset = Dataset::findOrFail($id);

        // Check if user has access to this dataset
        if (!$dataset->hasAccess()) {
            abort(404);
        }

        $searchTerm = $request->get('q', '');

        $rows = DatasetRow::where('dataset_id', $dataset->id)
            ->where(function ($query) use ($searchTerm, $dataset) {
                $columnNames = $dataset->column_definitions->pluck('name')->toArray();
                foreach ($columnNames as $column) {
                    $query->orWhere('data->' . $column, 'like', '%' . $searchTerm . '%');
                }
            })
            ->paginate(50);

        return response()->json([
            'data' => $rows->items(),
            'total' => $rows->total(),
        ]);
    }

    public function publicView($id)
    {
        $dataset = Dataset::findOrFail($id);
        
        // Check if dataset is public (you need to add a 'is_public' field to datasets table)
        if (!$dataset->is_public) {
            abort(404);
        }
        
        $rows = $dataset->rows()->paginate(50);
        
        return view('datasets.public', compact('dataset', 'rows'));
    }

    private function getRowDistribution($dataset)
    {
        // Get distribution of rows by date
        $distribution = DatasetRow::where('dataset_id', $dataset->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return $distribution;
    }
}