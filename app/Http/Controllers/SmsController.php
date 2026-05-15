<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendSmsRequest;
use App\Http\Resources\SmsMessageResource;
use App\Models\SmsMessage;
use App\Services\Sms\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SmsController extends Controller
{
    public function store(SendSmsRequest $request, SmsService $smsService): JsonResponse
    {
        $smsMessage = $smsService->send(
            $request->validated('recipient'),
            $request->validated('message'),
        );

        return (new SmsMessageResource($smsMessage))
            ->response()
            ->setStatusCode(202);
    }

    public function index(): AnonymousResourceCollection
    {
        $messages = SmsMessage::query()
            ->latest()
            ->paginate(20);

        return SmsMessageResource::collection($messages);
    }
}
