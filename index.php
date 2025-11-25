<?php
require 'server/db_connection.php';

try {
    // Load all applications from database
    $stmt = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC");
    $applications = [];

    while ($app = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Fetch applicants for this application
        $stmt_applicants = $pdo->prepare("SELECT * FROM applicants WHERE pnr = ?");
        $stmt_applicants->execute([$app['pnr']]);
        $applicants = [];

        while ($ap = $stmt_applicants->fetch(PDO::FETCH_ASSOC)) {
            // SAFE data extraction with column existence check
            $applicantData = [
                "pnr" => $ap['pnr'] ?? null,
                "user_pnr" => $ap['user_pnr'] ?? null,
                "completed" => isset($ap['completed']) ? (bool)$ap['completed'] : false
            ];

            // Only include columns that actually exist in your database
            $jsonColumns = [
                'passport_info', 'contact_info', 'family_info', 'us_contact_info',
                'employment_info', 'educational_info', 'travel_info', 'travel_history', 'other_info'
            ];

            foreach ($jsonColumns as $column) {
                if (isset($ap[$column]) && $ap[$column] !== null) {
                    $key = str_replace('_info', 'Info', $column);
                    $key = str_replace('_history', 'History', $key);
                    $key = lcfirst(str_replace('_', '', ucwords($key, '_')));
                    
                    $applicantData[$key] = json_decode($ap[$column], true) ?? [];
                } else {
                    // If column doesn't exist, set empty array
                    $key = str_replace('_info', 'Info', $column);
                    $key = str_replace('_history', 'History', $key);
                    $key = lcfirst(str_replace('_', '', ucwords($key, '_')));
                    $applicantData[$key] = [];
                }
            }

            $applicants[] = $applicantData;
        }

        $applications[] = [
            "pnr" => $app['pnr'] ?? '',
            "nameOfApplicant" => $app['name_of_applicant'] ?? '',
            "totalApplicants" => $app['total_applicants'] ?? 0,
            "status" => $app['status'] ?? 'draft',
            "timestamp" => $app['created_at'] ?? date('Y-m-d H:i:s'),
            "applicants" => $applicants
        ];
    }

} catch (Exception $e) {
    $applications = [];
    error_log("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>US Visa Applications Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .application-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .application-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-left-color: #3b82f6;
        }
        .progress-bar {
            transition: width 0.5s ease-in-out;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
        }
        .status-complete {
            background-color: #10b981;
            color: white;
        }
        .status-in-progress {
            background-color: #f59e0b;
            color: white;
        }
        .status-not-started {
            background-color: #6b7280;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <header class="text-center mb-12">
            <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-passport text-white text-xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-800">US Visa/DS-160 Applications</h1>
            </div>
            <p class="text-gray-600 max-w-2xl mx-auto">Manage your saved US visa applications. Continue where you left off or start a new application.</p>
        </header>

        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Applications</p>
                        <h3 id="total-applications" class="text-2xl font-bold text-gray-800">0</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Completed</p>
                        <h3 id="completed-applications" class="text-2xl font-bold text-gray-800">0</h3>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-clock text-amber-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">In Progress</p>
                        <h3 id="inprogress-applications" class="text-2xl font-bold text-gray-800">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Dashboard -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Saved Applications</h2>
                    <p class="text-gray-600 text-sm">Your US visa applications saved in this browser</p>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <button id="refresh-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i> Refresh
                    </button>
                    <button id="new-application" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-plus mr-2"></i> New Application
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div id="applications-list" class="space-y-4">
                    <!-- Applications will be listed here -->
                </div>

                <div id="no-applications" class="text-center py-12 hidden">
                    <div class="max-w-md mx-auto">
                        <i class="fas fa-folder-open text-gray-300 text-6xl mb-6"></i>
                        <h3 class="text-xl font-medium text-gray-600 mb-2">No applications found</h3>
                        <p class="text-gray-500 mb-6">You haven't created any US visa applications yet.</p>
                        <button id="create-first-app" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition duration-300">
                            Create Your First Application
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-12 text-center text-gray-500 text-sm">
            <p>© 2025 TravHub Global Limited. All rights reserved.</p>
            <p class="mt-2">This is a demonstration dashboard only. For official visa applications, visit the <a href="#" class="text-blue-600 hover:underline">GOV.US website</a>.</p>
        </footer>
    </div>

    <script>
        // Application data structure
        let applications = <?php echo json_encode($applications, JSON_PRETTY_PRINT); ?>;
        let currentModalPNR = '';

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            loadApplications();
            setupEventListeners();
        });

        // Set up event listeners
        function setupEventListeners() {
            document.getElementById('refresh-btn').addEventListener('click', loadApplications);
            document.getElementById('new-application').addEventListener('click', createNewApplication);
            document.getElementById('create-first-app').addEventListener('click', createNewApplication);
        }

        // Load all applications from localStorage and database
        function loadApplications() {
            // Merge with localStorage applications
            const localStorageApps = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key.startsWith("usaVisaApplication-")) {
                    try {
                        const appData = JSON.parse(localStorage.getItem(key));
                        if (appData) {
                            appData.source = "local";
                            localStorageApps.push(appData);
                        }
                    } catch (e) {
                        console.error("Error parsing localStorage:", key, e);
                    }
                }
            }

            // Combine database and localStorage applications
            const allApplications = [...applications];
            localStorageApps.forEach(localApp => {
                if (!allApplications.some(dbApp => dbApp.pnr === localApp.pnr)) {
                    allApplications.push(localApp);
                }
            });

            // Sort by latest timestamp
            allApplications.sort((a, b) => new Date(b.timestamp || b.created_at) - new Date(a.timestamp || a.created_at));

            applications = allApplications;
            renderApplications();
            updateStats();
        }

        // Render the applications list
        function renderApplications() {
            const listContainer = document.getElementById('applications-list');
            const noApplications = document.getElementById('no-applications');

            if (applications.length === 0) {
                listContainer.innerHTML = '';
                noApplications.classList.remove('hidden');
                return;
            }

            noApplications.classList.add('hidden');

            let html = '';
            applications.forEach((app, index) => {
                const completedCount = app.applicants ? app.applicants.filter(a => a.completed).length : 0;
                const totalApplicants = app.totalApplicants || 1;
                const progress = Math.round((completedCount / totalApplicants) * 100);
                const lastUpdated = new Date(app.timestamp || app.created_at || Date.now()).toLocaleDateString();

                // Determine status
                let status, statusClass, statusText;
                if (progress === 100) {
                    status = 'complete';
                    statusClass = 'status-complete';
                    statusText = 'Complete';
                } else if (progress > 0) {
                    status = 'in-progress';
                    statusClass = 'status-in-progress';
                    statusText = 'In Progress';
                } else {
                    status = 'not-started';
                    statusClass = 'status-not-started';
                    statusText = 'Not Started';
                }

                html += `
                    <div class="application-card bg-white border border-gray-200 rounded-lg p-5 fade-in">
                        <div class="flex flex-col md:flex-row md:items-center justify-between">
                            <div class="flex-1 mb-4 md:mb-0 md:mr-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-bold text-gray-800 text-lg">
                                            ${app.pnr || 'Unknown PNR'}
                                            ${app.source === 'local' ? '<span class="ml-2 text-xs px-2 py-0.5 bg-yellow-200 text-yellow-800 rounded-full">Local</span>' : ''}
                                        </h3>
                                        <div class="flex flex-wrap items-center mt-2 text-sm text-gray-600 gap-2">
                                            <span class="flex items-center">
                                                <i class="fas fa-users mr-1"></i> ${totalApplicants} applicant(s)
                                            </span>
                                            <span class="flex items-center">
                                                <i class="fas fa-check-circle mr-1"></i> ${completedCount} completed
                                            </span>
                                            <span class="flex items-center">
                                                <i class="fas fa-calendar mr-1"></i> Updated: ${lastUpdated}
                                            </span>
                                        </div>
                                    </div>
                                    <span class="status-badge ${statusClass}">${statusText}</span>
                                </div>
                                
                                <div class="mt-4">
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span>Overall Progress</span>
                                        <span>${progress}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full progress-bar" style="width: ${progress}%"></div>
                                    </div>
                                </div>
                                
                                <!-- Applicant Progress -->
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-${Math.min(totalApplicants, 4)} gap-2">
                                    ${app.applicants ? app.applicants.map((applicant, idx) => {
                                        const applicantProgress = calculateApplicantProgress(applicant);
                                        return `
                                            <div class="text-xs">
                                                <div class="flex justify-between mb-1">
                                                    <span class="text-gray-600">Applicant ${idx + 1}</span>
                                                    <span class="text-gray-500">${applicantProgress}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                    <div class="h-1.5 rounded-full ${applicant.completed ? 'bg-green-500' : 'bg-blue-500'}" style="width: ${applicantProgress}%"></div>
                                                </div>
                                            </div>
                                        `;
                                    }).join('') : ''}
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button class="view-app-btn bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition duration-300 flex items-center" onclick="showApplicationDetails('${app.pnr}')" title="View Details">
                                    <i class="fas fa-eye mr-1"></i>
                                    <span class="hidden sm:inline">Details</span>
                                </button>
                                <button class="continue-app-btn bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition duration-300 flex items-center" onclick="continueApplicationDirect('${app.pnr}')" title="Continue Application">
                                    <i class="fas fa-edit mr-1"></i>
                                    <span class="hidden sm:inline">Continue</span>
                                </button>
                                <button class="delete-app-btn bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg transition duration-300 flex items-center" onclick="deleteApplication('${app.pnr}')" title="Delete Application">
                                    <i class="fas fa-trash mr-1"></i>
                                    <span class="hidden sm:inline">Delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            listContainer.innerHTML = html;
        }

        // Calculate progress for an individual applicant
        function calculateApplicantProgress(applicant) {
            if (!applicant) return 0;
            if (applicant.completed) return 100;

            // Simple progress calculation based on filled fields
            let filledFields = 0;
            let totalFields = 0;

            // Check each section for data
            const sections = ['passportInfo', 'contactInfo', 'travelInfo', 'familyInfo', 'employmentInfo', 'educationalInfo'];
            
            sections.forEach(section => {
                if (applicant[section]) {
                    const sectionData = applicant[section];
                    const fields = Object.keys(sectionData).filter(key => 
                        sectionData[key] !== null && 
                        sectionData[key] !== undefined && 
                        sectionData[key] !== '' &&
                        sectionData[key] !== false
                    );
                    filledFields += fields.length;
                    totalFields += Object.keys(sectionData).length;
                }
            });

            return totalFields > 0 ? Math.round((filledFields / totalFields) * 100) : 0;
        }

        // Update dashboard statistics
        function updateStats() {
            const totalApplications = applications.length;
            const completedApplications = applications.filter(app => {
                if (!app.applicants || app.applicants.length === 0) return false;
                return app.applicants.every(applicant => applicant.completed);
            }).length;
            const inProgressApplications = totalApplications - completedApplications;

            document.getElementById('total-applications').textContent = totalApplications;
            document.getElementById('completed-applications').textContent = completedApplications;
            document.getElementById('inprogress-applications').textContent = inProgressApplications;
        }

        // Show application details
        function showApplicationDetails(pnr) {
            window.location.href = `show.php?pnr=${encodeURIComponent(pnr)}`;
        }

        // Continue application directly
        function continueApplicationDirect(pnr) {
            window.location.href = `application-form.php?pnr=${encodeURIComponent(pnr)}`;
        }

        // Create a new application
        function createNewApplication() {
            window.location.href = 'application-form.php';
        }

        // Delete an application
        function deleteApplication(pnr) {
            if (!confirm('Are you sure you want to delete this application? This action cannot be undone.')) {
                return;
            }

            // Remove from localStorage
            localStorage.removeItem('usaVisaApplication-' + pnr);

            // Remove from displayed list
            applications = applications.filter(app => app.pnr !== pnr);

            // Show success message
            alert(`Application ${pnr} has been deleted.`);

            // Reload the applications list
            renderApplications();
            updateStats();
        }
    </script>
</body>
</html>