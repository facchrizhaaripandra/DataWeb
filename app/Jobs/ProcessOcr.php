<?php

namespace App\Jobs;

use App\Models\OcrResult;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ProcessOcr implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ocrResult;

    public function __construct(OcrResult $ocrResult)
    {
        $this->ocrResult = $ocrResult;
    }

    public function handle()
    {
        $this->ocrResult->update(['status' => 'processing']);
        
        try {
            $imagePath = storage_path('app/' . $this->ocrResult->image_path);
            
            $text = (new TesseractOCR($imagePath))
                ->lang('eng+ind')
                ->run();

            // Parse the text (simplified version)
            $lines = explode("\n", $text);
            $data = [];
            
            foreach ($lines as $line) {
                if (trim($line)) {
                    $data[] = array_map('trim', preg_split('/\s{2,}|\t/', $line));
                }
            }
            
            $this->ocrResult->update([
                'detected_data' => $data,
                'status' => 'processed'
            ]);
            
        } catch (\Exception $e) {
            $this->ocrResult->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
}