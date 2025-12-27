<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
if (!isset($_SESSION['user'])) {
    die("<h3 style='color:red; text-align:center;'>Access denied. Please log in first.</h3>");
}
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

// Fetch user's background color
$stmt = $conn->prepare("SELECT favorite_color FROM userinfo WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$bgColor = "#f2f2f2"; // Default background
$row = $result->fetch_assoc();
if ($row && !empty($row['favorite_color'])) {
    $bgColor = $row['favorite_color'];
}
$stmt->close();

// Fetch cities
$sql = "SELECT DISTINCT city FROM info ORDER BY city ASC";
$result = $conn->query($sql);

$cities = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cities[] = $row['city'];
    }
}
$conn->close();

// Server-side validation if needed
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['cities']) || count($_POST['cities']) !== 10) {
        $error = "Please select exactly 10 cities.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select 10 Cities</title>
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
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  margin: 0;
  padding: 2rem;
  min-height: 100vh;
  background-color: <?php echo htmlspecialchars($bgColor); ?>;
  color: var(--text-dark);
  line-height: 1.5;
  transition: var(--transition);
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
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

.logout-btn {
  padding: 0.5rem 1rem;
  background-color: var(--primary);
  color: white;
  border: none;
  border-radius: var(--radius);
  cursor: pointer;
  transition: var(--transition);
  font-size: 0.9rem;
  text-decoration: none;
  display: inline-block;
}

.logout-btn:hover {
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
}

table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5rem 0;
}

td {
  padding: 0.75rem;
  vertical-align: top;
}

label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
  cursor: pointer;
  transition: var(--transition);
  padding: 0.5rem;
  border-radius: 0.25rem;
}

label:hover {
  background-color: rgba(67, 97, 238, 0.05);
}

input[type="checkbox"] {
  appearance: none;
  width: 1.2em;
  height: 1.2em;
  border: 2px solid #adb5bd;
  border-radius: 0.25em;
  transition: var(--transition);
  position: relative;
  cursor: pointer;
  flex-shrink: 0;
}

input[type="checkbox"]:checked {
  background-color: var(--primary);
  border-color: var(--primary);
}

.submit-btn {
  display: block;
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
}

.submit-btn:hover {
  background-color: var(--primary-hover);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.error {
  color: #e63946;
  font-weight: 500;
  text-align: center;
  padding: 1rem;
  background-color: rgba(230, 57, 70, 0.1);
  border-radius: var(--radius);
  margin-bottom: 1.5rem;
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
    gap: 1rem;
  }
  
  table {
    display: block;
  }
  
  tr {
    display: flex;
    flex-direction: column;
  }
  
  td {
    padding: 0.5rem;
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
        <h2>Select Exactly 10 Cities</h2>
        <div class="user-info">
            <span class="user-email"><?php echo htmlspecialchars($email); ?></span>
            <a href="logout.php" class="logout-btn">Log Out</a>
        </div>
    </div>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="showaqi.php?email=<?php echo urlencode($email); ?>" onsubmit="return validateForm()">
        <table>
            <?php
            $cols = 2;
            $rows = ceil(count($cities) / $cols);
            for ($i = 0; $i < $rows; $i++) {
                echo "<tr>";
                for ($j = 0; $j < $cols; $j++) {
                    $index = $i + $j * $rows;
                    echo "<td>";
                    if (isset($cities[$index])) {
                        $city = htmlspecialchars($cities[$index]);
                        echo "<label><input type='checkbox' name='cities[]' value='$city'> $city</label>";
                    }
                    echo "</td>";
                }
                echo "</tr>";
            }
            ?>
        </table>
        <button type="submit" class="submit-btn">Submit Selection</button>
    </form>
</div>

<script>
function validateForm() {
    const checkboxes = document.querySelectorAll('input[name="cities[]"]:checked');
    if (checkboxes.length !== 10) {
        alert("Please select exactly 10 cities.");
        return false;
    }
    return true;
}
</script>

</body>
</html>