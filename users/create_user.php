<?php 
include 'config.php';
include 'navbar.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Assuming there are no manual errors array as validation is handled in JS
    if (empty($errors)) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $mobile = $_POST['mobile'];
        $gender = $_POST['gender'];
        $companies = $_POST['companies'];

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, gender) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $mobile, $gender]);
            $user_id = $pdo->lastInsertId();

            foreach ($companies as $company) {
                $years_exp = $company['years'];
                $months_exp = $company['months'];
                $stmt_exp = $pdo->prepare("INSERT INTO experiences (user_id, years, months) VALUES (?, ?, ?)");
                $stmt_exp->execute([$user_id, $years_exp, $months_exp]);
            }

            $pdo->commit();

            echo "User created successfully!";
            header("Location: read_user.php");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        foreach ($errors as $error) {
            echo '<p>' . htmlspecialchars($error) . '</p>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<script src="script.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
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
        .company-section {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .add-company {
            margin-bottom: 10px;
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<center><h2>Create New User</h2></center>

<form method="POST" action="" onsubmit="return validateForm(event);">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" required>

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required>

    <label for="mobile">Mobile:</label>
    <input type="text" name="mobile" id="mobile" required>
    
    <label for="gender">Gender:</label>
    <select name="gender" id="gender" required>
        <option value="" disabled selected>Select your gender</option>
        <option value="male">Male</option>
        <option value="female">Female</option>
        <option value="other">Other</option>
    </select>

    <h3>Experience Details</h3>
    <div id="companies">
        <div class="company-section">
            <label for="years_exp_0">Years of Experience:</label>
            <input type="number" name="companies[0][years]" id="years_exp_0" min="0" required>

            <label for="months_exp_0">Months of Experience:</label>
            <input type="number" name="companies[0][months]" id="months_exp_0" min="0" max="11" required>
        </div>
    </div>
    <div class="add-company" onclick="addCompanySection()"> Add another company</div>

    <input type="submit" value="Create User">
</form>

<script>
    let companyIndex = 1;

    function addCompanySection() {
        const companiesDiv = document.getElementById('companies');
        const newCompanySection = document.createElement('div');
        newCompanySection.className = 'company-section';
        newCompanySection.innerHTML = `
            <label for="years_exp_${companyIndex}">Years of Experience:</label>
            <input type="number" name="companies[${companyIndex}][years]" id="years_exp_${companyIndex}" min="0" required>
            
            <label for="months_exp_${companyIndex}">Months of Experience:</label>
            <input type="number" name="companies[${companyIndex}][months]" id="months_exp_${companyIndex}" min="0" max="11" required>

            <button type="button" onclick="removeCompanySection(this)">Remove</button>
        `;
        companiesDiv.appendChild(newCompanySection);
        companyIndex++;
    }
    
    function removeCompanySection(button) {
        const section = button.parentElement;
        section.parentElement.removeChild(section);
    }
</script>

<?php include 'footer.php'; ?>

</body>
</html>
