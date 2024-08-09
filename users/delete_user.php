<?php
// Include the database connection
include 'config.php'; // Ensure the correct path to your db.php file

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    try {
        // Start transaction
        $pdo->beginTransaction(); // Use $pdo as the connection variable

        // Delete experience data associated with the user
        $stmt_exp = $pdo->prepare("DELETE FROM experiences WHERE user_id = ?");
        $stmt_exp->execute([$user_id]);

        // Delete user data
        $stmt_user = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt_user->execute([$user_id]);

        // Commit the transaction
        $pdo->commit();

        // Redirect to the index page after successful deletion
        header("Location: read_user.php");
        exit;
    } catch (PDOException $e) {
        // Roll back the transaction if an error occurs
        $pdo->rollBack();
        echo 'Error: ' . $e->getMessage();
    }
} else {
    echo "Invalid user ID!";
}
