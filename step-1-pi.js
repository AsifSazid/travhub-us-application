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
                                    ${generateSocialMediaFields(ci.socialMedia || [{ platform: '', username: '' }])}
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