<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\RegisterDeviceRequest;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /** Register (or refresh) the calling device's push token. */
    public function store(RegisterDeviceRequest $request): JsonResponse
    {
        $device = DeviceToken::updateOrCreate(
            ['token' => $request->string('token')->value()],
            [
                'user_id' => $request->user()->id,
                'platform' => $request->string('platform')->value(),
                'locale' => $request->input('locale'),
                'last_used_at' => now(),
            ],
        );

        return response()->json(['id' => $device->id, 'platform' => $device->platform->value], 201);
    }

    /** Unregister a device (e.g. on logout). */
    public function destroy(Request $request, string $token): JsonResponse
    {
        $request->user()->deviceTokens()->where('token', $token)->delete();

        return response()->json(['message' => 'Dispositivo removido.']);
    }
}
