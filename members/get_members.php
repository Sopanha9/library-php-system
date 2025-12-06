<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'librarian'])) {
    exit('Unauthorized');
}

$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$membership = $_GET['membership'] ?? '';

$query = "SELECT * FROM members WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($status) {
    $query .= " AND status = ?";
    $params[] = $status;
}

if ($membership) {
    $query .= " AND membership_type = ?";
    $params[] = $membership;
}

$query .= " ORDER BY join_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$members = $stmt->fetchAll();

if (count($members) === 0) {
    echo '<div class="flex flex-col items-center justify-center py-16">
            <i class="fas fa-users text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Members Found</h3>
            <p class="text-gray-500">Try adjusting your search or filters</p>
          </div>';
    exit();
}
?>

<div class="overflow-x-auto">
  <table class="w-full">
    <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
      <tr>
        <th class="px-6 py-4 text-left text-sm font-semibold">Member</th>
        <th class="px-6 py-4 text-left text-sm font-semibold">Contact</th>
        <th class="px-6 py-4 text-left text-sm font-semibold">Membership</th>
        <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
        <th class="px-6 py-4 text-left text-sm font-semibold">Join Date</th>
        <th class="px-6 py-4 text-left text-sm font-semibold">Books Limit</th>
        <th class="px-6 py-4 text-center text-sm font-semibold">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <?php foreach ($members as $member): ?>
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-6 py-4">
          <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0 bg-gradient-to-br from-indigo-500 to-purple-600">
              <?php if ($member['profile_photo'] && file_exists(__DIR__ . '/../uploads/members/' . $member['profile_photo'])): ?>
                <img src="../uploads/members/<?= htmlspecialchars($member['profile_photo']) ?>" 
                     class="w-full h-full object-cover" alt="<?= htmlspecialchars($member['full_name']) ?>">
              <?php else: ?>
                <div class="w-full h-full flex items-center justify-center text-white font-semibold">
                  <?= strtoupper(substr($member['full_name'], 0, 1)) ?>
                </div>
              <?php endif; ?>
            </div>
            <div>
              <div class="font-semibold text-gray-800"><?= htmlspecialchars($member['full_name']) ?></div>
              <div class="text-sm text-gray-500">ID: #<?= $member['member_id'] ?></div>
            </div>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="text-sm">
            <div class="flex items-center text-gray-700 mb-1">
              <i class="fas fa-envelope text-indigo-600 w-4 mr-2"></i>
              <?= htmlspecialchars($member['email']) ?>
            </div>
            <div class="flex items-center text-gray-700">
              <i class="fas fa-phone text-indigo-600 w-4 mr-2"></i>
              <?= htmlspecialchars($member['phone']) ?>
            </div>
          </div>
        </td>
        <td class="px-6 py-4">
          <?php
          $badges = [
            'standard' => ['bg' => 'bg-blue-100 text-blue-700', 'icon' => '📚'],
            'premium' => ['bg' => 'bg-purple-100 text-purple-700', 'icon' => '⭐'],
            'student' => ['bg' => 'bg-green-100 text-green-700', 'icon' => '🎓']
          ];
          $badge = $badges[$member['membership_type']] ?? $badges['standard'];
          ?>
          <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $badge['bg'] ?>">
            <?= $badge['icon'] ?> <?= ucfirst($member['membership_type']) ?>
          </span>
        </td>
        <td class="px-6 py-4">
          <?php
          $statusConfig = [
            'active' => ['bg' => 'bg-green-100 text-green-700', 'icon' => 'fa-check-circle', 'text' => 'Active'],
            'inactive' => ['bg' => 'bg-gray-100 text-gray-700', 'icon' => 'fa-circle', 'text' => 'Inactive'],
            'suspended' => ['bg' => 'bg-red-100 text-red-700', 'icon' => 'fa-ban', 'text' => 'Suspended']
          ];
          $config = $statusConfig[$member['status']] ?? $statusConfig['inactive'];
          ?>
          <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $config['bg'] ?> inline-flex items-center">
            <i class="fas <?= $config['icon'] ?> mr-1"></i>
            <?= $config['text'] ?>
          </span>
        </td>
        <td class="px-6 py-4 text-sm text-gray-700">
          <i class="fas fa-calendar text-indigo-600 mr-2"></i>
          <?= date('M d, Y', strtotime($member['join_date'])) ?>
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center justify-center">
            <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-sm font-semibold">
              <?= $member['max_books_allowed'] ?> books
            </span>
          </div>
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center justify-center space-x-2">
            <button onclick="editMember(<?= $member['member_id'] ?>)" 
                    class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition-all flex items-center justify-center"
                    title="Edit Member">
              <i class="fas fa-edit"></i>
            </button>
            
            <?php if ($member['status'] === 'active'): ?>
              <button onclick="toggleStatus(<?= $member['member_id'] ?>, 'active')" 
                      class="w-9 h-9 rounded-lg bg-orange-100 text-orange-600 hover:bg-orange-200 transition-all flex items-center justify-center"
                      title="Suspend Member">
                <i class="fas fa-ban"></i>
              </button>
            <?php else: ?>
              <button onclick="toggleStatus(<?= $member['member_id'] ?>, '<?= $member['status'] ?>')" 
                      class="w-9 h-9 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-all flex items-center justify-center"
                      title="Activate Member">
                <i class="fas fa-check-circle"></i>
              </button>
            <?php endif; ?>
            
            <button onclick="deleteMember(<?= $member['member_id'] ?>)" 
                    class="w-9 h-9 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-all flex items-center justify-center"
                    title="Delete Member">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="px-6 py-4 bg-gray-50 border-t">
  <p class="text-sm text-gray-600">
    Showing <span class="font-semibold text-indigo-600"><?= count($members) ?></span> member(s)
  </p>
</div>
