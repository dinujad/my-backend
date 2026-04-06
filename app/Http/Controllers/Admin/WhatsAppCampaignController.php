<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WhatsAppCampaignController extends Controller
{
    public function index()
    {
        return view('admin.whatsapp.campaigns');
    }

    public function downloadSample()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="whatsapp_campaign_sample.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Phone Number']);
            fputcsv($file, ['+94712345678']);
            fputcsv($file, ['0712345678']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function send(Request $request, WhatsAppService $whatsAppService)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'manual_numbers' => 'nullable|string',
            'csv_file' => 'nullable|file|mimes:csv,txt|max:2048',
        ]);

        if (empty($request->manual_numbers) && !$request->hasFile('csv_file')) {
            throw ValidationException::withMessages([
                'manual_numbers' => 'Please provide phone numbers manually or upload a CSV file.',
            ]);
        }

        $numbers = [];

        // Parse manual numbers
        if (!empty($request->manual_numbers)) {
            // Split by comma, newline, spaces
            $manual = preg_split('/[\s,]+/', $request->manual_numbers, -1, PREG_SPLIT_NO_EMPTY);
            $numbers = array_merge($numbers, $manual);
        }

        // Parse CSV
        if ($request->hasFile('csv_file')) {
            $path = $request->file('csv_file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
            // skip header if present
            $startIndex = (isset($data[0][0]) && strtolower(trim($data[0][0])) === 'phone number') ? 1 : 0;
            
            for ($i = $startIndex; $i < count($data); $i++) {
                if (isset($data[$i][0]) && !empty(trim($data[$i][0]))) {
                    $numbers[] = trim($data[$i][0]);
                }
            }
        }

        $numbers = array_unique($numbers);
        $successCount = 0;
        $failCount = 0;

        foreach ($numbers as $number) {
            $number = trim($number);
            if (empty($number)) continue;

            $sent = $whatsAppService->sendMessage($number, $request->message);
            if ($sent) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $total = $successCount + $failCount;
        $status = "Campaign finished. Successfully sent to {$successCount} of {$total} numbers.";
        if ($failCount > 0) {
            Log::warning("Bulk WhatsApp campaign finished with failures: $successCount success, $failCount failed.");
        }

        return redirect()->route('admin.whatsapp.campaigns')->with('success', $status);
    }
}
