<?php
/**
 * Seed initial users with bcrypt passwords.
 * Safe to re-run: checks if users exist first.
 * Usage: php setup.php (then delete or keep — auto-skip if seeded)
 */
include 'koneksi.php';

$check = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($check > 0) {
    echo "✓ Users already seeded. Skipping.\n";
    exit;
}

$users = [
    ['admin', password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]), 'admin'],
    ['staff', password_hash('staff123', PASSWORD_BCRYPT, ['cost' => 12]), 'staff'],
];

$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:u, :p, :r)");
foreach ($users as $u) {
    $stmt->execute([':u' => $u[0], ':p' => $u[1], ':r' => $u[2]]);
    echo "  ✓ user '{$u[0]}' seeded\n";
}
echo "✓ Users seeded. Password: admin/admin123, staff/staff123\n";
