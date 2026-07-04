<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    public function sendToDevice(DeviceToken $device, string $title, string $body, array $data = []): bool
    {
        $serverKey = Setting::get('firebase_key');

        if (!$serverKey) {
            Log::warning('FCM server key not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $device->token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ]);

            if ($response->successful()) {
                return true;
            }

            if ($response->json('results.0.error') === 'InvalidRegistration' ||
                $response->json('results.0.error') === 'NotRegistered') {
                $device->update(['is_active' => false]);
            }

            Log::warning('FCM response error', ['response' => $response->json()]);
            return false;
        } catch (\Exception $e) {
            Log::error('FCM send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): int
    {
        $tokens = $user->deviceTokens()->where('is_active', true)->get();
        $sent = 0;

        foreach ($tokens as $device) {
            if ($this->sendToDevice($device, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function sendToAll(string $title, string $body, array $data = []): int
    {
        $tokens = DeviceToken::where('is_active', true)->get();
        $sent = 0;

        foreach ($tokens as $device) {
            if ($this->sendToDevice($device, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function registerToken(User $user, string $token, string $platform = 'android', ?string $deviceName = null): void
    {
        DeviceToken::updateOrCreate(
            ['user_id' => $user->id, 'token' => $token],
            [
                'platform' => $platform,
                'device_name' => $deviceName,
                'is_active' => true,
            ]
        );
    }

    public function removeToken(string $token): void
    {
        DeviceToken::where('token', $token)->update(['is_active' => false]);
    }

    public function sendTestResultNotification(User $user, string $testName, float $score, float $accuracy): void
    {
        $this->sendToUser(
            $user,
            'Test Completed! 🎯',
            "You scored {$score} marks ({$accuracy}%) in {$testName}",
            ['type' => 'test_result', 'screen' => 'result']
        );
    }

    public function sendStreakReminder(User $user, int $streak): void
    {
        $this->sendToUser(
            $user,
            "Keep your {$streak}-day streak alive! 🔥",
            'Take a mock test today to maintain your streak.',
            ['type' => 'streak_reminder', 'screen' => 'home']
        );
    }

    public function sendNewTestNotification(string $testName, string $examName): void
    {
        $this->sendToAll(
            'New Mock Test Available! 📝',
            "{$testName} for {$examName} is now live.",
            ['type' => 'new_test', 'screen' => 'mock_tests']
        );
    }
}
