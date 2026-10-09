(function () {
    'use strict';

    const root = document.querySelector('.onboarding-shell');
    if (!root) return;

    const step = root.dataset.step || 'profile';
    const csrf = root.dataset.csrf || '';
    const messageAnswerAll = root.dataset.msgAnswerAll || '';
    const messageResult = root.dataset.msgResult || '';
    const messageCorrect = root.dataset.msgCorrect || '';
    const alertBox = document.getElementById('onboardingAlert');

    function showAlert(type, message) {
        alertBox.className = 'alert alert-' + type;
        alertBox.textContent = message || '';
        alertBox.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function jsonFetch(url, options) {
        const requestOptions = options || {};
        requestOptions.credentials = 'same-origin';
        requestOptions.headers = Object.assign(
            { Accept: 'application/json' },
            requestOptions.headers || {}
        );

        return fetch(url, requestOptions).then(function (response) {
            return response.json().then(function (json) {
                if (!response.ok || !json.success) {
                    throw new Error(json.message || 'Error');
                }
                return json;
            });
        });
    }

    const languageEndpoint = root.dataset.languageEndpoint || '';
    let languageChanging = false;
    const profileDraftStorageKey = 'sct_onboarding_profile_draft';

    function saveProfileDraftForLanguageChange() {
        if (step !== 'profile') return;

        const form = document.getElementById('onboardingProfileForm');
        if (!form) return;

        const values = {};
        const checks = {};

        form.querySelectorAll('input,select,textarea').forEach(function (field) {
            if (!field.name || field.disabled) return;

            if (field.type === 'checkbox' || field.type === 'radio') {
                if (!checks[field.name]) checks[field.name] = [];
                if (field.checked) checks[field.name].push(field.value);
                return;
            }

            values[field.name] = field.value;
        });

        try {
            sessionStorage.setItem(
                profileDraftStorageKey,
                JSON.stringify({ values: values, checks: checks })
            );
        } catch (error) {}
    }

    function restoreProfileDraftAfterLanguageChange() {
        if (step !== 'profile') return;

        const form = document.getElementById('onboardingProfileForm');
        if (!form) return;

        let draft = null;
        try {
            draft = JSON.parse(sessionStorage.getItem(profileDraftStorageKey) || 'null');
        } catch (error) {}

        if (!draft || typeof draft !== 'object') return;

        Object.keys(draft.values || {}).forEach(function (name) {
            const elements = form.querySelectorAll('[name="' + CSS.escape(name) + '"]');
            if (!elements.length) return;
            elements.forEach(function (field) {
                if (field.type !== 'checkbox' && field.type !== 'radio') {
                    field.value = draft.values[name];
                }
            });
        });

        form.querySelectorAll('input[type=checkbox],input[type=radio]').forEach(function (field) {
            const selected = (draft.checks && draft.checks[field.name]) || [];
            field.checked = selected.indexOf(field.value) >= 0;
        });

        try {
            sessionStorage.removeItem(profileDraftStorageKey);
        } catch (error) {}
    }

    function persistLanguage(language) {
        if (
            languageChanging
            || !languageEndpoint
            || !['es','en','pt','fr','zh'].includes(language)
        ) {
            return Promise.resolve();
        }

        languageChanging = true;
        showAlert('info', root.dataset.msgLanguageSaving || '');

        if (step === 'profile') {
            saveProfileDraftForLanguageChange();
        }

        const saveAssessmentBeforeLanguageChange =
            step === 'assessment' && typeof saveDraft === 'function'
                ? saveDraft().catch(function () {
                    throw new Error(root.dataset.msgDraftError || '');
                })
                : Promise.resolve();

        return saveAssessmentBeforeLanguageChange
            .then(function () {
                return jsonFetch(languageEndpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        csrf_token: csrf,
                        language: language,
                    }),
                });
            })
            .then(function () {
                const url = new URL(window.location.href);
                url.searchParams.set('lang', language);
                url.searchParams.set('step', step || 'profile');
                window.location.href = url.toString();
            })
            .catch(function (error) {
                languageChanging = false;
                try {
                    sessionStorage.removeItem(profileDraftStorageKey);
                } catch (storageError) {}
                showAlert(
                    'danger',
                    error.message || root.dataset.msgLanguageError || ''
                );
                throw error;
            });
    }

    document.querySelectorAll('[data-onboarding-language]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            const language = link.dataset.onboardingLanguage || '';
            if (language === root.dataset.currentLanguage) return;

            event.preventDefault();
            persistLanguage(language).catch(function () {});
        });
    });

    const inlineLanguageSelect = document.querySelector('[data-onboarding-language-select]');
    if (inlineLanguageSelect) {
        inlineLanguageSelect.addEventListener('change', function () {
            persistLanguage(inlineLanguageSelect.value).catch(function () {
                inlineLanguageSelect.value = root.dataset.currentLanguage || 'es';
            });
        });
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[character];
        });
    }

    if (step === 'profile') {
        const form = document.getElementById('onboardingProfileForm');
        const contractorSelect = form.querySelector('[name="hired_by_contractor"]');
        const contractorWrap = document.getElementById('contractorNameWrap');
        const contractorId = document.getElementById('contractorId');
        const contractorSearch = document.getElementById('contractorSearchInput');
        const contractorMenu = document.getElementById('contractorSearchMenu');
        const contractorClear = document.getElementById('contractorClearButton');
        const contractorComponent = document.getElementById('contractorSearchSelect');

        let contractorRequestToken = 0;
        let contractorDebounce = null;

        function clearContractorSelection(clearText) {
            if (contractorId) contractorId.value = '';
            if (clearText && contractorSearch) contractorSearch.value = '';
        }

        function updateContractorField() {
            const required = contractorSelect && contractorSelect.value === 'yes';

            if (contractorWrap) contractorWrap.hidden = !required;

            if (!required) {
                clearContractorSelection(true);
                if (contractorMenu) contractorMenu.hidden = true;
            }
        }

        function contractorItemHtml(item) {
            const secondary = [
                item.rut || '',
                item.trade_name || ''
            ].filter(Boolean).join(' · ');

            return (
                '<button type="button" class="onboarding-search-select__option" ' +
                    'data-id="' + item.id + '" ' +
                    'data-name="' + escapeHtml(item.business_name || '') + '" ' +
                    'data-rut="' + escapeHtml(item.rut || '') + '">' +
                    '<strong>' + escapeHtml(item.business_name || '') + '</strong>' +
                    (secondary ? '<small>' + escapeHtml(secondary) + '</small>' : '') +
                '</button>'
            );
        }

        function renderContractorResults(results) {
            if (!contractorMenu) return;

            if (!results.length) {
                contractorMenu.innerHTML =
                    '<div class="onboarding-search-select__empty">' +
                    escapeHtml(root.dataset.msgContractorEmpty || '') +
                    '</div>';
                contractorMenu.hidden = false;
                return;
            }

            contractorMenu.innerHTML = results.map(contractorItemHtml).join('');
            contractorMenu.hidden = false;
        }

        function loadContractors(term) {
            if (!contractorComponent) return;

            const endpoint = contractorComponent.dataset.endpoint || '';
            const token = ++contractorRequestToken;

            jsonFetch(endpoint + '?q=' + encodeURIComponent(term || ''))
                .then(function (json) {
                    if (token !== contractorRequestToken) return;
                    renderContractorResults((json.data && json.data.results) || []);
                })
                .catch(function (error) {
                    if (token !== contractorRequestToken) return;
                    if (contractorMenu) {
                        contractorMenu.innerHTML =
                            '<div class="onboarding-search-select__empty">' +
                            escapeHtml(error.message) +
                            '</div>';
                        contractorMenu.hidden = false;
                    }
                });
        }

        if (contractorSelect) {
            contractorSelect.addEventListener('change', updateContractorField);
            updateContractorField();
        }

        if (contractorSearch) {
            contractorSearch.addEventListener('focus', function () {
                if (contractorSelect && contractorSelect.value === 'yes') {
                    loadContractors(contractorSearch.value.trim());
                }
            });

            contractorSearch.addEventListener('input', function () {
                clearContractorSelection(false);

                window.clearTimeout(contractorDebounce);
                contractorDebounce = window.setTimeout(function () {
                    loadContractors(contractorSearch.value.trim());
                }, 220);
            });
        }

        if (contractorMenu) {
            contractorMenu.addEventListener('click', function (event) {
                const option = event.target.closest('.onboarding-search-select__option');
                if (!option) return;

                contractorId.value = option.dataset.id || '';
                contractorSearch.value = [
                    option.dataset.name || '',
                    option.dataset.rut || ''
                ].filter(Boolean).join(' · ');

                contractorMenu.hidden = true;
            });
        }

        if (contractorClear) {
            contractorClear.addEventListener('click', function () {
                clearContractorSelection(true);
                if (contractorSearch) {
                    contractorSearch.focus();
                    loadContractors('');
                }
            });
        }

        document.addEventListener('click', function (event) {
            if (
                contractorComponent
                && !contractorComponent.contains(event.target)
                && contractorMenu
            ) {
                contractorMenu.hidden = true;
            }
        });

        const healthPanel = form.querySelector('[data-onboarding-health]');

        function checkedValues(name) {
            return Array.from(
                form.querySelectorAll('input[name="' + name + '[]"]:checked')
            ).map(function (input) {
                return input.value;
            });
        }

        function radioValue(name) {
            const selected = form.querySelector(
                'input[name="' + name + '"]:checked'
            );
            return selected ? selected.value : '';
        }

        function hasAllergy() {
            return checkedValues('allergies').some(function (value) {
                return value !== 'none' && value !== 'prefer_not';
            });
        }

        function updateHealthConditional() {
            if (!healthPanel) return;

            healthPanel.querySelectorAll('[data-onboarding-show-if]').forEach(function (element) {
                const rule = element.dataset.onboardingShowIf || '';
                let visible = false;

                if (rule === 'has_allergy') {
                    visible = hasAllergy();
                } else {
                    const parts = rule.split(':');
                    const name = parts[0];
                    const expected = parts[1];

                    if (
                        name === 'conditions'
                        || name === 'allergies'
                        || name === 'occupational_diseases'
                    ) {
                        visible = checkedValues(name).indexOf(expected) >= 0;
                    } else {
                        visible = radioValue(name) === expected;
                    }
                }

                element.classList.toggle('d-none', !visible);
            });
        }

        function setupHealthExclusive(group) {
            if (!healthPanel) return;

            const fieldset = healthPanel.querySelector(
                '[data-onboarding-exclusive="' + group + '"]'
            );

            if (!fieldset) return;

            fieldset.addEventListener('change', function (event) {
                const input = event.target;

                if (!input.matches('input[type="checkbox"]')) return;

                const isExclusive =
                    input.value === 'none'
                    || input.value === 'prefer_not';

                const all = fieldset.querySelectorAll('input[type="checkbox"]');

                if (input.checked && isExclusive) {
                    all.forEach(function (item) {
                        if (item !== input) item.checked = false;
                    });
                } else if (input.checked) {
                    all.forEach(function (item) {
                        if (item.value === 'none' || item.value === 'prefer_not') {
                            item.checked = false;
                        }
                    });
                }

                updateHealthConditional();
            });
        }

        function validateHealthSection() {
            if (!healthPanel) return true;

            let valid = true;

            const emergencyName2 = (form.elements.emergency_name_2.value || '').trim();
            const emergencyRelation2 = form.elements.emergency_relation_2.value || '';
            const emergencyPhone2 = (form.elements.emergency_phone_2.value || '').trim();
            const emergency2Any = !!(
                emergencyName2
                || emergencyRelation2
                || emergencyPhone2
            );

            if (
                emergency2Any
                && (
                    !emergencyName2
                    || !emergencyRelation2
                    || !/^\+?[0-9][0-9\s().-]{6,24}$/.test(emergencyPhone2)
                )
            ) {
                valid = false;
            }

            const conditions = checkedValues('conditions');
            if (!conditions.length) valid = false;
            if (conditions.indexOf('other') >= 0 && !(form.elements.condition_other.value || '').trim()) {
                valid = false;
            }

            const medicationChoice = radioValue('medication_choice');
            if (!medicationChoice) valid = false;
            if (
                medicationChoice === 'yes'
                && !(form.elements.medications_text.value || '').trim()
            ) {
                valid = false;
            }

            const allergies = checkedValues('allergies');
            if (!allergies.length) valid = false;
            if (hasAllergy()) {
                if (!(form.elements.allergy_details.value || '').trim()) valid = false;
                if (!radioValue('severe_reaction')) valid = false;
            }

            const occupationalChoice = radioValue('occupational_choice');
            if (!occupationalChoice) valid = false;
            if (
                occupationalChoice === 'yes'
                && !checkedValues('occupational_diseases').length
            ) {
                valid = false;
            }
            if (
                checkedValues('occupational_diseases').indexOf('other') >= 0
                && !(form.elements.occupational_other.value || '').trim()
            ) {
                valid = false;
            }

            const restrictionChoice = radioValue('restriction_choice');
            if (!restrictionChoice) valid = false;
            if (
                restrictionChoice === 'yes'
                && !(form.elements.restriction_details.value || '').trim()
            ) {
                valid = false;
            }


            if (!valid) {
                showAlert(
                    'warning',
                    root.dataset.msgHealthRequired || ''
                );
            }

            return valid;
        }

        if (healthPanel) {
            setupHealthExclusive('conditions');
            setupHealthExclusive('allergies');
            healthPanel.addEventListener('change', updateHealthConditional);
            updateHealthConditional();
        }

        function profileSelectionContainer(input) {
            if (!input) return null;

            return (
                input.closest('.health-radio-row')
                || input.closest('.health-check-grid')
                || input.closest('.onboarding-mutuality-grid')
                || input.closest('.onboarding-consent')
            );
        }

        function profileSelectionQuestion(input) {
            if (!input) return null;

            return (
                input.closest('.health-subcard')
                || input.closest('.health-fieldset')
                || input.closest('.onboarding-mutuality-grid')
                || input.closest('.onboarding-consent')
            );
        }

        function syncProfileSelection(input, animate) {
            if (
                !input
                || !input.matches(
                    'input[type="radio"],input[type="checkbox"]'
                )
            ) {
                return;
            }

            const container = profileSelectionContainer(input);
            const question = profileSelectionQuestion(input);

            if (container) {
                container
                    .querySelectorAll('input[type="radio"],input[type="checkbox"]')
                    .forEach(function (control) {
                        const label = control.closest('label');
                        if (!label) return;

                        label.classList.toggle(
                            'is-selected',
                            !!control.checked
                        );
                    });
            }

            if (question) {
                question.classList.toggle(
                    'is-answered',
                    !!question.querySelector(
                        'input[type="radio"]:checked,input[type="checkbox"]:checked'
                    )
                );
            }

            if (animate && input.checked) {
                const selectedLabel = input.closest('label');

                if (selectedLabel) {
                    selectedLabel.classList.remove('is-just-selected');
                    void selectedLabel.offsetWidth;
                    selectedLabel.classList.add('is-just-selected');

                    window.setTimeout(function () {
                        selectedLabel.classList.remove('is-just-selected');
                    }, 520);
                }

                if (question) {
                    question.classList.remove('is-just-answered');
                    void question.offsetWidth;
                    question.classList.add('is-just-answered');

                    window.setTimeout(function () {
                        question.classList.remove('is-just-answered');
                    }, 520);
                }
            }
        }

        function syncAllProfileSelections() {
            form.querySelectorAll(
                'input[type="radio"],input[type="checkbox"]'
            ).forEach(function (input) {
                syncProfileSelection(input, false);
            });
        }

        function profilePhoneValid(value) {
            return /^\+?[0-9][0-9\s().-]{6,24}$/.test(
                (value || '').trim()
            );
        }

        function profileRutValid(value) {
            return /^[0-9]{7,8}-[0-9Kk]$/.test(
                (value || '').trim()
            );
        }

        function customProfileInvalidTargets() {
            const invalid = [];

            function push(target) {
                if (target && invalid.indexOf(target) === -1) {
                    invalid.push(target);
                }
            }

            const rut = form.elements.rut;
            if (rut && !profileRutValid(rut.value)) {
                push(rut);
            }

            const phone = form.elements.phone;
            if (
                phone
                && phone.required
                && !profilePhoneValid(phone.value)
            ) {
                push(phone);
            }

            const emergencyPhone = form.elements.emergency_contact_phone;
            if (
                emergencyPhone
                && !profilePhoneValid(emergencyPhone.value)
            ) {
                push(emergencyPhone);
            }

            const contractorFlag = form.elements.hired_by_contractor;
            const contractorId = form.elements.id_contractor_company;
            if (
                contractorFlag
                && contractorFlag.value === 'yes'
                && (!contractorId || !contractorId.value)
            ) {
                push(
                    document.getElementById('contractorSearchInput')
                    || contractorFlag
                );
            }

            if (!healthPanel) {
                return invalid;
            }

            const emergencyName2 = (
                form.elements.emergency_name_2.value || ''
            ).trim();
            const emergencyRelation2 =
                form.elements.emergency_relation_2.value || '';
            const emergencyPhone2 = (
                form.elements.emergency_phone_2.value || ''
            ).trim();

            const emergency2Any = !!(
                emergencyName2
                || emergencyRelation2
                || emergencyPhone2
            );

            if (emergency2Any) {
                if (!emergencyName2) {
                    push(form.elements.emergency_name_2);
                }
                if (!emergencyRelation2) {
                    push(form.elements.emergency_relation_2);
                }
                if (!profilePhoneValid(emergencyPhone2)) {
                    push(form.elements.emergency_phone_2);
                }
            }

            const conditions = checkedValues('conditions');
            if (!conditions.length) {
                push(
                    healthPanel.querySelector(
                        '[data-onboarding-exclusive="conditions"]'
                    )
                );
            }

            if (
                conditions.indexOf('other') >= 0
                && !(form.elements.condition_other.value || '').trim()
            ) {
                push(form.elements.condition_other);
            }

            const medicationChoice = radioValue('medication_choice');
            if (!medicationChoice) {
                push(
                    form.querySelector(
                        'input[name="medication_choice"]'
                    )?.closest('.health-subcard')
                );
            }

            if (
                medicationChoice === 'yes'
                && !(form.elements.medications_text.value || '').trim()
            ) {
                push(form.elements.medications_text);
            }

            const allergies = checkedValues('allergies');
            if (!allergies.length) {
                push(
                    healthPanel.querySelector(
                        '[data-onboarding-exclusive="allergies"]'
                    )
                );
            }

            if (hasAllergy()) {
                if (
                    !(form.elements.allergy_details.value || '').trim()
                ) {
                    push(form.elements.allergy_details);
                }

                if (!radioValue('severe_reaction')) {
                    push(
                        form.querySelector(
                            'input[name="severe_reaction"]'
                        )?.closest('.health-subcard')
                    );
                }
            }

            const occupationalChoice = radioValue('occupational_choice');
            if (!occupationalChoice) {
                push(
                    form.querySelector(
                        'input[name="occupational_choice"]'
                    )?.closest('.health-subcard')
                );
            }

            if (
                occupationalChoice === 'yes'
                && !checkedValues('occupational_diseases').length
            ) {
                push(
                    form.querySelector(
                        'input[name="occupational_diseases[]"]'
                    )?.closest('.health-fieldset')
                );
            }

            if (
                checkedValues('occupational_diseases').indexOf('other') >= 0
                && !(form.elements.occupational_other.value || '').trim()
            ) {
                push(form.elements.occupational_other);
            }

            const restrictionChoice = radioValue('restriction_choice');
            if (!restrictionChoice) {
                push(
                    form.querySelector(
                        'input[name="restriction_choice"]'
                    )?.closest('.health-subcard')
                );
            }

            if (
                restrictionChoice === 'yes'
                && !(form.elements.restriction_details.value || '').trim()
            ) {
                push(form.elements.restriction_details);
            }

            return invalid;
        }

        function nativeProfileInvalidTargets() {
            return Array.from(
                form.querySelectorAll(
                    'input:invalid,select:invalid,textarea:invalid'
                )
            ).filter(function (field) {
                return !field.disabled;
            });
        }

        function clearProfileInvalidState() {
            form.querySelectorAll(
                '.onboarding-profile-section.is-incomplete'
            ).forEach(function (section) {
                section.classList.remove('is-incomplete');
            });

            form.querySelectorAll('.is-profile-invalid').forEach(
                function (element) {
                    element.classList.remove('is-profile-invalid');
                }
            );
        }

        function markProfileInvalid(target) {
            if (!target) return;

            target.classList.add('is-profile-invalid');

            const section = target.closest(
                '.onboarding-profile-section'
            );

            if (section) {
                section.classList.remove('is-complete');
                section.classList.add('is-incomplete');
            }
        }

        function allProfileInvalidTargets() {
            const all = nativeProfileInvalidTargets();

            customProfileInvalidTargets().forEach(function (target) {
                if (target && all.indexOf(target) === -1) {
                    all.push(target);
                }
            });

            return all;
        }

        function sectionHasInvalidTarget(section, invalidTargets) {
            return invalidTargets.some(function (target) {
                return target === section || section.contains(target);
            });
        }

        function refreshProfileSectionCompletion() {
            const invalidTargets = allProfileInvalidTargets();

            form.querySelectorAll(
                '.onboarding-profile-section'
            ).forEach(function (section) {
                const hasInvalid = sectionHasInvalidTarget(
                    section,
                    invalidTargets
                );

                section.classList.toggle(
                    'is-complete',
                    !hasInvalid
                );

                if (!hasInvalid) {
                    section.classList.remove('is-incomplete');
                }
            });
        }

        function focusFirstProfileInvalid(invalidTargets) {
            const first = invalidTargets[0];
            if (!first) return;

            invalidTargets.forEach(markProfileInvalid);

            const section = first.closest(
                '.onboarding-profile-section'
            );

            (section || first).scrollIntoView({
                behavior: 'smooth',
                block: 'center',
            });

            window.setTimeout(function () {
                const focusable = first.matches(
                    'input,select,textarea,button'
                )
                    ? first
                    : first.querySelector(
                        'input,select,textarea,button'
                    );

                if (focusable) {
                    focusable.focus({ preventScroll: true });
                }
            }, 380);
        }

        // La capa visual se ejecuta después de los handlers funcionales
        // existentes. Nunca cambia checked/value: sólo refleja el estado DOM.
        form.addEventListener('change', function (event) {
            const input = event.target;

            window.requestAnimationFrame(function () {
                syncAllProfileSelections();
                refreshProfileSectionCompletion();

                if (
                    input
                    && input.matches(
                        'input[type="radio"],input[type="checkbox"]'
                    )
                ) {
                    syncProfileSelection(input, true);
                }
            });
        });

        form.addEventListener('input', function () {
            window.requestAnimationFrame(
                refreshProfileSectionCompletion
            );
        });

        restoreProfileDraftAfterLanguageChange();
        updateContractorField();
        updateHealthConditional();
        syncAllProfileSelections();
        refreshProfileSectionCompletion();

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (languageChanging) {
                showAlert('info', root.dataset.msgLanguageSaving || '');
                return;
            }

            clearProfileInvalidState();

            const invalidTargets = allProfileInvalidTargets();

            if (invalidTargets.length) {
                showAlert(
                    'warning',
                    root.dataset.msgProfileRequired
                    || root.dataset.msgHealthRequired
                    || ''
                );
                focusFirstProfileInvalid(invalidTargets);
                return;
            }

            refreshProfileSectionCompletion();

            const button = form.querySelector('button[type=submit]');
            button.disabled = true;

            const data = new FormData(form);
            const payload = {
                csrf_token: csrf,
                name: (data.get('name') || '').trim(),
                lastname: (data.get('lastname') || '').trim(),
                rut: (data.get('rut') || '').trim(),
                language: data.get('language') || 'es',
                phone: (data.get('phone') || '').trim(),
                position: (data.get('position') || '').trim(),

                confirmed_project_id: data.get('confirmed_project_id') || null,
                hired_by_contractor: data.get('hired_by_contractor') || '',
                id_contractor_company: data.get('id_contractor_company') || null,
                mutual_code: data.get('mutual_code') || '',
                years_experience_current_role: data.get('years_experience_current_role') || '',
                emergency_contact_name: (data.get('emergency_contact_name') || '').trim(),
                emergency_contact_phone: (data.get('emergency_contact_phone') || '').trim(),
                emergency_contact_relation: data.get('emergency_contact_relation') || '',

                emergency_name_2: (data.get('emergency_name_2') || '').trim(),
                emergency_relation_2: data.get('emergency_relation_2') || '',
                emergency_phone_2: (data.get('emergency_phone_2') || '').trim(),

                conditions: data.getAll('conditions[]'),
                condition_other: (data.get('condition_other') || '').trim(),

                medication_choice: data.get('medication_choice') || '',
                medications_text: (data.get('medications_text') || '').trim(),
                medications_emergency: (data.get('medications_emergency') || '').trim(),

                allergies: data.getAll('allergies[]'),
                allergy_details: (data.get('allergy_details') || '').trim(),
                severe_reaction: data.get('severe_reaction') || '',
                severe_reaction_info: (data.get('severe_reaction_info') || '').trim(),

                occupational_choice: data.get('occupational_choice') || '',
                occupational_diseases: data.getAll('occupational_diseases[]'),
                occupational_other: (data.get('occupational_other') || '').trim(),

                restriction_choice: data.get('restriction_choice') || '',
                restriction_details: (data.get('restriction_details') || '').trim(),

                accepted: data.get('accepted') === '1',
            };

            jsonFetch('api/onboarding/perfil-guardar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            })
                .then(function (json) {
                    location.href = json.data.redirect || 'onboarding.php?step=assessment';
                })
                .catch(function (error) {
                    showAlert('danger', error.message);
                    button.disabled = false;
                });
        });

        return;
    }

    const loading = document.getElementById('onboardingAssessmentLoading');
    const form = document.getElementById('onboardingAssessmentForm');
    const questionsBox = document.getElementById('onboardingQuestions');
    const attemptInput = document.getElementById('onboardingAttemptId');
    const backToProfile = document.getElementById('onboardingBackToProfile');
    const draftStatus = document.getElementById('onboardingDraftStatus');
    const resultModalElement = document.getElementById('onboardingAssessmentResultModal');
    const resultContinue = document.getElementById('onboardingAssessmentResultContinue');
    const resultCorrectPercentage = document.getElementById('onboardingResultCorrectPercentage');
    const resultIncorrectPercentage = document.getElementById('onboardingResultIncorrectPercentage');
    const resultCorrectCount = document.getElementById('onboardingResultCorrectCount');
    const resultIncorrectCount = document.getElementById('onboardingResultIncorrectCount');
    const resultScore = document.getElementById('onboardingResultScore');
    const resultScoreBox = document.getElementById('onboardingResultScoreBox');

    let resultRedirect = 'bienvenida.php';

    let draftTimer = null;
    let draftRequest = Promise.resolve();

    function collectAnsweredQuestions() {
        const answers = [];

        questionsBox.querySelectorAll('.onboarding-question').forEach(function (question) {
            const selected = question.querySelector('input[type=radio]:checked');

            if (!selected) return;

            answers.push({
                id_question: parseInt(question.dataset.questionId, 10),
                id_option: parseInt(selected.value, 10),
            });
        });

        return answers;
    }

    function setDraftStatus(message, state) {
        if (!draftStatus) return;

        draftStatus.textContent = message || '';
        draftStatus.dataset.state = state || '';
    }

    function saveDraft() {
        const attemptId = parseInt(attemptInput.value, 10);

        if (!attemptId) return Promise.resolve();

        setDraftStatus(root.dataset.msgDraftSaving || '', 'saving');

        const request = jsonFetch('api/onboarding/evaluacion-borrador-guardar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: csrf,
                attempt_id: attemptId,
                answers: collectAnsweredQuestions(),
            }),
        })
            .then(function () {
                setDraftStatus(root.dataset.msgDraftSaved || '', 'saved');
            })
            .catch(function (error) {
                setDraftStatus(
                    error.message || root.dataset.msgDraftError || '',
                    'error'
                );
                throw error;
            });

        draftRequest = request.catch(function () {});
        return request;
    }

    function scheduleDraftSave() {
        window.clearTimeout(draftTimer);

        draftTimer = window.setTimeout(function () {
            saveDraft().catch(function () {});
        }, 250);
    }

    jsonFetch('api/onboarding/evaluacion-obtener.php')
        .then(function (json) {
            const data = json.data || {};

            if (data.complete) {
                location.href = data.redirect || 'bienvenida.php';
                return;
            }

            attemptInput.value = data.attempt_id;
            const draftAnswers = data.draft_answers || {};

            questionsBox.innerHTML = (data.questions || [])
                .map(function (question, index) {
                    const options = (question.options || [])
                        .map(function (option) {
                            const id = 'onboarding_' + question.id_question + '_' + option.id_option;

                            return (
                                '<label class="onboarding-option" for="' + id + '">' +
                                    '<input type="radio" ' +
                                        'name="q_' + question.id_question + '" ' +
                                        'id="' + id + '" ' +
                                        'value="' + option.id_option + '" ' +
                                        (
                                            parseInt(
                                                draftAnswers[question.id_question],
                                                10
                                            ) === parseInt(option.id_option, 10)
                                                ? 'checked '
                                                : ''
                                        ) +
                                        'required>' +
                                    '<span>' + escapeHtml(option.option_text) + '</span>' +
                                '</label>'
                            );
                        })
                        .join('');

                    return (
                        '<fieldset class="onboarding-question" ' +
                            'data-question-id="' + question.id_question + '" ' +
                            'data-accent="' + ((index % 5) + 1) + '">' +
                            '<legend><span>' + (index + 1) + '</span>' +
                                escapeHtml(question.question) +
                            '</legend>' +
                            '<div class="onboarding-options">' + options + '</div>' +
                        '</fieldset>'
                    );
                })
                .join('');

            function updateQuestionSelection(question, animate) {
                if (!question) return;

                const options = Array.from(
                    question.querySelectorAll('.onboarding-option')
                );
                const selected = question.querySelector(
                    'input[type=radio]:checked'
                );

                options.forEach(function (option) {
                    const radio = option.querySelector('input[type=radio]');
                    const active = !!radio && !!selected && radio === selected;

                    option.classList.toggle('is-selected', active);
                    option.setAttribute(
                        'aria-selected',
                        active ? 'true' : 'false'
                    );
                });

                question.classList.toggle('is-answered', !!selected);

                if (animate && selected) {
                    const selectedOption = selected.closest('.onboarding-option');

                    question.classList.remove('is-just-answered');
                    if (selectedOption) {
                        selectedOption.classList.remove('is-just-selected');
                    }

                    // Reinicia la animación incluso al cambiar entre alternativas
                    // de la misma pregunta.
                    void question.offsetWidth;

                    question.classList.add('is-just-answered');
                    if (selectedOption) {
                        selectedOption.classList.add('is-just-selected');
                    }

                    window.setTimeout(function () {
                        question.classList.remove('is-just-answered');
                        if (selectedOption) {
                            selectedOption.classList.remove('is-just-selected');
                        }
                    }, 520);
                }
            }

            questionsBox
                .querySelectorAll('.onboarding-question')
                .forEach(function (question) {
                    updateQuestionSelection(question, false);
                });

            loading.classList.add('d-none');
            form.classList.remove('d-none');

            questionsBox.addEventListener('change', function (event) {
                if (event.target.matches('input[type=radio]')) {
                    updateQuestionSelection(
                        event.target.closest('.onboarding-question'),
                        true
                    );
                    scheduleDraftSave();
                }
            });
        })
        .catch(function (error) {
            loading.className = 'alert alert-danger';
            loading.textContent = error.message;
        });

    if (backToProfile) {
        backToProfile.addEventListener('click', function (event) {
            event.preventDefault();

            window.clearTimeout(draftTimer);

            const destination = backToProfile.href;

            saveDraft()
                .catch(function () {
                    // El borrador falló: mantenemos al usuario en la evaluación
                    // para evitar perder respuestas.
                    showAlert(
                        'danger',
                        root.dataset.msgDraftError || ''
                    );
                    throw new Error('draft_save_failed');
                })
                .then(function () {
                    location.href = destination;
                })
                .catch(function () {});
        });
    }

    function formatAssessmentPercentage(value) {
        const numeric = Number(value);
        const safeValue = Number.isFinite(numeric) ? numeric : 0;

        try {
            return new Intl.NumberFormat(
                document.documentElement.lang || undefined,
                {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 1,
                }
            ).format(safeValue) + '%';
        } catch (error) {
            return safeValue.toFixed(1).replace(/\.0$/, '') + '%';
        }
    }

    function showAssessmentResult(data) {
        const total = Math.max(0, parseInt(data.total, 10) || 0);
        const correct = Math.max(0, parseInt(data.correct, 10) || 0);
        const incorrect = Math.max(
            0,
            Number.isFinite(Number(data.incorrect))
                ? parseInt(data.incorrect, 10)
                : total - correct
        );
        const correctPercentage = Number.isFinite(Number(data.correct_percentage))
            ? Number(data.correct_percentage)
            : Number(data.score || 0);
        const incorrectPercentage = Number.isFinite(Number(data.incorrect_percentage))
            ? Number(data.incorrect_percentage)
            : Math.max(0, 100 - correctPercentage);
        const score = Number(data.score || correctPercentage || 0);

        resultRedirect = data.redirect || 'bienvenida.php';

        if (resultCorrectPercentage) {
            resultCorrectPercentage.textContent = formatAssessmentPercentage(
                correctPercentage
            );
        }
        if (resultIncorrectPercentage) {
            resultIncorrectPercentage.textContent = formatAssessmentPercentage(
                incorrectPercentage
            );
        }
        if (resultCorrectCount) {
            resultCorrectCount.textContent = correct + ' / ' + total;
        }
        if (resultIncorrectCount) {
            resultIncorrectCount.textContent = incorrect + ' / ' + total;
        }
        if (resultScore) {
            resultScore.textContent = formatAssessmentPercentage(score);
        }
        if (resultScoreBox) {
            resultScoreBox.classList.toggle('is-success', score >= 60);
            resultScoreBox.classList.toggle('is-danger', score < 60);
        }

        if (
            resultModalElement
            && window.bootstrap
            && window.bootstrap.Modal
        ) {
            window.bootstrap.Modal
                .getOrCreateInstance(resultModalElement, {
                    backdrop: 'static',
                    keyboard: false,
                })
                .show();
            return;
        }

        location.href = resultRedirect;
    }

    if (resultContinue) {
        resultContinue.addEventListener('click', function () {
            resultContinue.disabled = true;
            location.href = resultRedirect;
        });
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const answers = [];
        let missingAnswer = false;

        questionsBox.querySelectorAll('.onboarding-question').forEach(function (question) {
            const selected = question.querySelector('input[type=radio]:checked');

            if (!selected) {
                missingAnswer = true;
                return;
            }

            answers.push({
                id_question: parseInt(question.dataset.questionId, 10),
                id_option: parseInt(selected.value, 10),
            });
        });

        if (missingAnswer) {
            showAlert('warning', messageAnswerAll);
            return;
        }

        const button = form.querySelector('button[type=submit]');
        button.disabled = true;

        jsonFetch('api/onboarding/evaluacion-enviar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                csrf_token: csrf,
                attempt_id: parseInt(attemptInput.value, 10),
                answers: answers,
            }),
        })
            .then(function (json) {
                const data = json.data || {};

                window.clearTimeout(draftTimer);
                setDraftStatus('', '');

                showAssessmentResult(data);
            })
            .catch(function (error) {
                showAlert('danger', error.message);
                button.disabled = false;
            });
    });
})();
