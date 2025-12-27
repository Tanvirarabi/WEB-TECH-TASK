<?php
session_start();

// Set cookie for favorite color if submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['favcolor'])) {
        $color = $_POST['favcolor'];
        setcookie('favcolor', $color, time() + (86400 * 30), "/"); // 30 days
    }
}

$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "aqi";

$favorite_color = isset($_COOKIE['favcolor']) ? $_COOKIE['favcolor'] : '#667eea';
$insert_error = "";
$show_success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['final_submit'])) {
        
        if (!isset($_SESSION['form_data'])) {
            $insert_error = "Session expired or no data found.";
        } else {
            $data = $_SESSION['form_data'];
            $first_name = $data['fname'] ?? '';
            $last_name = $data['lname'] ?? '';
            $email = $data['email'] ?? '';
            $password_plain = $data['pwd'] ?? '';
            $dob = $data['birthday'] ?? '';
            $country = $data['countryInput'] ?? '';
            $gender = $data['Gender'] ?? '';
            $favorite_color = $data['favcolor'] ?? $favorite_color;

            $conn = new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) 
            {
                die("Connection failed: " . $conn->connect_error);
            }

            // Check if email exists
            $email_check_stmt = $conn->prepare("SELECT COUNT(*) FROM userinfo WHERE email = ?");
            $email_check_stmt->bind_param("s", $email);
            $email_check_stmt->execute();
            $email_check_stmt->bind_result($count);
            $email_check_stmt->fetch();
            $email_check_stmt->close();

            if ($count > 0) {
                $insert_error = "Email already exists. Please use another email.";
            } else {
                // Insert raw password (no encryption)
                $stmt = $conn->prepare("INSERT INTO userinfo (first_name, last_name, email, password, dob, country, gender, favorite_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $insert_error = "Prepare failed: " . $conn->error;
                } else {
                    $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $password_plain, $dob, $country, $gender, $favorite_color);
                    if ($stmt->execute()) {
                        unset($_SESSION['form_data']);
                        $show_success = true;
                    } else {
                        $insert_error = "Insert failed: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
            $conn->close();
        }
    } else {
        // First POST 
        $_SESSION['form_data'] = $_POST;

        $first_name = htmlspecialchars($_POST['fname'] ?? '');
        $last_name = htmlspecialchars($_POST['lname'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $password = $_POST['pwd'] ?? '';
        $dob = htmlspecialchars($_POST['birthday'] ?? '');
        $country = htmlspecialchars($_POST['countryInput'] ?? '');
        $gender = htmlspecialchars($_POST['Gender'] ?? '');
        $favorite_color = htmlspecialchars($_POST['favcolor'] ?? $favorite_color);

        $masked_password = str_repeat('•', strlen($password));
    }
} else {
    $first_name = $last_name = $email = $password = $dob = $country = $gender = "";
    $masked_password = "";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Form Submission</title>
    <style>
        :root {
  --primary: #667eea;
  --primary-dark: #5563c1;
  --secondary: #764ba2;
  --success: #48bb78;
  --success-light: #d4edda;
  --success-dark: #155724;
  --error: #e53e3e;
  --error-light: #f8d7da;
  --error-dark: #721c24;
  --text-dark: #2d3748;
  --text-light: #f8f9fa;
  --bg-light: #ffffff;
  --border-light: #e2e8f0;
  --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --radius: 0.5rem;
  --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  margin: 0;
  padding: 2rem;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: <?php echo $favorite_color; ?>;
  background: linear-gradient(135deg, <?php echo $favorite_color; ?>, var(--secondary));
  color: var(--text-light);
  line-height: 1.5;
  transition: var(--transition);
}

.container {
  background: var(--bg-light);
  color: var(--text-dark);
  width: 100%;
  max-width: 40rem;
  margin: 2rem auto;
  padding: 2.5rem;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  transition: var(--transition);
  overflow: hidden;
}

.container:hover {
  transform: translateY(-0.25rem);
  box-shadow: var(--shadow-hover);
}

h2 {
  text-align: center;
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 1.75rem;
  color: var(--secondary);
  position: relative;
  padding-bottom: 0.75rem;
}

h2::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 4rem;
  height: 0.25rem;
  background: linear-gradient(90deg, var(--primary), var(--secondary));
  border-radius: 0.25rem;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 1rem;
  margin: 1.5rem 0;
  background: white;
  border-radius: var(--radius);
  overflow: hidden;
}

td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border-light);
  text-align: left;
}

tr:last-child td {
  border-bottom: none;
}

tr:hover {
  background-color: rgba(118, 75, 162, 0.05);
}

.color-box {
  display: inline-block;
  width: 1.25rem;
  height: 1.25rem;
  vertical-align: middle;
  border-radius: 0.25rem;
  margin-right: 0.5rem;
  box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
}

.button-group {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  border-radius: var(--radius);
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
  transform: translateY(-0.125rem);
}

.btn-outline {
  background-color: transparent;
  color: var(--primary);
  box-shadow: inset 0 0 0 1px var(--primary);
}

.btn-outline:hover {
  background-color: rgba(102, 126, 234, 0.1);
}

.success-msg {
  padding: 1.25rem;
  background-color: var(--success-light);
  color: var(--success-dark);
  border-radius: var(--radius);
  margin-bottom: 1.5rem;
  font-weight: 500;
  text-align: center;
  border-left: 0.25rem solid var(--success);
}

.error-msg {
  padding: 1.25rem;
  background-color: var(--error-light);
  color: var(--error-dark);
  border-radius: var(--radius);
  margin-bottom: 1.5rem;
  font-weight: 500;
  text-align: center;
  border-left: 0.25rem solid var(--error);
}

.password-toggle {
  margin-left: 0.75rem;
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
  background-color: rgba(102, 126, 234, 0.1);
  color: var(--primary);
  border-radius: 0.25rem;
  transition: var(--transition);
}

.password-toggle:hover {
  background-color: rgba(102, 126, 234, 0.2);
}

@media (max-width: 640px) {
  body {
    padding: 1rem;
  }
  
  .container {
    padding: 1.5rem;
  }
  
  h2 {
    font-size: 1.5rem;
  }
  
  td {
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
  }
  
  .button-group {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .btn {
    width: 100%;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(0.5rem);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.container {
  animation: fadeIn 0.3s ease-out forwards;
}
    </style>
</head>
<body>
    <div class="container">
        <h2>Data submitted successfully</h2>

        <?php if ($show_success): ?>
            <div class="success-msg">
                 Data saved successfully,
                 Redirecting to homepage in 2 seconds<<..
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = "index.html";
                }, 2000);
            </script>
        <?php else: ?>

            <?php if (!empty($insert_error)) : ?>
                <div class="error-msg"><?php echo $insert_error; ?></div>
            <?php endif; ?>

            <?php if (!empty($first_name)) : ?>
                <table>
                    <tr>
                        <td><strong>First Name</strong></td>
                        <td><?php echo $first_name; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last Name</strong></td>
                        <td><?php echo $last_name; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td><?php echo $email; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Password</strong></td>
                        <td>
                            <span id="passwordMasked"><?php echo str_repeat('•', strlen($password)); ?></span>
                            <span id="passwordText" style="display:none;"><?php echo htmlspecialchars($password); ?></span>
                            <button type="button" class="toggle-btn" onclick="togglePassword(event)">Show</button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Date of Birth</strong></td>
                        <td><?php echo $dob; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Country</strong></td>
                        <td><?php echo $country; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Gender</strong></td>
                        <td><?php echo $gender; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Favorite Color</strong></td>
                        <td><span class="color-box" style="background: <?php echo htmlspecialchars($favorite_color); ?>;"></span> <?php echo htmlspecialchars($favorite_color); ?></td>
                    </tr>
                </table>

                <form method="post" style="margin-top: 20px;">
                    <button type="button" class="action-btn" onclick="goBack()">Back</button>
                    <button type="submit" name="final_submit" class="action-btn">Confirm</button>
                </form>
            <?php else: ?>
                <p>No data submitted.</p>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <script>
        function togglePassword(event) {
            const btn = event.target;
            const passwordMasked = document.getElementById('passwordMasked');
            const passwordText = document.getElementById('passwordText');

            if (passwordMasked.style.display === 'none') {
                passwordMasked.style.display = 'inline';
                passwordText.style.display = 'none';
                btn.textContent = 'Show';
            } else {
                passwordMasked.style.display = 'none';
                passwordText.style.display = 'inline';
                btn.textContent = 'Hide';
            }
        }

        function goBack() {
            window.location.href = 'index.html';
        }
    </script>
</body>
</html>