<?php
session_start();


// Redirect if not logged in or is admin
if (!isset($_SESSION['user_id']) || (isset($_SESSION['admin']) && $_SESSION['admin'] == 1)) {
    header('Location: ClinicPhp.php');
    exit();
}

// Initialize user variable
$user = null;
$user_id = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "clinicphp";

    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Update the SQL query to include all necessary fields
$sql = "SELECT id, firstName, surname, middleInitial, contactNumber, email, age, address, 
        description, checkup, birthday, gender, civilStatus, occupation, 
        existingMedical, currentMedication, allergies, familyMedical, 
        bloodPressure, heartRate, temperature, height, weight, 
        guardian, relationship, guardianContact, emergency_contact_number,
        philhealth, seniorCitizen 
        FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// In your update_user.php, before the database update:
    if (isset($data['contactNumber'])) {  // Added missing parenthesis here
        if (!preg_match('/^\+63\d{10}$/', $data['contactNumber'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid contact number format']);
            exit();
        }
    }
    
    if (isset($data['guardianContact'])) {
        if (!preg_match('/^\+63\d{10}$/', $data['guardianContact'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid emergency contact number format']);
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
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" />
    <link rel="stylesheet" href="user.css" />
</head>
<body>
<div id="modalContainer" class="modal-container" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Schedule Patient</h2>
        <form id="scheduleForm">
            <label for="patientName">Patient Name:</label>
            <input type="text" id="patientName" name="patientName" 
                   value="<?php echo isset($user) ? htmlspecialchars($user['firstName'] . ' ' . $user['surname']) : ''; ?>" readonly>
            <label for="appointmentDate">Appointment Date:</label>
            <input type="date" id="appointmentDate" name="appointmentDate" required>
            <button type="submit" class="btn">Save Schedule</button>
        </form>
    </div>
</div>

    <div class="dashboard">
        <div class="sidebar">
            <div class="user-info">
                <a href="#" class="logo">
                    <i class="fas fa-heartbeat"></i> <strong>Med</strong>vault
                </a>
                <div class="user-icon">👤</div>
                <div class="user-name">
                    <?php echo isset($user) ? htmlspecialchars($user['firstName'] . ' ' . $user['surname']) : 'Guest'; ?>
                </div>
                <div class="user-id">
            ID: <?php echo isset($user['id']) ? htmlspecialchars($user['id']) : 'N/A'; ?>
        </div>
                <a href="../logout.php" class="btn">Logout</a>
            </div>
            <div class="menu">
    <button class="menu-btn" data-section="record">MY RECORD</button>
    <button class="menu-btn" data-section="registration">SCHEDULE</button>
    <button class="menu-btn" data-section="stockOfMedicines">MEDICINES</button>
</div>
        </div>
        <div class="main-content">
            <div id="content">
                <!-- Default Content -->
                <div id="defaultContent" class="content-section">
                    <h2>Welcome to the Barangay Looc Clinic Dashboard</h2>
                    <p>Please Choose an option from the sidebar.</p>
                </div>


            
<!-- Update the record section in user.php -->
<div id="record" class="content-section" style="display: none;">
    <h2>My Medical Record</h2>
    <button id="editRecordBtn" class="btn">Edit Information</button>
    <button id="saveRecordBtn" class="btn" style="display: none;">Save Changes</button>
    <button id="cancelEditBtn" class="btn" style="display: none;">Cancel</button>
    
    <div class="record-container">
        <form id="recordForm">
            <div class="record-section">
                <h3>Personal Information</h3>
                <div class="record-row">
                    <div class="record-field">
                        <span class="record-label">Surname:</span>
                        <span class="record-value view-mode"><?php echo isset($user['surname']) ? htmlspecialchars($user['surname']) : 'N/A'; ?></span>
                        <input type="text" class="edit-mode" name="surname" value="<?php echo isset($user['surname']) ? htmlspecialchars($user['surname']) : ''; ?>" style="display: none;">
                    </div>
                    <div class="record-field">
                        <span class="record-label">First Name:</span>
                        <span class="record-value view-mode"><?php echo isset($user['firstName']) ? htmlspecialchars($user['firstName']) : 'N/A'; ?></span>
                        <input type="text" class="edit-mode" name="firstName" value="<?php echo isset($user['firstName']) ? htmlspecialchars($user['firstName']) : ''; ?>" style="display: none;">
                    </div>
                    <div class="record-field">
    <span class="record-label">Middle Name:</span>
    <span class="record-value view-mode"><?php echo isset($user['middleInitial']) ? htmlspecialchars($user['middleInitial']) : 'N/A'; ?></span>
    <input type="text" class="edit-mode" name="middleInitial" 
           value="<?php echo isset($user['middleInitial']) ? htmlspecialchars($user['middleInitial']) : ''; ?>" 
           style="display: none;">
</div>
                </div>
        
                <div class="record-row">
    <div class="record-field">
        <span class="record-label">Date of Birth:</span>
        <span class="record-value view-mode">
            <?php 
            if (isset($user['birthday']) && !empty($user['birthday'])) {
                $birthday = new DateTime($user['birthday']);
                echo htmlspecialchars($birthday->format('F j, Y'));
            } else {
                echo 'N/A';
            }
            ?>
        </span>
        <div class="edit-mode" style="display: none;">
            <input type="date" name="birthday" 
                   value="<?php echo isset($user['birthday']) ? htmlspecialchars($user['birthday']) : ''; ?>"
                   class="date-input">
        </div>
    </div>
    <div class="record-field">
        <span class="record-label">Age:</span>
        <span class="record-value view-mode"><?php echo isset($user['age']) ? htmlspecialchars($user['age']) : 'N/A'; ?></span>
        <input type="number" class="edit-mode" name="age" 
               value="<?php echo isset($user['age']) ? htmlspecialchars($user['age']) : ''; ?>" 
               style="display: none;" min="0" max="120">
    </div>
    <div class="record-field">
        <span class="record-label">Gender:</span>
        <span class="record-value view-mode">
            <?php 
            if(isset($user['gender'])) {
                switch($user['gender']) {
                    case 'male': echo 'Male'; break;
                    case 'female': echo 'Female'; break;
                    case 'rather_not_say': echo 'Rather not say'; break;
                    default: echo 'N/A';
                }
            } else {
                echo 'N/A';
            }
            ?>
        </span>
        <div class="edit-mode" style="display: none;">
            <select name="gender" class="gender-select">
                <option value="male" <?php echo (isset($user['gender']) && $user['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo (isset($user['gender']) && $user['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="rather_not_say" <?php echo (isset($user['gender']) && $user['gender'] == 'rather_not_say') ? 'selected' : ''; ?>>Rather not say</option>
            </select>
        </div>
    </div>
</div>
                
                <div class="record-row">
                    <div class="record-field full-width">
                        <span class="record-label">Address:</span>
                        <span class="record-value view-mode"><?php echo isset($user['address']) ? htmlspecialchars($user['address']) : 'N/A'; ?></span>
                        <input type="text" class="edit-mode" name="address" 
                               value="<?php echo isset($user['address']) ? htmlspecialchars($user['address']) : ''; ?>" 
                               style="display: none;">
                    </div>
                </div>
                
                <div class="record-row">
                    <div class="record-field">
                        <span class="record-label">Contact Number:</span>
                        <span class="record-value view-mode"><?php echo isset($user['contactNumber']) ? htmlspecialchars($user['contactNumber']) : 'N/A'; ?></span>
                        <input type="tel" class="edit-mode" name="contactNumber" 
                               value="<?php echo isset($user['contactNumber']) ? htmlspecialchars($user['contactNumber']) : ''; ?>" 
                               style="display: none;" 
                               pattern="\+63\d{10}" 
                               title="Phone number must start with +63 followed by 10 digits"
                               required>
                    </div>
                    <div class="record-field">
                        <span class="record-label">Email:</span>
                        <span class="record-value view-mode"><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : 'N/A'; ?></span>
                        <input type="email" class="edit-mode" name="email" 
                               value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" 
                               style="display: none;">
                    </div>
                </div>

                <div class="record-row">
                    <div class="record-field">
                        <span class="record-label">Civil Status:</span>
                        <span class="record-value view-mode"><?php echo isset($user['civilStatus']) ? htmlspecialchars($user['civilStatus']) : 'N/A'; ?></span>
                        <select class="edit-mode" name="civilStatus" style="display: none;">
                            <option value="Single" <?php echo (isset($user['civilStatus']) && $user['civilStatus'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                            <option value="Married" <?php echo (isset($user['civilStatus']) && $user['civilStatus'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                            <option value="Divorced" <?php echo (isset($user['civilStatus']) && $user['civilStatus'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                            <option value="Widowed" <?php echo (isset($user['civilStatus']) && $user['civilStatus'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                            <option value="Separated" <?php echo (isset($user['civilStatus']) && $user['civilStatus'] == 'Separated') ? 'selected' : ''; ?>>Separated</option>
                        </select>
                    </div>
                    <div class="record-field">
                        <span class="record-label">Occupation:</span>
                        <span class="record-value view-mode"><?php echo isset($user['occupation']) ? htmlspecialchars($user['occupation']) : 'N/A'; ?></span>
                        <input type="text" class="edit-mode" name="occupation" 
                               value="<?php echo isset($user['occupation']) ? htmlspecialchars($user['occupation']) : ''; ?>" 
                               style="display: none;">
                    </div>
                </div>

                        
            <h3>Medical History</h3>
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Existing Medical Conditions:</span>
                    <span class="record-value"><?php echo isset($user['existingMedical']) ? htmlspecialchars($user['existingMedical']) : 'None'; ?></span>
                </div>
            </div>
            
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Current Medications:</span>
                    <span class="record-value"><?php echo isset($user['currentMedication']) ? htmlspecialchars($user['currentMedication']) : 'None'; ?></span>
                </div>
            </div>
            
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Allergies:</span>
                    <span class="record-value"><?php echo isset($user['allergies']) ? htmlspecialchars($user['allergies']) : 'None'; ?></span>
                </div>
            </div>
            
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Family Medical History:</span>
                    <span class="record-value"><?php echo isset($user['familyMedical']) ? htmlspecialchars($user['familyMedical']) : 'None'; ?></span>
                </div>
            </div>
            
           <!-- Vital Signs Section -->
<h3>Vital Signs & Physical Examination</h3>
<div class="vital-signs-row">
    <div class="vital-signs-field">
        <span class="record-label">Blood Pressure:</span>
        <span class="record-value"><?php echo isset($user['bloodPressure']) ? htmlspecialchars($user['bloodPressure']) : 'N/A'; ?></span>
    </div>
    <div class="vital-signs-field">
        <span class="record-label">Heart Rate:</span>
        <span class="record-value"><?php echo isset($user['heartRate']) ? htmlspecialchars($user['heartRate']) : 'N/A'; ?></span>
    </div>
    <div class="vital-signs-field">
        <span class="record-label">Temperature:</span>
        <span class="record-value"><?php echo isset($user['temperature']) ? htmlspecialchars($user['temperature']) : 'N/A'; ?></span>
    </div>
</div>

<div class="vital-signs-row">
    <div class="vital-signs-field">
        <span class="record-label">Height (cm):</span>
        <span class="record-value"><?php echo isset($user['height']) ? htmlspecialchars($user['height']) : 'N/A'; ?></span>
    </div>
    <div class="vital-signs-field">
        <span class="record-label">Weight (kg):</span>
        <span class="record-value"><?php echo isset($user['weight']) ? htmlspecialchars($user['weight']) : 'N/A'; ?></span>
    </div>
    <div class="vital-signs-field bmi-field">
        <span class="record-label">BMI:</span>
        <span class="record-value">
            <?php
            if (isset($user['height']) && isset($user['weight']) && 
                is_numeric($user['height']) && is_numeric($user['weight']) && 
                $user['height'] > 0) {
                $heightInMeters = $user['height'] / 100;
                $bmi = $user['weight'] / ($heightInMeters * $heightInMeters);
                echo '<span class="bmi-value">' . number_format($bmi, 1) . '</span> (';
                if ($bmi < 18.5) {
                    echo 'Underweight';
                } elseif ($bmi >= 18.5 && $bmi < 25) {
                    echo 'Normal weight';
                } elseif ($bmi >= 25 && $bmi < 30) {
                    echo 'Overweight';
                } else {
                    echo 'Obese';
                }
                echo ')';
            } else {
                echo 'N/A';
            }
            ?>
        </span>
    </div>
</div>
            
            <h3>Consultation Details</h3>
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Medicine Intake:</span>
                    <span class="record-value"><?php echo isset($user['description']) ? htmlspecialchars($user['description']) : 'N/A'; ?></span>
                </div>
            </div>
            
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Checked by:</span>
                    <span class="record-value"><?php echo isset($user['checkup']) ? htmlspecialchars($user['checkup']) : 'No checkup information available'; ?></span>
                </div>
            </div>
                
                <h3>Emergency Contact Information</h3>
                <div class="record-row">
                    <div class="record-field">
                        <span class="record-label">Guardian/Family Member:</span>
                        <span class="record-value view-mode"><?php echo isset($user['guardian']) ? htmlspecialchars($user['guardian']) : 'N/A'; ?></span>
                        <input type="text" class="edit-mode" name="guardian" value="<?php echo isset($user['guardian']) ? htmlspecialchars($user['guardian']) : ''; ?>" style="display: none;">
                    </div>
                    <div class="record-field">
                        <span class="record-label">Relationship:</span>
                        <span class="record-value view-mode"><?php echo isset($user['relationship']) ? htmlspecialchars($user['relationship']) : 'N/A'; ?></span>
                        <input type="text" class="edit-mode" name="relationship" value="<?php echo isset($user['relationship']) ? htmlspecialchars($user['relationship']) : ''; ?>" style="display: none;">
                    </div>
                    <div class="record-field">
                        <span class="record-label">Contact Number:</span>
                        <span class="record-value view-mode"><?php echo isset($user['guardianContact']) ? htmlspecialchars($user['guardianContact']) : 'N/A'; ?></span>
                        <input type="tel" class="edit-mode" name="guardianContact" 
                               value="<?php echo isset($user['guardianContact']) ? htmlspecialchars($user['guardianContact']) : ''; ?>" 
                               style="display: none;"
                               pattern="\+63\d{10}"
                               title="Phone number must start with +63 followed by 10 digits">
                    </div>
                    <div class="record-field">
    <span class="record-label">Emergency Contact #2:</span>
    <span class="record-value view-mode"><?php echo isset($user['emergency_contact_number']) ? htmlspecialchars($user['emergency_contact_number']) : 'N/A'; ?></span>
    <input type="tel" class="edit-mode" name="emergency_contact_number" 
           value="<?php echo isset($user['emergency_contact_number']) ? htmlspecialchars($user['emergency_contact_number']) : ''; ?>" 
           style="display: none;"
           pattern="\+63\d{10}"
           title="Phone number must start with +63 followed by 10 digits">
</div>
                </div>
                
                <h3>Health Program</h3>
                <div class="record-row">
                    <div class="record-field">
                        <span class="record-label">Philhealth Member:</span>
                        <span class="record-value view-mode">
                            <?php echo isset($user['philhealth']) && $user['philhealth'] ? 'Yes' : 'No'; ?>
                        </span>
                        <label class="edit-mode" style="display: none;">
                            <input type="checkbox" name="philhealth" <?php echo isset($user['philhealth']) && $user['philhealth'] ? 'checked' : ''; ?>>
                            Philhealth Member
                        </label>
                    </div>
                    <div class="record-field">
                        <span class="record-label">Senior Citizen/PWD:</span>
                        <span class="record-value view-mode">
                            <?php echo isset($user['seniorCitizen']) && $user['seniorCitizen'] ? 'Yes' : 'No'; ?>
                        </span>
                        <label class="edit-mode" style="display: none;">
                            <input type="checkbox" name="seniorCitizen" <?php echo isset($user['seniorCitizen']) && $user['seniorCitizen'] ? 'checked' : ''; ?>>
                            Senior Citizen/PWD
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

                <!-- Registration Section -->
<div id="registration" class="content-section" style="display: none;">
    <div class="registration-header">
        <h2>Schedule</h2>
        <div class="button-group">
            <button id="scheduleBtn" class="btn">Schedule Patient</button>
            <button id="deleteScheduleBtn" class="btn btn-danger" style="background-color: #dc3545;">Delete My Schedule</button>
        </div>
    </div>
    <div id="calendar"></div>
</div>

                <!-- Stock of Medicines Section -->
                <div id="stockOfMedicines" class="content-section" style="display: none;">
    <h2>Medicines</h2>
    <div class="search-bar">
        <input type="text" id="searchMedicine" placeholder="Search for a medicine...">
    </div>
    <table id="medicineTable">
        <thead>
            <tr>
                <th>Medicine Name</th>
                <th>Type</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php
            try {
                $conn = new mysqli($servername, $username, $password, $dbname);
                // Optimized query to only pull necessary columns
                $sql = "SELECT id, name, type, description FROM medicines";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // Removed Date formatting logic and extra <td> cells
                        echo "<tr>
                                <td>" . htmlspecialchars($row["name"]) . "</td>
                                <td>" . htmlspecialchars($row["type"]) . "</td>
                                <td>" . htmlspecialchars($row["description"]) . "</td>
                              </tr>";
                    }
                } else {
                    // Updated colspan to 3 to match new column count
                    echo "<tr><td colspan='3'>No medicines found</td></tr>";
                }
                $conn->close();
            } catch (Exception $e) {
                echo "<tr><td colspan='3'>Error loading medicines</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

            </div>
        </div>
    </div>

<script>
    const userId = <?php echo isset($user_id) ? $user_id : 'null'; ?>;
    const userGender = '<?php echo isset($user['gender']) ? $user['gender'] : ''; ?>';
</script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="user.js"></script>
</body>
</html>