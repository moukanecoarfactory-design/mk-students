<?php
session_start();
require_once "../config/database.php";
require_once "../includes/auth.php";

requireAdmin();

$adminId    = $_SESSION['user_id'];
$search     = trim($_GET['search'] ?? '');
$filter_level = $_GET['study_level'] ?? '';

// Build query
$sql = "SELECT id, first_name, last_name, email, phone, study_level, status 
        FROM users WHERE 1=1";
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
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Stats
$total     = count($users);
$active    = count(array_filter($users, fn($u) => $u['status'] === 'active'));
$inactive  = $total - $active;

$pageTitle = "Dashboard - mk-students";
require_once "../includes/header.php";
?>

<style>
    /* ================= ADMIN DASHBOARD ================= */
    .admin-page {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }

    /* Page header */
    .admin-header {
        margin-bottom: 28px;
    }
    .admin-header h1 {
        font-size: 30px;
        font-weight: 700;
        color: #17213c;
        letter-spacing: -0.4px;
        margin-bottom: 6px;
    }
    .admin-header p {
        color: #888;
        font-size: 15px;
    }

    /* Stats strip */
    .admin-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 26px;
    }
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px 24px;
        box-shadow: 0 8px 25px rgba(45, 58, 100, 0.07);
        border-left: 4px solid #667eea;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .stat-card.active   { border-left-color: #28a745; }
    .stat-card.inactive { border-left-color: #d93030; }
    .stat-icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .stat-card.active   .stat-icon { background: linear-gradient(135deg, #34c759, #1fa04a); }
    .stat-card.inactive .stat-icon { background: linear-gradient(135deg, #ff5b5b, #c92a2a); }
    .stat-value {
        font-size: 22px;
        font-weight: 700;
        color: #17213c;
        line-height: 1.1;
    }
    .stat-label {
        font-size: 12px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 600;
    }

    /* Card */
    .admin-card {
        background: #fff;
        border-radius: 20px;
        padding: 26px 28px 30px;
        box-shadow: 0 12px 35px rgba(45, 58, 100, 0.08);
    }

    /* Filter bar */
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 22px;
        align-items: center;
    }
    .filter-bar input,
    .filter-bar select {
        padding: 11px 15px;
        border: 2px solid #e1e5eb;
        border-radius: 11px;
        font-size: 13.5px;
        font-family: inherit;
        background: #f8faff;
        color: #27324d;
        outline: none;
        transition: 0.22s;
    }
    .filter-bar input:focus,
    .filter-bar select:focus {
        border-color: #667eea;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    .filter-bar input { flex: 1; min-width: 220px; }

    .filter-btn {
        padding: 11px 20px;
        border-radius: 11px;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: 0.22s;
        font-family: inherit;
        white-space: nowrap;
    }
    .filter-btn.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.28);
    }
    .filter-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 26px rgba(102, 126, 234, 0.38);
    }
    .filter-btn.outline {
        background: #fff;
        color: #555;
        border: 1.5px solid #e1e5eb;
    }
    .filter-btn.outline:hover {
        border-color: #667eea;
        color: #667eea;
    }

    /* Table */
    .user-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 14px;
    }
    .user-table thead th {
        text-align: left;
        padding: 14px 14px;
        background: #f7f9ff;
        color: #55607c;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.7px;
        text-transform: uppercase;
        border-bottom: 2px solid #e6ebf5;
    }
    .user-table thead th:first-child { border-top-left-radius: 12px; }
    .user-table thead th:last-child  { border-top-right-radius: 12px; }

    .user-table tbody td {
        padding: 16px 14px;
        border-bottom: 1px solid #f0f2f8;
        color: #333;
        vertical-align: middle;
    }
    .user-table tbody tr { transition: 0.2s; }
    .user-table tbody tr:nth-child(even) { background: #fafbff; }
    .user-table tbody tr:hover           { background: #f0f5ff; }
    .user-table tbody tr.is-me           { background: #f4f0ff; }
    .user-table tbody tr.is-me:hover     { background: #ede5ff; }

    .user-name {
        font-weight: 600;
        color: #17213c;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .user-id {
        display: inline-block;
        background: #eef2ff;
        color: #4b5ecb;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
    }
    .you-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .level-badge {
        display: inline-block;
        background: #eef2ff;
        color: #4b5ecb;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    /* Status pill */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 13px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }
    .status-badge.active   { background: #d4f5e0; color: #167b3a; }
    .status-badge.inactive { background: #ffe0e0; color: #b42318; }

    /* Action buttons */
    .action-group {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 13px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 8px;
        text-decoration: none;
        transition: 0.2s;
        white-space: nowrap;
    }
    .action-btn.view     { background: #eef2ff; color: #4b5ecb; }
    .action-btn.edit     { background: #fff4e6; color: #c46a12; }
    .action-btn.toggle   { background: #eef8f1; color: #1f7a3c; }
    .action-btn.delete   { background: #ffecec; color: #c9302c; }
    .action-btn.view:hover   { background: #dde4ff; }
    .action-btn.edit:hover   { background: #ffe6c7; }
    .action-btn.toggle:hover { background: #d8f0e0; }
    .action-btn.delete:hover { background: #ffd6d6; }

    .action-btn.disabled {
        opacity: 0.4;
        pointer-events: none;
        cursor: not-allowed;
    }

    /* Empty state */
    .empty-row td {
        text-align: center;
        padding: 50px 20px;
        color: #888;
    }
    .empty-row .empty-icon {
        font-size: 42px;
        color: #d0d6e3;
        display: block;
        margin-bottom: 10px;
    }

    /* Mobile */
    @media (max-width: 820px) {
        .admin-stats {
            grid-template-columns: 1fr;
        }
        .admin-header h1 { font-size: 24px; }
    }
    @media (max-width: 600px) {
        .admin-card { padding: 20px 16px; }
        .filter-bar { flex-direction: column; }
        .filter-bar input,
        .filter-bar select,
        .filter-btn { width: 100%; }
    }
</style>

<main class="admin-page">

    <div class="admin-header">
    <h1 style="color:#ffffff !important; -webkit-text-fill-color:#ffffff !important; text-shadow:0 2px 12px rgba(0,0,0,0.5) !important;">User Management</h1>
    <p style="color:rgba(255,255,255,0.65) !important;">Manage all registered users on the platform</p>
</div>
  <div style="display:flex; gap:10px; flex-wrap:wrap;">
    <a href="courses.php" class="filter-btn primary" style="text-decoration:none;">
        <i class="fa-solid fa-book"></i> Courses
    </a>
    <a href="announcements.php" class="filter-btn primary" style="text-decoration:none;">
        <i class="fa-solid fa-bullhorn"></i> Announcements
    </a>
    <a href="reset-requests.php" class="filter-btn primary" style="text-decoration:none;">
        <i class="fa-solid fa-key"></i> Reset Requests
    </a>
</div>
</div>

    <!-- Stats -->
    <div class="admin-stats">
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div>
                <div class="stat-value"><?php echo $total; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="stat-card active">
            <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
            <div>
                <div class="stat-value"><?php echo $active; ?></div>
                <div class="stat-label">Active</div>
            </div>
        </div>
        <div class="stat-card inactive">
            <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
            <div>
                <div class="stat-value"><?php echo $inactive; ?></div>
                <div class="stat-label">Inactive</div>
            </div>
        </div>
    </div>

    <!-- Card -->
    <div class="admin-card">

        <!-- Filter bar -->
        <form method="GET" action="dashboard.php" class="filter-bar">
            <input type="text" name="search"
                   placeholder="Search by name or email…"
                   value="<?php echo htmlspecialchars($search); ?>">
            <select name="study_level">
                <option value="">All Levels</option>
                <?php
                $levels = ["Bac", "Bac+1", "Bac+2", "Bac+3", "Bac+4", "Bac+5", "Doctorat"];
                foreach ($levels as $lvl) {
                    $sel = ($filter_level === $lvl) ? 'selected' : '';
                    echo "<option value=\"$lvl\" $sel>$lvl</option>";
                }
                ?>
            </select>
            <button type="submit" class="filter-btn primary">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
            <a href="dashboard.php" class="filter-btn outline">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </a>
        </form>

        <!-- Table -->
        <div style="overflow-x:auto;">
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
                        <?php foreach ($users as $u):
                            $isMe = ($u['id'] == $adminId);
                        ?>
                            <tr class="<?php echo $isMe ? 'is-me' : ''; ?>">
                                <td><span class="user-id">#<?php echo $u['id']; ?></span></td>
                                <td>
                                    <div class="user-name">
                                        <?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?>
                                        <?php if ($isMe): ?>
                                            <span class="you-badge">You</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="level-badge">
                                        <?php echo htmlspecialchars($u['study_level'] ?? '—'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $u['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                        <i class="fa-solid fa-circle" style="font-size:7px;"></i>
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

                                        <?php if (!$isMe): ?>
                                            <a href="toggle-user.php?id=<?php echo $u['id']; ?>" class="action-btn toggle"
                                               onclick="return confirm('<?php echo $u['status'] === 'active' ? 'Deactivate' : 'Activate'; ?> this user?');">
                                                <i class="fa-solid fa-power-off"></i>
                                                <?php echo $u['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                            </a>

                                            <a href="delete-user.php?id=<?php echo $u['id']; ?>" class="action-btn delete"
                                               onclick="return confirm('Delete <?php echo htmlspecialchars($u['first_name']); ?>? This cannot be undone.');">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="action-btn toggle disabled">
                                                <i class="fa-solid fa-power-off"></i> Deactivate
                                            </span>
                                            <span class="action-btn delete disabled">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr class="empty-row">
                            <td colspan="6">
                                <i class="fa-solid fa-users-slash empty-icon"></i>
                                No users match your search.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</main>

<?php require_once "../includes/footer.php"; ?>