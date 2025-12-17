<?php
session_start();
header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin']) || $_SESSION['admin'] != 1) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get patient ID
    $patientId = $_POST['id'] ?? null;
    if (!$patientId) {
        die(json_encode(['success' => false, 'message' => 'Patient ID is required']));
    }

    // Prepare the update query
    $sql = "UPDATE users SET 
            surname = :surname,
            firstName = :firstName,
            middleInitial = :middleInitial,
            birthday = :birthday,
            age = :age,
            gender = :gender,
            address = :address,
            email = :email,
            contactNumber = :contactNumber,
            civilStatus = :civilStatus,
            occupation = :occupation,
            existingMedical = :existingMedical,
            currentMedication = :currentMedication,
            allergies = :allergies,
            familyMedical = :familyMedical,
            bloodPressure = :bloodPressure,
            heartRate = :heartRate,
            temperature = :temperature,
            height = :height,
            weight = :weight,
            description = :description,
            checkup = :checkup,
            guardian = :guardian,
            relationship = :relationship,
            guardianContact = :guardianContact,
            emergency_contact_number = :emergency_contact_number,
            philhealth = :philhealth,
            seniorCitizen = :seniorCitizen
            WHERE id = :id";

    $stmt = $conn->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':surname', $_POST['surname']);
    $stmt->bindParam(':firstName', $_POST['firstName']);
    $stmt->bindParam(':middleInitial', $_POST['middleInitial']);
    $stmt->bindParam(':birthday', $_POST['birthday']);
    $stmt->bindParam(':age', $_POST['age']);
    $stmt->bindParam(':gender', $_POST['gender']);
    $stmt->bindParam(':address', $_POST['address']);
    $stmt->bindParam(':email', $_POST['email']);
    $stmt->bindParam(':contactNumber', $_POST['contactNumber']);
    $stmt->bindParam(':civilStatus', $_POST['civilStatus']);
    $stmt->bindParam(':occupation', $_POST['occupation']);
    $stmt->bindParam(':existingMedical', $_POST['existingMedical']);
    $stmt->bindParam(':currentMedication', $_POST['currentMedication']);
    $stmt->bindParam(':allergies', $_POST['allergies']);
    $stmt->bindParam(':familyMedical', $_POST['familyMedical']);
    $stmt->bindParam(':bloodPressure', $_POST['bloodPressure']);
    $stmt->bindParam(':heartRate', $_POST['heartRate']);
    $stmt->bindParam(':temperature', $_POST['temperature']);
    $stmt->bindParam(':height', $_POST['height']);
    $stmt->bindParam(':weight', $_POST['weight']);
    $stmt->bindParam(':description', $_POST['description']);
    $stmt->bindParam(':checkup', $_POST['checkup']);
    $stmt->bindParam(':guardian', $_POST['guardian']);
    $stmt->bindParam(':relationship', $_POST['relationship']);
    $stmt->bindParam(':guardianContact', $_POST['guardianContact']);
    $stmt->bindParam(':emergency_contact_number', $_POST['emergency_contact_number']);
    $stmt->bindParam(':philhealth', $_POST['philhealth'], PDO::PARAM_INT);
    $stmt->bindParam(':seniorCitizen', $_POST['seniorCitizen'], PDO::PARAM_INT);
    $stmt->bindParam(':id', $patientId, PDO::PARAM_INT);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Patient updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update patient']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>