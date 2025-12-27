<?php
session_start();

if (!isset($_SESSION['user'])) {
    die("<h3 style='color:red; text-align:center;'>Access denied. Please log in first.</h3>");
}

$email = $_SESSION['user'];

// Database connection
$host = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbname = "aqi";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM userinfo WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<h3 style='color:red; text-align:center;'>User information not found.</h3>");
}

$user = $result->fetch_assoc();

// Set background color
$bgColor = "#f9f9f9"; // default
if (!empty($user['favorite_color'])) {
    $bgColor = $user['favorite_color'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>User Information</title>
<style>
    body {
        font-family: 'Segoe UI', sans-serif;
        margin: 0;
        padding: 40px;
        background-color: <?php echo htmlspecialchars($bgColor); ?>;
        color: #333;
    }
    .container {
        max-width: 600px;
        margin: auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #222;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    th {
        background-color: #f4f4f4;
        width: 35%;
    }
    .back-link {
        display: block;
        text-align: center;
        margin-top: 25px;
        color: #0077cc;
        text-decoration: none;
        font-weight: bold;
    }
    .back-link:hover {
        text-decoration: underline;
    }
    .password-container {
        display: flex;
        align-items: center;
    }
    .password-container input {
        margin-right: 10px;
    }
</style>
</head>
<body>

<div class="container">
    <h2>User Information</h2>
    <table>
        <?php foreach ($user as $key => $value): ?>
            <tr>
                <th><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?></th>
                <td>
                    <?php if ($key === 'password'): ?>
                        <div class="password-container">
                            <input type="password" id="password" value="<?php echo htmlspecialchars($value); ?>" readonly>
                            <button type="button" onclick="togglePassword()">Show</button>
                        </div>
                    <?php else: ?>
                        <?php echo htmlspecialchars($value); ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="requestaqi.php" class="back-link">Back to City Selection</a>
</div>

<script>
function togglePassword() {
    const passwordField = document.getElementById("password");
    const button = passwordField.nextElementSibling;
    if (passwordField.type === "password") {
        passwordField.type = "text";
        button.textContent = "Hide";
    } else {
        passwordField.type = "password";
        button.textContent = "Show";
    }
}
</script>

</body>
</html>
