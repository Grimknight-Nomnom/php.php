<?php
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinicphp";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the raw POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Get the user ID from POST data
    $patientId = isset($data['userId']) ? $data['userId'] : null;
    
    if (!$patientId) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }

    // Prepare the update query with all fields that can be updated
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
            seniorCitizen = :seniorCitizen,
            profile_edited = 1
            WHERE id = :id";

    $stmt = $conn->prepare($sql);

    // Bind all parameters - make sure these match exactly with the fields in your SQL query
    $stmt->bindParam(':surname', $data['surname']);
    $stmt->bindParam(':firstName', $data['firstName']);
    $stmt->bindParam(':middleInitial', $data['middleInitial']);
    $stmt->bindParam(':birthday', $data['birthday']);
    $stmt->bindParam(':age', $data['age']);
    $stmt->bindParam(':gender', $data['gender']);
    $stmt->bindParam(':address', $data['address']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':contactNumber', $data['contactNumber']);
    $stmt->bindParam(':civilStatus', $data['civilStatus']);
    $stmt->bindParam(':occupation', $data['occupation']);
    $stmt->bindParam(':existingMedical', $data['existingMedical']);
    $stmt->bindParam(':currentMedication', $data['currentMedication']);
    $stmt->bindParam(':allergies', $data['allergies']);
    $stmt->bindParam(':familyMedical', $data['familyMedical']);
    $stmt->bindParam(':bloodPressure', $data['bloodPressure']);
    $stmt->bindParam(':heartRate', $data['heartRate']);
    $stmt->bindParam(':temperature', $data['temperature']);
    $stmt->bindParam(':height', $data['height']);
    $stmt->bindParam(':weight', $data['weight']);
    $stmt->bindParam(':description', $data['description']);
    $stmt->bindParam(':checkup', $data['checkup']);
    $stmt->bindParam(':guardian', $data['guardian']);
    $stmt->bindParam(':relationship', $data['relationship']);
    $stmt->bindParam(':guardianContact', $data['guardianContact']);
    $stmt->bindParam(':emergency_contact_number', $data['emergency_contact_number']);
    $stmt->bindParam(':philhealth', $data['philhealth'], PDO::PARAM_INT);
    $stmt->bindParam(':seniorCitizen', $data['seniorCitizen'], PDO::PARAM_INT);
    $stmt->bindParam(':id', $patientId, PDO::PARAM_INT);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Patient updated successfully']);
    } else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode(['success' => false, 'message' => 'Failed to update patient: ' . $errorInfo[2]]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}