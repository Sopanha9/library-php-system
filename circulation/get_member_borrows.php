<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    exit('Unauthorized');
}

$memberId = $_GET['member_id'] ?? null;

if (!$memberId) {
    exit('<div class="text-center py-8 text-red-500">Member ID required</div>');
}

try {
    $sql = "SELECT 
                ib.*,
                b.title as book_title,
                b.author as book_author,
                b.isbn,
                b.cover_image,
                m.full_name as member_name,
                m.email as member_email,
                m.phone as member_phone
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.book_id
            JOIN members m ON ib.member_id = m.member_id
            WHERE ib.member_id = ?
            AND ib.return_date IS NULL
            ORDER BY ib.due_date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId]);
    $borrows = $stmt->fetchAll();
    
    if (count($borrows) === 0) {
        echo '<div class="text-center py-8 text-gray-400">
                <i class="fas fa-inbox text-4xl mb-3"></i>
                <p class="text-lg font-semibold">No Active Borrows</p>
                <p class="text-sm">This member has no borrowed books</p>
              </div>';
        exit();
    }
    
    // Get library settings for fine calculation
    $settings = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM library_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    $finePerDay = $settings['fine_per_day'] ?? 1.00;
    $gracePeriod = $settings['grace_period_days'] ?? 0;
    
    foreach ($borrows as $borrow):
        $dueDate = new DateTime($borrow['due_date']);
        $today = new DateTime();
        $diff = $today->diff($dueDate);
        $daysOverdue = $diff->days;
        
        $isOverdue = $today > $dueDate;
        $fine = 0;
        
        if ($isOverdue && $daysOverdue > $gracePeriod) {
            $fine = ($daysOverdue - $gracePeriod) * $finePerDay;
            $maxFine = $settings['max_fine_amount'] ?? 50.00;
            $fine = min($fine, $maxFine);
        }
        
        $statusClass = $isOverdue ? 'bg-red-100 text-red-700 border-red-300' : 'bg-green-100 text-green-700 border-green-300';
?>
        <div class="border-2 <?= $statusClass ?> rounded-lg p-4 mb-4 hover:shadow-lg transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4 flex-1">
                    <div class="w-16 h-20 rounded bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white overflow-hidden flex-shrink-0">
                        <?php if ($borrow['cover_image']): ?>
                            <img src="../uploads/books/<?= htmlspecialchars($borrow['cover_image']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-book text-2xl"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-800 text-lg mb-1"><?= htmlspecialchars($borrow['book_title']) ?></h4>
                        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($borrow['book_author']) ?></p>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-barcode mr-1"></i>
                                <?= htmlspecialchars($borrow['isbn']) ?>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-hashtag mr-1"></i>
                                Issue #<?= $borrow['issue_id'] ?>
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-calendar mr-1"></i>
                                Issued: <?= date('M d, Y', strtotime($borrow['issue_date'])) ?>
                            </div>
                            <div class="flex items-center font-semibold <?= $isOverdue ? 'text-red-600' : 'text-green-600' ?>">
                                <i class="fas fa-clock mr-1"></i>
                                Due: <?= date('M d, Y', strtotime($borrow['due_date'])) ?>
                            </div>
                        </div>
                        
                        <?php if ($isOverdue): ?>
                            <div class="bg-red-50 border border-red-200 rounded p-2 mb-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-red-700 font-semibold">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        <?= $daysOverdue ?> days overdue
                                    </span>
                                    <span class="text-red-800 font-bold">
                                        Fine: <?= number_format($fine, 2) ?> Riel
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="bg-green-50 border border-green-200 rounded p-2 mb-2">
                                <div class="text-xs text-green-700 font-semibold">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    <?= $diff->days ?> days remaining
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button onclick='openReturnModal(<?= json_encode([
                    "issue_id" => $borrow["issue_id"],
                    "book_title" => $borrow["book_title"],
                    "book_author" => $borrow["book_author"],
                    "isbn" => $borrow["isbn"],
                    "cover_image" => $borrow["cover_image"],
                    "issue_date" => date('M d, Y', strtotime($borrow["issue_date"])),
                    "due_date" => $borrow["due_date"],
                    "member_id" => $borrow["member_id"],
                    "member_name" => $borrow["member_name"],
                    "member_email" => $borrow["member_email"],
                    "member_phone" => $borrow["member_phone"]
                ]) ?>)' 
                        class="ml-4 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl flex-shrink-0">
                    <i class="fas fa-rotate-left mr-2"></i>Return
                </button>
            </div>
        </div>
<?php
    endforeach;
    
} catch (PDOException $e) {
    echo '<div class="text-center py-8 text-red-500">
            <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
            <p>Error loading borrowed books</p>
          </div>';
}
