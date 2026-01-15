<?php

namespace App\Helpers;

use App\Models\Tournament;
use Ably\AblyRest;
use Illuminate\Support\Facades\Log;

class AblyHelper
{
    /**
     * Get Ably channel name for tournament display
     * 
     * @param Tournament $tournament
     * @param string $type 'best-race' or 'track'
     * @param int|null $trackNumber
     * @return string
     */
    public static function getChannelName(Tournament $tournament, string $type, ?int $trackNumber = null): string
    {
        $slug = $tournament->slug;
        
        if ($type === 'track' && $trackNumber) {
            return "{$slug}:track-{$trackNumber}";
        }
        
        return "{$slug}:{$type}";
    }
    
    /**
     * Publish message to Ably channel
     * 
     * @param string $channelName
     * @param string $eventName
     * @param array $data
     * @return bool Success status
     */
    public static function publish(string $channelName, string $eventName, array $data): bool
    {
        try {
            $key = config('services.ably.key');
            
            if (!$key) {
                Log::warning('Ably key not configured', [
                    'channel' => $channelName,
                    'event' => $eventName
                ]);
                return false;
            }
            
            // Initialize Ably with timeout options to prevent hanging
            $ably = new AblyRest($key, [
                'timeout' => 10, // 10 second timeout
                'tls' => true
            ]);
            
            $channel = $ably->channels->get($channelName);
            $channel->publish($eventName, $data);
            
            Log::info('Ably message published', [
                'channel' => $channelName,
                'event' => $eventName,
            ]);
            
            return true;
        } catch (\Ably\Exceptions\AblyException $e) {
            Log::error('Ably API error', [
                'channel' => $channelName,
                'event' => $eventName,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        } catch (\Exception $e) {
            Log::error('Ably publish failed', [
                'channel' => $channelName,
                'event' => $eventName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Publish best race update
     * 
     * @param Tournament $tournament
     * @param array $items
     * @return bool
     */
    public static function publishBestRace(Tournament $tournament, array $items): bool
    {
        $channelName = self::getChannelName($tournament, 'best-race');
        
        $data = [
            'type' => 'snapshot',
            'updatedAt' => now()->timestamp * 1000,
            'items' => $items
        ];
        
        return self::publish($channelName, 'update', $data);
    }
    
    /**
     * Publish track update
     * 
     * @param Tournament $tournament
     * @param int $trackNumber
     * @param array|null $btoData
     * @param array|null $sessionData
     * @return bool
     */
    public static function publishTrack(Tournament $tournament, int $trackNumber, ?array $btoData, ?array $sessionData): bool
    {
        $channelName = self::getChannelName($tournament, 'track', $trackNumber);
        
        $data = [
            'track' => $trackNumber,
            'bto' => $btoData,
            'sesi' => $sessionData
        ];
        
        return self::publish($channelName, 'update', $data);
    }
    
    /**
     * Convert timer string (MM:SS) to centiseconds
     * 
     * @param string $timer Format: "12:34"
     * @return int
     */
    public static function timerToCentiseconds(string $timer): int
    {
        $parts = explode(':', $timer);
        $seconds = (int) ($parts[0] ?? 0);
        $centiseconds = (int) ($parts[1] ?? 0);
        
        return ($seconds * 100) + $centiseconds;
    }
    
    /**
     * Convert centiseconds to timer string
     * 
     * @param int $centiseconds
     * @return string Format: "12:34"
     */
    public static function centisecondsToTimer(int $centiseconds): string
    {
        $seconds = floor($centiseconds / 100);
        $centi = $centiseconds % 100;
        
        return sprintf('%d:%02d', $seconds, $centi);
    }
}
