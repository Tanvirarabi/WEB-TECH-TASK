<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['favcolor'])) {
        $color = $_POST['favcolor'];
        setcookie('favcolor', $color, time() + (86400 * 30), "/"); // 30 days
    }
}

$servername = "localhost";
$dbusername = "root"; // Change if different
$dbpassword = "";     // Change if different
$dbname = "aqi";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!empty($email) && !empty($password)) {
    $stmt = $conn->prepare("SELECT * FROM userinfo WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $_SESSION['user'] = $email;
        echo "<script>
            setTimeout(function() {
                window.location.href = 'requestaqi.php';
            }, 1000);
        </script>";
        echo "<h2 style='text-align:center;margin-top:100px;'> USER Login Successful! Redirecting...</h2>";
    } else {
        echo "<script>
            alert('Incorrect email or password.');
            window.location.href = 'index.html';
        </script>";
    }

    $stmt->close();
} else {
    echo "<script>
        alert('Please fill in all fields.');
        window.location.href = 'index.html';
    </script>";
}

$conn->close();
?>
