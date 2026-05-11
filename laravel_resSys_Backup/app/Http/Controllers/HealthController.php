<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $status = [];

        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $status['database'] = 'OK';
        } catch (\Exception $exception) {
            $status['database'] = 'Error';
        }

        try {
            Cache::store('redis')->put('health_check', 'OK', 10);
            $value = Cache::store('redis')->get('health_check');
            $status['redis'] = $value === 'OK' ? 'OK' : 'Error';
        } catch (\Exception $exception) {
            $status['redis'] = 'Error';
        }

        try {
            $testFile = 'health_check.txt';
            Storage::put($testFile, 'OK');
            $content = Storage::get($testFile);
            Storage::delete($testFile);

            $status['storage'] = $content === 'OK' ? 'OK' : 'Error';
        } catch (\Exception $exception) {
            $status['storage'] = 'Error';
        }

        $isHealthy = collect($status)->every(fn ($value) => $value === 'OK');

        return response()->json($status, $isHealthy ? 200 : 503);
    }
}
