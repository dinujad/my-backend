<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AiChatRequest;
use App\Services\AiService;
use App\Services\LlmGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Throwable;

class AiController extends Controller
{
    public function overview(Request $request, AiService $ai): \Illuminate\View\View
    {
        $period = (string) $request->query('period', 'last_30_days');

        try {
            $overview = $ai->getOverview($period);
            return view('admin.ai.overview', ['overview' => $overview, 'period' => $period]);
        } catch (Throwable $e) {
            return view('admin.ai.overview', [
                'overview' => null,
                'period' => $period,
                'error' => 'Could not load overview data.',
            ]);
        }
    }

    public function predictions(Request $request, AiService $ai): \Illuminate\View\View
    {
        $period = (string) $request->query('period', 'last_30_days');

        try {
            $overview = $ai->getOverview($period);
            return view('admin.ai.predictions', ['overview' => $overview, 'period' => $period]);
        } catch (Throwable $e) {
            return view('admin.ai.predictions', [
                'overview' => null,
                'period' => $period,
                'error' => 'Could not load overview data.',
            ]);
        }
    }

    public function chatUi(): \Illuminate\View\View
    {
        return view('admin.ai.chat');
    }

    /**
     * AJAX endpoint: admin chat -> Gemini (Laravel) using live store data.
     */
    public function chatApi(AiChatRequest $request, AiService $ai): JsonResponse
    {
        $message = $request->validated('message');
        $adminId = $request->user()?->id;

        try {
            $result = $ai->chat($message, $adminId);

            return response()->json([
                'response_text' => Arr::get($result, 'response_text', 'No response available.'),
                'intent' => Arr::get($result, 'intent', 'unknown'),
                'data' => Arr::get($result, 'data'),
                'metrics' => Arr::get($result, 'metrics'),
                'recommendations' => Arr::get($result, 'recommendations'),
                'table_data' => Arr::get($result, 'table_data'),
                'confidence' => Arr::get($result, 'confidence', 0),
                'error' => Arr::get($result, 'error'),
            ]);
        } catch (Throwable $e) {
            $msg = $e->getMessage();
            $isRateLimit = LlmGatewayService::isQuotaOrRateLimitError($msg);
            $friendly = $isRateLimit
                ? LlmGatewayService::friendlyQuotaMessage($msg)
                : 'AI request failed. Check that AI API keys are set in backend environment settings, then try again.';

            return response()->json([
                'response_text' => $friendly,
                'intent' => 'unknown',
                'data' => null,
                'metrics' => null,
                'recommendations' => null,
                'table_data' => null,
                'confidence' => 0,
                'error' => LlmGatewayService::userFacingErrorCode($msg),
            ], $isRateLimit ? 200 : 503);
        }
    }
}

