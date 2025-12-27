<?php
session_start();

if (!isset($_SESSION['user'])) {
    die("<h3 style='color:red; text-align:center;'>Access denied. Please log in first.</h3>");
}

$user_email = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cities']) || count($_POST['cities']) !== 10) {
    echo "<h3 style='color:red; text-align:center;'>Access denied or invalid selection. Please go back and select exactly 10 cities.</h3>";
    exit;
}

$selectedCities = $_POST['cities'];

$host = "localhost";
$username = "root";
$password = "";
$dbname = "aqi";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch favorite color
$bgColor = "#f2f2f2"; // default same as requestaqi.php
$stmtColor = $conn->prepare("SELECT favorite_color FROM userinfo WHERE email = ?");
$stmtColor->bind_param("s", $user_email);
$stmtColor->execute();
$resultColor = $stmtColor->get_result();
if ($rowColor = $resultColor->fetch_assoc()) {
    if (!empty($rowColor['favorite_color'])) {
        $bgColor = $rowColor['favorite_color'];
    }
}
$stmtColor->close();

// Prepare statement for cities
$placeholders = implode(',', array_fill(0, count($selectedCities), '?'));
$sql = "SELECT city, country, aqi FROM info WHERE city IN ($placeholders)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param(str_repeat('s', count($selectedCities)), ...$selectedCities);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<title>Selected AQI Info</title>
<style>
    :root {
  --primary: #4361ee;
  --primary-hover: #3a56d4;
  --text-dark: #2b2d42;
  --text-light: #f8f9fa;
  --bg-light: #ffffff;
  --border-light: #e9ecef;
  --shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.1);
  --radius: 0.5rem;
  --transition: all 0.2s ease-in-out;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, 
               Ubuntu, Cantarell, sans-serif;
  margin: 0;
  padding: 2rem;
  min-height: 100vh;
  background-color: <?php echo htmlspecialchars($bgColor); ?>;
  color: var(--text-dark);
  line-height: 1.5;
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-email {
  font-weight: 500;
  color: var(--primary);
}

.signout-btn {
  padding: 0.5rem 1rem;
  background-color: var(--primary);
  color: white;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  transition: var(--transition);
  font-size: 0.9rem;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.signout-btn::before {
  content: '\f2f5';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
  font-size: 0.9em;
}

.signout-btn:hover {
  background-color: var(--primary-hover);
  transform: translateY(-1px);
}

.form-container {
  max-width: 800px;
  margin: 0 auto;
  background-color: var(--bg-light);
  padding: 2rem;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  transition: var(--transition);
}

.form-container:hover {
  box-shadow: var(--shadow-hover);
  transform: translateY(-0.125rem);
}

h2 {
  text-align: center;
  color: var(--text-dark);
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

h2::before {
  content: '\f0ce';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
  color: var(--primary);
}

table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5rem 0;
  box-shadow: 0 0 0 1px var(--border-light);
  border-radius: var(--radius);
  overflow: hidden;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border-light);
}

th {
  background-color: rgba(67, 97, 238, 0.1);
  color: var(--primary);
  font-weight: 600;
  position: relative;
}

th::after {
  content: '\f0d7';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
  position: absolute;
  right: 1rem;
  opacity: 0.7;
}

tr:nth-child(even) {
  background-color: rgba(67, 97, 238, 0.02);
}

tr:hover {
  background-color: rgba(67, 97, 238, 0.05);
}

.back-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  margin: 2rem auto 0;
  padding: 0.875rem 2rem;
  font-size: 1rem;
  font-weight: 500;
  background-color: var(--primary);
  color: white;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  transition: var(--transition);
  width: fit-content;
  text-decoration: none;
}

.back-button::before {
  content: '\f060';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
}

.back-button:hover {
  background-color: var(--primary-hover);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.no-data {
  text-align: center;
  font-weight: 500;
  color: #6c757d;
  padding: 1.5rem;
  background-color: rgba(108, 117, 125, 0.05);
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.no-data::before {
  content: '\f05e';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
}

@media (max-width: 768px) {
  body {
    padding: 1rem;
  }
  
  .form-container {
    padding: 1.5rem;
  }
  
  .header-container {
    flex-direction: column;
    align-items: flex-start;
  }
  
  table {
    display: block;
    overflow-x: auto;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-container {
  animation: fadeIn 0.4s ease-out forwards;
}
</style>
</head>
<body>

<div class="form-container">
    <div class="header-container">
        <h2>AQI Information for Selected Cities</h2>
        <div class="user-info">
            <span class="user-email">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($user_email); ?>
            </span>
            <a href="logout.php" class="signout-btn">Log Out</a>
        </div>
    </div>

    <?php if (count($data) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>City</th>
                    <th>Country</th>
                    <th>AQI</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['city']); ?></td>
                    <td><?php echo htmlspecialchars($row['country']); ?></td>
                    <td><?php echo htmlspecialchars($row['aqi']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No data found for the selected cities.</p>
    <?php endif; ?>

    <a href="requestaqi.php" class="back-button">Back</a>
</div>

</body>
</html>