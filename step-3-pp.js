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