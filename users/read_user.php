<?php
include 'config.php';
include 'navbar.php';

// Define how many results you want per page
$results_per_page = 5;

// Get total number of records
try {
    $total_results = $pdo->query('SELECT COUNT(DISTINCT u.id) FROM users u LEFT JOIN experiences e ON u.id = e.user_id')->fetchColumn();
    $total_pages = ceil($total_results / $results_per_page);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}

// Determine the current page number from the URL (default is 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($total_pages, $page)); // Ensure page number is within valid range

// Calculate the offset for the current page
$offset = ($page - 1) * $results_per_page;

try {
    // Fetch users and their experience data for the current page
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.mobile, 
               COUNT(e.id) AS total_companies, 
               SUM(e.years) AS total_years, 
               SUM(e.months) AS total_months
        FROM users u
        LEFT JOIN experiences e ON u.id = e.user_id
        GROUP BY u.id
        LIMIT :offset, :limit
    ");
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $results_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Experience Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #495057;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .edit-button {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }

        .delete-button {
            color: red;
            text-decoration: underline;
            cursor: pointer;
        }

        .pagination {
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #007bff;
        }

        .pagination a.active {
            background-color: #007bff;
            color: white;
            border: 1px solid #007bff;
        }

        .pagination a:hover {
            background-color: #0056b3;
            color: white;
        }
    </style>
</head>
<body>

    <h1>User Experience List</h1>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Total Companies Served</th>
                <th>Total Experience</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user):
                // Calculate total experience
                $total_years = $user['total_years'] + intdiv($user['total_months'], 12);
                $remaining_months = $user['total_months'] % 12;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                    <td><?php echo htmlspecialchars($user['total_companies']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($total_years) . ' Years'; ?>
                        <?php if ($remaining_months > 0): ?>
                            , <?php echo htmlspecialchars($remaining_months) . ' Months'; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="edit-button">Edit</a>
                        <a href="delete_user.php?id=<?php echo htmlspecialchars($user['id']); ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination Links -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="read_user.php?page=<?php echo $page - 1; ?>">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="read_user.php?page=<?php echo $i; ?>"<?php if ($i == $page) echo ' class="active"'; ?>>
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="read_user.php?page=<?php echo $page + 1; ?>">Next</a>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>
