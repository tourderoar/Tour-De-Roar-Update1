<?php
/**
 * Admin Users View
 * View all registered users
 */

define('ADMIN_PAGE', true);
$page_title = 'Users';

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Get all users
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="margin-bottom: 2rem;">
    <h3 style="font-size: 1.5rem; font-weight: 700; color: #2d3748; margin: 0;">Registered Users</h3>
    <p style="color: #718096; margin: 0.5rem 0 0 0;">View all user accounts</p>
</div>

<!-- Users Table -->
<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #a0aec0; padding: 3rem;">
                        <i class="fas fa-users text-4xl mb-4" style="display: block;"></i>
                        No users yet
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong>#<?= $user['id'] ?></strong></td>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone'] ?? '—') ?></td>
                        <td>
                            <?php if ($user['status'] === 'active'): ?>
                                <span class="badge badge-success">Active</span>
                            <?php elseif ($user['status'] === 'inactive'): ?>
                                <span class="badge badge-danger">Inactive</span>
                            <?php else: ?>
                                <span class="badge badge-warning"><?= ucfirst($user['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
