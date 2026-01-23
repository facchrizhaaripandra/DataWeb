<?php

namespace App\Http\Controllers;

use App\Models\Dataset;
use App\Models\DatasetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DataImport;

class DatasetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $datasets = Dataset::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('datasets.index', compact('datasets'));
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
            'columns.*' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $dataset = Dataset::create([
            'name' => $request->name,
            'description' => $request->description,
            'columns' => $request->columns,
            'user_id' => auth()->id()
        ]);

        return redirect()->route('datasets.show', $dataset->id)
            ->with('success', 'Dataset berhasil dibuat!');
    }

    public function addColumn(Request $request, $id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'column_name' => 'required|string|max:255'
        ]);
        
        $columns = $dataset->columns;
        $columns[] = $request->column_name;
        
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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string|max:255'
        ]);
        
        $columns = $dataset->columns;
        $index = array_search($request->old_name, $columns);
        
        if ($index !== false) {
            $columns[$index] = $request->new_name;
            $dataset->update(['columns' => $columns]);
            
            // Update all rows with new column name
            DatasetRow::where('dataset_id', $dataset->id)->chunk(100, function($rows) use ($request) {
                foreach ($rows as $row) {
                    $data = $row->data;
                    if (isset($data[$request->old_name])) {
                        $data[$request->new_name] = $data[$request->old_name];
                        unset($data[$request->old_name]);
                        $row->update(['data' => $data]);
                    }
                }
            });
            
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false], 400);
    }

    public function deleteColumn(Request $request, $id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
        $request->validate([
            'columns' => 'required|array'
        ]);
        
        $dataset->update(['columns' => $request->columns]);
        
        // Reorder data in all rows according to new column order
        DatasetRow::where('dataset_id', $dataset->id)->chunk(100, function($rows) use ($request) {
            foreach ($rows as $row) {
                $newData = [];
                foreach ($request->columns as $column) {
                    $newData[$column] = $row->data[$column] ?? null;
                }
                $row->update(['data' => $newData]);
            }
        });
        
        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        $rows = $dataset->rows()->paginate(50);
        
        return view('datasets.show', compact('dataset', 'rows'));
    }

    public function edit($id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        return view('datasets.edit', compact('dataset'));
    }

    public function update(Request $request, $id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);

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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($datasetId);
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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($datasetId);
        $row = DatasetRow::where('dataset_id', $datasetId)->findOrFail($rowId);
        
        $row->delete();
        $dataset->decrement('row_count');

        return response()->json(['success' => true]);
    }

    public function editRowForm($datasetId, $rowId)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($datasetId);
        $row = DatasetRow::where('dataset_id', $datasetId)->findOrFail($rowId);
        
        $html = view('partials.edit-row-form', compact('dataset', 'row'))->render();
        
        return response()->json(['html' => $html]);
    }

    public function deleteSelectedRows(Request $request, $id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);

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

    public function export($id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
        return Excel::download(new DataExport($dataset), $dataset->name . '.xlsx');
    }

    public function analyze($id)
    {
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
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
        $dataset = Dataset::where('user_id', auth()->id())->findOrFail($id);
        
        $searchTerm = $request->get('q', '');
        
        $rows = DatasetRow::where('dataset_id', $dataset->id)
            ->where(function ($query) use ($searchTerm, $dataset) {
                foreach ($dataset->columns as $column) {
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