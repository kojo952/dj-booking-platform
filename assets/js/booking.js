/**
 * Booking Form — Client-side validation and AJAX submission
 */

'use strict';

(function initBookingForm() {
    const form      = document.getElementById('booking-form');
    const submitBtn = document.getElementById('submit-booking');
    const successEl = document.getElementById('booking-success');
    const errorBanner = document.getElementById('form-error-banner');
    const errorText   = document.getElementById('form-error-text');

    if (!form) return;

    // ── Validators ─────────────────────────────────────────────────────────────

    const validators = {
        full_name: (val) => {
            if (!val.trim())           return 'Full name is required.';
            if (val.trim().length < 2) return 'Name must be at least 2 characters.';
            if (val.trim().length > 150) return 'Name is too long.';
            return null;
        },
        email: (val) => {
            if (!val.trim()) return 'Email address is required.';
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim())) return 'Please enter a valid email address.';
            return null;
        },
        phone: (val) => {
            if (!val.trim()) return 'Phone number is required.';
            if (!/^[+\d\s\-(). ]{7,30}$/.test(val.trim())) return 'Please enter a valid phone number.';
            return null;
        },
        event_type: (val) => {
            if (!val) return 'Please select an event type.';
            return null;
        },
        event_date: (val) => {
            if (!val) return 'Event date is required.';
            const selected = new Date(val);
            const today    = new Date();
            today.setHours(0, 0, 0, 0);
            if (selected <= today) return 'Event date must be in the future.';
            return null;
        },
        event_time: (val) => {
            if (!val) return 'Event time is required.';
            return null;
        },
    };

    // ── Real-time validation feedback ─────────────────────────────────────────

    function validateField(field) {
        const name      = field.name;
        const value     = field.value;
        const validator = validators[name];
        const errEl     = document.getElementById(`err-${name}`);

        if (!validator) return true;

        const error = validator(value);

        field.classList.toggle('valid',   !error);
        field.classList.toggle('invalid', !!error);

        if (errEl) {
            errEl.textContent = error || '';
        }

        return !error;
    }

    // Attach real-time validation to each field
    Object.keys(validators).forEach(name => {
        const field = form.elements[name];
        if (!field) return;

        field.addEventListener('blur',  () => validateField(field));
        field.addEventListener('input', () => {
            if (field.classList.contains('invalid')) validateField(field);
        });
    });

    // Phone number formatter: auto-add spaces
    const phoneInput = form.querySelector('#phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            // Keep only allowed characters
            let val = this.value.replace(/[^\d+\s\-(). ]/g, '');
            // Prevent multiple + signs
            val = val.replace(/(?<=.)\+/g, '');
            this.value = val;
        });
    }

    // Min date enforcement
    const dateInput = form.querySelector('#event_date');
    if (dateInput) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.min = tomorrow.toISOString().split('T')[0];
    }

    // ── Form submission ────────────────────────────────────────────────────────

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Validate all required fields
        let isValid = true;
        Object.keys(validators).forEach(name => {
            const field = form.elements[name];
            if (field && !validateField(field)) isValid = false;
        });

        if (!isValid) {
            // Scroll to first error
            const firstError = form.querySelector('.form-input.invalid');
            if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Loading state
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
        hideError();

        try {
            const response = await fetch('/dj-booking-platform/api/booking.php', {
                method: 'POST',
                body:   new FormData(form),
            });

            const data = await response.json();

            if (data.success) {
                // Show success screen
                form.style.display        = 'none';
                successEl.style.display   = 'block';
                successEl.scrollIntoView({ behavior: 'smooth' });
            } else {
                // Show field-level errors if available
                if (data.fields && typeof data.fields === 'object') {
                    Object.entries(data.fields).forEach(([name, msg]) => {
                        const field = form.elements[name];
                        const errEl = document.getElementById(`err-${name}`);
                        if (field) field.classList.add('invalid');
                        if (errEl) errEl.textContent = msg;
                    });
                    // Scroll to first error
                    const firstError = form.querySelector('.form-input.invalid');
                    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    showError(data.message || 'An error occurred. Please try again.');
                }
            }
        } catch (err) {
            showError('Network error. Please check your connection and try again.');
            console.error('Booking submission error:', err);
        } finally {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
        }
    });

    function showError(msg) {
        if (errorBanner) errorBanner.style.display = 'flex';
        if (errorText)   errorText.textContent     = msg;
    }

    function hideError() {
        if (errorBanner) errorBanner.style.display = 'none';
    }
})();
