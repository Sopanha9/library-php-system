<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    exit('Unauthorized');
}

$search = $_GET['search'] ?? '';

try {
    // Get library settings for fine calculation
    $settings = [];
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM library_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    $finePerDay = $settings['fine_per_day'] ?? 1.00;
    $gracePeriod = $settings['grace_period_days'] ?? 0;
    $maxFine = $settings['max_fine_amount'] ?? 50.00;
    
    // Get overdue books
    $sql = "SELECT 
                ib.*,
                b.title as book_title,
                b.author as book_author,
                b.isbn,
                b.cover_image,
                m.full_name as member_name,
                m.email as member_email,
                m.phone as member_phone,
                m.profile_photo
            FROM issued_books ib
            JOIN books b ON ib.book_id = b.book_id
            JOIN members m ON ib.member_id = m.member_id
            WHERE ib.return_date IS NULL
            AND ib.due_date < CURDATE()";
    
    if ($search) {
        $sql .= " AND (b.title LIKE ? OR m.full_name LIKE ? OR m.email LIKE ?)";
    }
    
    $sql .= " ORDER BY ib.due_date ASC";
    
    $stmt = $pdo->prepare($sql);
    
    if ($search) {
        $searchTerm = "%{$search}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt->execute();
    }
    
    $overdueBooks = $stmt->fetchAll();
    
    if (count($overdueBooks) === 0) {
        echo '<div class="text-center py-16">
                <i class="fas fa-check-circle text-6xl text-green-500 mb-4"></i>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">All Clear!</h3>
                <p class="text-gray-600">No overdue books at the moment</p>
              </div>';
        exit();
    }
    
    echo '<div class="space-y-4">';
    
    foreach ($overdueBooks as $book):
        $dueDate = new DateTime($book['due_date']);
        $today = new DateTime();
        $diff = $today->diff($dueDate);
        $daysOverdue = $diff->days;
        
        // Calculate fine
        $fine = 0;
        if ($daysOverdue > $gracePeriod) {
            $fine = ($daysOverdue - $gracePeriod) * $finePerDay;
            $fine = min($fine, $maxFine);
        }
        
        $outstanding = $fine - $book['fine_paid'];
?>
        <div class="border-2 border-red-200 bg-gradient-to-r from-red-50 to-orange-50 rounded-lg p-5 hover:shadow-lg transition-all">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4 flex-1">
                    <div class="w-20 h-24 rounded bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white overflow-hidden flex-shrink-0">
                        <?php if ($book['cover_image']): ?>
                            <img src="../uploads/books/<?= htmlspecialchars($book['cover_image']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i class="fas fa-book text-3xl"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h4 class="font-bold text-gray-800 text-xl mb-1"><?= htmlspecialchars($book['book_title']) ?></h4>
                                <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($book['book_author']) ?></p>
                            </div>
                            <span class="px-4 py-2 bg-red-500 text-white rounded-full text-sm font-bold flex items-center ml-4">
                                <i class="fas fa-clock mr-2"></i><?= $daysOverdue ?> days overdue
                            </span>
                        </div>
                        
                        <!-- Member Info -->
                        <div class="bg-white/70 rounded-lg p-3 mb-3">
                            <div class="flex items-start gap-3">
                                <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0 border-2 border-indigo-200">
                                    <?php if ($book['profile_photo'] && file_exists(__DIR__ . '/../uploads/members/' . $book['profile_photo'])): ?>
                                        <img src="../uploads/members/<?= htmlspecialchars($book['profile_photo']) ?>" 
                                             class="w-full h-full object-cover" alt="Member">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                            <?= strtoupper(substr($book['member_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 grid grid-cols-2 gap-3 text-sm">
                                    <div class="flex items-center">
                                        <i class="fas fa-user text-indigo-600 mr-2 w-4"></i>
                                        <span class="font-semibold"><?= htmlspecialchars($book['member_name']) ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-envelope text-indigo-600 mr-2 w-4"></i>
                                        <span class="text-gray-600"><?= htmlspecialchars($book['member_email']) ?></span>
                                    </div>
                                    <div class="flex items-center col-span-2">
                                        <i class="fas fa-phone text-indigo-600 mr-2 w-4"></i>
                                        <span class="text-gray-600"><?= htmlspecialchars($book['member_phone']) ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 text-sm mt-3 pt-3 border-t border-gray-200">
                                <div class="flex items-center">
                                    <i class="fas fa-hashtag text-indigo-600 mr-2 w-4"></i>
                                    <span class="text-gray-600">Issue #<?= $book['issue_id'] ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dates & Fine Info -->
                        <div class="grid grid-cols-4 gap-3 text-sm">
                            <div class="bg-white/70 rounded p-2">
                                <p class="text-xs text-gray-600 mb-1">Issued Date</p>
                                <p class="font-semibold"><?= date('M d, Y', strtotime($book['issue_date'])) ?></p>
                            </div>
                            <div class="bg-white/70 rounded p-2">
                                <p class="text-xs text-gray-600 mb-1">Due Date</p>
                                <p class="font-semibold text-red-600"><?= date('M d, Y', strtotime($book['due_date'])) ?></p>
                            </div>
                            <div class="bg-red-100 border border-red-300 rounded p-2">
                                <p class="text-xs text-red-700 mb-1">Total Fine</p>
                                <p class="font-bold text-red-800"><?= number_format($fine, 2) ?> Riel</p>
                            </div>
                            <div class="<?= $outstanding > 0 ? 'bg-orange-100 border-orange-300' : 'bg-green-100 border-green-300' ?> border rounded p-2">
                                <p class="text-xs <?= $outstanding > 0 ? 'text-orange-700' : 'text-green-700' ?> mb-1">Outstanding</p>
                                <p class="font-bold <?= $outstanding > 0 ? 'text-orange-800' : 'text-green-800' ?>"><?= number_format($outstanding, 2) ?> Riel</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <a href="return_book.php" class="ml-4 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl flex-shrink-0">
                    <i class="fas fa-rotate-left mr-2"></i>Process Return
                </a>
            </div>
        </div>
<?php
    endforeach;
    
    echo '</div>';
    
} catch (PDOException $e) {
    echo '<div class="text-center py-16 text-red-500">
            <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
            <p class="text-lg font-semibold">Error loading overdue books</p>
            <p class="text-sm">' . htmlspecialchars($e->getMessage()) . '</p>
          </div>';
}
