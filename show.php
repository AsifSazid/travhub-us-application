<?php
require 'server/db_connection.php'; // your PDO connection

$pnr = $_GET['pnr'] ?? null;
$applicationData = null;

if ($pnr) {
    // 1. Fetch application info
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE pnr = ?");
    $stmt->execute([$pnr]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($application) {
        // 2. Fetch all applicants
        $stmt2 = $pdo->prepare("SELECT * FROM applicants WHERE pnr = ?");
        $stmt2->execute([$pnr]);
        $appRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $applicants = [];
        foreach ($appRows as $ap) {
            $applicants[] = [
                "id" => $ap['user_pnr'],
                "pnr" => $ap['pnr'],
                "user_pnr" => $ap['user_pnr'],
                "completed" => (bool)$ap['completed'],
                "passportInfo" => json_decode($ap['passport_info'], true) ?? [],
                "contactInfo" => json_decode($ap['contact_info'], true) ?? [],
                "familyInfo" => json_decode($ap['family_info'], true) ?? [],
                "usContactInfo" => json_decode($ap['us_contact_info'], true) ?? [],
                "employmentInfo" => json_decode($ap['employment_info'], true) ?? [],
                "educationalInfo" => json_decode($ap['educational_info'], true) ?? [],
                "travelInfo" => json_decode($ap['travel_info'], true) ?? [],
                "travelHistory" => json_decode($ap['travel_history'], true) ?? [],
                "otherInfo" => json_decode($ap['other_info'], true) ?? []
            ];
        }

        // 3. Combine into JS-friendly structure
        $applicationData = [
            'pnr' => $application['pnr'],
            'nameOfApplicant' => $applicants[0]['passportInfo']['pi_sur_name'] ?? '',
            'totalApplicants' => count($applicants),
            'timestamp' => $application['created_at'],
            'applicants' => $applicants
        ];
    }
}

// Pass $applicationData to JS
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USA DS-160 Visa Application Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .copy-field {
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            margin: 0.125rem 0;
            border: 1px solid #e5e7eb;
            background-color: white;
            font-family: inherit;
        }

        .copy-field:hover {
            background-color: #f3f4f6;
            border-color: #3b82f6;
        }

        .copy-field.copied {
            background-color: #d1fae5;
            color: #065f46;
            border-color: #10b981;
        }

        .applicant-section {
            border-left: 4px solid #3b82f6;
            background-color: #f8fafc;
            transition: all 0.3s ease;
        }

        .applicant-section.highlighted {
            border-left-color: #10b981;
            background-color: #f0fdf4;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
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

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background-color: #10b981;
            color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .step-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .step-item {
            text-align: center;
            flex: 1;
            position: relative;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .step-item:hover {
            transform: translateY(-2px);
        }

        .step-item:not(:last-child):after {
            content: '';
            position: absolute;
            top: 20px;
            right: -50%;
            width: 100%;
            height: 2px;
            background-color: #e5e7eb;
            z-index: 1;
        }

        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            position: relative;
            z-index: 2;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .step-icon.active {
            background-color: #3b82f6;
            color: white;
        }

        .step-icon.highlighted {
            background-color: #10b981;
            color: white;
            transform: scale(1.1);
        }

        .step-label {
            font-size: 0.75rem;
            color: #6b7280;
            transition: all 0.3s ease;
        }

        .step-label.active {
            color: #3b82f6;
            font-weight: 600;
        }

        .step-label.highlighted {
            color: #10b981;
            font-weight: 700;
        }

        .applicant-tab {
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .applicant-tab.active {
            background-color: #3b82f6;
            color: white;
            border-bottom-color: #1d4ed8;
        }

        .applicant-tab:hover:not(.active) {
            background-color: #f3f4f6;
        }

        .multi-entry-group {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f9fafb;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <header class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-file-alt text-white text-xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">USA DS-160 Visa Application Details</h1>
            </div>
            <p class="text-gray-600 max-w-2xl mx-auto">View and copy application information. Click on any value to copy it to clipboard.</p>
        </header>

        <!-- Application Details -->
        <div id="application-container" class="space-y-6">
            <!-- Application will be loaded here -->
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-center space-x-4">
            <button id="back-to-dashboard" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded-lg transition duration-300 flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </button>
            <button id="download-json" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300 flex items-center">
                <i class="fas fa-download mr-2"></i> Download JSON
            </button>
        </div>

        <!-- Toast Notification -->
        <div id="toast" class="toast hidden">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span id="toast-message">Copied to clipboard!</span>
            </div>
        </div>
    </div>

    <script>
        let applicationData = <?php
                                // If DB record exists, send JSON to JS
                                if ($applicationData) {
                                    echo json_encode($applicationData);
                                } else {
                                    echo 'null';
                                }
                                ?>;
        let currentApplicantIndex = 0;
        const stepSections = [
            'personal-info', 'travel-info', 'passport-info', 'travel-companion-info',
            'previous-travel', 'us-contact-info', 'family-info', 'work-info',
            'education-info', 'other-info'
        ];

        // Country data
        const countries = [
            { code: 'USA', name: 'United States' }, { code: 'UK', name: 'United Kingdom' },
            { code: 'BD', name: 'Bangladesh' }, { code: 'IN', name: 'India' },
            { code: 'CA', name: 'Canada' }, { code: 'AU', name: 'Australia' },
            { code: 'DE', name: 'Germany' }, { code: 'FR', name: 'France' }
        ];

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            if (!applicationData) {
                const savedApplication = localStorage.getItem('usaVisaApplication-<?php echo $pnr; ?>');
                if (savedApplication) {
                    applicationData = JSON.parse(savedApplication);
                }
            }

            if (!applicationData) {
                // Show error if nothing found
                document.getElementById('application-container').innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-red-800 mb-2">Application Not Found</h3>
                        <p class="text-red-600">No application found in DB or localStorage for PNR: <?php echo $pnr ?? 'N/A'; ?></p>
                        <button onclick="window.location.href='index.php'" class="mt-4 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                            Back to Dashboard
                        </button>
                    </div>
                `;
                return;
            }

            // Otherwise, render the application
            renderApplication();
            setupEventListeners();
        });

        // Set up event listeners
        function setupEventListeners() {
            document.getElementById('back-to-dashboard').addEventListener('click', function() {
                window.location.href = 'index.php';
            });

            document.getElementById('download-json').addEventListener('click', downloadJSON);
        }

        // Render the application
        function renderApplication() {
            const container = document.getElementById('application-container');

            let html = `
                <!-- Application Header -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="section-header px-6 py-4 text-white">
                        <div class="flex flex-col md:flex-row md:items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold">USA DS-160 Application Overview</h2>
                                <p class="text-blue-100">PNR: ${applicationData.pnr}</p>
                            </div>
                            <div class="mt-2 md:mt-0 text-sm">
                                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full">
                                    ${applicationData.totalApplicants} Applicant(s)
                                </span>
                                <span class="bg-white bg-opacity-20 px-3 py-1 rounded-full ml-2">
                                    ${new Date(applicationData.timestamp).toLocaleDateString()}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Applicant Tabs for multiple applicants
            if (applicationData.totalApplicants > 1) {
                html += `
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="border-b border-gray-200">
                            <div class="flex overflow-x-auto" id="applicant-tabs">
                                ${applicationData.applicants.map((applicant, index) => `
                                    <div class="applicant-tab py-3 px-6 text-sm font-medium flex items-center min-w-40 ${
                                        index === currentApplicantIndex ? 'active bg-blue-600 text-white' : 'text-gray-500 bg-white'
                                    }" data-applicant="${index}">
                                        <i class="fas fa-user mr-2 ${index === currentApplicantIndex ? 'text-white' : 'text-gray-400'}"></i>
                                        Applicant ${index + 1}
                                        ${applicant.completed ? 
                                            `<i class="fas fa-check-circle ml-2 ${index === currentApplicantIndex ? 'text-white' : 'text-green-500'}"></i>` : 
                                            ''
                                        }
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    </div>
                `;
            }

            // Render current applicant
            html += renderApplicant(applicationData.applicants[currentApplicantIndex], currentApplicantIndex);

            container.innerHTML = html;

            // Add event listeners for tabs
            if (applicationData.totalApplicants > 1) {
                document.querySelectorAll('.applicant-tab').forEach(tab => {
                    tab.addEventListener('click', function() {
                        const applicantIndex = parseInt(this.getAttribute('data-applicant'));
                        switchApplicant(applicantIndex);
                    });
                });
            }

            // Add copy functionality to all copy fields
            setupCopyFunctionality();

            // Add step click functionality
            setupStepNavigation();
        }

        // Switch between applicants
        function switchApplicant(applicantIndex) {
            currentApplicantIndex = applicantIndex;
            renderApplication();

            // Also update the step highlighting for the new applicant
            setTimeout(() => {
                setupStepNavigation();
                highlightStepSection(0); // Highlight first section by default
            }, 100);
        }

        // Render a single applicant
        function renderApplicant(applicant, index) {
            const pi = applicant.passportInfo || {};
            const ci = applicant.contactInfo || {};
            const ti = applicant.travelInfo || {};
            const pp = applicant.passportInfo || {};
            const tci = applicant.travelInfo || {};
            const pust = applicant.travelHistory || {};
            const usci = applicant.usContactInfo || {};
            const fm = applicant.familyInfo || {};
            const wi = applicant.employmentInfo || {};
            const edi = applicant.educationalInfo || {};
            const oi = applicant.otherInfo || {};

            return `
                <div class="bg-white rounded-xl shadow-lg overflow-hidden fade-in">
                    <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-user mr-2 text-blue-500"></i>
                            Applicant ${index + 1} - ${applicant.user_pnr ?? applicant.id}
                            ${applicant.completed ? 
                                '<span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Completed</span>' : 
                                '<span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">In Progress</span>'
                            }
                        </h3>
                    </div>
                    
                    <!-- Step Progress -->
                    <div class="step-progress">
                        <div class="step-item" data-step="0">
                            <div class="step-icon active">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="step-label active">Personal Info</div>
                        </div>
                        <div class="step-item" data-step="1">
                            <div class="step-icon active">
                                <i class="fas fa-plane"></i>
                            </div>
                            <div class="step-label active">Travel Info</div>
                        </div>
                        <div class="step-item" data-step="2">
                            <div class="step-icon active">
                                <i class="fas fa-passport"></i>
                            </div>
                            <div class="step-label active">Passport</div>
                        </div>
                        <div class="step-item" data-step="3">
                            <div class="step-icon active">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="step-label active">Travel Companion</div>
                        </div>
                        <div class="step-item" data-step="4">
                            <div class="step-icon active">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="step-label active">Previous Travel</div>
                        </div>
                        <div class="step-item" data-step="5">
                            <div class="step-icon active">
                                <i class="fas fa-address-book"></i>
                            </div>
                            <div class="step-label active">US Contact</div>
                        </div>
                        <div class="step-item" data-step="6">
                            <div class="step-icon active">
                                <i class="fas fa-user-friends"></i>
                            </div>
                            <div class="step-label active">Family</div>
                        </div>
                        <div class="step-item" data-step="7">
                            <div class="step-icon active">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="step-label active">Work</div>
                        </div>
                        <div class="step-item" data-step="8">
                            <div class="step-icon active">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="step-label active">Education</div>
                        </div>
                        <div class="step-item" data-step="9">
                            <div class="step-icon active">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="step-label active">Other Info</div>
                        </div>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Personal Information -->
                        <div id="personal-info" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-user mr-2 text-blue-500"></i>
                                Personal Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                ${renderField('Surname', pi.pi_sur_name)}
                                ${renderField('Given Name', pi.pi_given_name)}
                                ${renderField('Gender', pi.pi_gender)}
                                ${renderField('Marital Status', pi.pi_marital_status)}
                                ${renderField('Date of Birth', pi.pi_dob ? formatDate(pi.pi_dob) : '')}
                                ${renderField('Place of Birth', pi.pi_pob)}
                                ${renderField('Country of Birth', getCountryName(pi.pi_cob))}
                                ${renderField('Other Names', pi.pi_have_other_name ? `${pi.pi_other_sur_name || ''} ${pi.pi_other_given_name || ''}`.trim() : 'No')}
                                ${renderField('Other Nationality', pi.pi_have_other_nationality ? getCountryName(pi.pi_other_nationality_country) : 'No')}
                                ${renderField('National ID', pi.pi_nid)}
                            </div>

                            <!-- Contact Information -->
                            <div class="mt-6 border-t pt-6">
                                <h5 class="text-md font-semibold text-gray-700 mb-4">Contact Information</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    ${renderField('Primary Phone', ci.pi_primary_no)}
                                    ${renderField('Secondary Phone', ci.pi_secondary_no)}
                                    ${renderField('Work Phone', ci.pi_work_no)}
                                    ${renderField('Address Line 1', ci.pi_address_line_1)}
                                    ${renderField('Address Line 2', ci.pi_address_line_2)}
                                    ${renderField('City', ci.pi_address_city)}
                                    ${renderField('State', ci.pi_address_state)}
                                    ${renderField('Zip Code', ci.pi_address_zip_code)}
                                    ${renderField('Country', getCountryName(ci.pi_address_country))}
                                </div>

                                <!-- Email Addresses -->
                                ${ci.emails && ci.emails.length > 0 ? `
                                    <div class="mt-4">
                                        <h6 class="font-medium text-gray-700 mb-2">Email Addresses</h6>
                                        <div class="space-y-2">
                                            ${ci.emails.filter(email => email).map(email => renderField('Email', email)).join('')}
                                        </div>
                                    </div>
                                ` : ''}

                                <!-- Social Media -->
                                ${ci.socialMedia && ci.socialMedia.length > 0 ? `
                                    <div class="mt-4">
                                        <h6 class="font-medium text-gray-700 mb-2">Social Media Profiles</h6>
                                        <div class="space-y-2">
                                            ${ci.socialMedia.filter(sm => sm.platform || sm.username).map(sm => 
                                                renderField(sm.platform, sm.username)
                                            ).join('')}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Travel Information -->
                        <div id="travel-info" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-plane mr-2 text-green-500"></i>
                                Travel Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                ${renderField('Purpose of Travel', ti.ti_travel_purpose)}
                                ${renderField('Travel Plans Made', ti.ti_have_travel_plan)}
                                ${renderField('Intended Arrival Date', ti.ti_intended_arrival_date ? formatDate(ti.ti_intended_arrival_date) : '')}
                                ${renderField('Length of Stay', ti.ti_stay_length)}
                                ${renderField('Arrival Date', ti.ti_arrival_date ? formatDate(ti.ti_arrival_date) : '')}
                                ${renderField('Arrival Flight', ti.ti_arrival_flight_no)}
                                ${renderField('Arrival City', ti.ti_arrival_city)}
                                ${renderField('Departure Date', ti.ti_departure_date ? formatDate(ti.ti_departure_date) : '')}
                                ${renderField('Departure Flight', ti.ti_departure_flight_no)}
                                ${renderField('Departure City', ti.ti_departure_city)}
                                ${renderField('Trip Payment By', ti.trip_payment)}
                            </div>

                            <!-- Locations -->
                            ${ti.locations && ti.locations.length > 0 ? `
                                <div class="mt-6 border-t pt-6">
                                    <h5 class="text-md font-semibold text-gray-700 mb-4">Planned Visit Locations</h5>
                                    <div class="space-y-4">
                                        ${ti.locations.filter(loc => loc.address_line_1 || loc.city).map((location, idx) => `
                                            <div class="multi-entry-group">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    ${renderField('Address Line 1', location.address_line_1)}
                                                    ${renderField('Address Line 2', location.address_line_2)}
                                                    ${renderField('City', location.city)}
                                                    ${renderField('State', location.state)}
                                                    ${renderField('Zip Code', location.zip_code)}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>

                        <!-- Passport Information -->
                        <div id="passport-info" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-passport mr-2 text-purple-500"></i>
                                Passport Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                ${renderField('Passport Type', pp.pp_type)}
                                ${renderField('Passport Number', pp.pp_number)}
                                ${renderField('Issue Date', pp.pp_issue_date ? formatDate(pp.pp_issue_date) : '')}
                                ${renderField('Expiry Date', pp.pp_expiry_date ? formatDate(pp.pp_expiry_date) : '')}
                                ${renderField('Issuing Authority', pp.pp_issuing_authority)}
                                ${renderField('Issued City', pp.pp_issued_city)}
                                ${renderField('Lost/Stolen Passport', pp.pp_have_stolen ? 'Yes' : 'No')}
                                ${pp.pp_have_stolen ? renderField('Lost Passport No', pp.pp_lost_passport_no) : ''}
                            </div>
                        </div>

                        <!-- Travel Companion Information -->
                        <div id="travel-companion-info" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-users mr-2 text-yellow-500"></i>
                                Travel Companion Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                ${renderField('Traveling with Others', tci.tci_have_anyone ? 'Yes' : 'No')}
                                ${tci.tci_have_anyone ? renderField('Companion Surname', tci.tci_surname) : ''}
                                ${tci.tci_have_anyone ? renderField('Companion Given Name', tci.tci_given_name) : ''}
                                ${tci.tci_have_anyone ? renderField('Relationship', tci.tci_relationship) : ''}
                                ${renderField('Traveling in Group', tci.tci_have_group ? 'Yes' : 'No')}
                                ${tci.tci_have_group ? renderField('Group Name', tci.tci_group_name) : ''}
                            </div>
                        </div>

                        <!-- Previous U.S. Travel -->
                        <div id="previous-travel" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-history mr-2 text-orange-500"></i>
                                Previous U.S. Travel
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                ${renderField('Previously Issued US Visa', pust.pust_have_ever_issued ? 'Yes' : 'No')}
                                ${pust.pust_have_ever_issued ? renderField('Last Visa Issue Date', pust.pust_last_issued_visa_date ? formatDate(pust.pust_last_issued_visa_date) : '') : ''}
                                ${pust.pust_have_ever_issued ? renderField('Visa Number', pust.pust_visa_no) : ''}
                                ${renderField('Applied Same Visa Type', pust.pust_have_applied_same_visa ? 'Yes' : 'No')}
                                ${renderField('Applied Same Country', pust.pust_have_applied_same_country ? 'Yes' : 'No')}
                                ${renderField('Traveled to US Before', pust.pust_have_travelled_before ? 'Yes' : 'No')}
                                ${renderField('US Social Security Number', pust.pust_have_social_security_no ? pust.pust_social_security_no : 'No')}
                                ${renderField('US Taxpayer ID', pust.pust_have_us_tin ? pust.pust_us_tin : 'No')}
                                ${renderField('US Driver License', pust.pust_have_us_driving_license ? 'Yes' : 'No')}
                                ${renderField('Ten Fingerprinted', pust.pust_have_ten_fingerprint ? 'Yes' : 'No')}
                                ${renderField('Visa Refused', pust.pust_have_refused_us_visa ? 'Yes' : 'No')}
                                ${renderField('Legal Permanent Resident', pust.pust_have_legal_permanent_resident ? 'Yes' : 'No')}
                            </div>
                        </div>

                        <!-- U.S. Contact Information -->
                        <div id="us-contact-info" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-address-book mr-2 text-red-500"></i>
                                U.S. Contact Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                ${renderField('Contact Type', usci.usci_contact_type)}
                                ${usci.usci_contact_type === 'Person' ? renderField('Contact Surname', usci.usci_contact_person_surname) : ''}
                                ${usci.usci_contact_type === 'Person' ? renderField('Contact Given Name', usci.usci_contact_person_given_name) : ''}
                                ${usci.usci_contact_type === 'Company' ? renderField('Company Name', usci.usci_contact_company_name) : ''}
                                ${usci.usci_contact_type === 'Hotel' ? renderField('Hotel Name', usci.usci_contact_hotel_name) : ''}
                                ${renderField('Telephone', usci['usci contact person telephone'] || usci['usci contact company telephone'] || usci['usci contact hotel telephone'])}
                                ${renderField('Email', usci['usci contact person email'] || usci['usci contact company email'] || usci['usci contact hotel email'])}
                                ${renderField('Relationship', usci['usci contact person relationship'] || usci['usci contact company relationship'] || usci['usci contact hotel relationship'])}
                            </div>
                        </div>

                        <!-- Family Information -->
                        <div id="family-info" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-user-friends mr-2 text-pink-500"></i>
                                Family Information
                            </h4>
                            ${fm.familyMembers && fm.familyMembers.length > 0 ? `
                                <div class="space-y-4">
                                    ${fm.familyMembers.map((member, idx) => `
                                        <div class="multi-entry-group">
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                ${renderField('Relation', member.relation)}
                                                ${renderField('Given Name', member.given_name)}
                                                ${renderField('Family Name', member.family_name)}
                                                ${renderField('Date of Birth', member.dob ? formatDate(member.dob) : '')}
                                                ${renderField('Nationality', getCountryName(member.nationality))}
                                                ${renderField('In USA', member.in_usa ? 'Yes' : 'No')}
                                                ${member.in_usa ? renderField('Status in USA', member.person_status) : ''}
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : '<p class="text-gray-500">No family members added.</p>'}
                        </div>

                        <!-- Work Information -->
                        <div id="work-info" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-briefcase mr-2 text-indigo-500"></i>
                                Work Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                ${renderField('Primary Occupation', wi.wi_primary_occupation_type)}
                                ${renderField('Company/School Name', wi.wi_company_or_school_name)}
                                ${renderField('Monthly Salary', wi.wi_salary)}
                                ${renderField('Job Duties', wi.wi_your_duties, true)}
                            </div>

                            <!-- Previous Employment -->
                            ${wi.previousEmployment && wi.previousEmployment.length > 0 ? `
                                <div class="mt-6 border-t pt-6">
                                    <h5 class="text-md font-semibold text-gray-700 mb-4">Previous Employment</h5>
                                    <div class="space-y-4">
                                        ${wi.previousEmployment.map((employment, idx) => `
                                            <div class="multi-entry-group">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    ${renderField('Company Name', employment.wi_pre_company_name)}
                                                    ${renderField('Job Title', employment.wi_pre_company_job_title)}
                                                    ${renderField('Start Date', employment.wi_pre_employment_started ? formatDate(employment.wi_pre_employment_started) : '')}
                                                    ${renderField('End Date', employment.wi_pre_employment_ended ? formatDate(employment.wi_pre_employment_ended) : '')}
                                                    ${renderField('Salary', employment.wi_pre_company_salary)}
                                                    ${renderField('Duties', employment.wi_pre_company_duties, true)}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>

                        <!-- Educational Information -->
                        <div id="education-info" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-graduation-cap mr-2 text-teal-500"></i>
                                Educational Information
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                ${renderField('Attended Secondary+ Education', edi.edi_have_attended_secondary_level ? 'Yes' : 'No')}
                            </div>

                            ${edi.institutions && edi.institutions.length > 0 ? `
                                <div class="mt-6 border-t pt-6">
                                    <h5 class="text-md font-semibold text-gray-700 mb-4">Educational Institutions</h5>
                                    <div class="space-y-4">
                                        ${edi.institutions.map((institution, idx) => `
                                            <div class="multi-entry-group">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    ${renderField('Institution Name', institution.name)}
                                                    ${renderField('Course of Study', institution.course)}
                                                    ${renderField('Attendance From', institution.attendanceFrom ? formatDate(institution.attendanceFrom) : '')}
                                                    ${renderField('Attendance To', institution.attendanceTo ? formatDate(institution.attendanceTo) : '')}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>

                        <!-- Other Information -->
                        <div id="other-info" class="applicant-section p-4 rounded-lg">
                            <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-info-circle mr-2 text-gray-500"></i>
                                Other Information
                            </h4>
                            <div class="space-y-4">
                                ${renderField('Languages Spoken', oi.oi_spoken_language_list, true)}
                                ${renderField('Traveled Last 5 Years', oi.oi_have_travel_country_5years ? 'Yes' : 'No')}
                                
                                ${oi.oi_travelled_country && oi.oi_travelled_country.length > 0 ? `
                                    <div>
                                        <h6 class="font-medium text-gray-700 mb-2">Countries Visited (Last 5 Years)</h6>
                                        <div class="flex flex-wrap gap-2">
                                            ${oi.oi_travelled_country.map(countryCode => 
                                                `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">${getCountryName(countryCode)}</span>`
                                            ).join('')}
                                        </div>
                                    </div>
                                ` : ''}

                                ${renderField('Belongs to Organizations', oi.oi_have_you_belong_orgntion ? 'Yes' : 'No')}
                                
                                ${oi.oi_organization_name && oi.oi_organization_name.length > 0 ? `
                                    <div>
                                        <h6 class="font-medium text-gray-700 mb-2">Organizations</h6>
                                        <div class="space-y-2">
                                            ${oi.oi_organization_name.map(org => renderField('Organization', org.name)).join('')}
                                        </div>
                                    </div>
                                ` : ''}

                                ${renderField('Special Skills/Training', oi.oi_have_special_skills ? 'Yes' : 'No')}
                                ${oi.oi_have_special_skills ? renderField('Special Skills Details', oi.oi_special_skills, true) : ''}
                                ${renderField('Military Service', oi.oi_have_served_military ? 'Yes' : 'No')}

                                ${oi.oi_military_service && oi.oi_military_service.length > 0 ? `
                                    <div class="mt-4">
                                        <h6 class="font-medium text-gray-700 mb-2">Military Service History</h6>
                                        <div class="space-y-4">
                                            ${oi.oi_military_service.map((service, idx) => `
                                                <div class="multi-entry-group">
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                        ${renderField('Country', getCountryName(service.oi_sm_country_name))}
                                                        ${renderField('Branch', service.oi_sm_service_branch)}
                                                        ${renderField('Rank', service.oi_sm_rank)}
                                                        ${renderField('Specialty', service.oi_militay_speciality, true)}
                                                        ${renderField('Service From', service.oi_sm_serve_from ? formatDate(service.oi_sm_serve_from) : '')}
                                                        ${renderField('Service To', service.oi_sm_serve_to ? formatDate(service.oi_sm_serve_to) : '')}
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Render a field with copy functionality
        function renderField(label, value, isTextarea = false) {
            if (!value && value !== 0 && value !== false) {
                value = 'Not provided';
            }

            if (value === true) value = 'Yes';
            if (value === false) value = 'No';

            const displayValue = String(value).trim();
            const fieldId = `field-${Math.random().toString(36).substr(2, 9)}`;

            if (isTextarea) {
                return `
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">${label}</label>
                        <div id="${fieldId}" class="copy-field p-3 border border-gray-200 rounded bg-white min-h-[100px] whitespace-pre-wrap" data-value="${displayValue.replace(/"/g, '&quot;')}">
                            ${displayValue}
                        </div>
                    </div>
                `;
            }

            return `
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">${label}</label>
                    <div id="${fieldId}" class="copy-field p-2 border border-gray-200 rounded bg-white truncate" data-value="${displayValue.replace(/"/g, '&quot;')}" title="Click to copy: ${displayValue}">
                        ${displayValue}
                    </div>
                </div>
            `;
        }

        // Setup copy functionality
        function setupCopyFunctionality() {
            const copyFields = document.querySelectorAll('.copy-field');

            copyFields.forEach(field => {
                field.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    copyToClipboard(value, this);
                });
            });
        }

        // Setup step navigation
        function setupStepNavigation() {
            const stepItems = document.querySelectorAll('.step-item');

            stepItems.forEach((step, index) => {
                step.addEventListener('click', function() {
                    const stepIndex = parseInt(this.getAttribute('data-step'));
                    highlightStepSection(stepIndex);
                });
            });
        }

        // Highlight step section
        function highlightStepSection(stepIndex) {
            // Remove all highlights
            document.querySelectorAll('.applicant-section').forEach(section => {
                section.classList.remove('highlighted');
            });

            document.querySelectorAll('.step-icon').forEach(icon => {
                icon.classList.remove('highlighted');
            });

            document.querySelectorAll('.step-label').forEach(label => {
                label.classList.remove('highlighted');
            });

            // Add highlight to selected step
            const stepElement = document.querySelector(`.step-item[data-step="${stepIndex}"]`);
            if (stepElement) {
                stepElement.querySelector('.step-icon').classList.add('highlighted');
                stepElement.querySelector('.step-label').classList.add('highlighted');
            }

            // Highlight corresponding section
            const sectionId = stepSections[stepIndex];
            const sectionElement = document.getElementById(sectionId);
            if (sectionElement) {
                sectionElement.classList.add('highlighted');

                // Scroll to section
                sectionElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }

        // Copy text to clipboard
        function copyToClipboard(text, element) {
            // Create a temporary textarea element
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-999999px';
            textarea.style.top = '-999999px';
            document.body.appendChild(textarea);

            // Select and copy the text
            textarea.focus();
            textarea.select();

            try {
                const successful = document.execCommand('copy');
                document.body.removeChild(textarea);

                if (successful) {
                    // Show visual feedback
                    element.classList.add('copied');

                    // Show toast
                    showToast('Copied to clipboard!');

                    // Remove copied class after 1 second
                    setTimeout(() => {
                        element.classList.remove('copied');
                    }, 1000);
                } else {
                    showToast('Failed to copy to clipboard');
                }
            } catch (err) {
                document.body.removeChild(textarea);
                console.error('Failed to copy: ', err);
                showToast('Failed to copy to clipboard');
            }
        }

        // Show toast notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');

            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            toast.classList.add('show');

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.classList.add('hidden');
                }, 300);
            }, 2000);
        }

        // Utility functions
        function formatDate(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-GB'); // DD/MM/YYYY format
            } catch (e) {
                return dateString;
            }
        }

        function getCountryName(countryCode) {
            if (!countryCode) return 'Not provided';
            const country = countries.find(c => c.code === countryCode);
            return country ? country.name : countryCode;
        }

        // Download JSON
        function downloadJSON() {
            if (!applicationData) return;

            const dataStr = JSON.stringify(applicationData, null, 2);
            const dataBlob = new Blob([dataStr], {
                type: 'application/json'
            });

            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = `usa-ds160-application-${applicationData.pnr}.json`;
            link.click();

            showToast('JSON file downloaded!');
        }
    </script>
</body>

</html>