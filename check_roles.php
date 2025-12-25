$users = App\Models\User::with('roles')->get();
foreach($users as $user) {
echo "User: " . $user->email . " | Roles: " . $user->roles->pluck('role_name')->implode(', ') . "\n";
}