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
            
            console.log('Initializing application...');
            console.log('PNR from URL:', pnrFromUrl);
            console.log('State PNR:', state.pnr);
            
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
                passportInfo: {},
                nidInfo: {},
                contactInfo: {
                    emails: [''],
                    phones: [''],
                    socialMedia: [{
                        platform: '',
                        username: ''
                    }]
                },
                familyInfo: {
                    familyMembers: []
                },
                accommodationDetails: {},
                employmentInfo: {
                    previousEmployment: []
                },
                incomeExpenditure: {},
                travelInfo: {
                    locations: [{
                        address_line_1: '',
                        address_line_2: '',
                        city: '',
                        state: '',
                        zip_code: ''
                    }]
                },
                travelHistory: {},
                usContactInfo: {},
                educationalInfo: {
                    institutions: []
                },
                otherInfo: {}
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
        const countries = [{
                code: 'USA',
                name: 'United States'
            },
            {
                code: 'UK',
                name: 'United Kingdom'
            },
            {
                code: 'BD',
                name: 'Bangladesh'
            },
            {
                code: 'IN',
                name: 'India'
            },
            {
                code: 'CA',
                name: 'Canada'
            },
            {
                code: 'AU',
                name: 'Australia'
            },
            {
                code: 'DE',
                name: 'Germany'
            },
            {
                code: 'FR',
                name: 'France'
            }
        ];

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

        // Erpor theke amr form er function 
        // Form er function shesh
        
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
            generateFormSteps();
            saveToLocalStorage();
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
            generateFormSteps();
            saveToLocalStorage();
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
            generateFormSteps();
            saveToLocalStorage();
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
            saveToLocalStorage();
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
            
            // Auto-save both localStorage AND database
            saveToLocalStorage();
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
                submittedAt: new Date().toISOString()
            };

            fetch('server/submit-application.php', {
                method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                },
                body: JSON.stringify(submissionData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                showSubmissionSuccess();
                // Clear both localStorage AND database draft
                localStorage.removeItem('usaVisaApplication-' + state.pnr);
                
                // Update database status to 'submitted'
                return fetch('server/update_application_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({pnr: state.pnr, status: 'submitted'})
                });
                } else {
                throw new Error(data.message || 'Submission failed');
                }
            })
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