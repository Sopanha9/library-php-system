<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    exit('Unauthorized');
}

$dateFrom = $_GET['from'] ?? date('Y-m-01');
$dateTo = $_GET['to'] ?? date('Y-m-d');

try {
    $sql = "SELECT 
                fp.*,
                m.full_name as member_name,
                m.email as member_email,
                b.title as book_title,
                b.author as book_author,
                u.username as recorded_by_name
            FROM fine_payments fp
            JOIN members m ON fp.member_id = m.member_id
            JOIN issued_books ib ON fp.issue_id = ib.issue_id
            JOIN books b ON ib.book_id = b.book_id
            LEFT JOIN users u ON fp.recorded_by = u.user_id
            WHERE DATE(fp.payment_date) BETWEEN ? AND ?
            ORDER BY fp.payment_date DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$dateFrom, $dateTo]);
    $payments = $stmt->fetchAll();
    
    if (count($payments) === 0) {
        echo '<div class="text-center py-16">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Payments Found</h3>
                <p class="text-gray-500">No fine payments in the selected date range</p>
              </div>';
        exit();
    }
    
    // Calculate total
    $totalAmount = array_sum(array_column($payments, 'amount'));
?>
    <div class="mb-4 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600">Total Collected</p>
                <h3 class="text-2xl font-bold text-green-700"><?= number_format($totalAmount, 2) ?> Riel</h3>
            </div>
            <div>
                <p class="text-sm text-gray-600">Period</p>
                <p class="font-semibold text-gray-800"><?= date('M d', strtotime($dateFrom)) ?> - <?= date('M d, Y', strtotime($dateTo)) ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Transactions</p>
                <p class="font-semibold text-gray-800"><?= count($payments) ?></p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Date</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Receipt</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Member</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Book</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Method</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold">Amount</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold">Recorded By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($payments as $payment): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-sm">
                        <div class="font-semibold text-gray-800"><?= date('M d, Y', strtotime($payment['payment_date'])) ?></div>
                        <div class="text-xs text-gray-500"><?= date('h:i A', strtotime($payment['payment_date'])) ?></div>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded font-mono text-xs">
                            <?= htmlspecialchars($payment['receipt_number']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($payment['member_name']) ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($payment['member_email']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <div class="font-semibold text-gray-800"><?= htmlspecialchars($payment['book_title']) ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($payment['book_author']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <?php
                        $methods = [
                            'cash' => ['icon' => '💵', 'color' => 'green'],
                            'card' => ['icon' => '💳', 'color' => 'blue'],
                            'online' => ['icon' => '🌐', 'color' => 'purple'],
                            'other' => ['icon' => '📝', 'color' => 'gray']
                        ];
                        $method = $methods[$payment['payment_method']] ?? $methods['other'];
                        ?>
                        <span class="px-2 py-1 bg-<?= $method['color'] ?>-100 text-<?= $method['color'] ?>-700 rounded text-xs">
                            <?= $method['icon'] ?> <?= ucfirst($payment['payment_method']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-right">
                        <span class="font-bold text-green-700"><?= number_format($payment['amount'], 2) ?> Riel</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        <?= htmlspecialchars($payment['recorded_by_name'] ?? 'System') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
    
} catch (PDOException $e) {
    echo '<div class="text-center py-16 text-red-500">
            <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
            <p class="text-lg font-semibold">Error loading payment history</p>
          </div>';
}
