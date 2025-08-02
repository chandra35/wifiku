<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Exception;
use Illuminate\Support\Facades\Log;

class MikrotikService
{
    protected $client;
    protected $config;

    public function __construct()
    {
        $this->config = new Config();
    }

    /**
     * Test connection to RouterOS
     */
    public function testConnection($host, $username, $password, $port = 8728): array
    {
        try {
            Log::info('Testing MikroTik connection', [
                'host' => $host,
                'username' => $username,
                'port' => $port
            ]);

            // Validate inputs
            if (empty($host) || empty($username)) {
                return [
                    'success' => false,
                    'message' => 'Host and username are required',
                    'data' => null
                ];
            }

            // Check if host is reachable
            if (!filter_var($host, FILTER_VALIDATE_IP)) {
                return [
                    'success' => false,
                    'message' => 'Invalid IP address format',
                    'data' => null
                ];
            }

            $config = (new Config())
                ->set('host', $host)
                ->set('user', $username)
                ->set('pass', $password)
                ->set('port', (int)$port)
                ->set('timeout', 5); // 5 second timeout

            Log::info('Creating MikroTik client with config');
            $client = new Client($config);
            
            // Try to get system identity to test connection
            Log::info('Sending test query to MikroTik');
            $query = new Query('/system/identity/print');
            $response = $client->query($query)->read();
            
            Log::info('MikroTik connection successful', ['response' => $response]);
            
            return [
                'success' => true,
                'message' => 'Connection successful',
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Mikrotik connection failed', [
                'host' => $host,
                'username' => $username,
                'port' => $port,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = $e->getMessage();
            
            // More specific error messages based on exception type
            if ($e instanceof \RouterOS\Exceptions\BadCredentialsException) {
                $errorMessage = 'Invalid username or password. Please check your MikroTik credentials.';
            } elseif ($e instanceof \RouterOS\Exceptions\ConnectException) {
                $errorMessage = 'Cannot connect to MikroTik. Please check IP address and ensure API is enabled.';
            } elseif ($e instanceof \RouterOS\Exceptions\ConfigException) {
                $errorMessage = 'Configuration error: ' . $e->getMessage();
            } elseif (strpos($errorMessage, 'Connection refused') !== false) {
                $errorMessage = 'Connection refused. Please check if MikroTik API is enabled and port ' . $port . ' is accessible.';
            } elseif (strpos($errorMessage, 'timeout') !== false) {
                $errorMessage = 'Connection timeout. Please check if host ' . $host . ' is reachable.';
            } elseif (strpos($errorMessage, 'No route to host') !== false) {
                $errorMessage = 'No route to host. Please check if IP address ' . $host . ' is correct and reachable.';
            }
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'data' => null
            ];
        }
    }

    /**
     * Connect to RouterOS with given credentials
     */
    public function connect($host, $username, $password, $port = 8728): bool
    {
        try {
            Log::info('MikrotikService connect called', [
                'host' => $host,
                'username' => $username,
                'port' => $port
            ]);

            $this->config = (new Config())
                ->set('host', $host)
                ->set('user', $username)
                ->set('pass', $password)
                ->set('port', (int)$port);

            $this->client = new Client($this->config);
            
            // Test the connection by getting system identity
            $response = $this->client->query('/system/identity/print')->read();
            
            Log::info('MikroTik client created and tested successfully', ['response' => $response]);
            return true;
        } catch (Exception $e) {
            Log::error('Mikrotik connection failed: ' . $e->getMessage(), [
                'host' => $host,
                'username' => $username,
                'port' => $port
            ]);
            $this->client = null;
            return false;
        }
    }

    /**
     * Get all PPP secrets
     */
    public function getPppSecrets(): array
    {
        try {
            if (!$this->client) {
                throw new Exception('Not connected to RouterOS');
            }

            $query = new Query('/ppp/secret/print');
            $response = $this->client->query($query)->read();
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Failed to get PPP secrets: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Create PPP secret
     */
    public function createPppSecret($data): array
    {
        try {
            if (!$this->client) {
                throw new Exception('Not connected to RouterOS');
            }

            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $data['username'])
                ->equal('password', $data['password']);

            if (isset($data['service'])) {
                $query->equal('service', $data['service']);
            }
            
            if (isset($data['profile'])) {
                $query->equal('profile', $data['profile']);
            }
            
            if (isset($data['local_address'])) {
                $query->equal('local-address', $data['local_address']);
            }
            
            if (isset($data['remote_address'])) {
                $query->equal('remote-address', $data['remote_address']);
            }
            
            if (isset($data['comment'])) {
                $query->equal('comment', $data['comment']);
            }

            if (isset($data['disabled']) && $data['disabled']) {
                $query->equal('disabled', 'yes');
            }

            $response = $this->client->query($query)->read();
            
            return [
                'success' => true,
                'data' => $response,
                'id' => $response[0]['after']['ret'] ?? null
            ];
        } catch (Exception $e) {
            Log::error('Failed to create PPP secret: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update PPP secret
     */
    public function updatePppSecret($mikrotikId, $data): array
    {
        try {
            if (!$this->client) {
                throw new Exception('Not connected to RouterOS');
            }

            $query = (new Query('/ppp/secret/set'))
                ->equal('.id', $mikrotikId);

            if (isset($data['password'])) {
                $query->equal('password', $data['password']);
            }
            
            if (isset($data['service'])) {
                $query->equal('service', $data['service']);
            }
            
            if (isset($data['profile'])) {
                $query->equal('profile', $data['profile']);
            }
            
            if (isset($data['local_address'])) {
                $query->equal('local-address', $data['local_address']);
            }
            
            if (isset($data['remote_address'])) {
                $query->equal('remote-address', $data['remote_address']);
            }
            
            if (isset($data['comment'])) {
                $query->equal('comment', $data['comment']);
            }

            if (isset($data['disabled'])) {
                $query->equal('disabled', $data['disabled'] ? 'yes' : 'no');
            }

            $response = $this->client->query($query)->read();
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Failed to update PPP secret: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Delete PPP secret
     */
    public function deletePppSecret($mikrotikId): array
    {
        try {
            if (!$this->client) {
                throw new Exception('Not connected to RouterOS');
            }

            $query = (new Query('/ppp/secret/remove'))
                ->equal('.id', $mikrotikId);

            $response = $this->client->query($query)->read();
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Failed to delete PPP secret: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get PPP profiles
     */
    public function getPppProfiles(): array
    {
        try {
            if (!$this->client) {
                throw new Exception('Not connected to RouterOS');
            }

            Log::info('Getting PPP profiles from MikroTik');
            $query = new Query('/ppp/profile/print');
            $response = $this->client->query($query)->read();
            
            Log::info('Successfully retrieved PPP profiles', ['count' => count($response)]);
            
            return [
                'success' => true,
                'data' => $response
            ];
        } catch (Exception $e) {
            Log::error('Failed to get PPP profiles: ' . $e->getMessage(), [
                'client_exists' => !is_null($this->client),
                'error_class' => get_class($e)
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Get the RouterOS client instance
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Create PPP profile
     */
    public function createPppProfile($data): array
    {
        try {
            if (!$this->client) {
                throw new Exception('Not connected to RouterOS');
            }

            $query = new Query('/ppp/profile/add');
            
            // Add profile data
            foreach ($data as $key => $value) {
                if ($value !== null && $value !== '') {
                    $query->equal($key, $value);
                }
            }
            
            $response = $this->client->query($query)->read();
            
            // Get the created profile ID
            $profileId = $response[0]['after']['ret'] ?? null;
            
            Log::info('PPP profile created successfully', [
                'profile_name' => $data['name'],
                'profile_id' => $profileId
            ]);
            
            return [
                'success' => true,
                'message' => 'PPP profile created successfully',
                'id' => $profileId
            ];
        } catch (Exception $e) {
            Log::error('Failed to create PPP profile: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Update PPP profile
     */
    public function updatePppProfile($profileId, $data): array
    {
        try {
            if (!$this->client) {
                throw new Exception('Not connected to RouterOS');
            }

            $query = new Query('/ppp/profile/set');
            $query->equal('.id', $profileId);
            
            // Add updated data
            foreach ($data as $key => $value) {
                if ($value !== null) {
                    $query->equal($key, $value);
                }
            }
            
            $this->client->query($query)->read();
            
            Log::info('PPP profile updated successfully', [
                'profile_id' => $profileId
            ]);
            
            return [
                'success' => true,
                'message' => 'PPP profile updated successfully'
            ];
        } catch (Exception $e) {
            Log::error('Failed to update PPP profile: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete PPP profile
     */
    public function deletePppProfile($profileId): array
    {
        try {
            if (!$this->client) {
                throw new Exception('Not connected to RouterOS');
            }

            $query = new Query('/ppp/profile/remove');
            $query->equal('.id', $profileId);
            
            $this->client->query($query)->read();
            
            Log::info('PPP profile deleted successfully', [
                'profile_id' => $profileId
            ]);
            
            return [
                'success' => true,
                'message' => 'PPP profile deleted successfully'
            ];
        } catch (Exception $e) {
            Log::error('Failed to delete PPP profile: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get system resource information
     */
    public function getSystemResource(): array
    {
        try {
            if (!$this->client) {
                return [
                    'success' => false,
                    'message' => 'Not connected to router',
                    'data' => null
                ];
            }

            $query = new Query('/system/resource/print');
            $response = $this->client->query($query)->read();

            if (empty($response)) {
                return [
                    'success' => false,
                    'message' => 'No system resource data found',
                    'data' => null
                ];
            }

            $resource = $response[0];
            
            return [
                'success' => true,
                'data' => [
                    'cpu_load' => isset($resource['cpu-load']) ? (int)$resource['cpu-load'] : 0,
                    'free_memory' => isset($resource['free-memory']) ? (int)$resource['free-memory'] : 0,
                    'total_memory' => isset($resource['total-memory']) ? (int)$resource['total-memory'] : 0,
                    'used_memory' => isset($resource['total-memory'], $resource['free-memory']) 
                        ? (int)$resource['total-memory'] - (int)$resource['free-memory'] : 0,
                    'memory_usage_percent' => isset($resource['total-memory'], $resource['free-memory']) && (int)$resource['total-memory'] > 0
                        ? round(((int)$resource['total-memory'] - (int)$resource['free-memory']) / (int)$resource['total-memory'] * 100, 1) : 0,
                    'uptime' => $resource['uptime'] ?? '0s',
                    'version' => $resource['version'] ?? 'Unknown',
                    'board_name' => $resource['board-name'] ?? 'Unknown',
                    'architecture_name' => $resource['architecture-name'] ?? 'Unknown'
                ]
            ];
        } catch (Exception $e) {
            Log::error('Failed to get system resource: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get active PPP sessions count
     */
    public function getActivePppSessions(): array
    {
        try {
            if (!$this->client) {
                return [
                    'success' => false,
                    'message' => 'Not connected to router',
                    'data' => null
                ];
            }

            $query = new Query('/ppp/active/print');
            $response = $this->client->query($query)->read();

            return [
                'success' => true,
                'data' => [
                    'active_sessions' => count($response),
                    'sessions' => $response
                ]
            ];
        } catch (Exception $e) {
            Log::error('Failed to get active PPP sessions: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get router status information (CPU, RAM, active PPP)
     */
    public function getRouterStatus(): array
    {
        try {
            $systemResource = $this->getSystemResource();
            $activePpp = $this->getActivePppSessions();

            if (!$systemResource['success']) {
                return $systemResource;
            }

            $statusData = $systemResource['data'];
            
            if ($activePpp['success']) {
                $statusData['active_ppp_sessions'] = $activePpp['data']['active_sessions'];
            } else {
                $statusData['active_ppp_sessions'] = 0;
            }

            return [
                'success' => true,
                'data' => $statusData
            ];
        } catch (Exception $e) {
            Log::error('Failed to get router status: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Format memory size to human readable format
     */
    public function formatMemorySize($bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }

    /**
     * Check if client is connected
     */
    public function isConnected(): bool
    {
        return $this->client !== null;
    }

    /**
     * Ping to specific host and return average response time
     */
    public function ping($host, $count = 3): array
    {
        try {
            if (!$this->client) {
                return [
                    'success' => false,
                    'message' => 'Not connected to router',
                    'data' => null
                ];
            }

            $query = new Query('/ping');
            $query->equal('address', $host);
            $query->equal('count', $count);
            
            $response = $this->client->query($query)->read();
            
            if (empty($response)) {
                return [
                    'success' => false,
                    'message' => 'No response from ping',
                    'data' => null
                ];
            }

            $totalTime = 0;
            $successCount = 0;
            
            foreach ($response as $result) {
                if (isset($result['time']) && $result['time'] !== 'timeout') {
                    // Parse RouterOS time format (e.g., "17ms454us" should be 17.454ms)
                    $timeStr = $result['time'];
                    $time = $this->parseRouterOSTime($timeStr);
                    
                    if ($time !== null) {
                        $totalTime += $time;
                        $successCount++;
                    }
                }
            }

            if ($successCount > 0) {
                $avgTime = round($totalTime / $successCount, 1);
                return [
                    'success' => true,
                    'message' => 'Ping successful',
                    'data' => [
                        'avg_time' => $avgTime,
                        'host' => $host,
                        'success_count' => $successCount,
                        'total_count' => $count
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'All ping attempts failed',
                    'data' => null
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Ping failed', [
                'host' => $host,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Ping error: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Parse RouterOS time format to milliseconds
     * Examples: "17ms454us" -> 17.454, "1s500ms" -> 1500, "100ms" -> 100
     */
    private function parseRouterOSTime($timeStr): ?float
    {
        if (empty($timeStr) || $timeStr === 'timeout') {
            return null;
        }

        $totalMs = 0;
        
        // Parse seconds (s)
        if (preg_match('/(\d+(?:\.\d+)?)s/', $timeStr, $matches)) {
            $totalMs += floatval($matches[1]) * 1000;
        }
        
        // Parse milliseconds (ms)
        if (preg_match('/(\d+(?:\.\d+)?)ms/', $timeStr, $matches)) {
            $totalMs += floatval($matches[1]);
        }
        
        // Parse microseconds (us) - convert to milliseconds
        if (preg_match('/(\d+(?:\.\d+)?)us/', $timeStr, $matches)) {
            $totalMs += floatval($matches[1]) / 1000;
        }
        
        // Parse nanoseconds (ns) - convert to milliseconds  
        if (preg_match('/(\d+(?:\.\d+)?)ns/', $timeStr, $matches)) {
            $totalMs += floatval($matches[1]) / 1000000;
        }
        
        return $totalMs > 0 ? $totalMs : null;
    }
}
