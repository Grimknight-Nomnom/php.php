<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Include database connection
include 'includes/db_connection.php';

// Handle session errors (e.g., login errors)
if (isset($_SESSION['login_error'])) {
    echo "<p style='color:red;'>" . $_SESSION['login_error'] . "</p>";
    unset($_SESSION['login_error']);
}

// Replace your current registration code with this:
// Replace your current registration code with this:
if (isset($_POST['register'])) {
    $surname = isset($_POST['surname']) ? htmlspecialchars($_POST['surname']) : '';
    $middleInitial = isset($_POST['middleInitial']) ? htmlspecialchars($_POST['middleInitial']) : '';
    $firstName = isset($_POST['firstName']) ? htmlspecialchars($_POST['firstName']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $contactNumber = isset($_POST['contactNumber']) ? htmlspecialchars($_POST['contactNumber']) : '';
    $birthMonth = isset($_POST['birthMonth']) ? $_POST['birthMonth'] : '';
    $birthDay = isset($_POST['birthDay']) ? $_POST['birthDay'] : '';
    $birthYear = isset($_POST['birthYear']) ? $_POST['birthYear'] : '';
    $birthday = '';
    if ($birthMonth && $birthDay && $birthYear) 
        $birthday = sprintf('%04d-%02d-%02d', $birthYear, $birthMonth, $birthDay);
    $password = $_POST['regPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validate passwords match
    if ($password !== $confirmPassword) {
        echo "<script>showErrorModal('Passwords do not match.');</script>";
    } else {
        try {
            // Generate a 3-digit random ID number
            $userId = null;
            $maxAttempts = 10; // Prevent infinite loops
            $attempts = 0;
            
            do {
                $userId = mt_rand(100, 999);
                $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $existingUser = $stmt->fetch();
                $attempts++;
                
                if ($attempts >= $maxAttempts) {
                    throw new Exception("Could not generate a unique user ID after $maxAttempts attempts.");
                }
            } while ($existingUser);
            
            // Insert user data
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (id, surname, middleInitial, firstName, email, contactNumber, birthday, password) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$userId, $surname, $middleInitial, $firstName, $email, $contactNumber, $birthday, $hashedPassword])) {
                // Show congratulations modal with user ID
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const congratsModal = document.getElementById('congratsModal');
                        const displayUserId = document.getElementById('displayUserId');
                        
                        if (congratsModal && displayUserId) {
                            displayUserId.textContent = '$userId';
                            congratsModal.style.display = 'flex';
                            registerModal.style.display = 'none';
                        }
                    });
                </script>";
            } else {
                $errorInfo = $stmt->errorInfo();
                echo "<script>showErrorModal('Registration failed: " . addslashes($errorInfo[2]) . "');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>showErrorModal('Database error: " . addslashes($e->getMessage()) . "');</script>";
        } catch (Exception $e) {
            echo "<script>showErrorModal('" . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Handle Login
if (isset($_POST['login'])) {
    $idNumber = htmlspecialchars($_POST['idNumber']);
    $password = $_POST['password'];

    try {
        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$idNumber]);
        $user = $stmt->fetch();

        if (empty($user)) {
            // User not found case
            $_SESSION['login_error'] = "Wrong ID or Password! Make sure that you input correct ID and Password. If you forgot your ID and password you can check it on the Barangay Looc Health Center";
            header('Location: ClinicPhp.php');
            exit();
        }

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['firstName'] = $user['firstName'];
            $_SESSION['admin'] = $user['admin']; // Store admin status in session
            
            // Redirect based on admin status
            if ($user['admin'] == 1) {
                header('Location: Admin/admin-dashboard.php');
                exit();
            } else {
                header('Location: user/user.php');
                exit();
            }
        } else {
            // Wrong password case
            $_SESSION['login_error'] = "Wrong ID or Password! Make sure that you input correct ID and Password. If you forgot your ID and password you can check it on the Barangay Looc Health Center";
            header('Location: ClinicPhp.php');
            exit();
        }
    } catch (PDOException $e) {
        // Database error case
        $_SESSION['login_error'] = "A database error occurred. Please try again later.";
        header('Location: ClinicPhp.php');
        exit();
    }
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Barangay Looc Health Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="stylephp.css" />
</head>
<body>

<!-- Header Section -->
<header class="header">
        <a href="#" class="logo">
            <i class="fas fa-heartbeat"></i> <strong>Med</strong>vault
        </a>

        <nav class="navbar">
            <a href="#home">home</a>
            <a href="#about">about</a>
            <a href="#details">details</a>
            <a href="#staff">staff</a>
        </nav>

        <div class="auth-buttons">
            <button class="btn" id="loginBtn">Login</button>
            <button class="btn" id="registerBtn">Register</button>
        </div>

        <div id="menu-btn" class="fas fa-bars"></div>
    </header>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeLoginModal">&times;</span>
            <h2>Login</h2>
            <form id="loginForm" method="post" action="ClinicPhp.php">
                <input type="hidden" name="login" />
                <label for="idNumber">ID Number:</label>
                <input type="text" id="idNumber" name="idNumber" required />

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required />

                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>

<!-- Error Modal -->
<div id="errorModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close-btn" onclick="closeErrorModal()">&times;</span>
        <h2>Error</h2>
        <p id="errorMessage"></p>
        <button class="btn" onclick="closeErrorModal()">OK</button>
    </div>
</div>

<!-- Register Modal -->
<div id="registerModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" id="closeRegisterModal">&times;</span>
        <h2>Register</h2>
        <form id="registerForm" method="post" action="ClinicPhp.php">
            <input type="hidden" name="register" />
            
            <!-- First Row: Name Fields -->
            <div class="form-row">
                <div class="form-group">
                    <label for="surname">Surname:</label>
                    <input type="text" id="surname" name="surname" required />
                </div>
                <div class="form-group">
                    <label for="firstName">First Name:</label>
                    <input type="text" id="firstName" name="firstName" required />
                </div>
                <div class="form-group">
                    <label for="middleInitial">Middle Name:</label>
                    <input type="text" id="middleInitial" name="middleInitial" />
                </div>
            </div>
            
            <!-- Second Row: Email -->
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required />
                </div>
            </div>
            
<!-- Third Row: Contact and Birthday -->
<div class="form-row">
<div class="form-group">
    <label for="contactNumber">Contact Number:</label>
    <div class="input-with-prefix">
        <span class="prefix">+63</span>
        <input type="tel" id="contactNumber" name="contactNumber" 
               pattern="[0-9]{10}" maxlength="10" required 
               oninput="validateContactNumber(this)" />
    </div>
</div>
    <div class="form-group">
        <label>Birthday:</label>
        <div class="birthday-selector">
    <select id="birthMonth" name="birthMonth" required>
        <option value="" disabled selected>Month</option>
        <!-- Months will be populated by JavaScript -->
    </select>
    <select id="birthDay" name="birthDay" required disabled>
        <option value="" disabled selected>Day</option>
        <!-- Days will be populated by JavaScript -->
    </select>
    <select id="birthYear" name="birthYear" required>
        <option value="" disabled selected>Year</option>
        <!-- Years will be populated by JavaScript -->
    </select>
</div>
        <input type="hidden" id="birthday" name="birthday" />
    </div>
</div>
            
            <!-- Fourth Row: Passwords -->
            <div class="form-row">
                <div class="form-group">
                    <label for="regPassword">Password:</label>
                    <input type="password" id="regPassword" name="regPassword" required oninput="checkPasswordStrength()" />
                    <div class="password-strength">
                        <div class="strength-meter"></div>
                        <div class="strength-text" id="strengthText">Weak</div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required oninput="checkPasswordMatch()" />
                    <div class="password-match" id="passwordMatch">
                        <span class="match-icon"></span>
                        <span class="match-text">Passwords don't match</span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn" id="registerSubmit">Register</button>
        </form>
    </div>
</div>

    <!-- Congratulations Modal -->
<div id="congratsModal" class="modal">
    <div class="congrats-content">
        <h2>CONGRATULATIONS!</h2>
        <p>You have completed the registration.</p>
        <p>Please use this ID number to login:</p>
        <div class="id-number" id="displayUserId"></div>
        <button class="btn" id="closeCongratsModal">OK</button>
    </div>
</div>

    <!-- header section ends -->

    <!-- home section starts  -->

    <section class="home" id="home">
      <div class="image">
        <img src="Images/2.gif" alt="" />
      </div>

      <div class="content">
        <h3>Barangay Looc Clinic</h3>
        <p>
          At Barangay Looc Clinic, we are committed to providing compassionate,
          reliable, and high-quality healthcare to our community. Led by Dr.
          Adelinno Labro and supported by our dedicated team of skilled nurses
          and staff, we prioritize patient safety, confidentiality, and
          personalized care. With a strong emphasis on accuracy and
          professionalism in all our services, you can trust us to meet your
          health needs with integrity and excellence.
        </p>
      </div>
    </section>

    <!-- home section ends -->

    <!-- about section starts  -->

    <section class="about" id="about">
      <h1 class="heading"><span>about</span> us</h1>

      <div class="row">
        <div class="image">
          <img src="Images/1.png" alt="" />
        </div>

        <div class="content">
          <p>
            In the 1980s, the City Health Office of Calamba was established by
            the barangay captain of Looc, aiming to enhance community health
            services. While it has become a vital resource for local residents,
            one of its biggest challenges is fostering effective communication
            with patients. Many individuals struggle to fully understand the
            information provided, often due to varying levels of health
            literacy. The dedicated staff work hard to adapt their communication
            methods, utilizing visual aids and simplified language, yet the
            persistent gap in understanding highlights the need for ongoing
            training and resources to ensure all patients can grasp essential
            health information.
          </p>

          <p>
            The Barangay Looc Clinic, situated in Barangay Looc, Calamba,
            Laguna, serves as a vital healthcare resource for the local
            community, providing essential services such as free medical
            checkups and, when available, complementary medications. This
            community clinic is focused on delivering accessible healthcare,
            particularly to residents who may not have the means to visit larger
            facilities.
          </p>
        </div>
      </div>
    </section>

    <!-- about section ends -->

    <!-- services section starts  -->

    <section class="details" id="details">
      <h1 class="heading">our <span>details</span></h1>

      <div class="box-container">
        <div class="box">
          <h3>Mission</h3>
          <p>Provide Efficient, Effective and Quality Public Health Care.</p>
        </div>

        <div class="box">
          <h3>Goal</h3>
          <p>To Improve Health Status of all Calambuños.</p>
        </div>

        <div class="box">
          <h3>Vision</h3>
          <p>A Healthy City, A Healthy Community with a Healthy Population.</p>
        </div>
        
      </div>
    </section>

    <!-- services section ends -->

    <!-- staff section starts  -->

    <section class="staff" id="staff">
      <h1 class="heading">our <span>staff</span></h1>

      <div class="box-container">
        <div class="box">
          <h3>Dr.Adelinno Labro</h3>
          <span>Doctor</span>
        </div>

        <div class="box">
          <h3>John Paul Dela Cruz</h3>
          <span>Nurse</span>
        </div>

        <div class="box">
          <h3>Krystal Mae Anarna </h3>
          <span>Nurse</span>
        </div>

        <div class="box">
          <h3>Elena Divina</h3>
          <span>Nutrition Scholar</span>
        </div>

        <div class="box">
          <h3>Nena Alcaraz </h3>
          <span>Nutrition Scholar</span>
        </div>

        <div class="box">
          <h3>Lolita Mane</h3>
          <span>Nutrition Scholar</span>
        </div>

        <div class="box">
          <h3>Christine Manalac</h3>
          <span>Health Workers</span>
        </div>

        <div class="box">
          <h3>Roberta Manlapaz</h3>
          <span>Health Workers</span>
        </div>

        <div class="box">
          <h3>Fia Delima</h3>
          <span>Health Workers</span>
        </div>

        <div class="box">
          <h3>Corazon Alcala</h3>
          <span>Health Workers</span>
        </div>

        <div class="box">
          <h3>Roberta Alintanahin</h3>
          <span>Health Workers</span>
        </div>

        <div class="box">
          <h3>Precila Magpantay</h3>
          <span>Health Workers</span>
        </div>

        <div class="box">
          <h3>Charmaine Dazo</h3>
          <span>Health Workers</span>
        </div>

        <div class="box">
          <h3>Evangeline Ignacio</h3>
          <span>Health Workers</span>
        </div>

        <div class="box">
          <h3>Marites ilanes</h3>
          <span>Health Workers</span>
        </div>

      </div>
    </section>

    <!-- doctors section ends -->

    <!-- footer section starts  -->

    <section class="footer">
    <div class="box-container">
        <div class="box">
            <h3>quick links</h3>
            <a href="#home"> <i class="fas fa-chevron-right"></i> home </a>
            <a href="#about"> <i class="fas fa-chevron-right"></i> about </a>
            <a href="#details"> <i class="fas fa-chevron-right"></i> services </a>
            <a href="#staff"> <i class="fas fa-chevron-right"></i> staff </a>
        </div>

        <div class="box">
            <h3>appointment info</h3>
            <a href="#"> <i class="fas fa-phone"></i> +639999999999 </a>
            <a href="#"> <i class="fas fa-phone"></i> +639999999999 </a>
            <a href="#"> <i class="fas fa-envelope"></i> @gmail.com </a>
            <a href="#"> <i class="fas fa-envelope"></i> @gmail.com </a>
            <a href="#">
                <i class="fas fa-map-marker-alt"></i> laguna, Brgy. Looc
            </a>
        </div> <!-- This closing div was missing -->

        <div class="box">
            <h3>follow us</h3>
            <a href="#"> <i class="fab fa-twitter"></i> twitter </a>
            <a href="#"> <i class="fab fa-facebook"></i> facebook </a>
            <a href="#"> <i class="fab fa-instagram"></i> instagram </a>
        </div>
    </div>

    <div class="credit">
        created by <span>Princess Rilaine</span> | all rights reserved
    </div>
</section>

    <!-- footer section ends -->

    <!-- js file link  -->
     <script src="admin.js"></script>
  </body>
</html>
