<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

require 'db_connection.php';

try {
    // Insert applications table
    $stmt = $pdo->prepare("INSERT INTO applications (pnr, name_of_applicant, total_applicants, status, timestamp) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['pnr'],
        $data['nameOfApplicant'],
        $data['totalApplicants'],
        $data['status'],
        date('Y-m-d H:i:s', strtotime($data['submittedAt']))
    ]);

    // Insert applicants with new structured columns
    foreach ($data['applicants'] as $applicant) {
        // Map frontend data to database columns
        $personal_information = json_encode([
            'passportInfo' => $applicant['passportInfo'] ?? [],
            'contactInfo' => $applicant['contactInfo'] ?? []
        ]);
        
        $travel_information = json_encode($applicant['travelInfo'] ?? []);
        $passport_information = json_encode($applicant['passportInfo'] ?? []);
        $travel_companion_information = json_encode($applicant['travelInfo'] ?? []); // TCI is part of travelInfo
        $previous_us_travel = json_encode($applicant['travelHistory'] ?? []);
        $us_contact_information = json_encode($applicant['usContactInfo'] ?? []);
        $family_member_information = json_encode($applicant['familyInfo'] ?? []);
        $work_information = json_encode($applicant['employmentInfo'] ?? []);
        $educational_information = json_encode($applicant['educationalInfo'] ?? []);
        $other_information = json_encode($applicant['otherInfo'] ?? []);

        // Prepare insert with all sections
        $stmt = $pdo->prepare("
            INSERT INTO applicants (
                pnr,
                user_pnr,
                completed,
                personal_information,
                travel_information,
                passport_information,
                travel_companion_information,
                previous_us_travel,
                us_contact_information,
                family_member_information,
                work_information,
                educational_information,
                other_information
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Execute insert
        $stmt->execute([
            $applicant['pnr'] ?? null,
            $applicant['user_pnr'] ?? null,
            ($applicant['completed'] ?? false) ? 1 : 0,
            $personal_information,
            $travel_information,
            $passport_information,
            $travel_companion_information,
            $previous_us_travel,
            $us_contact_information,
            $family_member_information,
            $work_information,
            $educational_information,
            $other_information
        ]);
    }

    echo json_encode(["status" => "success", "message" => "Data saved successfully"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}