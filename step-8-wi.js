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