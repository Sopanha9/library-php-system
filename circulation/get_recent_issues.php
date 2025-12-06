<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    exit('Unauthorized');
}

try {
    $sql = "SELECT 
                ib.*,
                b.title as book_title,
                b.author as book_author,
                m.full_name as member_name,
                u.username as issued_by_name
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.book_id
            JOIN members m ON ib.member_id = m.member_id
            LEFT JOIN users u ON ib.issued_by = u.user_id
            WHERE ib.status = 'Issued'
            ORDER BY ib.created_at DESC
            LIMIT 5";
    
    $stmt = $pdo->query($sql);
    $issues = $stmt->fetchAll();
    
    if (count($issues) === 0) {
        echo '<div class="text-center py-8 text-gray-400">
                <i class="fas fa-inbox text-3xl mb-2"></i>
                <p class="text-sm">No recent issues</p>
              </div>';
        exit();
    }
    
    foreach ($issues as $issue):
        $daysUntilDue = floor((strtotime($issue['due_date']) - time()) / 86400);
        $dueClass = $daysUntilDue < 0 ? 'text-red-600' : ($daysUntilDue <= 3 ? 'text-orange-600' : 'text-green-600');
?>
        <div class="p-3 bg-gray-50 rounded-lg">
            <div class="flex items-start justify-between mb-2">
                <h4 class="font-semibold text-sm text-gray-800 truncate"><?= htmlspecialchars($issue['book_title']) ?></h4>
                <span class="text-xs px-2 py-1 bg-indigo-100 text-indigo-700 rounded flex-shrink-0 ml-2">
                    #<?= $issue['issue_id'] ?>
                </span>
            </div>
            <p class="text-xs text-gray-600 mb-1">
                <i class="fas fa-user mr-1"></i><?= htmlspecialchars($issue['member_name']) ?>
            </p>
            <div class="flex items-center justify-between text-xs">
                <span class="text-gray-500">
                    <i class="fas fa-calendar mr-1"></i><?= date('M d, Y', strtotime($issue['issue_date'])) ?>
                </span>
                <span class="<?= $dueClass ?> font-medium">
                    <i class="fas fa-clock mr-1"></i>
                    <?php if ($daysUntilDue < 0): ?>
                        <?= abs($daysUntilDue) ?> days overdue
                    <?php elseif ($daysUntilDue === 0): ?>
                        Due today
                    <?php else: ?>
                        <?= $daysUntilDue ?> days left
                    <?php endif; ?>
                </span>
            </div>
        </div>
<?php
    endforeach;
    
} catch (PDOException $e) {
    echo '<div class="text-center py-4 text-red-500">
            <i class="fas fa-exclamation-triangle mb-2"></i>
            <p class="text-xs">Error loading recent issues</p>
          </div>';
}
