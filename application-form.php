<?php
require 'server/db_connection.php';

$pnr = $_GET['pnr'] ?? null;
$dbApplicationData = null;

if ($pnr) {
    // 1. Fetch application info from DATABASE FIRST 
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE pnr = ?");
    $stmt->execute([$pnr]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($application) {
        // 2. Fetch all applicants from DATABASE
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
                "nidInfo" => json_decode($ap['nid_info'], true) ?? [],
                "contactInfo" => json_decode($ap['contact_info'], true) ?? [],
                "familyInfo" => json_decode($ap['family_info'], true) ?? [],
                "accommodationDetails" => json_decode($ap['accommodation_details'], true) ?? [],
                "employmentInfo" => json_decode($ap['employment_info'], true) ?? [],
                "incomeExpenditure" => json_decode($ap['income_expenditure'], true) ?? [],
                "travelInfo" => json_decode($ap['travel_info'], true) ?? [],
                "travelHistory" => json_decode($ap['travel_history'], true) ?? []
            ];
        }

        // 3. Prepare DB data for JS
        $dbApplicationData = [
            'pnr' => $application['pnr'],
            'nameOfApplicant' => $applicants[0]['passportInfo']['pi_sur_name'] ?? '',
            'totalApplicants' => count($applicants),
            'applicants' => $applicants,
            'currentApplicant' => 0,
            'currentStep' => 0,
            'timestamp' => $application['created_at'],
            'source' => 'database'
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>USA Visa Application</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .tab {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab.active {
            background-color: #3b82f6;
            color: white;
        }

        .progress-bar {
            transition: width 0.5s ease-in-out;
        }

        .step-nav-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .step-nav-item:hover {
            background-color: #f3f4f6;
        }

        .step-nav-item.active {
            border-left-color: #3b82f6;
            background-color: #eff6ff;
        }

        .step-nav-item.completed .step-icon {
            background-color: #10b981;
            color: white;
        }

        .step-nav-item.current .step-icon {
            background-color: #3b82f6;
            color: white;
        }

        .dynamic-field-group {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            background-color: #f9fafb;
        }

        .conditional-block {
            display: none;
        }

        .conditional-block.active {
            display: block;
        }

        .preview-section {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1.5rem;
            background-color: #f9fafb;
        }

        .preview-section h3 {
            color: #374151;
        }

        .preview-section div {
            margin-bottom: 0.5rem;
        }

        .preview-section strong {
            color: #4b5563;
            min-width: 120px;
            display: inline-block;
        }

        /* Smooth transitions */
        .step {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <header class="text-center mb-12">
            <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-passport text-white text-xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">USA Visa Application</h1>
            </div>
            <p class="text-gray-600 max-w-2xl mx-auto">Complete your USA visa application form. Please ensure all information is accurate and matches your official documents.</p>
        </header>

        <!-- Main Application Container -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <!-- Initial Screen -->
            <div id="initial-screen" class="p-8">
                <div class="max-w-md mx-auto text-center">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">How many applicants are under the same PNR?</h2>
                    <div class="mb-8">
                        <label for="applicant-count" class="block text-gray-700 mb-2">Number of Applicants</label>
                        <select id="applicant-count" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="1">1 Applicant</option>
                            <option value="2">2 Applicants</option>
                            <option value="3">3 Applicants</option>
                            <option value="4">4 Applicants</option>
                            <option value="5">5 Applicants</option>
                        </select>
                    </div>
                    <button id="start-application" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-300">
                        Start Application
                    </button>

                    <!-- Load Saved Application -->
                    <div id="saved-application-section" class="mt-8 p-4 bg-yellow-50 rounded-lg border border-yellow-200 <?= $dbApplicationData ? '' : 'hidden' ?>">
                        <h3 class="font-medium text-yellow-800 mb-2">Saved Application Found</h3>
                        <p class="text-yellow-700 text-sm mb-3">We found a saved application with PNR: <span id="saved-pnr" class="font-mono font-bold"><?= $dbApplicationData['pnr'] ?? '' ?></span></p>
                        <button id="load-application" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg transition duration-300">
                            Load Saved Application
                        </button>
                    </div>
                </div>
            </div>

            <!-- Multi-Applicant Form (Hidden Initially) -->
            <div id="multi-applicant-form" class="<?= $dbApplicationData ? '' : 'hidden' ?>">
                <!-- PNR Display -->
                <div class="px-8 pt-8 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Application PNR: <span id="pnr-display" class="font-mono text-blue-600"><?= $dbApplicationData['pnr'] ?? '' ?></span></h2>
                        <p class="text-gray-600 text-sm">Your application is automatically saved as you progress</p>
                    </div>
                    <div class="flex space-x-2">
                        <button id="back-to-dashboard" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition duration-300 text-sm">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </button>
                        <button id="save-exit" class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition duration-300 text-sm">
                            <i class="fas fa-save mr-2"></i>Save & Exit
                        </button>
                    </div>
                </div>

                <!-- Overall Progress -->
                <div class="px-8 pt-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-blue-600">Overall Progress</span>
                        <span class="text-sm font-medium text-gray-500"><span id="completed-applicants">0</span> of <span id="total-applicants"><?= $dbApplicationData['totalApplicants'] ?? 1 ?></span> applicants completed</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-6">
                        <div id="overall-progress-bar" class="bg-blue-600 h-2.5 rounded-full progress-bar" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Applicant Tabs with Individual Progress -->
                <div id="applicant-tabs" class="flex overflow-x-auto border-b border-gray-200 px-8">
                    <!-- Tabs will be dynamically generated here -->
                </div>

                <!-- Current Applicant Progress -->
                <div class="px-8 pt-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700">Applicant <span id="current-applicant-number">1</span> Progress</span>
                        <span class="text-sm font-medium text-gray-500"><span id="current-step">1</span> of <span id="total-steps">10</span></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="individual-progress-bar" class="bg-green-600 h-2.5 rounded-full progress-bar" style="width: 10%"></div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex flex-col md:flex-row p-8">
                    <!-- Step Navigation Sidebar -->
                    <div class="w-full md:w-1/4 mb-6 md:mb-0 md:pr-6">
                        <div class="bg-gray-50 rounded-lg p-4 sticky top-4">
                            <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-list-ol mr-2 text-blue-500"></i> Application Steps
                            </h3>
                            <div id="step-navigation" class="space-y-2">
                                <!-- Step navigation items will be dynamically generated here -->
                            </div>
                        </div>
                    </div>

                    <!-- Form Steps -->
                    <div id="form-steps" class="w-full md:w-3/4">
                        <!-- Steps will be dynamically generated here -->
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between px-8 pb-8">
                    <button id="prev-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i> Previous
                    </button>
                    <div class="flex space-x-4">
                        <button id="next-applicant-btn" class="hidden bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-6 rounded-lg transition duration-300 flex items-center">
                            Save & Next Applicant <i class="fas fa-user-plus ml-2"></i>
                        </button>
                        <button id="next-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300 flex items-center">
                            Save & Next Step <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                    <button id="submit-btn" class="hidden bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300">
                        Submit Application
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-12 text-center text-gray-500 text-sm">
            <p>© 2025 TravHub Global Limited. All rights reserved.</p>
            <p class="mt-2">This is a demonstration form only. For official visa applications, visit the <a href="#" class="text-blue-600 hover:underline">official U.S. government website</a>.</p>
        </footer>
    </div>

    <script>
        // তারিখ validation ফাংশন
        function isValidDate(dateString) {
            // DD/MM/YYYY format validate
            const pattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
            if (!pattern.test(dateString)) return false;

            const [_, day, month, year] = pattern.exec(dateString);
            const date = new Date(year, month - 1, day);

            return date.getDate() == day &&
                date.getMonth() == month - 1 &&
                date.getFullYear() == year;
        }

        // DD/MM/YYYY থেকে YYYY-MM-DD তে convert
        function convertToISO(dateString) {
            if (!isValidDate(dateString)) return '';

            const [day, month, year] = dateString.split('/');
            return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
        }

        // YYYY-MM-DD থেকে DD/MM/YYYY তে convert
        function convertToDisplay(isoDate) {
            if (!isoDate) return '';

            const [year, month, day] = isoDate.split('-');
            return `${day}/${month}/${year}`;
        }

        // Application state
        const state = {
            currentApplicant: <?= $dbApplicationData['currentApplicant'] ?? 0 ?>,
            currentStep: <?= $dbApplicationData['currentStep'] ?? 0 ?>,
            totalSteps: 10,
            totalApplicants: <?= $dbApplicationData['totalApplicants'] ?? 1 ?>,
            pnr: '<?= $dbApplicationData['pnr'] ?? '' ?>',
            applicants: <?= json_encode($dbApplicationData['applicants'] ?? []) ?>,
            steps: [{
                    name: 'Personal Information (PI)',
                    icon: 'fa-user',
                    description: 'Personal and contact details'
                },
                {
                    name: 'Travel Information (TI)',
                    icon: 'fa-plane',
                    description: 'Travel plans and purpose'
                },
                {
                    name: 'Passport Information (PP)',
                    icon: 'fa-passport',
                    description: 'Passport details'
                },
                {
                    name: 'Travel Companion Information (TCI)',
                    icon: 'fa-users',
                    description: 'Travel companions details'
                },
                {
                    name: 'Previous U.S. Travel (PUST)',
                    icon: 'fa-history',
                    description: 'Previous travel history to USA'
                },
                {
                    name: 'U.S. Contact Information (USCI)',
                    icon: 'fa-address-book',
                    description: 'Contacts in USA'
                },
                {
                    name: 'Family Member Information (FM)',
                    icon: 'fa-user-friends',
                    description: 'Family members details'
                },
                {
                    name: 'Work Information (WI)',
                    icon: 'fa-briefcase',
                    description: 'Employment and work history'
                },
                {
                    name: 'Educational Information (EDI)',
                    icon: 'fa-graduation-cap',
                    description: 'Educational background'
                },
                {
                    name: 'Other Information (OI)',
                    icon: 'fa-info-circle',
                    description: 'Additional information'
                }
            ],
            showPreview: false, // নতুন property
            previewApplicant: null // কোন applicant এর preview দেখাচ্ছে
        };

        // এর IMMEDIATELY পরে এই function গুলো যোগ করুন:
        function initializeApplication() {
            const urlParams = new URLSearchParams(window.location.search);
            const pnrFromUrl = urlParams.get('pnr');

            // console.log('Initializing application...');
            // console.log('PNR from URL:', pnrFromUrl);
            // console.log('State PNR:', state.pnr);

            if (pnrFromUrl) {
                loadApplicationByPNR(pnrFromUrl);
            } else if (state.pnr) {
                initializeFormFromState();
            } else {
                document.getElementById('initial-screen').classList.remove('hidden');
                document.getElementById('multi-applicant-form').classList.add('hidden');
            }
        }

        function loadApplicationByPNR(pnr) {
            const localData = localStorage.getItem('usaVisaApplication-' + pnr);

            if (localData) {
                const applicationData = JSON.parse(localData);
                loadApplicationData(applicationData);
            } else {
                if (state.pnr && state.pnr === pnr) {
                    initializeFormFromState();
                } else {
                    alert('Application not found. Please start a new application.');
                    document.getElementById('initial-screen').classList.remove('hidden');
                    document.getElementById('multi-applicant-form').classList.add('hidden');
                }
            }
        }

        function loadApplicationData(applicationData) {
            state.pnr = applicationData.pnr;
            state.totalApplicants = applicationData.totalApplicants;
            state.applicants = applicationData.applicants;
            state.currentApplicant = applicationData.currentApplicant || 0;
            state.currentStep = applicationData.currentStep || 0;

            initializeFormFromState();
        }

        // Initialize applicants if empty
        if (state.applicants.length === 0) {
            for (let i = 0; i < state.totalApplicants; i++) {
                initializeApplicant(i);
            }
        }

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            // Set up event listeners
            document.getElementById('start-application').addEventListener('click', startApplication);
            document.getElementById('load-application').addEventListener('click', loadSavedApplication);
            document.getElementById('prev-btn').addEventListener('click', previousStep);
            document.getElementById('next-btn').addEventListener('click', nextStep);
            document.getElementById('next-applicant-btn').addEventListener('click', nextApplicant);
            document.getElementById('submit-btn').addEventListener('click', submitApplication);
            document.getElementById('save-exit').addEventListener('click', saveAndExit);
            document.getElementById('back-to-dashboard').addEventListener('click', function() {
                window.location.href = 'index.php';
            });

            // If we have DB data, initialize the form
            initializeApplication();
        });

        function initializeFormFromState() {
            document.getElementById('initial-screen').classList.add('hidden');
            document.getElementById('multi-applicant-form').classList.remove('hidden');
            document.getElementById('pnr-display').textContent = state.pnr;
            document.getElementById('total-applicants').textContent = state.totalApplicants;
            generateTabs();
            generateStepNavigation();
            generateFormSteps();
            updateUI();
        }

        function startApplication() {
            const applicantCount = parseInt(document.getElementById('applicant-count').value);
            state.totalApplicants = applicantCount;

            // Generate PNR if not already set
            if (!state.pnr) {
                state.pnr = generatePNR();
            }

            // Initialize all applicants
            state.applicants = [];
            for (let i = 0; i < applicantCount; i++) {
                initializeApplicant(i);
            }

            // Hide initial screen and show form
            document.getElementById('initial-screen').classList.add('hidden');
            document.getElementById('multi-applicant-form').classList.remove('hidden');

            // Display PNR
            document.getElementById('pnr-display').textContent = state.pnr;
            document.getElementById('total-applicants').textContent = state.totalApplicants;

            // Generate UI components
            generateTabs();
            generateStepNavigation();
            generateFormSteps();
            updateUI();

            // Save initial state
            saveToLocalStorage();
        }

        function generatePNR() {
            const timestamp = Date.now().toString().slice(-6);
            const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            return `TRH-US-PNR-${timestamp}K${random}`;
        }

        function initializeApplicant(index) {
            state.applicants[index] = {
                id: `APPT-${(index + 1).toString().padStart(3, '0')}`,
                pnr: state.pnr,
                user_pnr: `${state.pnr}-APPT-${(index + 1).toString().padStart(3, '0')}`,
                completed: false,

                // Personal Information (PI)
                passportInfo: {
                    pi_traveler: '',
                    pi_sur_name: '',
                    pi_given_name: '',
                    pi_have_other_name: false,
                    pi_other_sur_name: '',
                    pi_other_given_name: '',
                    pi_gender: '',
                    pi_marital_status: '',
                    pi_dob: '',
                    pi_pob: '',
                    pi_cob: '',
                    pi_nid: '',
                    pi_have_other_nationality: false,
                    pi_other_nationality_country: '',
                    pi_have_other_country_paasport: false,
                    pi_other_country_passport: '',
                    pi_have_other_permanent_residence: false,
                    pi_other_permanent_residence_country: ''
                },

                // Contact Information
                contactInfo: {
                    // Address
                    pi_address_line_1: '',
                    pi_address_line_2: '',
                    pi_address_city: '',
                    pi_address_state: '',
                    pi_address_zip_code: '',
                    pi_address_country: '',

                    // Mailing Address
                    is_same_mailing_address: true,
                    pi_mail_address_line_1: '',
                    pi_mail_address_line_2: '',
                    pi_mail_address_city: '',
                    pi_mail_address_state: '',
                    pi_mail_address_zip_code: '',
                    pi_mail_address_country: '',

                    // Contact Numbers
                    pi_primary_no: '',
                    pi_secondary_no: '',
                    pi_work_no: '',
                    otherPhones: [''],

                    // Email & Social
                    emails: [''],
                    socialMedia: [{
                        platform: '',
                        username: ''
                    }]
                },

                // Travel Information (TI)
                travelInfo: {
                    ti_travel_purpose: '',
                    ti_have_travel_plan: '',
                    // No travel plan fields
                    ti_intended_arrival_date: '',
                    ti_stay_length: '',
                    ti_length_type: '',
                    // Yes travel plan fields  
                    ti_arrival_date: '',
                    ti_arrival_flight_no: '',
                    ti_arrival_city: '',
                    ti_departure_date: '',
                    ti_departure_flight_no: '',
                    ti_departure_city: '',

                    // Locations
                    locations: [{
                        address_line_1: '',
                        address_line_2: '',
                        city: '',
                        state: '',
                        zip_code: ''
                    }],

                    // Payment
                    trip_payment: '',
                    // Other person payment
                    trip_paying_person_surname: '',
                    ti_trip_paying_person_given_name: '',
                    ti_trip_paying_person_telephone: '',
                    ti_trip_paying_person_email: '',
                    _trip_paying_person_relationship: '',
                    trip_paying_person_have_same_address: true,
                    ti_trip_paying_person_address_line_1: '',
                    ti_trip_paying_person_address_line_2: '',
                    ti_trip_paying_person_address_city: '',
                    ti_trip_paying_person_address_state: '',
                    ti_trip_paying_person_address_zip_code: '',
                    trip_paying_person_address_country: '',

                    // Travel Companion (TCI)
                    tci_have_anyone: false,
                    tci_surname: '',
                    tci_given_name: '',
                    tci_relationship: '',
                    tci_have_group: false,
                    tci_group_name: ''
                },

                // Passport Information (PP)
                passportInfo: {
                    pp_type: '',
                    pp_number: '',
                    pp_issue_date: '',
                    pp_expiry_date: '',
                    pp_issuing_authority: '',
                    pp_issued_city: '',
                    pp_have_stolen: false,
                    pp_lost_passport_no: '',
                    pp_lost_passport_authority: '',
                    pp_lost_passport_explanation: ''
                },

                // Previous US Travel (PUST)
                travelHistory: {
                    pust_have_ever_issued: false,
                    pust_last_issued_visa_date: '',
                    pust_visa_no: '',
                    pust_remember_visa_no: false,
                    pust_have_applied_same_visa: false,
                    pust_have_applied_same_country: false,
                    pust_have_travelled_before: false,
                    previousTravels: [{
                        arrival_date: '',
                        staying_length: ''
                    }],
                    pust_have_social_security_no: false,
                    pust_social_security_no: '',
                    pust_have_us_tin: false,
                    pust_us_tin: '',
                    pust_have_us_driving_license: false,
                    driverLicenses: [{
                        license_no: '',
                        state: ''
                    }],
                    pust_have_ten_fingerprint: false,
                    pust_have_refused_us_visa: false,
                    pust_visa_refusal_explain: '',
                    pust_have_legal_permanent_resident: false,
                    pust_have_us_visa_lost: false,
                    pust_have_us_visa_cancelled: false
                },

                // US Contact Information (USCI)
                usContactInfo: {
                    usci_contact_type: '',
                    // Person contact
                    usci_contact_person_surname: '',
                    usci_contact_person_given_name: '',
                    'usci contact person telephone': '',
                    'usci contact person email': '',
                    'usci contact person relationship': '',
                    'usci contact person address line 1': '',
                    'usci contact person address line 2': '',
                    'usci contact person address city': '',
                    'usci contact person address state': '',
                    'usci contact person address zip code': '',
                    // Company contact (same fields reused)
                    usci_contact_company_name: '',
                    'usci contact company telephone': '',
                    'usci contact company email': '',
                    'usci contact company relationship': '',
                    // Hotel contact
                    usci_contact_hotel_name: ''
                },

                // Family Information (FM)
                familyInfo: {
                    familyMembers: [{
                        relation: '',
                        given_name: '',
                        family_name: '',
                        dob: '',
                        nationality: '',
                        in_usa: false,
                        person_status: '',
                        // Spouse specific
                        pob: '',
                        boc_country: '',
                        spouse_telephone: '',
                        spouse_email: '',
                        have_same_address: '',
                        spouse_address_line_1: '',
                        spouse_address_line_2: '',
                        spouse_address_city: '',
                        spouse_address_state: '',
                        spouse_address_zip_code: '',
                        spouse_address_country: ''
                    }]
                },

                // Work Information (WI)
                employmentInfo: {
                    wi_primary_occupation_type: '',
                    // Employment fields
                    wi_company_or_school_name: '',
                    wi_salary: '',
                    wi_your_duties: '',
                    wi_company_or_school_address_line_1: '',
                    wi_company_or_school_address_line_2: '',
                    wi_company_or_school_address_city: '',
                    wi_company_or_school_address_state: '',
                    wi_company_or_school_address_zip_code: '',
                    wi_company_or_school_address_country: '',
                    wi_company_or_school_address_telephone: '',

                    have_previous_experience: false,
                    previousEmployment: [{
                        wi_pre_company_name: '',
                        wi_pre_company_job_title: '',
                        wi_pre_company_supervisor_surname: '',
                        wi_pre_company_supervisor_given_name: '',
                        wi_pre_employment_started: '',
                        wi_pre_employment_ended: '',
                        wi_pre_company_salary: '',
                        wi_pre_company_address_line_1: '',
                        wi_pre_company_address_line_2: '',
                        wi_pre_company_address_city: '',
                        wi_pre_company_address_state: '',
                        wi_pre_company_address_zip_code: '',
                        wi_pre_company_address_country: '',
                        wi_pre_company_address_telephone: '',
                        wi_pre_company_duties: ''
                    }]
                },

                // Educational Information (EDI)
                educationalInfo: {
                    edi_have_attended_secondary_level: false,
                    institutions: [{
                        name: '',
                        course: '',
                        attendanceFrom: '',
                        attendanceTo: '',
                        edi_institution_address_line_1: '',
                        edi_institution_address_line_2: '',
                        edi_institution_address_city: '',
                        edi_institution_address_state: '',
                        edi_institution_address_zip_code: '',
                        edi_institution_address_country: ''
                    }]
                },

                // Other Information (OI)
                otherInfo: {
                    oi_spoken_language_list: '',
                    oi_have_travel_country_5years: false,
                    oi_travelled_country: [],
                    oi_have_you_belong_orgntion: false,
                    oi_organization_name: [],
                    oi_have_special_skills: false,
                    oi_special_skills: '',
                    oi_have_served_military: false,
                    oi_military_service: [{
                        oi_sm_country_name: '',
                        oi_sm_service_branch: '',
                        oi_sm_rank: '',
                        oi_militay_speciality: '',
                        oi_sm_serve_from: '',
                        oi_sm_serve_to: ''
                    }]
                }
            };
        }

        // Generate form steps based on Excel structure
        function generateFormSteps() {
            const formStepsContainer = document.getElementById('form-steps');
            formStepsContainer.innerHTML = '';

            state.steps.forEach((step, index) => {
                const stepElement = document.createElement('div');
                stepElement.className = `step fade-in ${index === state.currentStep ? 'active' : ''}`;
                stepElement.id = `step-${index}`;
                stepElement.innerHTML = `
                    <h2 class="text-xl font-bold text-gray-800 mb-6">${step.name} - Applicant ${state.currentApplicant + 1}</h2>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        ${generateStepContent(index)}
                    </div>
                `;
                formStepsContainer.appendChild(stepElement);
            });

            document.getElementById('total-steps').textContent = state.totalSteps;
        }

        function generateStepContent(stepIndex) {
            const applicant = state.applicants[state.currentApplicant];
            switch (stepIndex) {
                case 0:
                    return generatePersonalInfoStep(applicant);
                case 1:
                    return generateTravelInfoStep(applicant);
                case 2:
                    return generatePassportInfoStep(applicant);
                case 3:
                    return generateTravelCompanionStep(applicant);
                case 4:
                    return generatePreviousTravelStep(applicant);
                case 5:
                    return generateUSContactStep(applicant);
                case 6:
                    return generateFamilyInfoStep(applicant);
                case 7:
                    return generateWorkInfoStep(applicant);
                case 8:
                    return generateEducationInfoStep(applicant);
                case 9:
                    return generateOtherInfoStep(applicant);
                default:
                    return '<p>Step content not defined.</p>';
            }
        }

        // Country data - will be replaced with JSON API data
        try {
            const response = await fetch('/server/countries.json');
            const countries = await response.json();

            console.log(countries); // এখানে এখন সব দেশের ডেটা পাবে

            // Example: Dropdown এ দেখাতে চাইলে
            const select = document.getElementById('country-select');
            countries.forEach(c => {
                const option = document.createElement('option');
                option.value = c.code;
                option.textContent = c.name;
                select.appendChild(option);
            });

        } catch (error) {
            console.error('Error loading countries:', error);
        }

        // Social media platforms
        const socialMediaPlatforms = [
            'Facebook',
            'Twitter',
            'Instagram',
            'LinkedIn',
            'YouTube',
            'TikTok',
            'Snapchat',
            'Pinterest',
            'Reddit',
            'WhatsApp',
            'Telegram',
            'WeChat',
            'Other'
        ];

        // Personal Information Step (Based on Excel PI section)
        function generatePersonalInfoStep(applicant) {
            const pi = applicant.passportInfo || {};
            const ci = applicant.contactInfo || {};

            // Generate country options
            const generateCountryOptions = (selectedValue) => {
                return countries.map(country =>
                    `<option value="${country.code}" ${(selectedValue === country.code) ? 'selected' : ''}>${country.name}</option>`
                ).join('');
            };

            return `
                    <div class="space-y-6">
                        <!-- Traveler Field -->
                        <div>
                            <label class="block text-gray-700 mb-2">Traveler *</label>
                            <select name="pi_traveler" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_traveler', this.value)" required>
                                <option value="">Select Traveler</option>
                                <!-- Options will be populated dynamically from JSON/API -->
                                <option value="traveler1" ${(pi.pi_traveler === 'traveler1') ? 'selected' : ''}>Traveler 1</option>
                                <option value="traveler2" ${(pi.pi_traveler === 'traveler2') ? 'selected' : ''}>Traveler 2</option>
                                <option value="traveler3" ${(pi.pi_traveler === 'traveler3') ? 'selected' : ''}>Traveler 3</option>
                            </select>
                        </div>

                        <!-- Basic Personal Info -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Surname *</label>
                                <input type="text" name="pi_sur_name" value="${pi.pi_sur_name || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_sur_name', this.value)" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Given Name *</label>
                                <input type="text" name="pi_given_name" value="${pi.pi_given_name || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_given_name', this.value)" required>
                            </div>
                        </div>

                        <!-- Other Name Toggle -->
                        <div>
                            <label class="block text-gray-700 mb-2">Do you have other name?</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pi_have_other_name" value="1" ${pi.pi_have_other_name ? 'checked' : ''} onchange="toggleConditionalBlock('other-name', true); updateApplicantData('passportInfo', 'pi_have_other_name', true)">
                                    <span class="ml-2">Yes</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pi_have_other_name" value="0" ${!pi.pi_have_other_name ? 'checked' : ''} onchange="toggleConditionalBlock('other-name', false); updateApplicantData('passportInfo', 'pi_have_other_name', false)">
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Other Name Fields -->
                        <div id="other-name" class="conditional-block" style="display: ${pi.pi_have_other_name ? 'block' : 'none'};">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Other Surname</label>
                                    <input type="text" name="pi_other_sur_name" value="${pi.pi_other_sur_name || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_other_sur_name', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Other Given Name</label>
                                    <input type="text" name="pi_other_given_name" value="${pi.pi_other_given_name || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_other_given_name', this.value)">
                                </div>
                            </div>
                        </div>

                        <!-- Gender and Marital Status -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Gender *</label>
                                <select name="pi_gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_gender', this.value)" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" ${(pi.pi_gender === 'Male') ? 'selected' : ''}>Male</option>
                                    <option value="Female" ${(pi.pi_gender === 'Female') ? 'selected' : ''}>Female</option>
                                    <option value="Other" ${(pi.pi_gender === 'Other') ? 'selected' : ''}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Marital Status *</label>
                                <select name="pi_marital_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_marital_status', this.value)" required>
                                    <option value="">Select Marital Status</option>
                                    <option value="Single" ${(pi.pi_marital_status === 'Single') ? 'selected' : ''}>Single</option>
                                    <option value="Married" ${(pi.pi_marital_status === 'Married') ? 'selected' : ''}>Married</option>
                                    <option value="Divorced" ${(pi.pi_marital_status === 'Divorced') ? 'selected' : ''}>Divorced</option>
                                    <option value="Widowed" ${(pi.pi_marital_status === 'Widowed') ? 'selected' : ''}>Widowed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label class="block text-gray-700 mb-2">Date of Birth *</label>
                            <input type="text" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                value="${pi.pi_dob ? convertToDisplay(pi.pi_dob) : ''}" 
                                onchange="handleDateChange('passportInfo', 'pi_dob', this.value)"
                                placeholder="DD/MM/YYYY"
                                required>
                        </div>

                        <!-- Place and Country of Birth -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Place of Birth *</label>
                                <input type="text" name="pi_pob" value="${pi.pi_pob || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_pob', this.value)" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Country of Birth *</label>
                                <select name="pi_cob" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_cob', this.value)" required>
                                    <option value="">Select Country</option>
                                    ${generateCountryOptions(pi.pi_cob)}
                                </select>
                            </div>
                        </div>

                        <!-- Home Address Section -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Home Address</h3>
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Address Line 1 *</label>
                                    <input type="text" name="pi_address_line_1" value="${ci.pi_address_line_1 || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_address_line_1', this.value)" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Address Line 2</label>
                                    <input type="text" name="pi_address_line_2" value="${ci.pi_address_line_2 || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_address_line_2', this.value)">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">City *</label>
                                        <input type="text" name="pi_address_city" value="${ci.pi_address_city || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_address_city', this.value)" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">State *</label>
                                        <input type="text" name="pi_address_state" value="${ci.pi_address_state || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_address_state', this.value)" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Zip Code *</label>
                                        <input type="text" name="pi_address_zip_code" value="${ci.pi_address_zip_code || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_address_zip_code', this.value)" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Country *</label>
                                    <select name="pi_address_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_address_country', this.value)" required>
                                        <option value="">Select Country</option>
                                        ${generateCountryOptions(ci.pi_address_country)}
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Mailing Address Section -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Mailing Address</h3>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2">Is your mailing address same as home address?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="is_same_mailing_address" value="1" ${ci.is_same_mailing_address ? 'checked' : ''} onchange="toggleConditionalBlock('mailing-address', false); updateApplicantData('contactInfo', 'is_same_mailing_address', true)">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="is_same_mailing_address" value="0" ${!ci.is_same_mailing_address ? 'checked' : ''} onchange="toggleConditionalBlock('mailing-address', true); updateApplicantData('contactInfo', 'is_same_mailing_address', false)">
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>
                            <div id="mailing-address" class="conditional-block" style="display: ${!ci.is_same_mailing_address ? 'block' : 'none'};">
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 1 *</label>
                                        <input type="text" name="pi_mail_address_line_1" value="${ci.pi_mail_address_line_1 || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_mail_address_line_1', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 2</label>
                                        <input type="text" name="pi_mail_address_line_2" value="${ci.pi_mail_address_line_2 || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_mail_address_line_2', this.value)">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">City *</label>
                                            <input type="text" name="pi_mail_address_city" value="${ci.pi_mail_address_city || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_mail_address_city', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">State *</label>
                                            <input type="text" name="pi_mail_address_state" value="${ci.pi_mail_address_state || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_mail_address_state', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Zip Code *</label>
                                            <input type="text" name="pi_mail_address_zip_code" value="${ci.pi_mail_address_zip_code || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_mail_address_zip_code', this.value)">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Country *</label>
                                        <select name="pi_mail_address_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_mail_address_country', this.value)">
                                            <option value="">Select Country</option>
                                            ${generateCountryOptions(ci.pi_mail_address_country)}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Contact Information</h3>
                            
                            <!-- Phone Numbers -->
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-700 mb-3">Phone Numbers</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Primary Phone Number *</label>
                                        <input type="tel" name="pi_primary_no" value="${ci.pi_primary_no || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_primary_no', this.value)" required>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Secondary Phone Number</label>
                                        <input type="tel" name="pi_secondary_no" value="${ci.pi_secondary_no || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_secondary_no', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Work Phone Number</label>
                                        <input type="tel" name="pi_work_no" value="${ci.pi_work_no || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('contactInfo', 'pi_work_no', this.value)">
                                    </div>
                                </div>
                            </div>

                            <!-- Other Phone Numbers (Multi-entry JSON type) -->
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-700 mb-3">Other Phone Numbers</h4>
                                <div id="other-phone-fields">
                                    ${generateOtherPhoneFields(ci.otherPhones || [''])}
                                </div>
                                <button type="button" onclick="addOtherPhoneField()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Add Another Phone Number
                                </button>
                            </div>

                            <!-- Email Addresses (Multi-entry JSON type) -->
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-700 mb-3">Email Addresses</h4>
                                <div id="email-fields">
                                    ${generateEmailFields(ci.emails || [''])}
                                </div>
                                <button type="button" onclick="addEmailField()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Add Another Email
                                </button>
                            </div>

                            <!-- Social Media Section in the main form -->
                            <div class="mb-6">
                                <h4 class="font-medium text-gray-700 mb-3">Social Media Profiles</h4>
                                <div id="social-media-fields">
                                    ${generateSocialMediaFields(ci.socialMedia || [{platform: '', username: ''}])}
                                </div>
                                <button type="button" onclick="addSocialMediaField()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                    <i class="fas fa-plus mr-2"></i> Add Social Media Profile
                                </button>
                            </div>

                            <!-- National ID -->
                            <div>
                                <label class="block text-gray-700 mb-2">National ID</label>
                                <input type="text" name="pi_nid" value="${pi.pi_nid || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_nid', this.value)">
                            </div>
                        </div>

                        <!-- Other Nationality -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Other Nationality</h3>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2">Do you have any other nationality?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pi_have_other_nationality" value="1" ${pi.pi_have_other_nationality ? 'checked' : ''} onchange="toggleConditionalBlock('other-nationality', true); updateApplicantData('passportInfo', 'pi_have_other_nationality', true)">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pi_have_other_nationality" value="0" ${!pi.pi_have_other_nationality ? 'checked' : ''} onchange="toggleConditionalBlock('other-nationality', false); updateApplicantData('passportInfo', 'pi_have_other_nationality', false)">
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>
                            <div id="other-nationality" class="conditional-block" style="display: ${pi.pi_have_other_nationality ? 'block' : 'none'};">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Country</label>
                                        <select name="pi_other_nationality_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_other_nationality_country', this.value)">
                                            <option value="">Select Country</option>
                                            ${generateCountryOptions(pi.pi_other_nationality_country)}
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Do you have that country passport?</label>
                                        <div class="flex space-x-4">
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="pi_have_other_country_paasport" value="1" ${pi.pi_have_other_country_paasport ? 'checked' : ''} onchange="toggleConditionalBlock('other-passport', true); updateApplicantData('passportInfo', 'pi_have_other_country_paasport', true)">
                                                <span class="ml-2">Yes</span>
                                            </label>
                                            <label class="inline-flex items-center">
                                                <input type="radio" name="pi_have_other_country_paasport" value="0" ${!pi.pi_have_other_country_paasport ? 'checked' : ''} onchange="toggleConditionalBlock('other-passport', false); updateApplicantData('passportInfo', 'pi_have_other_country_paasport', false)">
                                                <span class="ml-2">No</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div id="other-passport" class="conditional-block" style="display: ${pi.pi_have_other_country_paasport ? 'block' : 'none'};">
                                        <label class="block text-gray-700 mb-2">Passport Number</label>
                                        <input type="text" name="pi_other_country_passport" value="${pi.pi_other_country_passport || ''}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_other_country_passport', this.value)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Other Permanent Residence -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Other Permanent Residence</h3>
                            <div class="mb-4">
                                <label class="block text-gray-700 mb-2">Do you have any other permanent residence?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pi_have_other_permanent_residence" value="1" ${pi.pi_have_other_permanent_residence ? 'checked' : ''} onchange="toggleConditionalBlock('other-residence', true); updateApplicantData('passportInfo', 'pi_have_other_permanent_residence', true)">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pi_have_other_permanent_residence" value="0" ${!pi.pi_have_other_permanent_residence ? 'checked' : ''} onchange="toggleConditionalBlock('other-residence', false); updateApplicantData('passportInfo', 'pi_have_other_permanent_residence', false)">
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>
                            <div id="other-residence" class="conditional-block" style="display: ${pi.pi_have_other_permanent_residence ? 'block' : 'none'};">
                                <div>
                                    <label class="block text-gray-700 mb-2">Country</label>
                                    <select name="pi_other_permanent_residence_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateApplicantData('passportInfo', 'pi_other_permanent_residence_country', this.value)">
                                        <option value="">Select Country</option>
                                        ${generateCountryOptions(pi.pi_other_permanent_residence_country)}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-4">* Required fields</p>
                    `;
        }

        // Helper function for Other Phone Numbers (multi-entry JSON type)
        function generateOtherPhoneFields(phones) {
            return phones.map((phone, index) => `
                <div class="other-phone-field flex items-center space-x-2 mb-2">
                    <input type="tel" name="pi_other_no[]" value="${phone}" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="Other phone number" 
                        onchange="updateOtherPhoneData(${index}, this.value)">
                    ${index > 0 ? `
                        <button type="button" onclick="removeOtherPhoneField(${index})" class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg">
                            <i class="fas fa-times"></i>
                        </button>
                    ` : ''}
                </div>
            `).join('');
        }

        function addOtherPhoneField() {
            const container = document.getElementById('other-phone-fields');
            const index = container.children.length;
            const newField = document.createElement('div');
            newField.className = 'other-phone-field flex items-center space-x-2 mb-2';
            newField.innerHTML = `
                <input type="tel" name="pi_other_no[]" 
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Other phone number" 
                    onchange="updateOtherPhoneData(${index}, this.value)">
                <button type="button" onclick="removeOtherPhoneField(${index})" class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(newField);
        }

        function removeOtherPhoneField(index) {
            const field = document.querySelector(`.other-phone-field:nth-child(${index + 1})`);
            if (field) {
                field.remove();
                // Update the data structure after removal
                updateOtherPhonesData();
            }
        }

        function updateOtherPhoneData(index, value) {
            if (!currentApplicant.contactInfo.otherPhones) {
                currentApplicant.contactInfo.otherPhones = [];
            }
            currentApplicant.contactInfo.otherPhones[index] = value;
        }

        function updateOtherPhonesData() {
            const inputs = document.querySelectorAll('input[name="pi_other_no[]"]');
            currentApplicant.contactInfo.otherPhones = Array.from(inputs).map(input => input.value);
        }

        // Updated Social Media functions with dropdown
        function generateSocialMediaFields(socialMedias) {
            return socialMedias.map((social, index) => `
                <div class="social-media-field grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Platform *</label>
                        <select name="social_platform[]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateSocialMediaData(${index}, 'platform', this.value)" required>
                            <option value="">Select Platform</option>
                            ${socialMediaPlatforms.map(platform => 
                                `<option value="${platform}" ${(social.platform === platform) ? 'selected' : ''}>${platform}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <div class="flex-1">
                            <label class="block text-gray-700 mb-2">Username/URL *</label>
                            <input type="text" name="social_username[]" value="${social.username || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Username or URL" 
                                onchange="updateSocialMediaData(${index}, 'username', this.value)"
                                required>
                        </div>
                        ${index > 0 ? `
                            <button type="button" onclick="removeSocialMediaField(${index})" class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg mb-2">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }

        function addSocialMediaField() {
            const container = document.getElementById('social-media-fields');
            const index = container.children.length;
            const newField = document.createElement('div');
            newField.className = 'social-media-field grid grid-cols-1 md:grid-cols-2 gap-4 mb-4';
            newField.innerHTML = `
                <div>
                    <label class="block text-gray-700 mb-2">Platform *</label>
                    <select name="social_platform[]" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="updateSocialMediaData(${index}, 'platform', this.value)" required>
                        <option value="">Select Platform</option>
                        ${socialMediaPlatforms.map(platform => 
                            `<option value="${platform}">${platform}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <div class="flex-1">
                        <label class="block text-gray-700 mb-2">Username/URL *</label>
                        <input type="text" name="social_username[]" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Username or URL" 
                            onchange="updateSocialMediaData(${index}, 'username', this.value)"
                            required>
                    </div>
                    <button type="button" onclick="removeSocialMediaField(${index})" class="bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg mb-2">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(newField);
        }

        function removeSocialMediaField(index) {
            const field = document.querySelector(`.social-media-field:nth-child(${index + 1})`);
            if (field) {
                field.remove();
                updateSocialMediaData();
            }
        }

        function updateSocialMediaData(index, field, value) {
            if (!currentApplicant.contactInfo.socialMedia) {
                currentApplicant.contactInfo.socialMedia = [];
            }
            if (!currentApplicant.contactInfo.socialMedia[index]) {
                currentApplicant.contactInfo.socialMedia[index] = {
                    platform: '',
                    username: ''
                };
            }
            if (field) {
                currentApplicant.contactInfo.socialMedia[index][field] = value;
            }
        }

        // Travel Information Step (Based on Excel TI section)
        function generateTravelInfoStep(applicant) {
            const ti = applicant.travelInfo || {};
            const locations = ti.locations || [{
                address_line_1: '',
                address_line_2: '',
                city: '',
                state: '',
                zip_code: ''
            }];

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Purpose of Travel *</label>
                        <input type="text" name="ti_travel_purpose" 
                            value="${ti.ti_travel_purpose || ''}" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateApplicantData('travelInfo', 'ti_travel_purpose', this.value)" required>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you made travel plans?</label>
                        <select name="ti_have_travel_plan" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="handleTravelPlanChange(this.value); updateApplicantData('travelInfo', 'ti_have_travel_plan', this.value)">
                            <option value="">Select</option>
                            <option value="yes" ${(ti.ti_have_travel_plan === 'yes') ? 'selected' : ''}>Yes</option>
                            <option value="no" ${(ti.ti_have_travel_plan === 'no') ? 'selected' : ''}>No</option>
                        </select>
                    </div>

                    <!-- No Travel Plan Fields -->
                    <div id="no-travel-plan" class="conditional-block" style="display: ${ti.ti_have_travel_plan === 'no' ? 'block' : 'none'};">
                        <!-- ... no travel plan fields content ... -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Intended date of arrival</label>
                                <input type="text" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    value="${ti.ti_intended_arrival_date ? convertToDisplay(ti.ti_intended_arrival_date) : ''}" 
                                    onchange="handleDateChange('travelInfo', 'ti_intended_arrival_date', this.value)"
                                    placeholder="DD/MM/YYYY"
                                    required>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Length of stay</label>
                                <input type="text" name="ti_stay_length" 
                                    value="${ti.ti_stay_length || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_stay_length', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Number + Select Option (Day, Month, Year)</label>
                                <select name="ti_length_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_length_type', this.value)">
                                    <option value="">Select</option>
                                    <option value="Days" ${(ti.ti_length_type === 'Days') ? 'selected' : ''}>Days</option>
                                    <option value="Months" ${(ti.ti_length_type === 'Months') ? 'selected' : ''}>Months</option>
                                    <option value="Years" ${(ti.ti_length_type === 'Years') ? 'selected' : ''}>Years</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Yes Travel Plan Fields -->
                    <div id="yes-travel-plan" class="conditional-block" style="display: ${ti.ti_have_travel_plan === 'yes' ? 'block' : 'none'};">
                        <!-- ... yes travel plan fields content ... -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Date of Arrival in the USA</label>
                                <input type="text" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    value="${ti.ti_arrival_date ? convertToDisplay(ti.ti_arrival_date) : ''}" 
                                    onchange="handleDateChange('travelInfo', 'ti_arrival_date', this.value)"
                                    placeholder="DD/MM/YYYY"
                                    required>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Arrival Flight Number</label>
                                <input type="text" name="ti_arrival_flight_no" 
                                    value="${ti.ti_arrival_flight_no || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_arrival_flight_no', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Arrival City</label>
                                <input type="text" name="ti_arrival_city" 
                                    value="${ti.ti_arrival_city || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_arrival_city', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Date of Departure</label>
                                <input type="text" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    value="${ti.ti_departure_date ? convertToDisplay(ti.ti_departure_date) : ''}" 
                                    onchange="handleDateChange('travelInfo', 'ti_departure_date', this.value)"
                                    placeholder="DD/MM/YYYY"
                                    required>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Departure Flight Number</label>
                                <input type="text" name="ti_departure_flight_no" 
                                    value="${ti.ti_departure_flight_no || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_departure_flight_no', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Departure City</label>
                                <input type="text" name="ti_departure_city" 
                                    value="${ti.ti_departure_city || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_departure_city', this.value)">
                            </div>
                        </div>
                    </div>

                    <!-- Locations to Visit -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Locations You Plan to Visit</h3>
                        <div id="location-fields">
                            ${generateLocationFields(locations)}
                        </div>
                        <button type="button" onclick="addLocationField()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Another Location
                        </button>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Who is paying for your trip?</label>
                        <select name="trip_payment" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="handlePaymentChange(this.value); updateApplicantData('travelInfo', 'trip_payment', this.value)">
                            <option value="">Select</option>
                            <option value="Self" ${(ti.trip_payment === 'Self') ? 'selected' : ''}>Self</option>
                            <option value="Other person" ${(ti.trip_payment === 'Other person') ? 'selected' : ''}>Other person</option>
                            <option value="Present employer" ${(ti.trip_payment === 'Present employer') ? 'selected' : ''}>Present employer</option>
                            <option value="Employer in the USA" ${(ti.trip_payment === 'Employer in the USA') ? 'selected' : ''}>Employer in the USA</option>
                            <option value="Other Company" ${(ti.trip_payment === 'Other Company') ? 'selected' : ''}>Other Company</option>
                        </select>
                    </div>

                    <!-- Payment Fields for Other Person / Others- -->
                    <div id="other-person-payment" class="conditional-block" style="display: ${(ti.trip_payment === 'Other person') ? 'block' : 'none'};">
                        <!-- ... other person payment fields content ... -->
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Paying Person Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Surname of Paying Person</label>
                                <input type="text" name="trip_paying_person_surname" 
                                    value="${ti.trip_paying_person_surname || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'trip_paying_person_surname', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Given Name of Paying Person</label>
                                <input type="text" name="ti_trip_paying_person_given_name" 
                                    value="${ti.ti_trip_paying_person_given_name || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_given_name', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Telephone of Paying Person</label>
                                <input type="tel" name="ti_trip_paying_person_telephone" 
                                    value="${ti.ti_trip_paying_person_telephone || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_telephone', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Email of Paying Person</label>
                                <input type="email" name="ti_trip_paying_person_email" 
                                    value="${ti.ti_trip_paying_person_email || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_email', this.value)">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-gray-700 mb-2">Relationship to Paying Person</label>
                                <input type="text" name="_trip_paying_person_relationship" 
                                    value="${ti._trip_paying_person_relationship || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelInfo', '_trip_paying_person_relationship', this.value)">
                            </div>
                        </div>


                        <!-- Address Toggle for Paying Person -->
                        <div class="mt-4">
                            <label class="block text-gray-700 mb-2">Is the address of paying person same as yours?</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="trip_paying_person_have_same_address" value="1" ${ti.trip_paying_person_have_same_address ? 'checked' : ''} onchange="toggleConditionalBlock('paying-person-address', false); updateApplicantData('travelInfo', 'trip_paying_person_have_same_address', true)">
                                    <span class="ml-2">Yes</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="trip_paying_person_have_same_address" value="0" ${!ti.trip_paying_person_have_same_address ? 'checked' : ''} onchange="toggleConditionalBlock('paying-person-address', true); updateApplicantData('travelInfo', 'trip_paying_person_have_same_address', false)">
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>

                        <!-- Conditional Address Block for Paying Person -->
                        <div id="paying-person-address" class="conditional-block mt-4" style="display: ${!ti.trip_paying_person_have_same_address ? 'block' : 'none'};">
                            <!-- ... paying person address fields ... -->
                            <div class="border-t pt-6">
                                <!-- Conditional Address Block for Paying Person -->
                                <div id="paying-person-address" class="conditional-block mt-4" style="display: ${!ti.trip_paying_person_have_same_address ? 'block' : 'none'};">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">Address Line 1</label>
                                            <input type="text" name="ti_trip_paying_person_address_line_1" 
                                                value="${ti.ti_trip_paying_person_address_line_1 || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_line_1', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Address Line 2</label>
                                            <input type="text" name="ti_trip_paying_person_address_line_2" 
                                                value="${ti.ti_trip_paying_person_address_line_2 || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_line_2', this.value)">
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-gray-700 mb-2">City</label>
                                                <input type="text" name="ti_trip_paying_person_address_city" 
                                                    value="${ti.ti_trip_paying_person_address_city || ''}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_city', this.value)">
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 mb-2">State</label>
                                                <input type="text" name="ti_trip_paying_person_address_state" 
                                                    value="${ti.ti_trip_paying_person_address_state || ''}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_state', this.value)">
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 mb-2">Zip Code</label>
                                                <input type="text" name="ti_trip_paying_person_address_zip_code" 
                                                    value="${ti.ti_trip_paying_person_address_zip_code || ''}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateApplicantData('travelInfo', 'ti_trip_paying_person_address_zip_code', this.value)">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Country</label>
                                            <select name="trip_paying_person_address_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateApplicantData('travelInfo', 'trip_paying_person_address_country', this.value)">
                                                <option value="">Select Country</option>
                                                ${countries.map(country => 
                                                    `<option value="${country.code}" ${(ti.trip_paying_person_address_country === country.code) ? 'selected' : ''}>${country.name}</option>`
                                                ).join('')}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Fields for Other Company -->
                    <div id="other-company-payment" class="conditional-block" style="display: ${ti.trip_payment === 'Other Company' ? 'block' : 'none'};">
                        <!-- ... other company payment fields content ... -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Other Company Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Name</label>
                                    <input type="text" name="ti_trip_paying_company_name" 
                                        value="${ti.ti_trip_paying_company_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_name', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Telephone</label>
                                    <input type="tel" name="ti_trip_paying_company_telephone" 
                                        value="${ti.ti_trip_paying_company_telephone || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_telephone', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Email</label>
                                    <input type="email" name="ti_trip_paying_company_email" 
                                        value="${ti.ti_trip_paying_company_email || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_email', this.value)">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Relationship to Company</label>
                                    <input type="text" name="ti_trip_paying_company_relationship" 
                                        value="${ti.ti_trip_paying_company_relationship || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_relationship', this.value)">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 mt-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Address Line 1</label>
                                    <input type="text" name="ti_trip_paying_company_address_line_1" 
                                        value="${ti.ti_trip_paying_company_address_line_1 || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_line_1', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Address Line 2</label>
                                    <input type="text" name="ti_trip_paying_company_address_line_2" 
                                        value="${ti.ti_trip_paying_company_address_line_2 || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_line_2', this.value)">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">City</label>
                                        <input type="text" name="ti_trip_paying_company_address_city" 
                                            value="${ti.ti_trip_paying_company_address_city || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_city', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">State</label>
                                        <input type="text" name="ti_trip_paying_company_address_state" 
                                            value="${ti.ti_trip_paying_company_address_state || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_state', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Zip Code</label>
                                        <input type="text" name="ti_trip_paying_company_address_zip_code" 
                                            value="${ti.ti_trip_paying_company_address_zip_code || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_zip_code', this.value)">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Country</label>
                                    <select name="ti_trip_paying_company_address_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'ti_trip_paying_company_address_country', this.value)">
                                        <option value="">Select Country</option>
                                        ${countries.map(country => 
                                            `<option value="${country.code}" ${(ti.ti_trip_paying_company_address_country === country.code) ? 'selected' : ''}>${country.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Simplified Helper Functions using toggleConditionalBlock
        function handleTravelPlanChange(value) {
            toggleConditionalBlock('no-travel-plan', value === 'no');
            toggleConditionalBlock('yes-travel-plan', value === 'yes');
        }

        function handlePaymentChange(value) {
            const showOtherPerson = (value === 'Other person' || value === 'Others-');
            const showOtherCompany = (value === 'Other Company');

            toggleConditionalBlock('other-person-payment', showOtherPerson);
            toggleConditionalBlock('other-company-payment', showOtherCompany);
        }

        // Initialize function to set initial states
        function initializeTravelStep() {
            const travelPlanSelect = document.querySelector('select[name="ti_have_travel_plan"]');
            const paymentSelect = document.querySelector('select[name="trip_payment"]');

            if (travelPlanSelect) {
                handleTravelPlanChange(travelPlanSelect.value);
            }
            if (paymentSelect) {
                handlePaymentChange(paymentSelect.value);
            }
        }

        // Call initialize when the step is loaded
        // You can call this function after generating the travel info step

        // Passport Information Step (Based on Excel PP section)
        function generatePassportInfoStep(applicant) {
            const pp = applicant.passportInfo || {};

            return `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Passport Type *</label>
                            <select name="pp_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('passportInfo', 'pp_type', this.value)" required>
                                <option value="">Select Type</option>
                                <option value="Regular" ${(pp.pp_type === 'Regular') ? 'selected' : ''}>Regular</option>
                                <option value="Official" ${(pp.pp_type === 'Official') ? 'selected' : ''}>Official</option>
                                <option value="Diplomatic" ${(pp.pp_type === 'Diplomatic') ? 'selected' : ''}>Diplomatic</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Passport Number *</label>
                            <input type="text" name="pp_number" 
                                value="${pp.pp_number || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateApplicantData('passportInfo', 'pp_number', this.value)" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Passport Issued Date *</label>
                            <input type="text" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                value="${pp.pp_issue_date ? convertToDisplay(pp.pp_issue_date) : ''}" 
                                onchange="handleDateChange('passportInfo', 'pp_issue_date', this.value)"
                                placeholder="DD/MM/YYYY"
                                required>
                        </div>
                        <div>
                        <label class="block text-gray-700 mb-2">Passport Expiry Date *</label>
                            <input type="text" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                value="${pp.pp_expiry_date ? convertToDisplay(pp.pp_expiry_date) : ''}" 
                                onchange="handleDateChange('passportInfo', 'pp_expiry_date', this.value)"
                                placeholder="DD/MM/YYYY"
                                required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Passport Issuing Authority *</label>
                            <input type="text" name="pp_issuing_authority" 
                                value="${pp.pp_issuing_authority || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateApplicantData('passportInfo', 'pp_issuing_authority', this.value)" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Passport Issued City *</label>
                            <input type="text" name="pp_issued_city" 
                                value="${pp.pp_issued_city || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateApplicantData('passportInfo', 'pp_issued_city', this.value)" required>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you ever lost or had your passport stolen?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pp_have_stolen" value="1" 
                                    ${pp.pp_have_stolen ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('lost-passport', true); updateApplicantData('passportInfo', 'pp_have_stolen', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pp_have_stolen" value="0" 
                                    ${!pp.pp_have_stolen ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('lost-passport', false); updateApplicantData('passportInfo', 'pp_have_stolen', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="lost-passport" class="conditional-block" style="display: ${pp.pp_have_stolen ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Passport Number</label>
                                <input type="text" name="pp_lost_passport_no" 
                                    value="${pp.pp_lost_passport_no || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('passportInfo', 'pp_lost_passport_no', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Issuing Authority</label>
                                <input type="text" name="pp_lost_passport_authority" 
                                    value="${pp.pp_lost_passport_authority || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('passportInfo', 'pp_lost_passport_authority', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Explanation</label>
                                <textarea name="pp_lost_passport_explanation" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('passportInfo', 'pp_lost_passport_explanation', this.value)">${pp.pp_lost_passport_explanation || ''}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Travel Companion Information Step (Based on Excel TCI section)
        function generateTravelCompanionStep(applicant) {
            const tci = applicant.travelInfo || {};

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Are you traveling with anyone?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="tci_have_anyone" value="1" 
                                    ${tci.tci_have_anyone ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('travel-companion', true); updateApplicantData('travelInfo', 'tci_have_anyone', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="tci_have_anyone" value="0" 
                                    ${!tci.tci_have_anyone ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('travel-companion', false); updateApplicantData('travelInfo', 'tci_have_anyone', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="travel-companion" class="conditional-block" style="display: ${tci.tci_have_anyone ? 'block' : 'none'};">
                        <div class="space-y-6">
                            <h4 class="text-lg font-medium text-gray-800">Travel Companion Details</h4>
                            
                            <!-- Companion Details (Multiple) -->
                            <div class="dynamic-field-group">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Surname</label>
                                        <input type="text" name="tci_surname" 
                                            value="${tci.tci_surname || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'tci_surname', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Given name</label>
                                        <input type="text" name="tci_given_name" 
                                            value="${tci.tci_given_name || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'tci_given_name', this.value)">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-gray-700 mb-2">Relationship to You</label>
                                        <input type="text" name="tci_relationship" 
                                            value="${tci.tci_relationship || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('travelInfo', 'tci_relationship', this.value)">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 mb-2">Are you traveling as part of a group?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="tci_have_group" value="1" 
                                            ${tci.tci_have_group ? 'checked' : ''}
                                            onchange="toggleConditionalBlock('group-travel', true); updateApplicantData('travelInfo', 'tci_have_group', true)">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="tci_have_group" value="0" 
                                            ${!tci.tci_have_group ? 'checked' : ''}
                                            onchange="toggleConditionalBlock('group-travel', false); updateApplicantData('travelInfo', 'tci_have_group', false)">
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>

                            <div id="group-travel" class="conditional-block" style="display: ${tci.tci_have_group ? 'block' : 'none'};">
                                <div>
                                    <label class="block text-gray-700 mb-2">Group Name</label>
                                    <input type="text" name="tci_group_name" 
                                        value="${tci.tci_group_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelInfo', 'tci_group_name', this.value)">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Previous U.S. Travel Step (Based on Excel PUST section)
        function generatePreviousTravelStep(applicant) {
            const pust = applicant.travelHistory || {};
            const previousTravels = pust.previousTravels || [{
                arrival_date: '',
                staying_length: ''
            }];
            const driverLicenses = pust.driverLicenses || [{
                license_no: '',
                state: ''
            }];

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Have you ever issued a visa to the USA?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ever_issued" value="1" 
                                    ${pust.pust_have_ever_issued ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-visa', true); updateApplicantData('travelHistory', 'pust_have_ever_issued', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ever_issued" value="0" 
                                    ${!pust.pust_have_ever_issued ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-visa', false); updateApplicantData('travelHistory', 'pust_have_ever_issued', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="previous-visa" class="conditional-block" style="display: ${pust.pust_have_ever_issued ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Date Last Issued Visa</label>
                                    <input type="text" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                        value="${pust.pust_last_issued_visa_date ? convertToDisplay(pust.pust_last_issued_visa_date) : ''}" 
                                        onchange="handleDateChange('travelHistory', 'pust_last_issued_visa_date', this.value)"
                                        placeholder="DD/MM/YYYY"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Visa Number</label>
                                    <input type="text" name="pust_visa_no" 
                                        value="${pust.pust_visa_no || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('travelHistory', 'pust_visa_no', this.value)">
                                </div>
                            </div>
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="pust_remember_visa_no" 
                                        ${pust.pust_remember_visa_no ? 'checked' : ''}
                                        onchange="updateApplicantData('travelHistory', 'pust_remember_visa_no', this.checked)">
                                    <span class="ml-2">Do not know visa number</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Are you applying for the same visa type?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_applied_same_visa" value="1" 
                                    ${pust.pust_have_applied_same_visa ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_applied_same_visa', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_applied_same_visa" value="0" 
                                    ${!pust.pust_have_applied_same_visa ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_applied_same_visa', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Are you applying in the same country?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_applied_same_country" value="1" 
                                    ${pust.pust_have_applied_same_country ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_applied_same_country', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_applied_same_country" value="0" 
                                    ${!pust.pust_have_applied_same_country ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_applied_same_country', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <!-- Missing Toggle Fields -->
                    <div>
                        <label class="block text-gray-700 mb-2">Have you traveled to the USA before?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_travelled_before" value="1" 
                                    ${pust.pust_have_travelled_before ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-travel-details', true); updateApplicantData('travelHistory', 'pust_have_travelled_before', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_travelled_before" value="0" 
                                    ${!pust.pust_have_travelled_before ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-travel-details', false); updateApplicantData('travelHistory', 'pust_have_travelled_before', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <!-- Multi-Entry Previous Travel Details -->
                    <div id="previous-travel-details" class="conditional-block" style="display: ${pust.pust_have_travelled_before ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">Previous Travel Details</h4>
                            <div id="previous-travel-fields">
                                ${generatePreviousTravelFields(previousTravels)}
                            </div>
                            <button type="button" onclick="addPreviousTravelField()" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Another Previous Travel
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Do you have a U.S. Social Security Number?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_social_security_no" value="1" 
                                    ${pust.pust_have_social_security_no ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('social-security-field', true); updateApplicantData('travelHistory', 'pust_have_social_security_no', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_social_security_no" value="0" 
                                    ${!pust.pust_have_social_security_no ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('social-security-field', false); updateApplicantData('travelHistory', 'pust_have_social_security_no', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="social-security-field" class="conditional-block" style="display: ${pust.pust_have_social_security_no ? 'block' : 'none'};">
                        <div>
                            <label class="block text-gray-700 mb-2">U.S. Social Security Number</label>
                            <input type="text" name="pust_social_security_no" 
                                value="${pust.pust_social_security_no || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateApplicantData('travelHistory', 'pust_social_security_no', this.value)">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Do you have a U.S. Taxpayer Identification Number?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_us_tin" value="1" 
                                    ${pust.pust_have_us_tin ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('tin-field', true); updateApplicantData('travelHistory', 'pust_have_us_tin', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_us_tin" value="0" 
                                    ${!pust.pust_have_us_tin ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('tin-field', false); updateApplicantData('travelHistory', 'pust_have_us_tin', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="tin-field" class="conditional-block" style="display: ${pust.pust_have_us_tin ? 'block' : 'none'};">
                        <div>
                            <label class="block text-gray-700 mb-2">U.S. Taxpayer Identification Number</label>
                            <input type="text" name="pust_us_tin" 
                                value="${pust.pust_us_tin || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateApplicantData('travelHistory', 'pust_us_tin', this.value)">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Do you ever hold a U.S. Driver License?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_us_driving_license" value="1" 
                                    ${pust.pust_have_us_driving_license ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('driver-license-section', true); updateApplicantData('travelHistory', 'pust_have_us_driving_license', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_us_driving_license" value="0" 
                                    ${!pust.pust_have_us_driving_license ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('driver-license-section', false); updateApplicantData('travelHistory', 'pust_have_us_driving_license', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="driver-license-section" class="conditional-block" style="display: ${pust.pust_have_us_driving_license ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">U.S. Driver License Details</h4>
                            <div id="driver-license-fields">
                                ${generateDriverLicenseFields(driverLicenses)}
                            </div>
                            <button type="button" onclick="addDriverLicenseField()" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Another Driver License
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you been ten fingerprinted?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ten_fingerprint" value="1" 
                                    ${pust.pust_have_ten_fingerprint ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_ten_fingerprint', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ten_fingerprint" value="0" 
                                    ${!pust.pust_have_ten_fingerprint ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_ten_fingerprint', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you ever been refused a visa to the USA?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_refused_us_visa" value="1" 
                                    ${pust.pust_have_refused_us_visa ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('visa-refusal-explain', true); updateApplicantData('travelHistory', 'pust_have_refused_us_visa', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_refused_us_visa" value="0" 
                                    ${!pust.pust_have_refused_us_visa ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('visa-refusal-explain', false); updateApplicantData('travelHistory', 'pust_have_refused_us_visa', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="visa-refusal-explain" class="conditional-block" style="display: ${pust.pust_have_refused_us_visa ? 'block' : 'none'};">
                        <div>
                            <label class="block text-gray-700 mb-2">Explain Visa Refusal</label>
                            <textarea name="pust_visa_refusal_explain" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('travelHistory', 'pust_visa_refusal_explain', this.value)">${pust.pust_visa_refusal_explain || ''}</textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Are you or have you ever been in the USA as a legal permanent resident?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_legal_permanent_resident" value="1" 
                                    ${pust.pust_have_legal_permanent_resident ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_legal_permanent_resident', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_legal_permanent_resident" value="0" 
                                    ${!pust.pust_have_legal_permanent_resident ? 'checked' : ''}
                                    onchange="updateApplicantData('travelHistory', 'pust_have_legal_permanent_resident', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <!-- Complex Conditional Logic -->
                    <div id="complex-visa-questions" class="conditional-block" style="display: ${(pust.pust_have_ever_issued && pust.pust_have_travelled_before) ? 'block' : 'none'};">
                        <div class="space-y-6 border-t pt-6">
                            <h4 class="text-lg font-medium text-gray-800">Additional Visa Information</h4>
                            
                            <div>
                                <label class="block text-gray-700 mb-2">Have your U.S. visa ever been lost or stolen?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pust_have_us_visa_lost" value="1" 
                                            ${pust.pust_have_us_visa_lost ? 'checked' : ''}
                                            onchange="updateApplicantData('travelHistory', 'pust_have_us_visa_lost', true)">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pust_have_us_visa_lost" value="0" 
                                            ${!pust.pust_have_us_visa_lost ? 'checked' : ''}
                                            onchange="updateApplicantData('travelHistory', 'pust_have_us_visa_lost', false)">
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-gray-700 mb-2">Have your U.S. visa ever been cancelled or revoked?</label>
                                <div class="flex space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pust_have_us_visa_cancelled" value="1" 
                                            ${pust.pust_have_us_visa_cancelled ? 'checked' : ''}
                                            onchange="updateApplicantData('travelHistory', 'pust_have_us_visa_cancelled', true)">
                                        <span class="ml-2">Yes</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="pust_have_us_visa_cancelled" value="0" 
                                            ${!pust.pust_have_us_visa_cancelled ? 'checked' : ''}
                                            onchange="updateApplicantData('travelHistory', 'pust_have_us_visa_cancelled', false)">
                                        <span class="ml-2">No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Helper Functions for Multi-Entry Sections
        function generatePreviousTravelFields(travels) {
            return travels.map((travel, index) => `
                <div class="previous-travel-field border-b pb-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Date of Arrival</label>
                            <input type="text" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                value="${travel.arrival_date ? convertToDisplay(travel.arrival_date) : ''}" 
                                onchange="handleDateChange(${index}, 'arrival_date', this.value)"
                                placeholder="DD/MM/YYYY"
                                required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Length of Stay</label>
                            <input type="text" name="pust_previous_staying_length" 
                                value="${travel.staying_length || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousTravelData(${index}, 'staying_length', this.value)">
                        </div>
                    </div>
                    ${index > 0 ? `
                        <button type="button" onclick="removePreviousTravelField(${index})" class="mt-2 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-times mr-2"></i> Remove Travel
                        </button>
                    ` : ''}
                </div>
            `).join('');
        }

        function addPreviousTravelField() {
            const container = document.getElementById('previous-travel-fields');
            const index = container.children.length;
            const newField = document.createElement('div');
            newField.className = 'previous-travel-field border-b pb-4 mb-4';
            newField.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Date of Arrival</label>
                        <input type="date" name="pust_arrival_date" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updatePreviousTravelData(${index}, 'arrival_date', this.value)">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Length of Stay</label>
                        <input type="text" name="pust_previous_staying_length" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updatePreviousTravelData(${index}, 'staying_length', this.value)">
                    </div>
                </div>
                <button type="button" onclick="removePreviousTravelField(${index})" class="mt-2 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-times mr-2"></i> Remove Travel
                </button>
            `;
            container.appendChild(newField);
        }

        function removePreviousTravelField(index) {
            const field = document.querySelector(`.previous-travel-field:nth-child(${index + 1})`);
            if (field) {
                field.remove();
                updatePreviousTravelsData();
            }
        }

        function updatePreviousTravelData(index, field, value) {
            if (!currentApplicant.travelHistory.previousTravels) {
                currentApplicant.travelHistory.previousTravels = [];
            }
            if (!currentApplicant.travelHistory.previousTravels[index]) {
                currentApplicant.travelHistory.previousTravels[index] = {};
            }
            currentApplicant.travelHistory.previousTravels[index][field] = value;
        }

        function updatePreviousTravelsData() {
            const travelFields = document.querySelectorAll('.previous-travel-field');
            currentApplicant.travelHistory.previousTravels = Array.from(travelFields).map((field, index) => {
                return {
                    arrival_date: field.querySelector('input[name="pust_arrival_date"]')?.value || '',
                    staying_length: field.querySelector('input[name="pust_previous_staying_length"]')?.value || ''
                };
            });
        }

        function generateDriverLicenseFields(licenses) {
            return licenses.map((license, index) => `
                <div class="driver-license-field border-b pb-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">License Number</label>
                            <input type="text" name="pust_driving_license_no" 
                                value="${license.license_no || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateDriverLicenseData(${index}, 'license_no', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">State</label>
                            <input type="text" name="pust_driving_license_state" 
                                value="${license.state || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateDriverLicenseData(${index}, 'state', this.value)">
                        </div>
                    </div>
                    ${index > 0 ? `
                        <button type="button" onclick="removeDriverLicenseField(${index})" class="mt-2 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-times mr-2"></i> Remove License
                        </button>
                    ` : ''}
                </div>
            `).join('');
        }

        function addDriverLicenseField() {
            const container = document.getElementById('driver-license-fields');
            const index = container.children.length;
            const newField = document.createElement('div');
            newField.className = 'driver-license-field border-b pb-4 mb-4';
            newField.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">License Number</label>
                        <input type="text" name="pust_driving_license_no" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateDriverLicenseData(${index}, 'license_no', this.value)">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">State</label>
                        <input type="text" name="pust_driving_license_state" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateDriverLicenseData(${index}, 'state', this.value)">
                    </div>
                </div>
                <button type="button" onclick="removeDriverLicenseField(${index})" class="mt-2 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                    <i class="fas fa-times mr-2"></i> Remove License
                </button>
            `;
            container.appendChild(newField);
        }

        function removeDriverLicenseField(index) {
            const field = document.querySelector(`.driver-license-field:nth-child(${index + 1})`);
            if (field) {
                field.remove();
                updateDriverLicensesData();
            }
        }

        function updateDriverLicenseData(index, field, value) {
            if (!currentApplicant.travelHistory.driverLicenses) {
                currentApplicant.travelHistory.driverLicenses = [];
            }
            if (!currentApplicant.travelHistory.driverLicenses[index]) {
                currentApplicant.travelHistory.driverLicenses[index] = {};
            }
            currentApplicant.travelHistory.driverLicenses[index][field] = value;
        }

        function updateDriverLicensesData() {
            const licenseFields = document.querySelectorAll('.driver-license-field');
            currentApplicant.travelHistory.driverLicenses = Array.from(licenseFields).map((field, index) => {
                return {
                    license_no: field.querySelector('input[name="pust_driving_license_no"]')?.value || '',
                    state: field.querySelector('input[name="pust_driving_license_state"]')?.value || ''
                };
            });
        }

        // U.S. Contact Information Step (Based on Excel USCI section)
        function generateUSContactStep(applicant) {
            const usci = applicant.usContactInfo || {};

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Contact Type</label>
                        <select name="usci_contact_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="toggleContactTypeFields(this.value); updateApplicantData('usContactInfo', 'usci_contact_type', this.value)">
                            <option value="">Select Type</option>
                            <option value="Person" ${(usci.usci_contact_type === 'Person') ? 'selected' : ''}>Person</option>
                            <option value="Company" ${(usci.usci_contact_type === 'Company') ? 'selected' : ''}>Company</option>
                            <option value="Hotel" ${(usci.usci_contact_type === 'Hotel') ? 'selected' : ''}>Hotel</option>
                        </select>
                    </div>

                    <!-- Person Contact -->
                    <div id="person-contact" class="conditional-block" style="display: ${usci.usci_contact_type === 'Person' ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">Person Contact Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Contact Person Surname</label>
                                    <input type="text" name="usci_contact_person_surname" 
                                        value="${usci.usci_contact_person_surname || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci_contact_person_surname', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Contact Person Given Name</label>
                                    <input type="text" name="usci_contact_person_given_name" 
                                        value="${usci.usci_contact_person_given_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci_contact_person_given_name', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Contact Person Telephone</label>
                                    <input type="tel" name="usci contact person telephone" 
                                        value="${usci['usci contact person telephone'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact person telephone', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Contact Person Email</label>
                                    <input type="email" name="usci contact person email" 
                                        value="${usci['usci contact person email'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact person email', this.value)">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Contact Person Relationship</label>
                                    <input type="text" name="usci contact person relationship" 
                                        value="${usci['usci contact person relationship'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact person relationship', this.value)">
                                </div>
                            </div>
                            
                            <!-- Person Address Block -->
                            <div class="border-t pt-4">
                                <h5 class="text-md font-medium text-gray-700 mb-3">Contact Person Address</h5>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 1</label>
                                        <input type="text" name="usci contact person address line 1" 
                                            value="${usci['usci contact person address line 1'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact person address line 1', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 2</label>
                                        <input type="text" name="usci contact person address line 2" 
                                            value="${usci['usci contact person address line 2'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact person address line 2', this.value)">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">City</label>
                                            <input type="text" name="usci contact person address city" 
                                                value="${usci['usci contact person address city'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact person address city', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">State</label>
                                            <input type="text" name="usci contact person address state" 
                                                value="${usci['usci contact person address state'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact person address state', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Zip Code</label>
                                            <input type="text" name="usci contact person address zip code" 
                                                value="${usci['usci contact person address zip code'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact person address zip code', this.value)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Contact -->
                    <div id="company-contact" class="conditional-block" style="display: ${usci.usci_contact_type === 'Company' ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">Company Contact Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Surname</label>
                                    <input type="text" name="usci_contact_company_name" 
                                        value="${usci.usci_contact_company_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci_contact_company_name', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Telephone</label>
                                    <input type="tel" name="usci contact company telephone" 
                                        value="${usci['usci contact company telephone'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact company telephone', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Company Email</label>
                                    <input type="email" name="usci contact company email" 
                                        value="${usci['usci contact company email'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact company email', this.value)">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Company Relationship</label>
                                    <input type="text" name="usci contact company relationship" 
                                        value="${usci['usci contact company relationship'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact company relationship', this.value)">
                                </div>
                            </div>
                            
                            <!-- Company Address Block -->
                            <div class="border-t pt-4">
                                <h5 class="text-md font-medium text-gray-700 mb-3">Company Address</h5>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 1</label>
                                        <input type="text" name="usci contact company address line 1" 
                                            value="${usci['usci contact company address line 1'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact company address line 1', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 2</label>
                                        <input type="text" name="usci contact company address line 2" 
                                            value="${usci['usci contact company address line 2'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact company address line 2', this.value)">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">City</label>
                                            <input type="text" name="usci contact company address city" 
                                                value="${usci['usci contact company address city'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address city', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">State</label>
                                            <input type="text" name="usci contact company address state" 
                                                value="${usci['usci contact company address state'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address state', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Zip Code</label>
                                            <input type="text" name="usci contact company address zip code" 
                                                value="${usci['usci contact company address zip code'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address zip code', this.value)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hotel Contact -->
                    <div id="hotel-contact" class="conditional-block" style="display: ${usci.usci_contact_type === 'Hotel' ? 'block' : 'none'};">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-800">Hotel Contact Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Hotel Surname</label>
                                    <input type="text" name="usci_contact_hotel_name" 
                                        value="${usci.usci_contact_hotel_name || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci_contact_hotel_name', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Hotel Telephone</label>
                                    <input type="tel" name="usci contact hotel telephone" 
                                        value="${usci['usci contact hotel telephone'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact hotel telephone', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Hotel Email</label>
                                    <input type="email" name="usci contact hotel email" 
                                        value="${usci['usci contact hotel email'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact hotel email', this.value)">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Hotel Relationship</label>
                                    <input type="text" name="usci contact hotel relationship" 
                                        value="${usci['usci contact hotel relationship'] || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('usContactInfo', 'usci contact hotel relationship', this.value)">
                                </div>
                            </div>
                            
                            <!-- Hotel Address Block (using same input names as Company) -->
                            <div class="border-t pt-4">
                                <h5 class="text-md font-medium text-gray-700 mb-3">Hotel Address</h5>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 1</label>
                                        <input type="text" name="usci contact company address line 1" 
                                            value="${usci['usci contact company address line 1'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact company address line 1', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Address Line 2</label>
                                        <input type="text" name="usci contact company address line 2" 
                                            value="${usci['usci contact company address line 2'] || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('usContactInfo', 'usci contact company address line 2', this.value)">
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">City</label>
                                            <input type="text" name="usci contact company address city" 
                                                value="${usci['usci contact company address city'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address city', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">State</label>
                                            <input type="text" name="usci contact company address state" 
                                                value="${usci['usci contact company address state'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address state', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Zip Code</label>
                                            <input type="text" name="usci contact company address zip code" 
                                                value="${usci['usci contact company address zip code'] || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('usContactInfo', 'usci contact company address zip code', this.value)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // Toggle function using your existing toggleConditionalBlock
        function toggleContactTypeFields(value) {
            toggleConditionalBlock('person-contact', value === 'Person');
            toggleConditionalBlock('company-contact', value === 'Company');
            toggleConditionalBlock('hotel-contact', value === 'Hotel');
        }

        // Initialize function for US Contact step
        function initializeUSContactStep() {
            const contactTypeSelect = document.querySelector('select[name="usci_contact_type"]');
            if (contactTypeSelect) {
                toggleContactTypeFields(contactTypeSelect.value);
            }
        }

        // Family Member Information Step (Based on Excel FM section)
        function generateFamilyInfoStep(applicant) {
            const fm = applicant.familyInfo || {};
            const familyMembers = fm.familyMembers || [{
                relation: '',
                given_name: '',
                family_name: '',
                dob: '',
                nationality: '',
                in_usa: false,
                person_status: '',
                pob: '',
                boc_country: '',
                spouse_telephone: '',
                spouse_email: '',
                have_same_address: '',
                spouse_address_line_1: '',
                spouse_address_line_2: '',
                spouse_address_city: '',
                spouse_address_state: '',
                spouse_address_zip_code: '',
                spouse_address_country: ''
            }];

            return `
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Family Members</h3>
                        <div id="family-member-fields">
                            ${generateFamilyMemberFields(familyMembers)}
                        </div>
                        <button type="button" onclick="addFamilyMemberField()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Family Member
                        </button>
                    </div>
                </div>
            `;
        }

        // Helper Functions for Family Members
        function updateFamilyMemberArray(scope, arrayName, index, fieldName, value) {
            if (!state.applicants[state.currentApplicant][scope][arrayName]) {
                state.applicants[state.currentApplicant][scope][arrayName] = [];
            }
            if (!state.applicants[state.currentApplicant][scope][arrayName][index]) {
                state.applicants[state.currentApplicant][scope][arrayName][index] = {};
            }
            state.applicants[state.currentApplicant][scope][arrayName][index][fieldName] = value;
            saveToLocalStorage();

            // Update UI for conditional fields
            setTimeout(() => {
                const member = state.applicants[state.currentApplicant][scope][arrayName][index];
                if (fieldName === 'relation') {
                    handleFamilyRelationChange(index, value);
                }
                if (fieldName === 'in_usa') {
                    toggleConditionalBlock(`usa-status-${index}`, value);
                }
                if (fieldName === 'have_same_address') {
                    handleSpouseAddressChange(index, value);
                }
            }, 100);
        }

        function generateFamilyMemberFields(familyMembers) {
            return familyMembers.map((member, index) => {
                const showUSASection = member.in_usa;
                const showSpouseSection = member.relation === 'Spouse';
                const showSpouseAddress = member.have_same_address === 'Others';
                return `
                <div class="family-member-field border border-gray-300 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Relation -->
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 mb-2">Relation *</label>
                            <select name="fm_relation_${index}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'relation', this.value)">
                                <option value="">Select Relation</option>
                                <option value="Father" ${(member.relation === 'Father') ? 'selected' : ''}>Father</option>
                                <option value="Mother" ${(member.relation === 'Mother') ? 'selected' : ''}>Mother</option>
                                <option value="Spouse" ${(member.relation === 'Spouse') ? 'selected' : ''}>Spouse</option>
                                <option value="Child" ${(member.relation === 'Child') ? 'selected' : ''}>Child</option>
                                <option value="Sibling" ${(member.relation === 'Sibling') ? 'selected' : ''}>Sibling</option>
                                <option value="Other" ${(member.relation === 'Other') ? 'selected' : ''}>Other</option>
                            </select>
                        </div>

                        <!-- Basic Information -->
                        <div>
                            <label class="block text-gray-700 mb-2">Given Name *</label>
                            <input type="text" name="fm_given_name_${index}" 
                                value="${member.given_name || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'given_name', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Family Name *</label>
                            <input type="text" name="fm_family_name_${index}" 
                                value="${member.family_name || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'family_name', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Date of Birth</label>
                            <input type="date" name="fm_dob_${index}" 
                                value="${member.dob || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'dob', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Nationality</label>
                            <select name="fm_nationality_${index}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'nationality', this.value)">
                                <option value="">Select Nationality</option>
                                ${countries.map(country => 
                                    `<option value="${country.code}" ${(member.nationality === country.code) ? 'selected' : ''}>${country.name}</option>`
                                ).join('')}
                            </select>
                        </div>
                    </div>

                    <!-- In USA Toggle - FOR ALL MEMBERS -->
                    <div class="mt-4">
                        <label class="block text-gray-700 mb-2">Is this family member in the USA?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="fm_in_usa_${index}" value="1" 
                                    ${member.in_usa ? 'checked' : ''}
                                    onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'in_usa', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="fm_in_usa_${index}" value="0" 
                                    ${!member.in_usa ? 'checked' : ''}
                                    onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'in_usa', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <!-- USA Status (Conditional) - FOR ALL MEMBERS -->
                    <div id="usa-status-${index}" class="conditional-block mt-4" style="display: ${showUSASection ? 'block' : 'none'};">
                        <div>
                            <label class="block text-gray-700 mb-2">Status in USA</label>
                            <select name="fm_person_status_${index}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'person_status', this.value)">
                                <option value="">Select Status</option>
                                <option value="Citizen" ${(member.person_status === 'Citizen') ? 'selected' : ''}>Citizen</option>
                                <option value="Permanent Resident" ${(member.person_status === 'Permanent Resident') ? 'selected' : ''}>Permanent Resident</option>
                                <option value="Non immigrant" ${(member.person_status === 'Non immigrant') ? 'selected' : ''}>Non immigrant</option>
                                <option value="Others" ${(member.person_status === 'Others') ? 'selected' : ''}>Others</option>
                            </select>
                        </div>
                    </div>

                    <!-- Spouse Specific Fields (Conditional) -->
                    <div id="spouse-fields-${index}" class="conditional-block mt-4" style="display: ${showSpouseSection ? 'block' : 'none'};">
                        <div class="border-t pt-4">
                            <h4 class="text-md font-medium text-gray-700 mb-3">Spouse Specific Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Place of Birth</label>
                                    <input type="text" name="fm_pob_${index}" 
                                        value="${member.pob || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'pob', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Country of Birth</label>
                                    <select name="fm_boc_country_${index}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'boc_country', this.value)">
                                        <option value="">Select Country</option>
                                        ${countries.map(country => 
                                            `<option value="${country.code}" ${(member.boc_country === country.code) ? 'selected' : ''}>${country.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Telephone</label>
                                    <input type="tel" name="fm_spouse_telephone_${index}" 
                                        value="${member.spouse_telephone || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'spouse_telephone', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Email</label>
                                    <input type="email" name="fm_spouse_email_${index}" 
                                        value="${member.spouse_email || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'spouse_email', this.value)">
                                </div>
                            </div>

                            <!-- Spouse Address Toggle -->
                            <div class="mt-4">
                                <label class="block text-gray-700 mb-2">Spouse Address</label>
                                <select name="fm_have_same_address_${index}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'have_same_address', this.value)">
                                    <option value="">Select Address Type</option>
                                    <option value="Same as home address" ${(member.have_same_address === 'Same as home address') ? 'selected' : ''}>Same as home address</option>
                                    <option value="Same as Billing" ${(member.have_same_address === 'Same as Billing') ? 'selected' : ''}>Same as Billing</option>
                                    <option value="Same as U.S. contact address" ${(member.have_same_address === 'Same as U.S. contact address') ? 'selected' : ''}>Same as U.S. contact address</option>
                                    <option value="Others" ${(member.have_same_address === 'Others') ? 'selected' : ''}>Others</option>
                                </select>
                            </div>

                            <!-- Spouse Address Fields (Conditional) -->
                            <div id="spouse-address-fields-${index}" class="conditional-block mt-4" style="display: ${showSpouseAddress ? 'block' : 'none'};">
                                <div class="border-t pt-4">
                                    <h5 class="text-sm font-medium text-gray-700 mb-3">Spouse Address Details</h5>
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label class="block text-gray-700 mb-2">Address Line 1</label>
                                            <input type="text" name="fm_spouse_address_line_1_${index}" 
                                                value="${member.spouse_address_line_1 || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'spouse_address_line_1', this.value)">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Address Line 2</label>
                                            <input type="text" name="fm_spouse_address_line_2_${index}" 
                                                value="${member.spouse_address_line_2 || ''}" 
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'spouse_address_line_2', this.value)">
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-gray-700 mb-2">City</label>
                                                <input type="text" name="fm_spouse_address_city_${index}" 
                                                    value="${member.spouse_address_city || ''}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'spouse_address_city', this.value)">
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 mb-2">State</label>
                                                <input type="text" name="fm_spouse_address_state_${index}" 
                                                    value="${member.spouse_address_state || ''}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'spouse_address_state', this.value)">
                                            </div>
                                            <div>
                                                <label class="block text-gray-700 mb-2">Zip Code</label>
                                                <input type="text" name="fm_spouse_address_zip_code_${index}" 
                                                    value="${member.spouse_address_zip_code || ''}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'spouse_address_zip_code', this.value)">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-2">Country</label>
                                            <select name="fm_spouse_address_country_${index}" 
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                    onchange="updateFamilyMemberArray('familyInfo', 'familyMembers', ${index}, 'spouse_address_country', this.value)">
                                                <option value="">Select Country</option>
                                                ${countries.map(country => 
                                                    `<option value="${country.code}" ${(member.spouse_address_country === country.code) ? 'selected' : ''}>${country.name}</option>`
                                                ).join('')}
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Remove Button -->
                    ${index > 0 ? `
                        <div class="mt-4">
                            <button type="button" onclick="removeFamilyMemberField(${index})" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-times mr-2"></i> Remove Family Member
                            </button>
                        </div>
                    ` : ''}
                </div>
                `;
            }).join('');
        }

        // Additional Helper Functions
        function handleFamilyRelationChange(index, relation) {
            toggleConditionalBlock(`spouse-fields-${index}`, relation === 'Spouse');
        }

        function handleSpouseAddressChange(index, addressType) {
            toggleConditionalBlock(`spouse-address-fields-${index}`, addressType === 'Others');
        }

        function addFamilyMemberField() {
            const container = document.getElementById('family-member-fields');
            const index = container.children.length;

            // Add new empty member to data structure
            if (!state.applicants[state.currentApplicant].familyInfo.familyMembers) {
                state.applicants[state.currentApplicant].familyInfo.familyMembers = [];
            }
            state.applicants[state.currentApplicant].familyInfo.familyMembers.push({});
            saveToLocalStorage();

            // Regenerate the entire fields to ensure proper indexing
            const familyMembers = state.applicants[state.currentApplicant].familyInfo.familyMembers;
            container.innerHTML = generateFamilyMemberFields(familyMembers);
        }

        function removeFamilyMemberField(index) {
            const field = document.querySelector(`.family-member-field:nth-child(${index + 1})`);
            if (field) {
                // Remove from data structure
                state.applicants[state.currentApplicant].familyInfo.familyMembers.splice(index, 1);
                saveToLocalStorage();

                // Regenerate fields
                const container = document.getElementById('family-member-fields');
                const familyMembers = state.applicants[state.currentApplicant].familyInfo.familyMembers;
                container.innerHTML = generateFamilyMemberFields(familyMembers);
            }
        }

        // Initialize function to set up conditional displays
        function initializeFamilyStep() {
            const familyMembers = state.applicants[state.currentApplicant].familyInfo.familyMembers || [];
            familyMembers.forEach((member, index) => {
                handleFamilyRelationChange(index, member.relation);
                toggleConditionalBlock(`usa-status-${index}`, member.in_usa);
                handleSpouseAddressChange(index, member.have_same_address);
            });
        }

        // Work Information Step (Based on Excel WI section)
        function generateWorkInfoStep(applicant) {
            const wi = applicant.employmentInfo || {};
            const previousEmployment = wi.previousEmployment || [];

            return `
                <div class="space-y-6">
                    <!-- Primary Occupation Type -->
                    <div>
                        <label class="block text-gray-700 mb-2">Primary Occupation Type *</label>
                        <select name="wi_primary_occupation_type" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="handleOccupationChange(this.value); updateApplicantData('employmentInfo', 'wi_primary_occupation_type', this.value)" required>
                            <option value="">Select Category</option>
                            <option value="Student" ${(wi.wi_primary_occupation_type === 'Student') ? 'selected' : ''}>Student</option>
                            <option value="Homemaker" ${(wi.wi_primary_occupation_type === 'Homemaker') ? 'selected' : ''}>Homemaker</option>
                            <option value="Retired" ${(wi.wi_primary_occupation_type === 'Retired') ? 'selected' : ''}>Retired</option>
                            <option value="Government" ${(wi.wi_primary_occupation_type === 'Government') ? 'selected' : ''}>Government</option>
                            <option value="Private Sector" ${(wi.wi_primary_occupation_type === 'Private Sector') ? 'selected' : ''}>Private Sector</option>
                            <option value="Military" ${(wi.wi_primary_occupation_type === 'Military') ? 'selected' : ''}>Military</option>
                            <option value="Unemployed" ${(wi.wi_primary_occupation_type === 'Unemployed') ? 'selected' : ''}>Unemployed</option>
                            <option value="Other" ${(wi.wi_primary_occupation_type === 'Other') ? 'selected' : ''}>Other</option>
                        </select>
                    </div>

                    <!-- Employment Fields (Conditional) -->
                    <div id="employment-fields" style="display: ${isEmploymentType(wi.wi_primary_occupation_type) ? 'block' : 'none'};">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Present Company Name *</label>
                                <input type="text" name="wi_company_or_school_name" 
                                    value="${wi.wi_company_or_school_name || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_name', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Monthly Salary</label>
                                <input type="text" name="wi_salary" 
                                    value="${wi.wi_salary || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('employmentInfo', 'wi_salary', this.value)"
                                    placeholder="Enter monthly salary">
                            </div>
                        </div>

                        <!-- Description of Duties -->
                        <div class="mt-4">
                            <label class="block text-gray-700 mb-2">Describe Your Duties</label>
                            <textarea name="wi_your_duties" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    rows="4"
                                    placeholder="Describe your duties..."
                                    onchange="updateApplicantData('employmentInfo', 'wi_your_duties', this.value)">${wi.wi_your_duties || ''}</textarea>
                        </div>

                        <!-- Company Address -->
                        <div class="border-t pt-6">
                            <h4 class="text-lg font-medium text-gray-800 mb-4">Present Company Address</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Present Company Address Line 1</label>
                                        <input type="text" name="wi_company_or_school_address_line_1" 
                                            value="${wi.wi_company_or_school_address_line_1 || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_line_1', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Present Company Address Line 2</label>
                                        <input type="text" name="wi_company_or_school_address_line_2" 
                                            value="${wi.wi_company_or_school_address_line_2 || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_line_2', this.value)">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Present Company Address City</label>
                                        <input type="text" name="wi_company_or_school_address_city" 
                                            value="${wi.wi_company_or_school_address_city || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_city', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Present Company Address State</label>
                                        <input type="text" name="wi_company_or_school_address_state" 
                                            value="${wi.wi_company_or_school_address_state || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_state', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Present Company Address Zip Code</label>
                                        <input type="text" name="wi_company_or_school_address_zip_code" 
                                            value="${wi.wi_company_or_school_address_zip_code || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_zip_code', this.value)">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">Present Company Address Country</label>
                                        <select name="wi_company_or_school_address_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_country', this.value)">
                                            <option value="">Select Country</option>
                                            ${countries.map(country => 
                                                `<option value="${country.code}" ${(wi.wi_company_or_school_address_country === country.code) ? 'selected' : ''}>${country.name}</option>`
                                            ).join('')}
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Present Company Address Telephone</label>
                                        <input type="tel" name="wi_company_or_school_address_telephone" 
                                            value="${wi.wi_company_or_school_address_telephone || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_telephone', this.value)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Fields (Conditional) -->
                    <div id="student-fields" style="display: ${wi.wi_primary_occupation_type === 'Student' ? 'block' : 'none'};">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">School/University Name *</label>
                                <input type="text" name="wi_company_or_school_name" 
                                    value="${wi.wi_company_or_school_name || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_name', this.value)">
                            </div>
                        </div>

                        <!-- School Address -->
                        <div class="border-t pt-6">
                            <h4 class="text-lg font-medium text-gray-800 mb-4">School/University Address</h4>
                            <div class="grid grid-cols-1 gap-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">School Address Line 1</label>
                                        <input type="text" name="wi_company_or_school_address_line_1" 
                                            value="${wi.wi_company_or_school_address_line_1 || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_line_1', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">School Address Line 2</label>
                                        <input type="text" name="wi_company_or_school_address_line_2" 
                                            value="${wi.wi_company_or_school_address_line_2 || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_line_2', this.value)">
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">School Address City</label>
                                        <input type="text" name="wi_company_or_school_address_city" 
                                            value="${wi.wi_company_or_school_address_city || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_city', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">School Address State</label>
                                        <input type="text" name="wi_company_or_school_address_state" 
                                            value="${wi.wi_company_or_school_address_state || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_state', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">School Address Zip Code</label>
                                        <input type="text" name="wi_company_or_school_address_zip_code" 
                                            value="${wi.wi_company_or_school_address_zip_code || ''}" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('employmentInfo', 'wi_company_or_school_address_zip_code', this.value)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Previous Employment Toggle -->
                    <div id="previous-employment-toggle" style="display: ${isEmploymentType(wi.wi_primary_occupation_type) ? 'block' : 'none'};">
                        <label class="block text-gray-700 mb-2">Were you previously employed?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="have_previous_experience" value="1" 
                                    ${wi.have_previous_experience ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-employment', true); updateApplicantData('employmentInfo', 'have_previous_experience', true)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="have_previous_experience" value="0" 
                                    ${!wi.have_previous_experience ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('previous-employment', false); updateApplicantData('employmentInfo', 'have_previous_experience', false)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <!-- Previous Employment Section -->
                    <div id="previous-employment" class="conditional-block" style="display: ${(wi.have_previous_experience && isEmploymentType(wi.wi_primary_occupation_type)) ? 'block' : 'none'};">
                        <div class="space-y-6">
                            <h4 class="text-lg font-medium text-gray-800">Previous Employment History</h4>
                            <div id="previous-employment-fields">
                                ${generatePreviousEmploymentFields(previousEmployment)}
                            </div>
                            <button type="button" onclick="addPreviousEmploymentField()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Previous Employment
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Helper Functions
        function isEmploymentType(occupation) {
            return ['Government', 'Private Sector', 'Military'].includes(occupation);
        }

        function handleOccupationChange(value) {
            const isEmployment = isEmploymentType(value);
            const isStudent = value === 'Student';

            toggleConditionalBlock('employment-fields', isEmployment);
            toggleConditionalBlock('student-fields', isStudent);
            toggleConditionalBlock('previous-employment-toggle', isEmployment);

            // Hide previous employment if not employment type
            if (!isEmployment) {
                toggleConditionalBlock('previous-employment', false);
            }
        }

        function generatePreviousEmploymentFields(previousEmployment) {
            if (!previousEmployment || previousEmployment.length === 0) {
                previousEmployment = [{}];
            }

            return previousEmployment.map((employment, index) => `
                <div class="previous-employment-field border border-gray-300 rounded-lg p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Previous Company Name</label>
                            <input type="text" name="wi_pre_company_name" 
                                value="${employment.wi_pre_company_name || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_name', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Previous Company Job Title</label>
                            <input type="text" name="wi_pre_company_job_title" 
                                value="${employment.wi_pre_company_job_title || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_job_title', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Previous Company Supervisor Surname</label>
                            <input type="text" name="wi_pre_company_supervisor_surname" 
                                value="${employment.wi_pre_company_supervisor_surname || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_supervisor_surname', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Previous Company Supervisor Given Name</label>
                            <input type="text" name="wi_pre_company_supervisor_given_name" 
                                value="${employment.wi_pre_company_supervisor_given_name || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_supervisor_given_name', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Employment Date Started From</label>
                            <input type="date" name="wi_pre_employment_started" 
                                value="${employment.wi_pre_employment_started || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_employment_started', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Employment Date Ended To</label>
                            <input type="date" name="wi_pre_employment_ended" 
                                value="${employment.wi_pre_employment_ended || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_employment_ended', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Previous Company Monthly Salary</label>
                            <input type="text" name="wi_pre_company_salary" 
                                value="${employment.wi_pre_company_salary || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_salary', this.value)">
                        </div>
                    </div>

                    <!-- Previous Company Address -->
                    <div class="border-t pt-4 mt-4">
                        <h5 class="text-md font-medium text-gray-700 mb-3">Previous Company Address</h5>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Previous Company Address Line 1</label>
                                    <input type="text" name="wi_pre_company_address_line_1" 
                                        value="${employment.wi_pre_company_address_line_1 || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_address_line_1', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Previous Company Address Line 2</label>
                                    <input type="text" name="wi_pre_company_address_line_2" 
                                        value="${employment.wi_pre_company_address_line_2 || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_address_line_2', this.value)">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Previous Company Address City</label>
                                    <input type="text" name="wi_pre_company_address_city" 
                                        value="${employment.wi_pre_company_address_city || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_address_city', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Previous Company Address State</label>
                                    <input type="text" name="wi_pre_company_address_state" 
                                        value="${employment.wi_pre_company_address_state || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_address_state', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Previous Company Address Zip Code</label>
                                    <input type="text" name="wi_pre_company_address_zip_code" 
                                        value="${employment.wi_pre_company_address_zip_code || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_address_zip_code', this.value)">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Previous Company Address Country</label>
                                    <select name="wi_pre_company_address_country" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_address_country', this.value)">
                                        <option value="">Select Country</option>
                                        ${countries.map(country => 
                                            `<option value="${country.code}" ${(employment.wi_pre_company_address_country === country.code) ? 'selected' : ''}>${country.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Previous Company Address Telephone</label>
                                    <input type="tel" name="wi_pre_company_address_telephone" 
                                        value="${employment.wi_pre_company_address_telephone || ''}" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_address_telephone', this.value)">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Previous Company Duties -->
                    <div class="mt-4">
                        <label class="block text-gray-700 mb-2">Previous Company Describe Your Duties</label>
                        <textarea name="wi_pre_company_duties" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                rows="3"
                                placeholder="Describe your duties at this company..."
                                onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'wi_pre_company_duties', this.value)">${employment.wi_pre_company_duties || ''}</textarea>
                    </div>

                    ${index > 0 ? `
                        <div class="mt-4">
                            <button type="button" onclick="removePreviousEmploymentField(${index})" class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-times mr-2"></i> Remove Employment
                            </button>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        // Keep the same add/remove functions as before
        function addPreviousEmploymentField() {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.employmentInfo.previousEmployment) { // workInfo -> employmentInfo
                applicant.employmentInfo.previousEmployment = [];
            }
            const container = document.getElementById('previous-employment-fields');
            const index = container.children.length;

            if (!state.applicants[state.currentApplicant].employmentInfo.previousEmployment) {
                state.applicants[state.currentApplicant].employmentInfo.previousEmployment = [];
            }
            state.applicants[state.currentApplicant].employmentInfo.previousEmployment.push({});
            saveToLocalStorage();

            const previousEmployment = state.applicants[state.currentApplicant].employmentInfo.previousEmployment;
            container.innerHTML = generatePreviousEmploymentFields(previousEmployment);
        }

        function removePreviousEmploymentField(index) {
            const field = document.querySelector(`.previous-employment-field:nth-child(${index + 1})`);
            if (field) {
                state.applicants[state.currentApplicant].employmentInfo.previousEmployment.splice(index, 1);
                saveToLocalStorage();

                const container = document.getElementById('previous-employment-fields');
                const previousEmployment = state.applicants[state.currentApplicant].employmentInfo.previousEmployment;
                container.innerHTML = generatePreviousEmploymentFields(previousEmployment);
            }
        }

        function updatePreviousEmploymentArray(scope, arrayName, index, fieldName, value) {
            if (!state.applicants[state.currentApplicant][scope][arrayName]) {
                state.applicants[state.currentApplicant][scope][arrayName] = [];
            }
            if (!state.applicants[state.currentApplicant][scope][arrayName][index]) {
                state.applicants[state.currentApplicant][scope][arrayName][index] = {};
            }
            state.applicants[state.currentApplicant][scope][arrayName][index][fieldName] = value;
            saveToLocalStorage();
        }

        // Educational Information Step (Based on Excel EDI section)
        function generateEducationInfoStep(applicant) {
            const edi = applicant.educationalInfo || {};
            const institutions = edi.institutions || [];

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Have you attended any educational institution at a secondary level or above?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="edi_have_attended_secondary_level" value="1" 
                                       ${edi.edi_have_attended_secondary_level ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('education-history', this.checked); updateApplicantData('educationalInfo', 'edi_have_attended_secondary_level', this.checked)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="edi_have_attended_secondary_level" value="0" 
                                       ${!edi.edi_have_attended_secondary_level ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('education-history', this.checked); updateApplicantData('educationalInfo', 'edi_have_attended_secondary_level', this.checked)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="education-history" class="conditional-block ${edi.edi_have_attended_secondary_level ? 'active' : ''}">
                        <div class="space-y-6">
                            <h4 class="text-lg font-medium text-gray-800">Educational Institutions</h4>
                            <div id="institution-fields">
                                ${generateInstitutionFields(institutions)}
                            </div>
                            <button type="button" onclick="addInstitutionField()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Institution
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function generateInstitutionFields(institutions) {
            return institutions.map((institution, index) => `
                <div class="dynamic-field-group">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-medium text-gray-700">Institution ${index + 1}</h4>
                        ${index > 0 ? `
                        <button type="button" class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-lg text-sm" onclick="removeInstitutionField(${index})">
                            Remove Institution
                        </button>
                        ` : ''}
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Institution Name</label>
                            <input type="text" 
                                   value="${institution.name || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateInstitutionData(${index}, 'name', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Course of Study</label>
                            <input type="text" 
                                   value="${institution.course || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateInstitutionData(${index}, 'course', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Attendance From</label>
                            <input type="date" 
                                   value="${institution.attendanceFrom || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateInstitutionData(${index}, 'attendanceFrom', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Attendance To</label>
                            <input type="date" 
                                   value="${institution.attendanceTo || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateInstitutionData(${index}, 'attendanceTo', this.value)">
                        </div>
                        <div>
                            <div>
                                <label class="block text-gray-700 mb-2">School Address Line 1</label>
                                <input type="text" name="edi_institution_address_line_1" 
                                    value="${institution.edi_institution_address_line_1 || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('employmentInfo', 'edi_institution_address_line_1', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">School Address Line 2</label>
                                <input type="text" name="edi_institution_address_line_2" 
                                    value="${institution.edi_institution_address_line_2 || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('employmentInfo', 'edi_institution_address_line_2', this.value)">
                            </div>
                        </div>
                        <div>
                            <div>
                                <label class="block text-gray-700 mb-2">School Address City</label>
                                <input type="text" name="edi_institution_address_city" 
                                    value="${institution.edi_institution_address_city || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('employmentInfo', 'edi_institution_address_city', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">School Address State</label>
                                <input type="text" name="edi_institution_address_state" 
                                    value="${institution.edi_institution_address_state || ''}" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('employmentInfo', 'edi_institution_address_state', this.value)">
                            </div>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">School Address Zip Code</label>
                            <input type="text" name="edi_institution_address_zip_code" 
                                value="${institution.edi_institution_address_zip_code || ''}" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateApplicantData('employmentInfo', 'edi_institution_address_zip_code', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">School Address Country</label>
                            <select name="edi_institution_address_country" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updatePreviousEmploymentArray('employmentInfo', 'previousEmployment', ${index}, 'edi_institution_address_country', this.value)">
                                <option value="">Select Country</option>
                                ${countries.map(country => 
                                    `<option value="${country.code}" ${(institution.edi_institution_address_country === country.code) ? 'selected' : ''}>${country.name}</option>`
                                ).join('')}
                            </select>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Other Information Step (Based on Excel OI section)
        function generateOtherInfoStep(applicant) {
            const oi = applicant.otherInfo || {};

            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">List of Languages Spoken</label>
                        <textarea name="oi_spoken_language_list" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                rows="3"
                                onchange="updateApplicantData('otherInfo', 'oi_spoken_language_list', this.value)">${oi.oi_spoken_language_list || ''}</textarea>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you traveled to any countries in the last five years?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_travel_country_5years" value="true" 
                                    ${oi.oi_have_travel_country_5years ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('traveled-countries', true); updateApplicantData('otherInfo', 'oi_have_travel_country_5years', this.value === 'true')">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_travel_country_5years" value="false" 
                                    ${!oi.oi_have_travel_country_5years ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('traveled-countries', false); updateApplicantData('otherInfo', 'oi_have_travel_country_5years', this.value === 'true')">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="traveled-countries" class="conditional-block" style="display: ${oi.oi_have_travel_country_5years ? 'block' : 'none'}">
                        <div>
                            <label class="block text-gray-700 mb-2">Traveled Countries</label>
                            <div id="travelled-countries-container">
                                ${generateTravelledCountryFields(oi.oi_travelled_country || [])}
                            </div>
                            <button type="button" 
                                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                                    onclick="addTravelledCountryField()">
                                Add Another Country
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you belonged to any professional, social, or charitable organizations?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_you_belong_orgntion" value="true" 
                                    ${oi.oi_have_you_belong_orgntion ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('organization-info', true); updateApplicantData('otherInfo', 'oi_have_you_belong_orgntion', this.value === 'true')">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_you_belong_orgntion" value="false" 
                                    ${!oi.oi_have_you_belong_orgntion ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('organization-info', false); updateApplicantData('otherInfo', 'oi_have_you_belong_orgntion', this.value === 'true')">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="organization-info" class="conditional-block" style="display: ${oi.oi_have_you_belong_orgntion ? 'block' : 'none'}">
                        <div>
                            <label class="block text-gray-700 mb-2">Organization Name*</label>
                            <div id="organizations-container">
                                ${generateOrganizationFields(oi.oi_organization_name || [])}
                            </div>
                            <button type="button" 
                                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                                    onclick="addOrganizationField()">
                                Add Another Organization
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Do you have any special skills or training in fire arms, explosives, or nuclear materials?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_special_skills" value="true" 
                                    ${oi.oi_have_special_skills ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('special-skills-info', true); updateApplicantData('otherInfo', 'oi_have_special_skills', this.value === 'true')">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_special_skills" value="false" 
                                    ${!oi.oi_have_special_skills ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('special-skills-info', false); updateApplicantData('otherInfo', 'oi_have_special_skills', this.value === 'true')">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="special-skills-info" class="conditional-block" style="display: ${oi.oi_have_special_skills ? 'block' : 'none'}">
                        <div>
                            <label class="block text-gray-700 mb-2">Explain your special skills or training</label>
                            <textarea name="oi_special_skills" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    rows="4"
                                    onchange="updateApplicantData('otherInfo', 'oi_special_skills', this.value)">${oi.oi_special_skills || ''}</textarea>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-2">Have you ever served in the military?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_served_military" value="true" 
                                    ${oi.oi_have_served_military ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('military-service-history', true); updateApplicantData('otherInfo', 'oi_have_served_military', this.value === 'true')">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="oi_have_served_military" value="false" 
                                    ${!oi.oi_have_served_military ? 'checked' : ''}
                                    onchange="toggleConditionalBlock('military-service-history', false); updateApplicantData('otherInfo', 'oi_have_served_military', this.value === 'true')">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="military-service-history" class="conditional-block" style="display: ${oi.oi_have_served_military ? 'block' : 'none'}">
                        <div>
                            <label class="block text-gray-700 mb-2">Military Service History</label>
                            <div id="military-service-container">
                                ${generateMilitaryServiceFields(oi.oi_military_service || [])}
                            </div>
                            <button type="button" 
                                    class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                                    onclick="addMilitaryServiceField()">
                                Add Another Service Period
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Keep all your existing helper functions as they are
        function updateTravelledCountryArray(scope, arrayName, index, fieldName, value) {
            if (!state.applicants[state.currentApplicant][scope]) {
                state.applicants[state.currentApplicant][scope] = {};
            }
            if (!state.applicants[state.currentApplicant][scope][arrayName]) {
                state.applicants[state.currentApplicant][scope][arrayName] = [];
            }

            // For travelled countries, we're storing just the country code as string
            state.applicants[state.currentApplicant][scope][arrayName][index] = value;
            saveToLocalStorage();
        }

        function addTravelledCountryField() {
            if (!state.applicants[state.currentApplicant].otherInfo) {
                state.applicants[state.currentApplicant].otherInfo = {};
            }
            if (!state.applicants[state.currentApplicant].otherInfo.oi_travelled_country) {
                state.applicants[state.currentApplicant].otherInfo.oi_travelled_country = [];
            }

            state.applicants[state.currentApplicant].otherInfo.oi_travelled_country.push('');

            const container = document.getElementById('travelled-countries-container');
            if (container) {
                container.innerHTML = generateTravelledCountryFields(state.applicants[state.currentApplicant].otherInfo.oi_travelled_country);
            }

            saveToLocalStorage();
        }

        function removeTravelledCountryField(index) {
            if (state.applicants[state.currentApplicant].otherInfo &&
                state.applicants[state.currentApplicant].otherInfo.oi_travelled_country) {
                state.applicants[state.currentApplicant].otherInfo.oi_travelled_country.splice(index, 1);

                const container = document.getElementById('travelled-countries-container');
                if (container) {
                    container.innerHTML = generateTravelledCountryFields(state.applicants[state.currentApplicant].otherInfo.oi_travelled_country);
                }

                saveToLocalStorage();
            }
        }

        function generateTravelledCountryFields(selectedCountries) {
            if (!selectedCountries || !Array.isArray(selectedCountries)) {
                selectedCountries = [''];
            }

            return selectedCountries.map((countryCode, index) => `
                <div class="flex items-center space-x-2 mb-2">
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="updateTravelledCountryArray('otherInfo', 'oi_travelled_country', ${index}, 'country', this.value)">
                        <option value="">Select Country</option>
                        ${countries.map(country => `
                            <option value="${country.code}" ${countryCode === country.code ? 'selected' : ''}>${country.name}</option>
                        `).join('')}
                    </select>
                    <button type="button" 
                            class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                            onclick="removeTravelledCountryField(${index})">
                        Remove
                    </button>
                </div>
            `).join('');
        }

        function updateOrganizationArray(scope, arrayName, index, fieldName, value) {
            if (!state.applicants[state.currentApplicant][scope]) {
                state.applicants[state.currentApplicant][scope] = {};
            }
            if (!state.applicants[state.currentApplicant][scope][arrayName]) {
                state.applicants[state.currentApplicant][scope][arrayName] = [];
            }

            if (!state.applicants[state.currentApplicant][scope][arrayName][index]) {
                state.applicants[state.currentApplicant][scope][arrayName][index] = {};
            }
            state.applicants[state.currentApplicant][scope][arrayName][index][fieldName] = value;
            saveToLocalStorage();
        }

        function addOrganizationField() {
            if (!state.applicants[state.currentApplicant].otherInfo) {
                state.applicants[state.currentApplicant].otherInfo = {};
            }
            if (!state.applicants[state.currentApplicant].otherInfo.oi_organization_name) {
                state.applicants[state.currentApplicant].otherInfo.oi_organization_name = [];
            }

            state.applicants[state.currentApplicant].otherInfo.oi_organization_name.push({
                name: ''
            });

            const container = document.getElementById('organizations-container');
            if (container) {
                container.innerHTML = generateOrganizationFields(state.applicants[state.currentApplicant].otherInfo.oi_organization_name);
            }

            saveToLocalStorage();
        }

        function removeOrganizationField(index) {
            if (state.applicants[state.currentApplicant].otherInfo &&
                state.applicants[state.currentApplicant].otherInfo.oi_organization_name) {
                state.applicants[state.currentApplicant].otherInfo.oi_organization_name.splice(index, 1);

                const container = document.getElementById('organizations-container');
                if (container) {
                    container.innerHTML = generateOrganizationFields(state.applicants[state.currentApplicant].otherInfo.oi_organization_name);
                }

                saveToLocalStorage();
            }
        }

        function generateOrganizationFields(organizations) {
            if (!organizations || !Array.isArray(organizations)) {
                organizations = [{
                    name: ''
                }];
            }

            return organizations.map((org, index) => `
                <div class="flex items-center space-x-2 mb-2">
                    <input type="text" 
                        value="${org.name || ''}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="updateOrganizationArray('otherInfo', 'oi_organization_name', ${index}, 'name', this.value)">
                    <button type="button" 
                            class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                            onclick="removeOrganizationField(${index})">
                        Remove
                    </button>
                </div>
            `).join('');
        }

        function updateMilitaryServiceArray(scope, arrayName, index, fieldName, value) {
            if (!state.applicants[state.currentApplicant][scope]) {
                state.applicants[state.currentApplicant][scope] = {};
            }
            if (!state.applicants[state.currentApplicant][scope][arrayName]) {
                state.applicants[state.currentApplicant][scope][arrayName] = [];
            }

            if (!state.applicants[state.currentApplicant][scope][arrayName][index]) {
                state.applicants[state.currentApplicant][scope][arrayName][index] = {};
            }
            state.applicants[state.currentApplicant][scope][arrayName][index][fieldName] = value;
            saveToLocalStorage();
        }

        function addMilitaryServiceField() {
            if (!state.applicants[state.currentApplicant].otherInfo) {
                state.applicants[state.currentApplicant].otherInfo = {};
            }
            if (!state.applicants[state.currentApplicant].otherInfo.oi_military_service) {
                state.applicants[state.currentApplicant].otherInfo.oi_military_service = [];
            }

            state.applicants[state.currentApplicant].otherInfo.oi_military_service.push({
                oi_sm_country_name: '',
                oi_sm_service_branch: '',
                oi_sm_rank: '',
                oi_militay_speciality: '',
                oi_sm_serve_from: '',
                oi_sm_serve_to: ''
            });

            const container = document.getElementById('military-service-container');
            if (container) {
                container.innerHTML = generateMilitaryServiceFields(state.applicants[state.currentApplicant].otherInfo.oi_military_service);
            }

            saveToLocalStorage();
        }

        function removeMilitaryServiceField(index) {
            if (state.applicants[state.currentApplicant].otherInfo &&
                state.applicants[state.currentApplicant].otherInfo.oi_military_service) {
                state.applicants[state.currentApplicant].otherInfo.oi_military_service.splice(index, 1);

                const container = document.getElementById('military-service-container');
                if (container) {
                    container.innerHTML = generateMilitaryServiceFields(state.applicants[state.currentApplicant].otherInfo.oi_military_service);
                }

                saveToLocalStorage();
            }
        }

        function generateMilitaryServiceFields(serviceHistory) {
            if (!serviceHistory || !Array.isArray(serviceHistory)) {
                serviceHistory = [{
                    oi_sm_country_name: '',
                    oi_sm_service_branch: '',
                    oi_sm_rank: '',
                    oi_militay_speciality: '',
                    oi_sm_serve_from: '',
                    oi_sm_serve_to: ''
                }];
            }

            return serviceHistory.map((service, index) => `
                <div class="border border-gray-300 p-4 rounded-lg mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Name of Country</label>
                            <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_country_name', this.value)">
                                <option value="">Select Country</option>
                                ${countries.map(country => `
                                    <option value="${country.code}" ${service.oi_sm_country_name === country.code ? 'selected' : ''}>${country.name}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Branch of Service</label>
                            <input type="text" 
                                value="${service.oi_sm_service_branch || ''}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_service_branch', this.value)">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Rank or Rating</label>
                            <input type="text" 
                                value="${service.oi_sm_rank || ''}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_rank', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Military Speciality</label>
                            <textarea class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    rows="3"
                                    onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_militay_speciality', this.value)">${service.oi_militay_speciality || ''}</textarea>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Service Date From</label>
                            <input type="date" 
                                value="${service.oi_sm_serve_from || ''}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_serve_from', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Service Date To</label>
                            <input type="date" 
                                value="${service.oi_sm_serve_to || ''}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateMilitaryServiceArray('otherInfo', 'oi_military_service', ${index}, 'oi_sm_serve_to', this.value)">
                        </div>
                    </div>
                    <button type="button" 
                            class="mt-4 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                            onclick="removeMilitaryServiceField(${index})">
                        Remove Service Record
                    </button>
                </div>
            `).join('');
        }

        // Helper functions for dynamic fields
        function generateEmailFields(emails) {
            return emails.map((email, index) => `
                <div class="dynamic-field-group flex items-end">
                    <div class="flex-1">
                        <label class="block text-gray-700 mb-2">Email Address ${index > 0 ? index + 1 : ''}</label>
                        <input type="email" 
                               value="${email}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               onchange="updateContactArrayData('emails', ${index}, this.value)" ${index === 0 ? 'required' : ''}>
                    </div>
                    ${index > 0 ? `
                    <button type="button" class="ml-2 bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg" onclick="removeContactField('emails', ${index})">
                        <i class="fas fa-times"></i>
                    </button>
                    ` : ''}
                </div>
            `).join('');
        }

        function generateLocationFields(locations) {
            return locations.map((location, index) => `
                <div class="dynamic-field-group">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-medium text-gray-700">Location ${index + 1}</h4>
                        ${index > 0 ? `
                        <button type="button" class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-lg text-sm" onclick="removeLocationField(${index})">
                            Remove Location
                        </button>
                        ` : ''}
                    </div>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Address Line 1</label>
                            <input type="text" 
                                   value="${location.address_line_1 || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateLocationData(${index}, 'address_line_1', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Address Line 2</label>
                            <input type="text" 
                                   value="${location.address_line_2 || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateLocationData(${index}, 'address_line_2', this.value)">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">City</label>
                                <input type="text" 
                                       value="${location.city || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateLocationData(${index}, 'city', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">State</label>
                                <input type="text" 
                                       value="${location.state || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateLocationData(${index}, 'state', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Zip Code</label>
                                <input type="text" 
                                       value="${location.zip_code || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateLocationData(${index}, 'zip_code', this.value)">
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Dynamic field management functions
        function addEmailField() {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.contactInfo.emails) {
                applicant.contactInfo.emails = [''];
            }
            applicant.contactInfo.emails.push('');
            saveToLocalStorage(); // ✅ localStorage-এ save করুন
        }

        function addSocialMediaField() {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.contactInfo.socialMedia) {
                applicant.contactInfo.socialMedia = [{
                    platform: '',
                    username: ''
                }];
            }
            applicant.contactInfo.socialMedia.push({
                platform: '',
                username: ''
            });
            saveToLocalStorage(); // ✅ localStorage-এ save করুন
        }

        function addLocationField() {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.travelInfo.locations) {
                applicant.travelInfo.locations = [{
                    address_line_1: '',
                    address_line_2: '',
                    city: '',
                    state: '',
                    zip_code: ''
                }];
            }
            applicant.travelInfo.locations.push({
                address_line_1: '',
                address_line_2: '',
                city: '',
                state: '',
                zip_code: ''
            });
            saveToLocalStorage(); // ✅ localStorage-এ save করুন
        }

        function addPreviousEmploymentField() {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.employmentInfo.previousEmployment) {
                applicant.employmentInfo.previousEmployment = [];
            }
            applicant.employmentInfo.previousEmployment.push({
                companyName: '',
                jobTitle: '',
                startDate: '',
                endDate: ''
            });
            generateFormSteps();
            saveToLocalStorage();
        }

        function addInstitutionField() {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.educationalInfo.institutions) {
                applicant.educationalInfo.institutions = [];
            }
            applicant.educationalInfo.institutions.push({
                name: '',
                course: '',
                attendanceFrom: '',
                attendanceTo: ''
            });
            generateFormSteps();
            saveToLocalStorage();
        }

        function removeContactField(type, index) {
            const applicant = state.applicants[state.currentApplicant];
            if (applicant.contactInfo[type]) {
                applicant.contactInfo[type].splice(index, 1);
                generateFormSteps();
                saveToLocalStorage();
            }
        }

        function removeLocationField(index) {
            const applicant = state.applicants[state.currentApplicant];
            if (applicant.travelInfo.locations && applicant.travelInfo.locations.length > 1) {
                applicant.travelInfo.locations.splice(index, 1);
                generateFormSteps();
                saveToLocalStorage();
            }
        }

        function removeFamilyMemberField(index) {
            const applicant = state.applicants[state.currentApplicant];
            if (applicant.familyInfo.familyMembers && applicant.familyInfo.familyMembers.length > 1) {
                applicant.familyInfo.familyMembers.splice(index, 1);
                generateFormSteps();
                saveToLocalStorage();
            }
        }

        function removePreviousEmploymentField(index) {
            const applicant = state.applicants[state.currentApplicant];
            if (applicant.employmentInfo.previousEmployment && applicant.employmentInfo.previousEmployment.length > 1) {
                applicant.employmentInfo.previousEmployment.splice(index, 1);
                generateFormSteps();
                saveToLocalStorage();
            }
        }

        function removeInstitutionField(index) {
            const applicant = state.applicants[state.currentApplicant];
            if (applicant.educationalInfo.institutions && applicant.educationalInfo.institutions.length > 1) {
                applicant.educationalInfo.institutions.splice(index, 1);
                generateFormSteps();
                saveToLocalStorage();
            }
        }

        function updateContactArrayData(field, index, value) {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.contactInfo[field]) {
                applicant.contactInfo[field] = [];
            }
            applicant.contactInfo[field][index] = value;
            saveToLocalStorage(); // ✅ এই line অবশ্যই থাকতে হবে
        }

        function updateSocialMediaData(index, field, value) {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.contactInfo.socialMedia) {
                applicant.contactInfo.socialMedia = [];
            }
            if (!applicant.contactInfo.socialMedia[index]) {
                applicant.contactInfo.socialMedia[index] = {};
            }
            applicant.contactInfo.socialMedia[index][field] = value;
            saveToLocalStorage();
        }

        function updateLocationData(index, field, value) {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.travelInfo.locations) {
                applicant.travelInfo.locations = [];
            }
            if (!applicant.travelInfo.locations[index]) {
                applicant.travelInfo.locations[index] = {};
            }
            applicant.travelInfo.locations[index][field] = value;
            saveToLocalStorage();
        }

        function updateFamilyMemberData(index, field, value) {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.familyInfo.familyMembers) {
                applicant.familyInfo.familyMembers = [];
            }
            if (!applicant.familyInfo.familyMembers[index]) {
                applicant.familyInfo.familyMembers[index] = {};
            }
            applicant.familyInfo.familyMembers[index][field] = value;
            saveToLocalStorage();
        }

        function updatePreviousEmploymentData(index, field, value) {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.employmentInfo.previousEmployment) { // workInfo -> employmentInfo
                applicant.employmentInfo.previousEmployment = [];
            }
            if (!applicant.employmentInfo.previousEmployment) {
                applicant.employmentInfo.previousEmployment = [];
            }
            if (!applicant.employmentInfo.previousEmployment[index]) {
                applicant.employmentInfo.previousEmployment[index] = {};
            }
            applicant.employmentInfo.previousEmployment[index][field] = value;
            saveToLocalStorage();
        }

        function updateInstitutionData(index, field, value) {
            const applicant = state.applicants[state.currentApplicant];
            if (!applicant.educationalInfo.institutions) {
                applicant.educationalInfo.institutions = [];
            }
            if (!applicant.educationalInfo.institutions[index]) {
                applicant.educationalInfo.institutions[index] = {};
            }
            applicant.educationalInfo.institutions[index][field] = value;
            saveToLocalStorage();
        }

        // Utility functions
        // Updated toggle function to properly handle conditional blocks
        function toggleConditionalBlock(blockId, show) {
            const block = document.getElementById(blockId);
            if (block) {
                if (show) {
                    block.style.display = 'block';
                    block.classList.add('active');
                } else {
                    block.style.display = 'none';
                    block.classList.remove('active');
                }
            }
        }

        function updateApplicantData(category, field, value) {
            if (!state.applicants[state.currentApplicant][category]) {
                state.applicants[state.currentApplicant][category] = {};
            }
            state.applicants[state.currentApplicant][category][field] = value;
            saveToLocalStorage(); // ✅ এই line অবশ্যই থাকতে হবে
        }

        // Update navigation for preview mode
        function updateNavigationForPreview() {
            document.getElementById('prev-btn').classList.remove('hidden');
            document.getElementById('next-btn').classList.add('hidden');
            document.getElementById('next-applicant-btn').classList.add('hidden');
            document.getElementById('submit-btn').classList.add('hidden');

            // Update step navigation to show preview as active
            const stepNavItems = document.querySelectorAll('.step-nav-item');
            stepNavItems.forEach((item, index) => {
                if (index === state.totalSteps - 1) { // Last step (Other Information)
                    item.classList.add('completed', 'active');
                    item.innerHTML = `
                        <div class="flex items-center">
                            <div class="step-icon w-8 h-8 rounded-full flex items-center justify-center mr-3 bg-green-500 text-white">
                                <i class="fas fa-check text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-800">Application Preview</div>
                                <div class="text-xs text-gray-500">Review your application</div>
                            </div>
                        </div>
                    `;
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Update navigation for form mode
        function updateNavigationForForm() {
            document.getElementById('prev-btn').classList.remove('hidden');
            document.getElementById('next-btn').classList.remove('hidden');
            document.getElementById('next-applicant-btn').classList.add('hidden');
            document.getElementById('submit-btn').classList.add('hidden');

            generateStepNavigation();
        }

        // Proceed to next applicant
        function proceedToNextApplicant(currentApplicantIndex) {
            if (currentApplicantIndex < state.totalApplicants - 1) {
                state.currentApplicant = currentApplicantIndex + 1;
                state.currentStep = 0;
                state.showPreview = false;
                state.previewApplicant = null;

                generateFormSteps();
                generateStepNavigation();
                generateTabs();
                updateUI();
                saveToLocalStorage();

                // Scroll to top
                window.scrollTo(0, 0);
            }
        }

        // Show final submission
        function showFinalSubmission() {
            if (confirm('Are you sure you want to submit the entire application? This action cannot be undone.')) {
                submitEntireApplication();
            }
        }

        // Submit entire application
        // UPDATED submitEntireApplication() function
        function submitEntireApplication() {
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
            submitBtn.disabled = true;

            const submissionData = {
                pnr: state.pnr,
                applicants: state.applicants,
                nameOfApplicant: state.applicants[0].passportInfo.pi_sur_name,
                totalApplicants: state.totalApplicants,
                status: "completed",
                submittedAt: new Date().toISOString()
            };

            console.log(submissionData);

            fetch('server/submit-application.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(submissionData)
                })
                .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //     showSubmissionSuccess();
                //     // Clear both localStorage AND database draft
                //     localStorage.removeItem('usaVisaApplication-' + state.pnr);
                //     }
                // })
                .then(response => response.json())
                .then(data => {
                    setTimeout(() => {
                        window.location.href = 'application_success.php?pnr=' + state.pnr;
                    }, 3000);
                })
                .catch(error => {
                    console.error('Submission error:', error);
                    alert('Submission failed: ' + error.message);
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        }

        // Show submission success
        function showSubmissionSuccess() {
            const formStepsContainer = document.getElementById('form-steps');
            formStepsContainer.innerHTML = `
                <div class="step active fade-in">
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-check text-green-600 text-3xl"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-gray-800 mb-4">Application Submitted Successfully!</h2>
                        <p class="text-gray-600 mb-6 text-lg">
                            Your USA visa application has been submitted successfully.
                        </p>
                        <div class="bg-gray-50 p-6 rounded-lg max-w-md mx-auto">
                            <p class="text-sm text-gray-700 mb-2">
                                <strong>PNR Number:</strong> ${state.pnr}
                            </p>
                            <p class="text-sm text-gray-700">
                                <strong>Total Applicants:</strong> ${state.totalApplicants}
                            </p>
                        </div>
                        <p class="text-gray-500 mt-6">
                            Redirecting to confirmation page...
                        </p>
                    </div>
                </div>
            `;

            // Hide all navigation buttons
            document.getElementById('prev-btn').classList.add('hidden');
            document.getElementById('next-btn').classList.add('hidden');
            document.getElementById('next-applicant-btn').classList.add('hidden');
            document.getElementById('submit-btn').classList.add('hidden');
        }

        // Show preview for specific applicant
        function showApplicantPreview(applicantIndex) {
            state.showPreview = true;
            state.previewApplicant = applicantIndex;
            generateApplicantPreview(applicantIndex);
            updateNavigationForPreview();
        }

        // Hide preview and return to form
        function hideApplicantPreview() {
            state.showPreview = false;
            state.previewApplicant = null;
            generateFormSteps();
            updateNavigationForForm();
        }

        // Generate preview content for an applicant
        function generateApplicantPreview(applicantIndex) {
            const formStepsContainer = document.getElementById('form-steps');
            const applicant = state.applicants[applicantIndex];

            formStepsContainer.innerHTML = `
                <div class="step active fade-in">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Application Preview - Applicant ${applicantIndex + 1}</h2>
                        <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                            <i class="fas fa-check-circle mr-1"></i>Completed
                        </span>
                    </div>
                    
                    <div class="bg-white border border-gray-200 rounded-lg">
                        ${generatePreviewSections(applicant, applicantIndex)}
                    </div>
                    
                    <div class="mt-8 p-6 bg-blue-50 rounded-lg border border-blue-200">
                        <h3 class="text-lg font-semibold text-blue-800 mb-2">Next Steps</h3>
                        <p class="text-blue-700 mb-4">
                            Please review all information carefully. Once confirmed, you can proceed to the next applicant or submit the entire application.
                        </p>
                        
                        <div class="flex space-x-4">
                            <button onclick="hideApplicantPreview()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300 flex items-center">
                                <i class="fas fa-edit mr-2"></i>Edit Application
                            </button>
                            
                            ${applicantIndex < state.totalApplicants - 1 ? `
                                <button onclick="proceedToNextApplicant(${applicantIndex})" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300 flex items-center">
                                    Confirm & Next Applicant <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            ` : `
                                <button onclick="showFinalSubmission()" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition duration-300 flex items-center">
                                    Confirm & Final Submit <i class="fas fa-check-circle ml-2"></i>
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            `;
        }

        // Generate all preview sections
        function generatePreviewSections(applicant, applicantIndex) {
            const pi = applicant.passportInfo || {};
            const ci = applicant.contactInfo || {};
            const pp = applicant.passportInfo || {};
            const ti = applicant.travelInfo || {};
            const tci = applicant.travelInfo || {};
            const pust = applicant.travelHistory || {};
            const usci = applicant.usContactInfo || {};
            const fm = applicant.familyInfo || {};
            const wi = applicant.employmentInfo || {};
            const edi = applicant.educationalInfo || {};
            const oi = applicant.otherInfo || {};

            return `
                <div class="space-y-6 p-6">
                    <!-- Personal Information -->
                    <div class="preview-section">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                            <i class="fas fa-user mr-2 text-blue-500"></i>Personal Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><strong>Name:</strong> ${pi.pi_sur_name || ''} ${pi.pi_given_name || ''}</div>
                            <div><strong>Gender:</strong> ${pi.pi_gender || ''}</div>
                            <div><strong>Date of Birth:</strong> ${pi.pi_dob ? convertToDisplay(pi.pi_dob) : ''}</div>
                            <div><strong>Place of Birth:</strong> ${pi.pi_pob || ''}</div>
                            <div><strong>Marital Status:</strong> ${pi.pi_marital_status || ''}</div>
                            <div><strong>National ID:</strong> ${pi.pi_nid || 'Not provided'}</div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="preview-section">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                            <i class="fas fa-address-book mr-2 text-blue-500"></i>Contact Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><strong>Primary Phone:</strong> ${ci.pi_primary_no || ''}</div>
                            <div><strong>Email:</strong> ${ci.emails ? ci.emails[0] : ''}</div>
                            <div class="md:col-span-2">
                                <strong>Address:</strong> ${ci.pi_address_line_1 || ''} ${ci.pi_address_line_2 || ''}, ${ci.pi_address_city || ''}, ${ci.pi_address_state || ''} ${ci.pi_address_zip_code || ''}
                            </div>
                        </div>
                    </div>

                    <!-- Passport Information -->
                    <div class="preview-section">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                            <i class="fas fa-passport mr-2 text-blue-500"></i>Passport Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><strong>Passport Number:</strong> ${pp.pp_number || ''}</div>
                            <div><strong>Passport Type:</strong> ${pp.pp_type || ''}</div>
                            <div><strong>Issue Date:</strong> ${pp.pp_issue_date ? convertToDisplay(pp.pp_issue_date) : ''}</div>
                            <div><strong>Expiry Date:</strong> ${pp.pp_expiry_date ? convertToDisplay(pp.pp_expiry_date) : ''}</div>
                            <div><strong>Issuing Authority:</strong> ${pp.pp_issuing_authority || ''}</div>
                            <div><strong>Issued City:</strong> ${pp.pp_issued_city || ''}</div>
                        </div>
                    </div>

                    <!-- Travel Information -->
                    <div class="preview-section">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                            <i class="fas fa-plane mr-2 text-blue-500"></i>Travel Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><strong>Purpose:</strong> ${ti.ti_travel_purpose || ''}</div>
                            <div><strong>Travel Plans:</strong> ${ti.ti_have_travel_plan || ''}</div>
                            ${ti.ti_arrival_date ? `<div><strong>Arrival Date:</strong> ${convertToDisplay(ti.ti_arrival_date)}</div>` : ''}
                            ${ti.ti_departure_date ? `<div><strong>Departure Date:</strong> ${convertToDisplay(ti.ti_departure_date)}</div>` : ''}
                        </div>
                    </div>

                    <!-- Work Information -->
                    <div class="preview-section">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                            <i class="fas fa-briefcase mr-2 text-blue-500"></i>Work Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div><strong>Occupation:</strong> ${wi.wi_primary_occupation_type || ''}</div>
                            <div><strong>Company/School:</strong> ${wi.wi_company_or_school_name || ''}</div>
                            ${wi.wi_salary ? `<div><strong>Monthly Salary:</strong> ${wi.wi_salary}</div>` : ''}
                        </div>
                    </div>

                    <!-- Add more sections as needed -->
                </div>
            `;
        }

        function nextStep() {
            if (state.currentStep < state.totalSteps - 1) {
                state.currentStep++;
                generateFormSteps();
                generateStepNavigation();
                updateUI();
            } else {
                // Last step - check if more applicants or show submit
                state.applicants[state.currentApplicant].completed = true;

                if (state.currentApplicant < state.totalApplicants - 1) {
                    // Show "Next Applicant" button
                    document.getElementById('next-applicant-btn').classList.remove('hidden');
                    document.getElementById('next-btn').classList.add('hidden');
                    // Auto show preview for current applicant
                    showApplicantPreview(state.currentApplicant);
                } else {
                    // Last applicant - show submit button
                    document.getElementById('submit-btn').classList.remove('hidden');
                    document.getElementById('next-btn').classList.add('hidden');
                    // Auto show preview for last applicant
                    showApplicantPreview(state.currentApplicant);
                }
            }
            saveToLocalStorage();
        }

        function previousStep() {
            if (state.showPreview) {
                // If in preview mode, go back to form
                hideApplicantPreview();
            } else if (state.currentStep > 0) {
                state.currentStep--;
                generateFormSteps();
                generateStepNavigation();
                updateUI();
            }
            saveToLocalStorage();
        }

        function nextApplicant() {
            if (state.currentApplicant < state.totalApplicants - 1) {
                state.currentApplicant++;
                state.currentStep = 0;
                document.getElementById('next-applicant-btn').classList.add('hidden');
                document.getElementById('next-btn').classList.remove('hidden');
                switchApplicant(state.currentApplicant);
            }
        }

        function switchApplicant(applicantIndex) {
            state.currentApplicant = applicantIndex;
            state.currentStep = 0;
            state.showPreview = false; // IMPORTANT: Preview mode off korbe
            state.previewApplicant = null;

            generateFormSteps();
            generateStepNavigation();
            generateTabs();
            updateUI();

            // Button visibility reset korbe
            document.getElementById('next-btn').classList.remove('hidden');
            document.getElementById('next-applicant-btn').classList.add('hidden');
            document.getElementById('submit-btn').classList.add('hidden');

            saveToLocalStorage();
        }

        function updateUI() {
            document.getElementById('current-step').textContent = state.currentStep + 1;
            document.getElementById('current-applicant-number').textContent = state.currentApplicant + 1;

            const individualProgressPercentage = ((state.currentStep + 1) / state.totalSteps) * 100;
            document.getElementById('individual-progress-bar').style.width = `${individualProgressPercentage}%`;
        }

        function saveToLocalStorage() {
            const applicationData = {
                pnr: state.pnr,
                nameOfApplicant: state.applicants[0]?.passportInfo?.pi_sur_name || '',
                totalApplicants: state.totalApplicants,
                applicants: state.applicants,
                currentApplicant: state.currentApplicant,
                currentStep: state.currentStep,
                timestamp: new Date().toISOString()
            };
            localStorage.setItem('usaVisaApplication-' + state.pnr, JSON.stringify(applicationData));
        }

        function saveAndExit() {
            saveToLocalStorage();
            alert('Your application has been saved. You can return later to complete it.');
        }

        function submitApplication() {
            // Submit application logic here
            alert('Application submitted successfully!');
        }

        // Generate tabs and step navigation functions
        function generateTabs() {
            const tabsContainer = document.getElementById('applicant-tabs');
            tabsContainer.innerHTML = '';

            for (let i = 0; i < state.totalApplicants; i++) {
                const applicant = state.applicants[i];
                const tab = document.createElement('div');
                tab.className = `tab py-3 px-6 text-sm font-medium flex flex-col items-center min-w-32 ${i === state.currentApplicant ? 'active bg-blue-600 text-white' : 'text-gray-500 hover:text-gray-700'}`;
                tab.dataset.applicant = i;
                tab.innerHTML = `
                    <div class="flex justify-between w-full items-center mb-1">
                        <span>Applicant ${i + 1} &nbsp;</span>
                        ${applicant.completed ? '<i class="fas fa-check-circle text-green-500"></i>' : ''}
                    </div>
                `;
                tab.addEventListener('click', function() {
                    switchApplicant(parseInt(this.dataset.applicant));
                });
                tabsContainer.appendChild(tab);
            }
        }

        function generateStepNavigation() {
            const stepNavContainer = document.getElementById('step-navigation');
            stepNavContainer.innerHTML = '';

            state.steps.forEach((step, index) => {
                const isCurrent = index === state.currentStep;
                const stepNavItem = document.createElement('div');
                stepNavItem.className = `step-nav-item p-3 rounded-lg ${isCurrent ? 'active current' : ''}`;
                stepNavItem.dataset.step = index;
                stepNavItem.innerHTML = `
                    <div class="flex items-center">
                        <div class="step-icon w-8 h-8 rounded-full flex items-center justify-center mr-3 ${isCurrent ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600'}">
                            <i class="fas ${step.icon} text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-gray-800">${step.name}</div>
                            <div class="text-xs text-gray-500">${step.description}</div>
                        </div>
                    </div>
                `;
                stepNavItem.addEventListener('click', function() {
                    const stepIndex = parseInt(this.dataset.step);
                    jumpToStep(stepIndex);
                });
                stepNavContainer.appendChild(stepNavItem);
            });
        }

        function jumpToStep(stepIndex) {
            state.currentStep = stepIndex;
            generateFormSteps();
            generateStepNavigation();
            updateUI();
            saveToLocalStorage();
        }

        function loadSavedApplication() {
            if (state.pnr) {
                initializeFormFromState();
            } else {
                const keys = Object.keys(localStorage);
                const appKeys = keys.filter(key => key.startsWith('usaVisaApplication-'));

                if (appKeys.length > 0) {
                    // Load the first one
                    const firstAppKey = appKeys[0];
                    const appData = JSON.parse(localStorage.getItem(firstAppKey));
                    loadApplicationData(appData);
                } else {
                    alert('No saved applications found.');
                }
            }
        }

        function handleDateChange(category, field, value) {
            if (isValidDate(value)) {
                const isoDate = convertToISO(value);
                updateApplicantData(category, field, isoDate);
            } else {
                alert('Please enter date in DD/MM/YYYY format');
                // ফোকাস ফিরিয়ে দিন এবং ভ্যালু ক্লিয়ার করুন
                event.target.focus();
                event.target.value = '';
            }
        }

        // আপনার existing code এর নিচে এই ফাংশনগুলো যোগ করুন

        // তারিখ validation
        function isValidDate(dateString) {
            const pattern = /^(\d{2})\/(\d{2})\/(\d{4})$/;
            if (!pattern.test(dateString)) return false;

            const day = parseInt(dateString.split('/')[0]);
            const month = parseInt(dateString.split('/')[1]);
            const year = parseInt(dateString.split('/')[2]);

            // সহজ validation
            if (day < 1 || day > 31) return false;
            if (month < 1 || month > 12) return false;
            if (year < 1900 || year > 2100) return false;

            return true;
        }

        // DD/MM/YYYY থেকে YYYY-MM-DD
        function convertToISO(dateString) {
            if (!isValidDate(dateString)) return '';
            const parts = dateString.split('/');
            return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
        }

        // YYYY-MM-DD থেকে DD/MM/YYYY
        function convertToDisplay(isoDate) {
            if (!isoDate) return '';
            const parts = isoDate.split('-');
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        // তারিখ change handler
        function handleDateChange(category, field, value) {
            if (value === '') {
                updateApplicantData(category, field, '');
                return;
            }

            if (isValidDate(value)) {
                const isoDate = convertToISO(value);
                updateApplicantData(category, field, isoDate);
            } else {
                alert('Invalid date format. Please use DD/MM/YYYY');
                event.target.value = '';
                updateApplicantData(category, field, '');
            }
        }
    </script>
</body>

</html>