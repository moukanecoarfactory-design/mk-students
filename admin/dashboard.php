<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$search = trim($_GET['search'] ?? '');
$filter_level = $_GET['study_level'] ?? '';

$sql = "SELECT id, first_name, last_name, email, phone, study_level, status FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $searchTerm = "%" . $search . "%";
    array_push($params, $searchTerm, $searchTerm, $searchTerm);
    $types .= "sss";
}

if (!empty($filter_level)) {
    $sql .= " AND study_level = ?";
    $params[] = $filter_level;
    $types .= "s";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "Dashboard - mk-students";
require_once "../includes/header.php";
?>

<main class="page-container">
    <section class="card">
        <div class="card-header">
            <h2>User Management</h2>
        </div>

       <form method="GET" action="dashboard.php" class="filter-bar">
    <input type="text" name="search" placeholder="Search name or email..." value="<?php echo htmlspecialchars($search); ?>">
    <select name="study_level">
        <option value="">-- All Levels --</option>
        <?php
        $levels = ["Bac", "Bac+2", "Bac+3", "Bac+5", "Other"];
        foreach ($levels as $lvl) {
            $selected = ($filter_level === $lvl) ? 'selected' : '';
            echo "<option value='$lvl' $selected>$lvl</option>";
        }
        ?>
    </select>
    <button type="submit" class="filter-btn primary"><i class="fa-solid fa-filter"></i> Filter</button>
    <a href="dashboard.php" class="filter-btn outline"><i class="fa-solid fa-rotate-left"></i> Reset</a>
</form>

<div style="overflow-x: auto;">
<table class="user-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Level</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($users) > 0): ?>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo htmlspecialchars($u['id']); ?></td>
                    <td><strong><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo htmlspecialchars($u['study_level'] ?? '-'); ?></td>
                    <td>
                        <span class="status-badge <?php echo $u['status'] === 'active' ? 'active' : 'inactive'; ?>">
                            <i class="fa-solid fa-circle" style="font-size: 7px;"></i>
                            <?php echo ucfirst(htmlspecialchars($u['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-group">
                            <a href="view-user.php?id=<?php echo $u['id']; ?>" class="action-btn view">
                                <i class="fa-solid fa-eye"></i> View
                            </a>
                            <a href="edit-user.php?id=<?php echo $u['id']; ?>" class="action-btn edit">
                                <i class="fa-solid fa-pen"></i> Edit
                            </a>
                            <a href="toggle-user.php?id=<?php echo $u['id']; ?>" class="action-btn toggle">
                                <i class="fa-solid fa-power-off"></i>
                                <?php echo $u['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <a href="delete-user.php?id=<?php echo $u['id']; ?>" class="action-btn delete"
                               onclick="return confirm('Delete <?php echo htmlspecialchars($u['first_name']); ?>? This cannot be undone.');">
                                <i class="fa-solid fa-trash"></i> Delete
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="padding: 30px; text-align: center; color: #888;">
                <i class="fa-solid fa-users-slash" style="font-size: 32px; display: block; margin-bottom: 10px; color: #d0d6e3;"></i>
                No users found.
            </td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
    </section>
</main>

<?php require_once "../includes/footer.php"; ?>