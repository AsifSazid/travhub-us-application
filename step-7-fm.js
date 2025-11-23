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