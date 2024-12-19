<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "oro_va_dental_records";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = (int) $_POST['appointment_id'];
    $appointmentDate = $_POST['appointment_date'] ?? '';
    $appointmentStartTime = $_POST['appointmentStartTime'] ?? '';
    $appointmentEndTime = $_POST['appointmentEndTime'] ?? '';
    $addInfo = $_POST['addInfo'] ?? '';

    // Handle services
    $services = isset($_POST['services']) ? implode(", ", $_POST['services']) : '';

    // Handle partial denture data
    $partialDenture = [];
    $partialDentureCount = [];
    if (isset($_POST['partial_denture'])) {
        foreach ($_POST['partial_denture'] as $key => $value) {
            if (isset($_POST["partial_denture"]["$key" . "_pontic_count"])) {
                $count = $_POST["partial_denture"]["$key" . "_pontic_count"];
                $partialDenture[] = $value;
                $partialDentureCount[] = $count;
            }
        }
    }
    $partialDentureStr = implode(", ", $partialDenture);
    $partialDentureCountStr = implode(", ", $partialDentureCount);

    // Handle full denture data
    $fullDenture = [];
    $fullDentureRanges = [];
    if (isset($_POST['full_denture'])) {
        foreach ($_POST['full_denture'] as $key => $value) {
            if (isset($_POST["full_denture"]["$key" . "_range"])) {
                $range = $_POST["full_denture"]["$key" . "_range"];
                $fullDenture[] = $value;
                $fullDentureRanges[] = $range;
            }
        }
    }
    $fullDentureStr = implode(", ", $fullDenture);
    $fullDentureRangesStr = implode(", ", $fullDentureRanges);

    /// Step 1: Check if the appointment date already exists
    $sqlCheckDate = "SELECT * FROM appointments WHERE appointment_date = ?";
    $stmtCheckDate = $conn->prepare($sqlCheckDate);
    $stmtCheckDate->bind_param("s", $appointmentDate);
    $stmtCheckDate->execute();
    $result = $stmtCheckDate->get_result();

    // Step 2: Check for time conflicts if the appointment date exists
    $timeConflict = false;
    if ($result->num_rows > 0) {
        // Check if the appointment time overlaps with an existing appointment on the same date
        while ($row = $result->fetch_assoc()) {
            $existingStartTime = $row['appointment_start_time'];
            $existingEndTime = $row['appointment_end_time'];

            // Allow consecutive appointments (one ends exactly when the next one starts)
            if (($appointmentStartTime >= $existingStartTime && $appointmentStartTime < $existingEndTime) || 
                ($appointmentEndTime > $existingStartTime && $appointmentEndTime <= $existingEndTime) || 
                ($appointmentStartTime == $existingEndTime)) {
                // Conflict found
                $timeConflict = true;
                break; // Exit loop once conflict is detected
            }
        }
    }

    // If conflict is found, show error and stop further processing
    if ($timeConflict) {
        echo "<script>alert('This appointment time overlaps with an existing appointment. Please choose another time.'); window.location.href = 'check_appointment.php';</script>";
        exit();
    }


    // Update query
    $update_sql = "
    UPDATE appointments 
    SET appointment_date = ?, 
        appointment_start_time = ?, 
        appointment_end_time = ?, 
        services = ?,
        partial_denture_service = ?,
        partial_denture_count = ?,
        full_denture_service = ?,
        full_denture_range = ?,
        add_info = ?
    WHERE id = ?";

    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param(
        "sssssssssi",
        $appointmentDate,
        $appointmentStartTime,
        $appointmentEndTime,
        $services,
        $partialDentureStr,
        $partialDentureCountStr,
        $fullDentureStr,
        $fullDentureRangesStr,
        $addInfo,
        $appointment_id
    );

    if ($stmt_update->execute()) {
        echo"<script>
                alert('Appointment updated successfully.');
                window.location.href='check_appointment.php';
              </script>";
    } else {
        echo "Error: " . $stmt_update->error;
    }
    
    $stmt_update->close();
}

$conn->close();
?>
