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
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .step { display: none; }
        .step.active { display: block; }
        .tab { cursor: pointer; transition: all 0.3s ease; }
        .tab.active { background-color: #3b82f6; color: white; }
        .progress-bar { transition: width 0.5s ease-in-out; }
        .step-nav-item { cursor: pointer; transition: all 0.3s ease; border-left: 3px solid transparent; }
        .step-nav-item:hover { background-color: #f3f4f6; }
        .step-nav-item.active { border-left-color: #3b82f6; background-color: #eff6ff; }
        .step-nav-item.completed .step-icon { background-color: #10b981; color: white; }
        .step-nav-item.current .step-icon { background-color: #3b82f6; color: white; }
        .dynamic-field-group { border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; margin-bottom: 1rem; background-color: #f9fafb; }
        .conditional-block { display: none; }
        .conditional-block.active { display: block; }
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
        // Application state
        const state = {
            currentApplicant: <?= $dbApplicationData['currentApplicant'] ?? 0 ?>,
            currentStep: <?= $dbApplicationData['currentStep'] ?? 0 ?>,
            totalSteps: 10,
            totalApplicants: <?= $dbApplicationData['totalApplicants'] ?? 1 ?>,
            pnr: '<?= $dbApplicationData['pnr'] ?? '' ?>',
            applicants: <?= json_encode($dbApplicationData['applicants'] ?? []) ?>,
            steps: [
                { name: 'Personal Information (PI)', icon: 'fa-user', description: 'Personal and contact details' },
                { name: 'Travel Information (TI)', icon: 'fa-plane', description: 'Travel plans and purpose' },
                { name: 'Passport Information (PP)', icon: 'fa-passport', description: 'Passport details' },
                { name: 'Travel Companion Information (TCI)', icon: 'fa-users', description: 'Travel companions details' },
                { name: 'Previous U.S. Travel (PUST)', icon: 'fa-history', description: 'Previous travel history to USA' },
                { name: 'U.S. Contact Information (USCI)', icon: 'fa-address-book', description: 'Contacts in USA' },
                { name: 'Family Member Information (FM)', icon: 'fa-user-friends', description: 'Family members details' },
                { name: 'Work Information (WI)', icon: 'fa-briefcase', description: 'Employment and work history' },
                { name: 'Educational Information (EDI)', icon: 'fa-graduation-cap', description: 'Educational background' },
                { name: 'Other Information (OI)', icon: 'fa-info-circle', description: 'Additional information' }
            ]
        };

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
            if (state.pnr) {
                initializeFormFromState();
            }
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
            switch(stepIndex) {
                case 0: return generatePersonalInfoStep(applicant);
                case 1: return generateTravelInfoStep(applicant);
                case 2: return generatePassportInfoStep(applicant);
                case 3: return generateTravelCompanionStep(applicant);
                case 4: return generatePreviousTravelStep(applicant);
                case 5: return generateUSContactStep(applicant);
                case 6: return generateFamilyInfoStep(applicant);
                case 7: return generateWorkInfoStep(applicant);
                case 8: return generateEducationInfoStep(applicant);
                case 9: return generateOtherInfoStep(applicant);
                default: return '<p>Step content not defined.</p>';
            }
        }

        // Personal Information Step (Based on Excel PI section)
        function generatePersonalInfoStep(applicant) {
            const pi = applicant.passportInfo || {};
            const ci = applicant.contactInfo || {};
            
            return `
                <div class="space-y-6">
                    <!-- Basic Personal Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Surname *</label>
                            <input type="text" name="pi_sur_name" 
                                   value="${pi.pi_sur_name || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateApplicantData('passportInfo', 'pi_sur_name', this.value)" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Given Name *</label>
                            <input type="text" name="pi_given_name" 
                                   value="${pi.pi_given_name || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateApplicantData('passportInfo', 'pi_given_name', this.value)" required>
                        </div>
                    </div>

                    <!-- Other Name Toggle -->
                    <div>
                        <label class="block text-gray-700 mb-2">Do you have other name?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pi_have_other_name" value="1" 
                                       ${pi.pi_have_other_name ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('other-name', this.checked); updateApplicantData('passportInfo', 'pi_have_other_name', this.checked)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pi_have_other_name" value="0" 
                                       ${!pi.pi_have_other_name ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('other-name', this.checked); updateApplicantData('passportInfo', 'pi_have_other_name', this.checked)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <!-- Conditional Other Name Fields -->
                    <div id="other-name" class="conditional-block ${pi.pi_have_other_name ? 'active' : ''}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Other Surname</label>
                                <input type="text" name="pi_other_sur_name" 
                                       value="${pi.pi_other_sur_name || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateApplicantData('passportInfo', 'pi_other_sur_name', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Other Given Name</label>
                                <input type="text" name="pi_other_given_name" 
                                       value="${pi.pi_other_given_name || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateApplicantData('passportInfo', 'pi_other_given_name', this.value)">
                            </div>
                        </div>
                    </div>

                    <!-- Gender and Marital Status -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Gender *</label>
                            <select name="pi_gender" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('passportInfo', 'pi_gender', this.value)" required>
                                <option value="">Select Gender</option>
                                <option value="Male" ${(pi.pi_gender === 'Male') ? 'selected' : ''}>Male</option>
                                <option value="Female" ${(pi.pi_gender === 'Female') ? 'selected' : ''}>Female</option>
                                <option value="Other" ${(pi.pi_gender === 'Other') ? 'selected' : ''}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Marital Status *</label>
                            <select name="pi_marital_status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('passportInfo', 'pi_marital_status', this.value)" required>
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
                        <input type="date" name="pi_dob" 
                               value="${pi.pi_dob || ''}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               onchange="updateApplicantData('passportInfo', 'pi_dob', this.value)" required>
                    </div>

                    <!-- Place and Country of Birth -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Place of Birth *</label>
                            <input type="text" name="pi_pob" 
                                   value="${pi.pi_pob || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateApplicantData('passportInfo', 'pi_pob', this.value)" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Country of Birth *</label>
                            <select name="pi_cob" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    onchange="updateApplicantData('passportInfo', 'pi_cob', this.value)" required>
                                <option value="">Select Country</option>
                                <option value="USA" ${(pi.pi_cob === 'USA') ? 'selected' : ''}>United States</option>
                                <option value="UK" ${(pi.pi_cob === 'UK') ? 'selected' : ''}>United Kingdom</option>
                                <option value="BD" ${(pi.pi_cob === 'BD') ? 'selected' : ''}>Bangladesh</option>
                                <option value="IN" ${(pi.pi_cob === 'IN') ? 'selected' : ''}>India</option>
                                <option value="CA" ${(pi.pi_cob === 'CA') ? 'selected' : ''}>Canada</option>
                            </select>
                        </div>
                    </div>

                    <!-- Home Address Section -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Home Address</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Address Line 1 *</label>
                                <input type="text" name="pi_address_line_1" 
                                       value="${ci.pi_address_line_1 || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateApplicantData('contactInfo', 'pi_address_line_1', this.value)" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Address Line 2</label>
                                <input type="text" name="pi_address_line_2" 
                                       value="${ci.pi_address_line_2 || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateApplicantData('contactInfo', 'pi_address_line_2', this.value)">
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">City *</label>
                                    <input type="text" name="pi_address_city" 
                                           value="${ci.pi_address_city || ''}" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onchange="updateApplicantData('contactInfo', 'pi_address_city', this.value)" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">State *</label>
                                    <input type="text" name="pi_address_state" 
                                           value="${ci.pi_address_state || ''}" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onchange="updateApplicantData('contactInfo', 'pi_address_state', this.value)" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Zip Code *</label>
                                    <input type="text" name="pi_address_zip_code" 
                                           value="${ci.pi_address_zip_code || ''}" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onchange="updateApplicantData('contactInfo', 'pi_address_zip_code', this.value)" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Country *</label>
                                <select name="pi_address_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('contactInfo', 'pi_address_country', this.value)" required>
                                    <option value="">Select Country</option>
                                    <option value="USA" ${(ci.pi_address_country === 'USA') ? 'selected' : ''}>United States</option>
                                    <option value="UK" ${(ci.pi_address_country === 'UK') ? 'selected' : ''}>United Kingdom</option>
                                    <option value="BD" ${(ci.pi_address_country === 'BD') ? 'selected' : ''}>Bangladesh</option>
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
                                    <input type="radio" name="is_same_mailing_address" value="1" 
                                           ${ci.is_same_mailing_address ? 'checked' : ''}
                                           onchange="toggleConditionalBlock('mailing-address', !this.checked); updateApplicantData('contactInfo', 'is_same_mailing_address', this.checked)">
                                    <span class="ml-2">Yes</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="is_same_mailing_address" value="0" 
                                           ${!ci.is_same_mailing_address ? 'checked' : ''}
                                           onchange="toggleConditionalBlock('mailing-address', !this.checked); updateApplicantData('contactInfo', 'is_same_mailing_address', this.checked)">
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>

                        <div id="mailing-address" class="conditional-block ${!ci.is_same_mailing_address ? 'active' : ''}">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Address Line 1 *</label>
                                    <input type="text" name="pi_mail_address_line_1" 
                                           value="${ci.pi_mail_address_line_1 || ''}" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onchange="updateApplicantData('contactInfo', 'pi_mail_address_line_1', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Address Line 2</label>
                                    <input type="text" name="pi_mail_address_line_2" 
                                           value="${ci.pi_mail_address_line_2 || ''}" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onchange="updateApplicantData('contactInfo', 'pi_mail_address_line_2', this.value)">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-gray-700 mb-2">City *</label>
                                        <input type="text" name="pi_mail_address_city" 
                                               value="${ci.pi_mail_address_city || ''}" 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               onchange="updateApplicantData('contactInfo', 'pi_mail_address_city', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">State *</label>
                                        <input type="text" name="pi_mail_address_state" 
                                               value="${ci.pi_mail_address_state || ''}" 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               onchange="updateApplicantData('contactInfo', 'pi_mail_address_state', this.value)">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-2">Zip Code *</label>
                                        <input type="text" name="pi_mail_address_zip_code" 
                                               value="${ci.pi_mail_address_zip_code || ''}" 
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               onchange="updateApplicantData('contactInfo', 'pi_mail_address_zip_code', this.value)">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Country *</label>
                                    <select name="pi_mail_address_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('contactInfo', 'pi_mail_address_country', this.value)">
                                        <option value="">Select Country</option>
                                        <option value="USA" ${(ci.pi_mail_address_country === 'USA') ? 'selected' : ''}>United States</option>
                                        <option value="UK" ${(ci.pi_mail_address_country === 'UK') ? 'selected' : ''}>United Kingdom</option>
                                        <option value="BD" ${(ci.pi_mail_address_country === 'BD') ? 'selected' : ''}>Bangladesh</option>
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
                                    <input type="tel" name="pi_primary_no" 
                                           value="${ci.pi_primary_no || ''}" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onchange="updateApplicantData('contactInfo', 'pi_primary_no', this.value)" required>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Secondary Phone Number</label>
                                    <input type="tel" name="pi_secondary_no" 
                                           value="${ci.pi_secondary_no || ''}" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onchange="updateApplicantData('contactInfo', 'pi_secondary_no', this.value)">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Work Phone Number</label>
                                    <input type="tel" name="pi_work_no" 
                                           value="${ci.pi_work_no || ''}" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onchange="updateApplicantData('contactInfo', 'pi_work_no', this.value)">
                                </div>
                            </div>
                        </div>

                        <!-- Email Addresses -->
                        <div class="mb-6">
                            <h4 class="font-medium text-gray-700 mb-3">Email Addresses</h4>
                            <div id="email-fields">
                                ${generateEmailFields(ci.emails || [''])}
                            </div>
                            <button type="button" onclick="addEmailField()" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg flex items-center">
                                <i class="fas fa-plus mr-2"></i> Add Another Email
                            </button>
                        </div>

                        <!-- Social Media -->
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
                            <input type="text" name="pi_nid" 
                                   value="${pi.pi_nid || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateApplicantData('passportInfo', 'pi_nid', this.value)">
                        </div>
                    </div>

                    <!-- Other Nationality -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Other Nationality</h3>
                        <div class="mb-4">
                            <label class="block text-gray-700 mb-2">Do you have any other nationality?</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pi_have_other_nationality" value="1" 
                                           ${pi.pi_have_other_nationality ? 'checked' : ''}
                                           onchange="toggleConditionalBlock('other-nationality', this.checked); updateApplicantData('passportInfo', 'pi_have_other_nationality', this.checked)">
                                    <span class="ml-2">Yes</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pi_have_other_nationality" value="0" 
                                           ${!pi.pi_have_other_nationality ? 'checked' : ''}
                                           onchange="toggleConditionalBlock('other-nationality', this.checked); updateApplicantData('passportInfo', 'pi_have_other_nationality', this.checked)">
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>

                        <div id="other-nationality" class="conditional-block ${pi.pi_have_other_nationality ? 'active' : ''}">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Country</label>
                                    <select name="pi_other_nationality_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            onchange="updateApplicantData('passportInfo', 'pi_other_nationality_country', this.value)">
                                        <option value="">Select Country</option>
                                        <option value="USA" ${(pi.pi_other_nationality_country === 'USA') ? 'selected' : ''}>United States</option>
                                        <option value="UK" ${(pi.pi_other_nationality_country === 'UK') ? 'selected' : ''}>United Kingdom</option>
                                        <option value="BD" ${(pi.pi_other_nationality_country === 'BD') ? 'selected' : ''}>Bangladesh</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Do you have that country passport?</label>
                                    <div class="flex space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="pi_have_other_country_paasport" value="1" 
                                                   ${pi.pi_have_other_country_paasport ? 'checked' : ''}
                                                   onchange="toggleConditionalBlock('other-passport', this.checked); updateApplicantData('passportInfo', 'pi_have_other_country_paasport', this.checked)">
                                            <span class="ml-2">Yes</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="pi_have_other_country_paasport" value="0" 
                                                   ${!pi.pi_have_other_country_paasport ? 'checked' : ''}
                                                   onchange="toggleConditionalBlock('other-passport', this.checked); updateApplicantData('passportInfo', 'pi_have_other_country_paasport', this.checked)">
                                            <span class="ml-2">No</span>
                                        </label>
                                    </div>
                                </div>
                                <div id="other-passport" class="conditional-block ${pi.pi_have_other_country_paasport ? 'active' : ''}">
                                    <label class="block text-gray-700 mb-2">Passport Number</label>
                                    <input type="text" name="pi_other_country_passport" 
                                           value="${pi.pi_other_country_passport || ''}" 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           onchange="updateApplicantData('passportInfo', 'pi_other_country_passport', this.value)">
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
                                    <input type="radio" name="pi_have_other_permanent_residence" value="1" 
                                           ${pi.pi_have_other_permanent_residence ? 'checked' : ''}
                                           onchange="toggleConditionalBlock('other-residence', this.checked); updateApplicantData('passportInfo', 'pi_have_other_permanent_residence', this.checked)">
                                    <span class="ml-2">Yes</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="pi_have_other_permanent_residence" value="0" 
                                           ${!pi.pi_have_other_permanent_residence ? 'checked' : ''}
                                           onchange="toggleConditionalBlock('other-residence', this.checked); updateApplicantData('passportInfo', 'pi_have_other_permanent_residence', this.checked)">
                                    <span class="ml-2">No</span>
                                </label>
                            </div>
                        </div>

                        <div id="other-residence" class="conditional-block ${pi.pi_have_other_permanent_residence ? 'active' : ''}">
                            <div>
                                <label class="block text-gray-700 mb-2">Country</label>
                                <select name="pi_other_permanent_residence_country" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        onchange="updateApplicantData('passportInfo', 'pi_other_permanent_residence_country', this.value)">
                                    <option value="">Select Country</option>
                                    <option value="USA" ${(pi.pi_other_permanent_residence_country === 'USA') ? 'selected' : ''}>United States</option>
                                    <option value="UK" ${(pi.pi_other_permanent_residence_country === 'UK') ? 'selected' : ''}>United Kingdom</option>
                                    <option value="BD" ${(pi.pi_other_permanent_residence_country === 'BD') ? 'selected' : ''}>Bangladesh</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-4">* Required fields</p>
            `;
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
                                onchange="toggleTravelPlanFields(this.value); updateApplicantData('travelInfo', 'ti_have_travel_plan', this.value)">
                            <option value="">Select</option>
                            <option value="yes" ${(ti.ti_have_travel_plan === 'yes') ? 'selected' : ''}>Yes</option>
                            <option value="no" ${(ti.ti_have_travel_plan === 'no') ? 'selected' : ''}>No</option>
                        </select>
                    </div>

                    <!-- No Travel Plan Fields -->
                    <div id="no-travel-plan" class="conditional-block ${ti.ti_have_travel_plan === 'no' ? 'active' : ''}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Intended date of arrival</label>
                                <input type="date" name="ti_intended_arrival_date" 
                                       value="${ti.ti_intended_arrival_date || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateApplicantData('travelInfo', 'ti_intended_arrival_date', this.value)">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-2">Length of stay</label>
                                <input type="text" name="ti_stay_length" 
                                       value="${ti.ti_stay_length || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateApplicantData('travelInfo', 'ti_stay_length', this.value)">
                            </div>
                        </div>
                    </div>

                    <!-- Yes Travel Plan Fields -->
                    <div id="yes-travel-plan" class="conditional-block ${ti.ti_have_travel_plan === 'yes' ? 'active' : ''}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-2">Date of Arrival in the USA</label>
                                <input type="date" name="ti_arrival_date" 
                                       value="${ti.ti_arrival_date || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateApplicantData('travelInfo', 'ti_arrival_date', this.value)">
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
                                <input type="date" name="ti_departure_date" 
                                       value="${ti.ti_departure_date || ''}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       onchange="updateApplicantData('travelInfo', 'ti_departure_date', this.value)">
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
                                onchange="updateApplicantData('travelInfo', 'trip_payment', this.value)">
                            <option value="">Select</option>
                            <option value="Self" ${(ti.trip_payment === 'Self') ? 'selected' : ''}>Self</option>
                            <option value="Other person" ${(ti.trip_payment === 'Other person') ? 'selected' : ''}>Other person</option>
                            <option value="Present employer" ${(ti.trip_payment === 'Present employer') ? 'selected' : ''}>Present employer</option>
                            <option value="Employer in the USA" ${(ti.trip_payment === 'Employer in the USA') ? 'selected' : ''}>Employer in the USA</option>
                            <option value="Other Company" ${(ti.trip_payment === 'Other Company') ? 'selected' : ''}>Other Company</option>
                        </select>
                    </div>
                </div>
            `;
        }

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
                            <input type="date" name="pp_issue_date" 
                                   value="${pp.pp_issue_date || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateApplicantData('passportInfo', 'pp_issue_date', this.value)" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">Passport Expiry Date *</label>
                            <input type="date" name="pp_expiry_date" 
                                   value="${pp.pp_expiry_date || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateApplicantData('passportInfo', 'pp_expiry_date', this.value)" required>
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
                                       onchange="toggleConditionalBlock('lost-passport', this.checked); updateApplicantData('passportInfo', 'pp_have_stolen', this.checked)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pp_have_stolen" value="0" 
                                       ${!pp.pp_have_stolen ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('lost-passport', this.checked); updateApplicantData('passportInfo', 'pp_have_stolen', this.checked)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>

                    <div id="lost-passport" class="conditional-block ${pp.pp_have_stolen ? 'active' : ''}">
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

        function generateSocialMediaFields(socialMedia) {
            return socialMedia.map((item, index) => `
                <div class="dynamic-field-group">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-medium text-gray-700">Social Media Profile ${index + 1}</h4>
                        ${index > 0 ? `
                        <button type="button" class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-lg text-sm" onclick="removeSocialMediaField(${index})">
                            Remove
                        </button>
                        ` : ''}
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 mb-2">Platform</label>
                            <input type="text" 
                                   value="${item.platform || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateSocialMediaData(${index}, 'platform', this.value)">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-2">User Name/Link</label>
                            <input type="text" 
                                   value="${item.username || ''}" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   onchange="updateSocialMediaData(${index}, 'username', this.value)">
                        </div>
                    </div>
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
                applicant.contactInfo.socialMedia = [{platform: '', username: ''}];
            }
            applicant.contactInfo.socialMedia.push({platform: '', username: ''});
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

        function removeContactField(type, index) {
            const applicant = state.applicants[state.currentApplicant];
            if (applicant.contactInfo[type]) {
                applicant.contactInfo[type].splice(index, 1);
                generateFormSteps();
                saveToLocalStorage();
            }
        }

        function removeSocialMediaField(index) {
            const applicant = state.applicants[state.currentApplicant];
            if (applicant.contactInfo.socialMedia && applicant.contactInfo.socialMedia.length > 1) {
                applicant.contactInfo.socialMedia.splice(index, 1);
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

        function toggleTravelPlanFields(value) {
            document.getElementById('no-travel-plan').classList.toggle('active', value === 'no');
            document.getElementById('yes-travel-plan').classList.toggle('active', value === 'yes');
        }

        // Utility functions
        function toggleConditionalBlock(blockId, show) {
            const block = document.getElementById(blockId);
            if (block) {
                if (show) {
                    block.classList.add('active');
                } else {
                    block.classList.remove('active');
                }
            }
        }

        function updateApplicantData(category, field, value) {
            if (!state.applicants[state.currentApplicant][category]) {
                state.applicants[state.currentApplicant][category] = {};
            }
            state.applicants[state.currentApplicant][category][field] = value;
            saveToLocalStorage();
        }

        function nextStep() {
            if (state.currentStep < state.totalSteps - 1) {
                state.currentStep++;
                generateFormSteps();
                generateStepNavigation();
                updateUI();
            } else {
                // Last step - show summary or move to next applicant
                if (state.currentApplicant < state.totalApplicants - 1) {
                    document.getElementById('next-applicant-btn').classList.remove('hidden');
                    document.getElementById('next-btn').classList.add('hidden');
                } else {
                    document.getElementById('submit-btn').classList.remove('hidden');
                    document.getElementById('next-btn').classList.add('hidden');
                }
            }
            saveToLocalStorage();
        }

        function previousStep() {
            if (state.currentStep > 0) {
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
            generateFormSteps();
            generateStepNavigation();
            updateUI();
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
            localStorage.setItem('usaVisaApplication-'+state.pnr, JSON.stringify(applicationData));
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
                        <span>Applicant ${i + 1}</span>
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

        // Placeholder functions for other steps
        function generateTravelCompanionStep(applicant) { 
            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Are you traveling with anyone?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="tci_have_anyone" value="1" 
                                       ${applicant.travelInfo.tci_have_anyone ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('travel-companion', this.checked); updateApplicantData('travelInfo', 'tci_have_anyone', this.checked)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="tci_have_anyone" value="0" 
                                       ${!applicant.travelInfo.tci_have_anyone ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('travel-companion', this.checked); updateApplicantData('travelInfo', 'tci_have_anyone', this.checked)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>
                    <!-- Add more travel companion fields as needed -->
                </div>
            `;
        }
        
        function generatePreviousTravelStep(applicant) { 
            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Have you ever issued a visa to the USA?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ever_issued" value="1" 
                                       ${applicant.travelHistory.pust_have_ever_issued ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('previous-visa', this.checked); updateApplicantData('travelHistory', 'pust_have_ever_issued', this.checked)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="pust_have_ever_issued" value="0" 
                                       ${!applicant.travelHistory.pust_have_ever_issued ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('previous-visa', this.checked); updateApplicantData('travelHistory', 'pust_have_ever_issued', this.checked)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>
                    <!-- Add more previous travel fields as needed -->
                </div>
            `;
        }
        
        function generateUSContactStep(applicant) { 
            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Contact Type</label>
                        <select name="usci_contact_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateApplicantData('usContactInfo', 'usci_contact_type', this.value)">
                            <option value="">Select Type</option>
                            <option value="Person" ${(applicant.usContactInfo.usci_contact_type === 'Person') ? 'selected' : ''}>Person</option>
                            <option value="Company" ${(applicant.usContactInfo.usci_contact_type === 'Company') ? 'selected' : ''}>Company</option>
                            <option value="Hotel" ${(applicant.usContactInfo.usci_contact_type === 'Hotel') ? 'selected' : ''}>Hotel</option>
                        </select>
                    </div>
                    <!-- Add more US contact fields as needed -->
                </div>
            `;
        }
        
        function generateFamilyInfoStep(applicant) { 
            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Is your father in the USA?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="fm_in_usa" value="1" 
                                       ${applicant.familyInfo.fm_in_usa ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('father-in-usa', this.checked); updateApplicantData('familyInfo', 'fm_in_usa', this.checked)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="fm_in_usa" value="0" 
                                       ${!applicant.familyInfo.fm_in_usa ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('father-in-usa', this.checked); updateApplicantData('familyInfo', 'fm_in_usa', this.checked)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>
                    <!-- Add more family info fields as needed -->
                </div>
            `;
        }
        
        function generateWorkInfoStep(applicant) { 
            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Occupation</label>
                        <input type="text" name="wi_occupation" 
                               value="${applicant.employmentInfo.wi_occupation || ''}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               onchange="updateApplicantData('employmentInfo', 'wi_occupation', this.value)">
                    </div>
                    <!-- Add more work info fields as needed -->
                </div>
            `;
        }
        
        function generateEducationInfoStep(applicant) { 
            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Have you attended any educational institution at a secondary level or above?</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="edi_have_attended_secondary_level" value="1" 
                                       ${applicant.educationalInfo.edi_have_attended_secondary_level ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('education-history', this.checked); updateApplicantData('educationalInfo', 'edi_have_attended_secondary_level', this.checked)">
                                <span class="ml-2">Yes</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="edi_have_attended_secondary_level" value="0" 
                                       ${!applicant.educationalInfo.edi_have_attended_secondary_level ? 'checked' : ''}
                                       onchange="toggleConditionalBlock('education-history', this.checked); updateApplicantData('educationalInfo', 'edi_have_attended_secondary_level', this.checked)">
                                <span class="ml-2">No</span>
                            </label>
                        </div>
                    </div>
                    <!-- Add more education info fields as needed -->
                </div>
            `;
        }
        
        function generateOtherInfoStep(applicant) { 
            return `
                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 mb-2">List of Languages Spoken</label>
                        <textarea name="oi_spoken_language_list" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  onchange="updateApplicantData('otherInfo', 'oi_spoken_language_list', this.value)">${applicant.otherInfo.oi_spoken_language_list || ''}</textarea>
                    </div>
                    <!-- Add more other info fields as needed -->
                </div>
            `;
        }

        function loadSavedApplication() {
            // Load from DB data that was passed from PHP
            initializeFormFromState();
        }
    </script>
</body>
</html>