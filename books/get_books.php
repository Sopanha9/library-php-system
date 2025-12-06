<?php
require_once '../config/db.php';
$search = $_GET['search'] ?? '';
$cat = $_GET['category'] ?? '';

$sql = "SELECT b.*, c.name as category_name FROM books b 
        LEFT JOIN categories c ON b.category_id = c.category_id WHERE 1=1";
if ($search) $sql .= " AND (b.title LIKE :search OR b.author LIKE :search OR b.isbn LIKE :search)";
if ($cat) $sql .= " AND b.category_id = :cat";
$sql .= " ORDER BY b.book_id DESC";

$stmt = $pdo->prepare($sql);
if ($search) $stmt->bindValue(':search', "%$search%");
if ($cat) $stmt->bindValue(':cat', $cat);
$stmt->execute();
$books = $stmt->fetchAll();
?>

<?php if (count($books) > 0): ?>
<div class="overflow-x-auto">
  <table class="w-full">
    <thead class="bg-gray-50 border-b-2 border-gray-200">
      <tr>
        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Book Details</th>
        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Total</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Available</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Damaged</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Lost</th>
        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
      <?php foreach ($books as $i => $b): ?>
      <tr class="hover:bg-gray-50 transition-colors">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?= $i + 1 ?></td>
        <td class="px-6 py-4">
          <div class="flex items-start space-x-3">
            <div class="w-12 h-16 rounded-lg overflow-hidden flex-shrink-0 shadow-md">
              <?php if (!empty($b['cover_image']) && file_exists('../uploads/books/' . $b['cover_image'])): ?>
                <img src="../uploads/books/<?= htmlspecialchars($b['cover_image']) ?>" 
                     alt="<?= htmlspecialchars($b['title']) ?>" 
                     class="w-full h-full object-cover">
              <?php else: ?>
                <div class="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
                  <i class="fas fa-book text-white text-lg"></i>
                </div>
              <?php endif; ?>
            </div>
            <div>
              <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($b['title']) ?></h4>
              <p class="text-sm text-gray-600"><i class="fas fa-user text-gray-400 mr-1"></i><?= htmlspecialchars($b['author']) ?></p>
              <?php if ($b['isbn']): ?>
              <p class="text-xs text-gray-500 mt-1"><i class="fas fa-barcode text-gray-400 mr-1"></i><?= htmlspecialchars($b['isbn']) ?></p>
              <?php endif; ?>
            </div>
          </div>
        </td>
        <td class="px-6 py-4">
          <?php if ($b['category_name']): ?>
          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
            <?= htmlspecialchars($b['category_name']) ?>
          </span>
          <?php else: ?>
          <span class="text-gray-400 text-sm">No category</span>
          <?php endif; ?>
        </td>
        <td class="px-6 py-4 text-center">
          <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-blue-100 text-blue-700 font-semibold">
            <?= $b['total_quantity'] ?>
          </span>
        </td>
        <td class="px-6 py-4 text-center">
          <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-green-100 text-green-700 font-semibold">
            <?= $b['available_quantity'] ?>
          </span>
        </td>
        <td class="px-6 py-4 text-center">
          <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-amber-100 text-amber-700 font-semibold">
            <?= $b['damaged_quantity'] ?>
          </span>
        </td>
        <td class="px-6 py-4 text-center">
          <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 text-red-700 font-semibold">
            <?= $b['lost_quantity'] ?>
          </span>
        </td>
        <td class="px-6 py-4 text-center">
          <div class="flex items-center justify-center space-x-2">
            <button onclick="editBook(<?= $b['book_id'] ?>)" 
                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-all" 
                    title="Edit">
              <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteBook(<?= $b['book_id'] ?>)" 
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all" 
                    title="Delete">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
  <p class="text-sm text-gray-600">
    Showing <span class="font-semibold text-gray-800"><?= count($books) ?></span> book(s)
  </p>
</div>

<?php else: ?>
<div class="flex flex-col items-center justify-center py-16">
  <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
    <i class="fas fa-book text-4xl text-gray-400"></i>
  </div>
  <h3 class="text-xl font-semibold text-gray-800 mb-2">No books found</h3>
  <p class="text-gray-600 text-center mb-6">
    <?= $search || $cat ? 'Try adjusting your search or filters' : 'Start by adding your first book' ?>
  </p>
  <?php if (!$search && !$cat): ?>
  <button onclick="openSlideOver('add')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition-all">
    <i class="fas fa-plus mr-2"></i>Add First Book
  </button>
  <?php endif; ?>
</div>
<?php endif; ?>