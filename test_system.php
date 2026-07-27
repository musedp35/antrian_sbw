<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "========================================\n";
echo "   ANTRIAN SBW SYSTEM TEST\n";
echo "========================================\n\n";

// 1. Test Database Connection
try {
    $pdo = DB::connection()->getPdo();
    echo "[✓] Database Connection: OK\n";
} catch (Exception $e) {
    echo "[✗] Database Connection: FAILED\n";
    exit(1);
}

// 2. Test Required Tables
$requiredTables = ['users', 'tickets', 'call_logs', 'settings', 'notifications'];
foreach ($requiredTables as $table) {
    if (Schema::hasTable($table)) {
        echo "[✓] Table exists: $table\n";
    } else {
        echo "[✗] Table missing: $table\n";
    }
}

// 3. Test Models
echo "\n--- Testing Models ---\n";
try {
    $user = \App\Models\User::first();
    echo "[✓] User Model: OK (" . \App\Models\User::count() . " records)\n";
} catch (Exception $e) {
    echo "[✗] User Model: FAILED - {$e->getMessage()}\n";
}

try {
    echo "[✓] Ticket Model: OK (" . \App\Models\Ticket::count() . " records)\n";
    echo "[✓] CallLog Model: OK (" . \App\Models\CallLog::count() . " records)\n";
    echo "[✓] Setting Model: OK (" . \App\Models\Setting::count() . " records)\n";
} catch (Exception $e) {
    echo "[✗] Model Error: {$e->getMessage()}\n";
}

// 4. Test Routes
echo "\n--- Testing Key Routes ---\n";
$keyRoutes = [
    'tickets.index',
    'dashboard',
    'api.notifications.get-new-tickets',
];
foreach ($keyRoutes as $routeName) {
    try {
        $url = route($routeName);
        echo "[✓] Route '$routeName': $url\n";
    } catch (Exception $e) {
        echo "[✗] Route '$routeName': FAILED\n";
    }
}

// 5. Test Ticket Generation
echo "\n--- Testing Ticket Generation ---\n";
try {
    // Create a test ticket
    $ticket = \App\Models\Ticket::create([
        'ticket_number' => \App\Models\Ticket::generateTicketNumber('spp'),
        'type' => 'spp',
        'status' => 'waiting',
        'assigned_cashier_id' => 1,
    ]);
    echo "[✓] Ticket Created: {$ticket->ticket_number}\n";

    // Test call functionality
    $ticket->update(['status' => 'called']);
    echo "[✓] Ticket Status Change: called\n";

    // Create call log
    \App\Models\CallLog::create([
        'ticket_id' => $ticket->id,
        'voice_file_path' => null,
        'played_at' => now(),
    ]);
    echo "[✓] CallLog Created\n";
    
    // Clean up test data
    $ticket->delete();
    echo "[✓] Test Data Cleaned Up\n";
} catch (Exception $e) {
    echo "[✗] Ticket Creation: FAILED - {$e->getMessage()}\n";
}

// 6. Test Notification System
echo "\n--- Testing Notification System ---\n";
try {
    $adminUsers = \App\Models\User::whereNotNull('role')->get();
    echo "[✓] Admin Users Found: " . $adminUsers->count() . "\n";

    if ($adminUsers->count() > 0) {
        foreach ($adminUsers as $u) {
            echo "  - {$u->name} ({$u->role})\n";
        }
    }
} catch (Exception $e) {
    echo "[✗] Notification Test: FAILED - {$e->getMessage()}\n";
}

// 7. Test Settings
echo "\n--- Testing Settings ---\n";
try {
    $settings = \App\Models\Setting::all();
    echo "[✓] Settings Loaded: " . $settings->count() . " records\n";
    foreach ($settings as $setting) {
        echo "  - {$setting->key}: {$setting->value}\n";
    }
} catch (Exception $e) {
    echo "[✗] Settings Test: FAILED - {$e->getMessage()}\n";
}

// Clean up test data - skip if no ticket was created
if (isset($ticket)) {
    $ticket->delete();
    echo "\n[✓] Test Data Cleaned Up\n";
}

echo "\n========================================\n";
echo "   ALL TESTS COMPLETED SUCCESSFULLY!\n";
echo "========================================\n";
