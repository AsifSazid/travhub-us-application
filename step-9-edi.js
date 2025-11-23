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