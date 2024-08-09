<?php
include 'config.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    try {
        // Fetch user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch experience data
        $stmt_exp = $pdo->prepare("SELECT * FROM experiences WHERE user_id = ?");
        $stmt_exp->execute([$user_id]);
        $experience = $stmt_exp->fetchAll(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "User not found!";
            exit;
        }
    } catch (PDOException $e) {
        echo 'Connection failed: ' . $e->getMessage();
        exit;
    }
} else {
    echo "Invalid user ID!";
    exit;
}

// Handle form submission to update user data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $gender = $_POST['gender'];
    $companies = isset($_POST['companies']) ? $_POST['companies'] : [];

    // Initialize an array to hold errors
    $errors = [];

    // Validate user data
    if (empty($name)) {
        $errors[] = 'Name is required.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }

    if (empty($mobile) || !preg_match('/^\d{10}$/', $mobile)) {
        $errors[] = 'A valid mobile number (10 digits) is required.';
    }

    // Validate company experience data
    foreach ($companies as $company) {
        if (!isset($company['years']) || !is_numeric($company['years']) || $company['years'] < 0) {
            $errors[] = 'Years of experience must be a non-negative number.';
        }
        if (!isset($company['months']) || !is_numeric($company['months']) || $company['months'] < 0 || $company['months'] > 11) {
            $errors[] = 'Months of experience must be between 0 and 11.';
        }
    }

    if (empty($errors)) {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Update user data
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, gender = ? WHERE id = ?");
            $stmt->execute([$name, $email, $mobile, $gender, $user_id]);

            // Delete old experience data
            $stmt_del_exp = $pdo->prepare("DELETE FROM experiences WHERE user_id = ?");
            $stmt_del_exp->execute([$user_id]);

            // Insert new experience data
            foreach ($companies as $company) {
                $years_exp = $company['years'];
                $months_exp = $company['months'];
                $stmt_exp = $pdo->prepare("INSERT INTO experiences (user_id, years, months) VALUES (?, ?, ?)");
                $stmt_exp->execute([$user_id, $years_exp, $months_exp]);
            }

            // Commit transaction
            $pdo->commit();

            echo "User updated successfully!";
            header("Location: read_user.php"); // Redirect to the user list
            exit;
        } catch (PDOException $e) {
            // Rollback transaction if something failed
            $pdo->rollBack();
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        // Display errors
        foreach ($errors as $error) {
            echo '<p>' . htmlspecialchars($error) . '</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        form {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
        }
        input[type="text"], input[type="email"], input[type="number"], select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .experience-entry {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<h1>Edit User</h1>

<form method="post">
    <label>Name:</label>
    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br><br>

    <label>Email:</label>
    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br><br>

    <label>Mobile:</label>
    <input type="text" name="mobile" id="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required><br><br>

    <label>Gender:</label>
    <select name="gender" required>
        <option value="" disabled>Select your gender</option>
        <option value="male" <?php echo $user['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
        <option value="female" <?php echo $user['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
        <option value="other" <?php echo $user['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
    </select><br><br>

    <h3>Experience:</h3>
    <div id="experience-container">
        <?php foreach ($experience as $index => $exp): ?>
            <div class="experience-entry">
                <label>Company #<?php echo $index + 1; ?>:</label><br>
                <label>Years of Experience:</label>
                <input type="number" name="companies[<?php echo $index; ?>][years]" value="<?php echo htmlspecialchars($exp['years']); ?>" required><br>
                <label>Months of Experience:</label>
                <input type="number" name="companies[<?php echo $index; ?>][months]" value="<?php echo htmlspecialchars($exp['months']); ?>" required><br><br>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" onclick="addExperience()">Add More Experience</button><br><br>

    <input type="submit" value="Update User">
</form>

<script>
    let experienceIndex = <?php echo count($experience); ?>;

    function addExperience() {
        const container = document.getElementById('experience-container');
        const div = document.createElement('div');
        div.classList.add('experience-entry');
        div.innerHTML = `
            <label>Company #${experienceIndex + 1}:</label><br>
            <label>Years of Experience:</label>
            <input type="number" name="companies[${experienceIndex}][years]" required><br>
            <label>Months of Experience:</label>
            <input type="number" name="companies[${experienceIndex}][months]" required><br><br>
            <button type="button" class="remove-button" onclick="removeExperience(this)">Remove</button>
        `;
        container.appendChild(div);
        experienceIndex++;
    }

    function removeExperience(button) {
        const container = document.getElementById('experience-container');
        container.removeChild(button.parentElement);
    }
</script>

</body>
</html>
