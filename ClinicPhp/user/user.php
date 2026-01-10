<?php
session_start();

// 1. SET TIMEZONE TO PHILIPPINES
date_default_timezone_set('Asia/Manila');

// Redirect if not logged in or is admin
if (!isset($_SESSION['user_id']) || (isset($_SESSION['admin']) && $_SESSION['admin'] == 1)) {
    header('Location: ClinicPhp.php');
    exit();
}

// Initialize variables
$user = null;
$user_id = null;
$my_schedule = null;

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

        // --- FETCH USER DETAILS ---
$sql = "SELECT id, firstName, surname, middleInitial, contactNumber, email, age, address, 
        description, checkup, birthday, gender, civilStatus, occupation, 
        existingMedical, currentMedication, allergies, familyMedical, 
        bloodPressure, heartRate, temperature, height, weight, 
        guardian, relationship, guardianContact, emergency_contact_number,
        philhealth, seniorCitizen, status 
        FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
        }
        $stmt->close();

        // --- FETCH UPCOMING SCHEDULE ---
        $today_date = date('Y-m-d');

        /** * FIX: CHANGED TABLE NAME TO 'schedules' 
         * (Based on your delete_schedule.php file)
         */
        $sched_sql = "SELECT appointment_date, appointment_number 
                      FROM schedules 
                      WHERE user_id = ? AND appointment_date >= ? 
                      ORDER BY appointment_date ASC LIMIT 1";
        
        $sched_stmt = $conn->prepare($sched_sql);
        $sched_stmt->bind_param("is", $user_id, $today_date); 
        $sched_stmt->execute();
        $sched_result = $sched_stmt->get_result();

        if ($sched_result->num_rows > 0) {
            $my_schedule = $sched_result->fetch_assoc();
        }
        
        // --- DEBUGGER (Uncomment these 4 lines if it still says "No Appointment") ---
        // echo "<div style='background:yellow; color:black; padding:10px; border:2px solid red;'>";
        // echo "<strong>DEBUG:</strong> Searching Table: 'schedules' <br>";
        // echo "User ID: $user_id | Date Searching: $today_date <br>";
        // echo "Rows Found: " . $sched_result->num_rows . "</div>";
        // ------------------------------------------------------------------------

        $sched_stmt->close();
        $conn->close();

    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
}

// Validation logic
if (isset($data['contactNumber'])) {
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

<div id="deleteConfirmModal" class="modal-container" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <div style="margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle" style="font-size: 3rem; color: #dc3545;"></i>
        </div>
        <h3 style="margin-bottom: 15px;">Delete Schedule?</h3>
        <p style="margin-bottom: 25px; color: #666;">
            Are you sure you want to remove your upcoming schedule? Past schedules cannot be deleted.
        </p>
        <div style="display: flex; justify-content: center; gap: 10px;">
            <button id="cancelDeleteActionBtn" class="btn" style="background-color: #6c757d;">Cancel</button>
            <button id="confirmDeleteActionBtn" class="btn" style="background-color: #dc3545;">OK</button>
        </div>
    </div>
</div>

<div id="deleteSuccessModal" class="modal-container" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <div style="margin-bottom: 20px;">
            <i class="fas fa-check-circle" style="font-size: 3rem; color: #28a745;"></i>
        </div>
        <h3 style="margin-bottom: 15px;">Success</h3>
        <p style="margin-bottom: 25px; color: #666;">
            Schedule cancelled successfully. You can now schedule appointment.
        </p>
        <button id="successOkBtn" class="btn" style="background-color: #FFFFFF;">OK</button>
    </div>
</div>

<div id="deleteErrorModal" class="modal-container" style="display: none;">
    <div class="modal-backdrop"></div>
    <div class="modal-content" style="max-width: 400px; text-align: center;">
        <div style="margin-bottom: 20px;">
            <i class="fas fa-lock" style="font-size: 3rem; color: #6c757d;"></i>
        </div>
        <h3 style="margin-bottom: 15px;">Cannot Delete</h3>
        <p style="margin-bottom: 25px; color: #666;">
            No upcoming schedule found to delete. Past appointments are locked.
        </p>
        <button id="errorOkBtn" class="btn" style="background-color: #6c757d;">Close</button>
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

<?php 
    $status = $user['status'] ?? 'pending';
    $statusClass = ($status === 'approved') ? 'status-approved' : 'status-pending';
    $statusText = ($status === 'approved') ? 'Account Approved' : 'Pending Approval';
    $clickAction = ($status === 'pending') ? 'onclick="goToDefaultContent()"' : '';
?>
<div class="status-box <?php echo $statusClass; ?>" <?php echo $clickAction; ?> title="Click to see details">
    <i class="fas <?php echo ($status === 'approved') ? 'fa-check-circle' : 'fa-clock'; ?>"></i> 
    <?php echo $statusText; ?>
</div>
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

    <div id="approvalWarning" class="alert-warning" style="display: none;">
        <h3><i class="fas fa-exclamation-triangle"></i> Account Pending</h3>
        <p>Your account needs to be approved by the admin before you can set a schedule or request medicines.</p>
        <p>Please wait for administrator confirmation.</p>
        <p>You can contact the Barangay Clinic for confirmation of your Account<B>(+630000000000)</b> or you can go to our barangay clinic.</p>
    </div>
</div>


            
<div id="record" class="content-section" style="display: none;">
    <h2>My Medical Record</h2>
    <div class="button-group" style="margin-bottom: 20px;">
        <button id="editRecordBtn" class="btn">Edit Information</button>
        <button id="saveRecordBtn" class="btn" style="display: none;">Save Changes</button>
        <button id="cancelEditBtn" class="btn" style="display: none;">Cancel</button>
    </div>
    
    <div class="record-container">
        <form id="recordForm"> 
    
    <div class="record-group">
        <h3 onclick="toggleRecordSection(this)">
            Personal Information 
            <i class="fas fa-chevron-down mobile-arrow"></i>
        </h3>
        <div class="record-content">
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
                    <input type="text" class="edit-mode" name="middleInitial" value="<?php echo isset($user['middleInitial']) ? htmlspecialchars($user['middleInitial']) : ''; ?>" style="display: none;">
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
                    <input type="date" class="edit-mode" name="birthday" value="<?php echo isset($user['birthday']) ? htmlspecialchars($user['birthday']) : ''; ?>" style="display: none;">
                </div>
                <div class="record-field">
                    <span class="record-label">Age:</span>
                    <span class="record-value view-mode"><?php echo isset($user['age']) ? htmlspecialchars($user['age']) : 'N/A'; ?></span>
                    <input type="number" class="edit-mode" name="age" value="<?php echo isset($user['age']) ? htmlspecialchars($user['age']) : ''; ?>" style="display: none;">
                </div>
                <div class="record-field">
                    <span class="record-label">Gender:</span>
                    <span class="record-value view-mode">
                        <?php echo isset($user['gender']) ? ucfirst($user['gender']) : 'N/A'; ?>
                    </span>
                    <select name="gender" class="edit-mode" style="display:none;">
                        <option value="male" <?php echo (isset($user['gender']) && $user['gender']=='male')?'selected':''; ?>>Male</option>
                        <option value="female" <?php echo (isset($user['gender']) && $user['gender']=='female')?'selected':''; ?>>Female</option>
                        <option value="rather_not_say" <?php echo (isset($user['gender']) && $user['gender']=='rather_not_say')?'selected':''; ?>>Rather not say</option>
                    </select>
                </div>
            </div>
            
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Address:</span>
                    <span class="record-value view-mode"><?php echo isset($user['address']) ? htmlspecialchars($user['address']) : 'N/A'; ?></span>
                    <input type="text" class="edit-mode" name="address" value="<?php echo isset($user['address']) ? htmlspecialchars($user['address']) : ''; ?>" style="display: none;">
                </div>
            </div>

            <div class="record-row">
                <div class="record-field">
                    <span class="record-label">Contact Number:</span>
                    <span class="record-value view-mode"><?php echo isset($user['contactNumber']) ? htmlspecialchars($user['contactNumber']) : 'N/A'; ?></span>
                    
                    <input type="tel" class="edit-mode" name="contactNumber" 
                           value="<?php echo (isset($user['contactNumber']) && strlen($user['contactNumber']) > 3) ? htmlspecialchars($user['contactNumber']) : '+63'; ?>" 
                           style="display: none;"
                           maxlength="13"
                           oninput="this.value = '+63' + this.value.substring(3).replace(/[^0-9]/g, ''); if(this.value.length > 13) this.value = this.value.slice(0, 13);"
                           placeholder="+639000000000">
                </div>
                <div class="record-field">
                    <span class="record-label">Email:</span>
                    <span class="record-value view-mode"><?php echo isset($user['email']) ? htmlspecialchars($user['email']) : 'N/A'; ?></span>
                    <input type="email" class="edit-mode" name="email" value="<?php echo isset($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" style="display: none;">
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
                    <input type="text" class="edit-mode" name="occupation" value="<?php echo isset($user['occupation']) ? htmlspecialchars($user['occupation']) : ''; ?>" style="display: none;">
                </div>
            </div>
        </div>
    </div>

    <div class="record-group">
        <h3 onclick="toggleRecordSection(this)">
            Medical History
            <i class="fas fa-chevron-down mobile-arrow"></i>
        </h3>
        <div class="record-content">
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Existing Medical Conditions:</span>
                    <span class="record-value view-mode"><?php echo isset($user['existingMedical']) ? htmlspecialchars($user['existingMedical']) : 'None'; ?></span>
                    <textarea class="edit-mode" name="existingMedical" style="display:none;"><?php echo isset($user['existingMedical']) ? htmlspecialchars($user['existingMedical']) : ''; ?></textarea>
                </div>
            </div>
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Current Medications:</span>
                    <span class="record-value view-mode"><?php echo isset($user['currentMedication']) ? htmlspecialchars($user['currentMedication']) : 'None'; ?></span>
                    <textarea class="edit-mode" name="currentMedication" style="display:none;"><?php echo isset($user['currentMedication']) ? htmlspecialchars($user['currentMedication']) : ''; ?></textarea>
                </div>
            </div>
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Allergies:</span>
                    <span class="record-value view-mode"><?php echo isset($user['allergies']) ? htmlspecialchars($user['allergies']) : 'None'; ?></span>
                    <textarea class="edit-mode" name="allergies" style="display:none;"><?php echo isset($user['allergies']) ? htmlspecialchars($user['allergies']) : ''; ?></textarea>
                </div>
            </div>
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Family Medical History:</span>
                    <span class="record-value view-mode"><?php echo isset($user['familyMedical']) ? htmlspecialchars($user['familyMedical']) : 'None'; ?></span>
                    <textarea class="edit-mode" name="familyMedical" style="display:none;"><?php echo isset($user['familyMedical']) ? htmlspecialchars($user['familyMedical']) : ''; ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="record-group">
        <h3 onclick="toggleRecordSection(this)">
            Vital Signs
            <i class="fas fa-chevron-down mobile-arrow"></i>
        </h3>
        <div class="record-content">
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
                        if (isset($user['height']) && isset($user['weight']) && is_numeric($user['height']) && is_numeric($user['weight']) && $user['height'] > 0) {
                            $heightInMeters = $user['height'] / 100;
                            $bmi = $user['weight'] / ($heightInMeters * $heightInMeters);
                            echo '<span class="bmi-value">' . number_format($bmi, 1) . '</span>';
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="record-group">
        <h3 onclick="toggleRecordSection(this)">
            Consultation Details
            <i class="fas fa-chevron-down mobile-arrow"></i>
        </h3>
        <div class="record-content">
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Medicine Intake:</span>
                    <span class="record-value"><?php echo isset($user['description']) ? htmlspecialchars($user['description']) : 'N/A'; ?></span>
                </div>
            </div>
            <div class="record-row">
                <div class="record-field full-width">
                    <span class="record-label">Checked by:</span>
                    <span class="record-value"><?php echo isset($user['checkup']) ? htmlspecialchars($user['checkup']) : 'No checkup info'; ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="record-group">
        <h3 onclick="toggleRecordSection(this)">
            Emergency Contact
            <i class="fas fa-chevron-down mobile-arrow"></i>
        </h3>
        <div class="record-content">
            <div class="record-row">
                <div class="record-field">
                    <span class="record-label">Guardian:</span>
                    <span class="record-value view-mode"><?php echo isset($user['guardian']) ? htmlspecialchars($user['guardian']) : 'N/A'; ?></span>
                    <input type="text" class="edit-mode" name="guardian" value="<?php echo isset($user['guardian']) ? htmlspecialchars($user['guardian']) : ''; ?>" style="display:none;">
                </div>
                <div class="record-field">
                    <span class="record-label">Relationship:</span>
                    <span class="record-value view-mode"><?php echo isset($user['relationship']) ? htmlspecialchars($user['relationship']) : 'N/A'; ?></span>
                    <input type="text" class="edit-mode" name="relationship" value="<?php echo isset($user['relationship']) ? htmlspecialchars($user['relationship']) : ''; ?>" style="display:none;">
                </div>
            </div>
            <div class="record-row">
                <div class="record-field">
                    <span class="record-label">Contact Number:</span>
                    <span class="record-value view-mode"><?php echo isset($user['guardianContact']) ? htmlspecialchars($user['guardianContact']) : 'N/A'; ?></span>
                    
                    <input type="tel" class="edit-mode" name="guardianContact" 
                           value="<?php echo (isset($user['guardianContact']) && strlen($user['guardianContact']) > 3) ? htmlspecialchars($user['guardianContact']) : '+63'; ?>" 
                           style="display:none;"
                           maxlength="13"
                           oninput="this.value = '+63' + this.value.substring(3).replace(/[^0-9]/g, ''); if(this.value.length > 13) this.value = this.value.slice(0, 13);"
                           placeholder="+639000000000">
                </div>
                <div class="record-field">
                    <span class="record-label">Emergency Contact #2:</span>
                    <span class="record-value view-mode"><?php echo isset($user['emergency_contact_number']) ? htmlspecialchars($user['emergency_contact_number']) : 'N/A'; ?></span>
                    
                    <input type="tel" class="edit-mode" name="emergency_contact_number" 
                           value="<?php echo (isset($user['emergency_contact_number']) && strlen($user['emergency_contact_number']) > 3) ? htmlspecialchars($user['emergency_contact_number']) : '+63'; ?>" 
                           style="display:none;"
                           maxlength="13"
                           oninput="this.value = '+63' + this.value.substring(3).replace(/[^0-9]/g, ''); if(this.value.length > 13) this.value = this.value.slice(0, 13);"
                           placeholder="+639000000000">
                </div>
            </div>
        </div>
    </div>

    <div class="record-group">
        <h3 onclick="toggleRecordSection(this)">
            Health Program
            <i class="fas fa-chevron-down mobile-arrow"></i>
        </h3>
        <div class="record-content">
            <div class="record-row">
                <div class="record-field">
                    <span class="record-label">Philhealth Member:</span>
                    <span class="record-value view-mode"><?php echo isset($user['philhealth']) && $user['philhealth'] ? 'Yes' : 'No'; ?></span>
                    <label class="edit-mode" style="display:none; border:none; box-shadow:none;">
                        <input type="checkbox" name="philhealth" <?php echo isset($user['philhealth']) && $user['philhealth'] ? 'checked' : ''; ?>>
                        Yes, I am a member
                    </label>
                </div>
                <div class="record-field">
                    <span class="record-label">Senior Citizen/PWD:</span>
                    <span class="record-value view-mode"><?php echo isset($user['seniorCitizen']) && $user['seniorCitizen'] ? 'Yes' : 'No'; ?></span>
                    <label class="edit-mode" style="display:none; border:none; box-shadow:none;">
                        <input type="checkbox" name="seniorCitizen" <?php echo isset($user['seniorCitizen']) && $user['seniorCitizen'] ? 'checked' : ''; ?>>
                        Yes, I am Senior/PWD
                    </label>
                </div>
            </div>
        </div>
    </div>

</form> 
 </div>
</div>

                <!-- Registration Section -->
<div id="registration" class="content-section" style="display: none;">
    <div class="registration-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Schedule</h2>
        
        <div class="my-schedule-info" style="text-align: center; background-color: #f8f9fa; padding: 10px 20px; border-radius: 8px; border: 1px solid #e9ecef; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
            <?php if ($my_schedule): ?>
                <?php 
                    $dateObj = new DateTime($my_schedule['appointment_date']);
                    $formattedDate = $dateObj->format('F j, Y (l)');
                ?>
                <div style="font-size: 0.9em; color: #555; margin-bottom: 5px;">Your Schedule:</div>
                <div style="font-weight: bold; color: #333; font-size: 1.1em;"><?php echo htmlspecialchars($formattedDate); ?></div>
                <div style="color: #6a11cb; font-weight: bold; font-size: 1.2em; margin-top: 5px;">
                    Queue #: <?php echo htmlspecialchars($my_schedule['appointment_number']); ?>
                </div>
            <?php else: ?>
                <div style="color: #777; font-style: italic;">No upcoming appointment</div>
            <?php endif; ?>
        </div>
        <div class="button-group">
            <button id="scheduleBtn" class="btn">Schedule Patient</button>
            <button id="deleteScheduleBtn" class="btn btn-danger" style="background-color: #ffffff; color: #dc3545; border: 1px solid #dc3545;">Delete My Schedule</button>
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
        // Only fetch what we need
        $sql = "SELECT id, name, type, description FROM medicines";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $desc = htmlspecialchars($row["description"]);
                $name = htmlspecialchars($row["name"]);
                $type = htmlspecialchars($row["type"]);

                // 1. MAIN ROW (Clickable on Mobile)
                echo "<tr class='med-row' onclick='toggleMedDescription(this)'>";
                echo "  <td>
                            <span class='med-name'>$name</span>
                            <i class='fas fa-chevron-down mobile-arrow'></i>
                        </td>";
                echo "  <td>$type</td>";
                // 3rd Column: Visible on Desktop, Hidden on Mobile
                echo "  <td class='desktop-desc'>$desc</td>";
                echo "</tr>";

                // 2. HIDDEN DESCRIPTION ROW (Mobile Only)
                echo "<tr class='mobile-desc-row' style='display:none;'>";
                echo "  <td colspan='2'>
                            <div class='desc-content'>
                                <strong>Description:</strong><br>
                                $desc
                            </div>
                        </td>";
                echo "</tr>";
            }
        } else {
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

    <div id="validationModal" class="modal-container" style="display: none;">
    <div class="modal-content" style="max-width: 400px; text-align: center; border-top: 5px solid #dc3545;">
        <span class="close-btn" onclick="closeValidationModal()">&times;</span>
        
        <div style="margin: 20px 0;">
            <i class="fas fa-exclamation-circle" style="font-size: 50px; color: #dc3545;"></i>
        </div>

        <h3 style="color: #dc3545; margin-bottom: 10px;">Missing Information</h3>
        <p id="validationMessage" style="color: #555; font-size: 1.1rem; margin-bottom: 25px;"></p>
        
        <button class="btn" onclick="closeValidationModal()" style="background-color: #dc3545; color: white; width: 100%;">
            Okay, I'll fix it
        </button>
    </div>
</div>

<script>
    // Define these BEFORE calling user.js
var userId = "<?php echo $_SESSION['user_id'] ?? ''; ?>";
    var userGender = "<?php echo isset($user['gender']) ? $user['gender'] : ''; ?>";
    
    var currentUserName = "<?php echo isset($user) ? $user['firstName'] . ' ' . $user['surname'] : 'Guest'; ?>";
    
    var userStatus = "<?php echo isset($user['status']) ? $user['status'] : 'pending'; ?>";

    // Troubleshooting: 
    console.log("Logged in as:", currentUserName);
    console.log("Gender:", userGender);
</script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="user.js"></script>
</body>
</html>