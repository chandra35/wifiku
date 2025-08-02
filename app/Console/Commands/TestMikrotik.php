<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;
use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;

class TestMikrotik extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mikrotik:test {host} {username} {password} {--port=8728}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test MikroTik RouterOS API connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = $this->argument('host');
        $username = $this->argument('username');
        $password = $this->argument('password');
        $port = $this->option('port');

        $this->info('Testing connection to MikroTik...');
        $this->info("Host: {$host}");
        $this->info("Username: {$username}");
        $this->info("Port: {$port}");

        // Test basic connectivity first
        $this->info('Testing basic connectivity...');
        if (!filter_var($host, FILTER_VALIDATE_IP)) {
            $this->error('Invalid IP address format');
            return 1;
        }

        // Test if host is reachable
        $this->info('Testing if host is reachable...');
        $connection = @fsockopen($host, $port, $errno, $errstr, 5);
        if (!$connection) {
            $this->error("Cannot connect to {$host}:{$port}");
            $this->error("Error: {$errstr} (Code: {$errno})");
            $this->info('Common issues:');
            $this->info('1. MikroTik API is not enabled');
            $this->info('2. Firewall blocking port ' . $port);
            $this->info('3. Wrong IP address or port');
            $this->info('4. MikroTik is not running');
            return 1;
        } else {
            fclose($connection);
            $this->info('Host is reachable on port ' . $port);
        }

        $service = new MikrotikService();
        
        $this->info('Testing MikroTik API connection...');
        
        try {
            $result = $service->testConnection($host, $username, $password, $port);
            
            if ($result['success']) {
                $this->info('✅ Connection successful!');
                $this->info('Response: ' . json_encode($result['data'], JSON_PRETTY_PRINT));
            } else {
                $this->error('❌ Connection failed!');
                $this->error('Error: ' . $result['message']);
            }
        } catch (\Exception $e) {
            $this->error('❌ Connection failed!');
            $this->error('Error: ' . $e->getMessage());
            $this->error('Class: ' . get_class($e));
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
}
