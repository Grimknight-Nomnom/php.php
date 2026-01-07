<?php
session_start();

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
header('Location: ClinicPhp.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" />
    <link rel="stylesheet" href="styleadmin.css?v=<?php echo time(); ?>" />
</head>
<body>
    <div class="dashboard">
        <div class="sidebar">
            <div class="user-info">
                <a href="#" class="logo">
                    <i class="fas fa-heartbeat"></i> <strong>Med</strong>vault
                </a>
                <div class="user-icon">👤</div>
                <div class="user-name">Admin</div>
                <a href="../logout.php" class="btn">Logout</a>
            </div>
            <div class="menu">
                <button onclick="showContent('registration')">LIST OF SCHEDULES</button>
                <button onclick="showContent('listOfPatients')">LIST OF PATIENT</button>
                <button onclick="showContent('approveAccounts')">APPROVE ACCOUNT</button> 
                <button onclick="showContent('stockOfMedicines')">STOCK OF MEDICINE</button>
                <button onclick="showContent('monthlyReport')">MONTHLY REPORT</button>
            </div>
        </div>
        <div class="main-content">
            <div id="content">
                <!-- Default Content -->
                <div id="defaultContent" class="content-section">
                    <h2>Welcome to the Dashboard</h2>
                    <p>Please select an option from the sidebar.</p>
                </div>

                <!-- Registration Section -->
                <div id="registration" class="content-section" style="display: none;">
                    <div class="registration-header">
                        <h2>List of Schedules</h2>
                    </div>
                    <div id="calendar"></div>
                </div>

<!-- In admin-dashboard.php, update the listOfPatients section -->
<div id="listOfPatients" class="content-section" style="display: none;">
    <h2>List of Patients</h2>
<div class="search-bar">
    <input type="text" id="searchPatients" placeholder="Search for a patient...">
    </div>
    <table id="patientsTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Surname</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th>Contact Number</th>
                <th>Age</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "clinicphp";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

           // Added: WHERE status = 'approved'
            $sql = "SELECT id, surname, firstName, middleInitial, contactNumber, email, age, address, description, checkup FROM users WHERE status = 'approved'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
echo "<tr>
    <td>" . $row["id"] . "</td>
    <td>" . $row["surname"] . "</td>
    <td>" . $row["firstName"] . "</td>
    <td>" . $row["middleInitial"] . "</td>
    <td>" . $row["contactNumber"] . "</td>
    <td>" . ($row["age"] ?? '') . "</td>
    <td>
        <button class='edit-btn' onclick='showPatientEditModal(" . $row["id"] . ")'>
            Edit
        </button>
        
        <button class='logs-btn' onclick='openMedicalLogs(" . $row["id"] . ", \"" . addslashes($row["surname"]) . "\")'>
            <i class='fas fa-file-medical'></i> Logs
        </button>
    </td>
</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No patients found</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</div>
<!-- Patient Edit Modal -->
<div id="patientEditModal" class="modal">
    <div class="modal-content" style="width: 80%; max-width: 900px; max-height: 90vh; overflow-y: auto;">
        <span class="close" onclick="closePatientEditModal()">&times;</span>
        <h2>Edit Patient Information</h2>
        <form id="patientEditForm" onsubmit="updatePatient(event)">
            <input type="hidden" id="editPatientId">
            
            <div class="form-section">
                <h3>Personal Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editSurname">Surname:</label>
                        <input type="text" id="editSurname" required>
                    </div>
                    <div class="form-group">
                        <label for="editFirstName">First Name:</label>
                        <input type="text" id="editFirstName" required>
                    </div>
                    <div class="form-group">
                        <label for="editMiddleName">Middle Name:</label>
                        <input type="text" id="editMiddleName">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editBirthday">Date of Birth:</label>
                        <input type="date" id="editBirthday" required>
                    </div>
                    <div class="form-group">
                        <label for="editAge">Age:</label>
                        <input type="number" id="editAge" required>
                    </div>
                    <div class="form-group">
                        <label for="editGender">Gender:</label>
<div class="form-group">
    <label for="editGender">Gender:</label>
    <select id="editGender" required>
        <option value="">Select</option>
        <option value="male">Male</option>
        <option value="female">Female</option>
        <option value="rather not say">Rather not say</option>
    </select>
</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editAddress">Address:</label>
                        <textarea id="editAddress" required></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editEmail">Email:</label>
                        <input type="email" id="editEmail">
                    </div>
                    <div class="form-group">
                        <label for="editContactNumber">Contact Number:</label>
                        <div class="phone-input">
                            <span>+63</span>
                            <input type="tel" id="editContactNumber" pattern="[0-9]{10}" maxlength="10" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editCivilStatus">Civil Status:</label>
                        <select id="editCivilStatus" required>
                            <option value="">Select</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editOccupation">Occupation:</label>
                        <input type="text" id="editOccupation">
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Medical History</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editExistingMedical">Existing Medical Conditions:</label>
                        <textarea id="editExistingMedical" placeholder="e.g., diabetes, hypertension, asthma"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editCurrentMedication">Current Medication:</label>
                        <textarea id="editCurrentMedication"></textarea>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editAllergies">Allergies:</label>
                        <textarea id="editAllergies"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editFamilyMedical">Family Medical History:</label>
                        <textarea id="editFamilyMedical"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Vital Signs & Physical Examination Findings</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editBloodPressure">Blood Pressure:</label>
                        <input type="text" id="editBloodPressure" placeholder="e.g., 120/80">
                    </div>
                    <div class="form-group">
                        <label for="editHeartRate">Heart Rate:</label>
                        <input type="number" id="editHeartRate" placeholder="bpm">
                    </div>
                    <div class="form-group">
                        <label for="editTemperature">Temperature:</label>
                        <input type="number" id="editTemperature" placeholder="°C">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editHeight">Height:</label>
                        <input type="number" id="editHeight" placeholder="cm">
                    </div>
                    <div class="form-group">
                        <label for="editWeight">Weight:</label>
                        <input type="number" id="editWeight" placeholder="kg">
                    </div>
                    <div class="form-group">
                        <label for="editBMI">BMI:</label>
                        <input type="number" id="editBMI" readonly>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Consultation Details</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editDescription">Description:</label>
                        <textarea id="editDescription"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editCheckup">Checkup:</label>
                        <select id="editCheckup">
                            <option value="">Select Doctor</option>
                            <option value="Dr. Adelinno Labro">Dr. Adelinno Labro</option>
                            <option value="John Paul Dela Cruz">John Paul Dela Cruz</option>
                            <option value="Krystal Mae Anarna">Krystal Mae Anarna</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Emergency Contact Information</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editGuardian">Name of Guardian/Family Member:</label>
                        <input type="text" id="editGuardian">
                    </div>
                    <div class="form-group">
                        <label for="editRelationship">Relationship to Patient:</label>
                        <input type="text" id="editRelationship">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editGuardianContact">Contact Number:</label>
                        <div class="phone-input">
                            <span>+63</span>
                            <input type="tel" id="editGuardianContact" pattern="[0-9]{10}" maxlength="10">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editEmergencyContactNumber">Emergency Contact Number:</label>
                        <div class="phone-input">
                            <span>+63</span>
                            <input type="tel" id="editEmergencyContactNumber" pattern="[0-9]{10}" maxlength="10">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <h3>Health Program</h3>
                <div class="form-row">
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="editPhilhealth"> Philhealth Member
                        </label>
                    </div>
                    <div class="form-group checkbox-group">
                        <label>
                            <input type="checkbox" id="editSeniorCitizen"> Senior Citizen ID Holder
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Update Patient</button>
                <button type="button" class="btn cancel" onclick="closePatientEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="approveAccounts" class="content-section" style="display: none;">
    <h2>Approve Accounts</h2>
    <div class="search-bar">
        <input type="text" id="searchPending" placeholder="Search pending accounts..." onkeyup="filterPendingAccounts()">
    </div>
    
    <table id="approveTable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Surname</th>
                <th>First Name</th>
                <th>Middle Name</th>
                <th style="text-align:center;">Edit Information</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
    <?php
    $connPending = new mysqli("localhost", "root", "", "clinicphp");
    if (!$connPending->connect_error) {
        // Fetch ONLY users with 'pending' status
        $sqlPending = "SELECT id, surname, firstName, middleInitial, profile_edited FROM users WHERE status = 'pending'";
        $resultPending = $connPending->query($sqlPending);

        if ($resultPending->num_rows > 0) {
            while ($row = $resultPending->fetch_assoc()) {
                // 1. Logic: If profile_edited is 1, show Green. If 0, show Red.
                $isEdited = ($row['profile_edited'] == 1);
                $badgeClass = $isEdited ? 'status-edited' : 'status-not-edited';
                $badgeText = $isEdited ? 'Edited' : 'Not Edited';
                
                // 2. Prepare status for JavaScript (1 or 0)
                $editStatus = $isEdited ? 1 : 0;

                echo "<tr id='pending-row-" . $row["id"] . "'>
                        <td>" . $row["id"] . "</td>
                        <td>" . htmlspecialchars($row["surname"]) . "</td>
                        <td>" . htmlspecialchars($row["firstName"]) . "</td>
                        <td>" . htmlspecialchars($row["middleInitial"]) . "</td>
                        <td style='text-align: center;'>
                            <span class='status-badge $badgeClass'>$badgeText</span>
                        </td>
                        <td>
                            <button class='approve-btn' onclick='checkApproval(" . $row["id"] . ", " . $editStatus . ")'>
                                <i class='fas fa-check'></i> APPROVED
                            </button>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center; padding: 20px; color: #666;'>No pending accounts found</td></tr>";
        }
        $connPending->close();
    }
    ?>
</tbody>
    </table>
</div>

<div id="medicineModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('medicineModal').style.display='none'">&times;</span>
        <h2>Add Medicine</h2>
        <form id="addMedicineForm" onsubmit="addMedicine(event)">
            <div class="form-group">
                <label for="medicineName">Medicine Name:</label>
                <input type="text" id="medicineName" required>
            </div>
            <div class="form-group">
                <label for="medicineType">Type:</label>
                <select id="medicineType" required>
                    <option value="">Select Type</option>
                    <option value="DROPS">[DROPS]</option>
                    <option value="SYRUP">[SYRUP]</option>
                    <option value="CAPSULE">[CAPSULE]</option>
                    <option value="PILLS">[PILLS]</option>
                    <option value="TABLET">[TABLET]</option>
                </select>
            </div>
            <div class="form-group">
                <label for="medicineDescription">Description:</label>
                <textarea id="medicineDescription"></textarea>
            </div>
            <div class="form-group">
                <label for="medicineQuantity">Quantity:</label>
                <input type="number" id="medicineQuantity" required min="1">
            </div>
            <div class="form-group">
                <label>Expiration Date:</label>
                <div class="month-year-picker">
                    <select id="expirationMonth" required>
                        <option value="">Month</option>
                        <option value="01">Jan</option><option value="02">Feb</option><option value="03">Mar</option>
                        <option value="04">Apr</option><option value="05">May</option><option value="06">Jun</option>
                        <option value="07">Jul</option><option value="08">Aug</option><option value="09">Sep</option>
                        <option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option>
                    </select>
                    <select id="expirationYear" required>
                        <option value="">Year</option>
                        <?php 
                        $currentYear = date('Y');
                        for ($year = $currentYear; $year <= $currentYear + 10; $year++) {
                            echo "<option value='$year'>$year</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Add Medicine</button>
                <button type="button" class="btn cancel" onclick="document.getElementById('medicineModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="editMedicineModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editMedicineModal').style.display='none'">&times;</span>
        <h2>Edit Medicine</h2>
        <form id="editMedicineForm" onsubmit="submitEditMedicine(event)">
            <input type="hidden" id="editMedId"> 
            
            <div class="form-group">
                <label for="editMedName">Medicine Name:</label>
                <input type="text" id="editMedName" required>
            </div>
            <div class="form-group">
                <label for="editMedType">Type:</label>
                <select id="editMedType" required>
                    <option value="">Select Type</option>
                    <option value="DROPS">[DROPS]</option>
                    <option value="SYRUP">[SYRUP]</option>
                    <option value="CAPSULE">[CAPSULE]</option>
                    <option value="PILLS">[PILLS]</option>
                    <option value="TABLET">[TABLET]</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editMedDesc">Description:</label>
                <textarea id="editMedDesc"></textarea>
            </div>
            <div class="form-group">
                <label for="editMedQty">Quantity:</label>
                <input type="number" id="editMedQty" required min="0">
            </div>
            <div class="form-group">
                <label>Expiration Date:</label>
                <div class="month-year-picker">
                    <select id="editMedMonth" required>
                        <option value="">Month</option>
                        <option value="01">Jan</option><option value="02">Feb</option><option value="03">Mar</option>
                        <option value="04">Apr</option><option value="05">May</option><option value="06">Jun</option>
                        <option value="07">Jul</option><option value="08">Aug</option><option value="09">Sep</option>
                        <option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option>
                    </select>
                    <select id="editMedYear" required>
                        <option value="">Year</option>
                        <?php 
                        $currentYear = date('Y');
                        for ($year = $currentYear; $year <= $currentYear + 10; $year++) {
                            echo "<option value='$year'>$year</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn">Update Medicine</button>
                <button type="button" class="btn cancel" onclick="document.getElementById('editMedicineModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="releaseMedicineModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('releaseMedicineModal').style.display='none'">&times;</span>
        <h2>Release Medicine</h2>
        <form id="releaseMedicineForm" onsubmit="submitReleaseMedicine(event)">
            <input type="hidden" id="releaseMedId">
            
            <div class="form-group">
                <label for="releaseMedName">Medicine Name:</label>
                <select id="releaseMedName" disabled style="background-color: #f0f0f0;">
                    <option value="">Select Medicine</option>
                </select>
            </div>

            <div class="form-group">
                <label for="releaseMedQty">Quantity to Release:</label>
                <select id="releaseMedQty" required>
                    <option value="">Select Quantity</option>
                </select>
            </div>

            <div class="form-group">
                <label for="releasePatientName">Patient Name:</label>
                <div class="custom-dropdown-container">
                    <input type="text" id="releasePatientName" 
                           placeholder="Type to search..." 
                           autocomplete="off" 
                           onkeyup="filterPatientDropdown()" 
                           onfocus="showPatientDropdown()">
                    
                    <div id="patientDropdownList" class="custom-dropdown-list">
                        <?php
                        $connTemp = new mysqli("localhost", "root", "", "clinicphp");
                        if (!$connTemp->connect_error) {
                            $patSql = "SELECT surname, firstName, gender, age FROM users ORDER BY surname ASC";
                            $patResult = $connTemp->query($patSql);
                            
                            if ($patResult->num_rows > 0) {
                                while($pat = $patResult->fetch_assoc()) {
                                    $fullName = htmlspecialchars($pat['surname'] . ", " . $pat['firstName']);
                                    $gender = ucfirst($pat['gender'] ?? 'N/A');
                                    $age = $pat['age'] ?? 'N/A';
                                    
                                    echo "<div class='dropdown-item' onclick='selectPatient(\"$fullName\")'>
                                            <span class='item-name'>$fullName</span>
                                            <span class='item-details'>Gender: $gender | Age: $age</span>
                                          </div>";
                                }
                            }
                            $connTemp->close();
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn">Confirm Release</button>
                <button type="button" class="btn cancel" onclick="document.getElementById('releaseMedicineModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div id="historyModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <span class="close" onclick="document.getElementById('historyModal').style.display='none'">&times;</span>
        <h2>Medicine Transaction History</h2>
        <div style="overflow-x: auto; max-height: 60vh;">
            <table id="historyTable" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #f2f2f2; text-align: left;">
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Action</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Medicine Name</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Qty</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Details</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Date</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Time</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div id="stockOfMedicines" class="content-section" style="display: none;">
    <h2>Stock of Medicines</h2>
    
    <div class="search-bar">
        <input type="text" id="searchMedicines" placeholder="Search Medicines..." onkeyup="filterMedicines()">
        
        <button class="history-btn" onclick="openHistoryModal()">
            <i class="fas fa-history"></i> Medicine History
        </button>

        <div class="notification-container">
            <div id="medicineNotificationIcon" class="notification-icon">
                <i class="fas fa-bell"></i>
                <span id="notificationBadge" class="notification-badge">0</span>
            </div>
            <div id="notificationDropdown" class="notification-dropdown">
                <div id="notificationList">
                    </div>
            </div>
        </div>
        
        <button id="addMedicineButton" class="admin-btn">Add Medicine</button>
    </div>
    
    <table id="medicineTable">
        <thead>
            <tr>
                <th>Medicine Name</th>
                <th>Type</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Expiration Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        // Database Connection
        $conn = new mysqli("localhost", "root", "", "clinicphp");
        
        if (!$conn->connect_error) {
            $sql = "SELECT id, name, type, description, quantity, expiration_date FROM medicines";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Date Formatting
                    if ($row["expiration_date"]) {
                        $expirationDate = new DateTime($row["expiration_date"]);
                        $formattedExpirationDate = $expirationDate->format('F Y');
                        $rawMonth = $expirationDate->format('m');
                        $rawYear = $expirationDate->format('Y');
                    } else {
                        $formattedExpirationDate = "N/A";
                        $rawMonth = "";
                        $rawYear = "";
                    }

                    echo "<tr>
                            <td>" . htmlspecialchars($row["name"]) . "</td>
                            <td>" . htmlspecialchars($row["type"]) . "</td>
                            <td>" . htmlspecialchars($row["description"]) . "</td>
                            <td id='quantity_" . $row["id"] . "'>" . $row["quantity"] . "</td>
                            <td>" . $formattedExpirationDate . "</td>
                            
                            <td style='white-space: nowrap;'> 
                                <button class='action-btn' 
                                    data-id='" . $row["id"] . "'
                                    data-name='" . htmlspecialchars($row["name"]) . "'
                                    data-type='" . htmlspecialchars($row["type"]) . "'
                                    data-desc='" . htmlspecialchars($row["description"]) . "'
                                    data-qty='" . $row["quantity"] . "'
                                    data-month='" . $rawMonth . "'
                                    data-year='" . $rawYear . "'
                                    onclick='openEditMedicineModal(this)'>
                                    Edit
                                </button>

                                <button class='action-btn' 
                                    data-id='" . $row["id"] . "'
                                    data-name='" . htmlspecialchars($row["name"]) . "'
                                    data-qty='" . $row["quantity"] . "'
                                    onclick='openReleaseMedicineModal(this)'>
                                    Release
                                </button>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No medicines found</td></tr>";
            }
            $conn->close();
        }
        ?>
        </tbody>
    </table>
</div> 
<div id="monthlyReport" class="content-section" style="display: none;">
    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 id="reportTitle">Monthly Report</h2>
        
        <div style="display: flex; gap: 10px;">
            <select id="reportFilterMonth" onchange="fetchReportData()" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                <option value="01">January</option>
                <option value="02">February</option>
                <option value="03">March</option>
                <option value="04">April</option>
                <option value="05">May</option>
                <option value="06">June</option>
                <option value="07">July</option>
                <option value="08">August</option>
                <option value="09">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <select id="reportFilterYear" onchange="fetchReportData()" style="padding: 5px; border-radius: 4px; border: 1px solid #ddd;">
                <?php 
                $cy = date('Y');
                // Show 2 years back and 5 years forward
                for ($y = $cy - 2; $y <= $cy + 5; $y++) { 
                    $selected = ($y == $cy) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>"; 
                }
                ?>
            </select>
        </div>
    </div>

    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #ddd;">

    <div class="lists-container">
        <div class="list-box">
            <h3 style="color: #e67e22;"><i class="fas fa-exclamation-circle"></i> Low on Stocks</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Type</th>
                        <th>Qty Remaining</th>
                    </tr>
                </thead>
                <tbody id="lowStockBody">
                    </tbody>
            </table>
        </div>

        <div class="list-box">
            <h3 style="color: #c0392b;"><i class="fas fa-skull-crossbones"></i> Expired / Near Expiry</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="expiryBody">
                    </tbody>
            </table>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="realAdmin.js"></script>
</body>
</html>