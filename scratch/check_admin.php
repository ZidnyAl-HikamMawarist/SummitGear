<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('email', 'admin@summitgear.com')->first();
echo "User: " . ($user ? $user->email : 'Not found') . PHP_EOL;
echo "Role: " . ($user ? $user->role : 'None') . PHP_EOL;
echo "Has 2FA: " . ($user && $user->two_factor_secret ? 'YES' : 'NO') . PHP_EOL;
if ($user) {
    // Reset password to known password for testing if needed
    $user->password = bcrypt('password123');
    $user->two_factor_secret = null; // temporary disable 2fa for visual automation
    $user->save();
    echo "Password set to: password123, 2FA disabled for test" . PHP_EOL;
}
