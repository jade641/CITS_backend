<?php

/**
 * Authentication Test Script
 * 
 * Run this to verify that authentication is working properly.
 * 
 * Usage: php test-auth.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

echo "=================================================\n";
echo "🔐 Authentication Test Script\n";
echo "=================================================\n\n";

// Test 1: Check database connection
echo "Test 1: Database Connection\n";
try {
    $userCount = User::count();
    echo "✅ Database connected! Found {$userCount} users.\n\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: {$e->getMessage()}\n\n";
    exit(1);
}

// Test 2: List all users
echo "Test 2: List All Users\n";
echo "------------------------\n";
$users = User::select('id', 'name', 'email')->get();
foreach ($users as $user) {
    echo "ID: {$user->id} | Email: {$user->email} | Name: {$user->name}\n";
}
echo "\n";

// Test 3: Test authentication for each user
echo "Test 3: Test Authentication\n";
echo "----------------------------\n";
$testPassword = 'password';

foreach ($users as $user) {
    $result = Auth::attempt([
        'email' => $user->email,
        'password' => $testPassword
    ]);
    
    $status = $result ? '✅ SUCCESS' : '❌ FAILED';
    echo "{$status} - {$user->email}\n";
    
    // Logout after each test
    Auth::logout();
}
echo "\n";

// Test 4: Check password hashes
echo "Test 4: Verify Password Hashes\n";
echo "--------------------------------\n";
foreach ($users as $user) {
    $fullUser = User::find($user->id);
    $hashValid = Hash::check($testPassword, $fullUser->password);
    $status = $hashValid ? '✅ VALID' : '❌ INVALID';
    echo "{$status} - {$user->email}\n";
}
echo "\n";

// Test 5: Check roles and permissions
echo "Test 5: Check User Roles\n";
echo "-------------------------\n";
foreach ($users as $user) {
    $fullUser = User::with('roles')->find($user->id);
    $roles = $fullUser->roles->pluck('name')->join(', ');
    echo "{$user->email}: {$roles}\n";
}
echo "\n";

echo "=================================================\n";
echo "✅ All tests completed!\n";
echo "=================================================\n";
echo "\nLogin Credentials:\n";
echo "-------------------\n";
foreach ($users as $user) {
    echo "Email: {$user->email}\n";
    echo "Password: {$testPassword}\n\n";
}
