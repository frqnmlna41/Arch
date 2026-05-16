/**
 * SPORA — Auth JavaScript
 * Handles: password toggle, strength meter, confirm match,
 *          form submit spinner, input animations.
 */

'use strict';

/* --------------------------------------------------------------------------
   Utility: toggle element visibility
   -------------------------------------------------------------------------- */
const show = (el) => el?.classList.remove('hidden');
const hide = (el) => el?.classList.add('hidden');

/* --------------------------------------------------------------------------
   1. Password Visibility Toggle
   -------------------------------------------------------------------------- */
function initPasswordToggles() {
    document.querySelectorAll('.auth-eye-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.target;
            const input    = document.getElementById(targetId);
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';

            const showIcon = btn.querySelector('.eye-icon--show');
            const hideIcon = btn.querySelector('.eye-icon--hide');

            if (isPassword) {
                hide(showIcon);
                show(hideIcon);
                btn.setAttribute('aria-label', 'Sembunyikan kata sandi');
            } else {
                show(showIcon);
                hide(hideIcon);
                btn.setAttribute('aria-label', 'Tampilkan kata sandi');
            }
        });
    });
}

/* --------------------------------------------------------------------------
   2. Password Strength Meter
   -------------------------------------------------------------------------- */
const STRENGTH_LEVELS = [
    { label: 'Lemah',   cls: 'pw-strength--weak',   min: 0  },
    { label: 'Cukup',   cls: 'pw-strength--fair',   min: 2  },
    { label: 'Bagus',   cls: 'pw-strength--good',   min: 3  },
    { label: 'Kuat',    cls: 'pw-strength--strong', min: 5  },
];

/**
 * Score a password 0–5 based on heuristics.
 * @param {string} pw
 * @returns {number}
 */
function scorePassword(pw) {
    if (!pw) return 0;
    let score = 0;
    if (pw.length >= 8)   score++;
    if (pw.length >= 12)  score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    return score;
}

function initStrengthMeter() {
    const pwInput  = document.getElementById('password');
    const meter    = document.getElementById('pwStrength');
    const fill     = document.getElementById('pwFill');
    const label    = document.getElementById('pwLabel');

    if (!pwInput || !meter || !fill || !label) return;

    pwInput.addEventListener('input', () => {
        const score = scorePassword(pwInput.value);
        const level = STRENGTH_LEVELS.reduce(
            (best, lvl) => score >= lvl.min ? lvl : best,
            STRENGTH_LEVELS[0]
        );

        // Remove all state classes then apply current
        STRENGTH_LEVELS.forEach(l => meter.classList.remove(l.cls));
        if (pwInput.value) meter.classList.add(level.cls);

        label.textContent = pwInput.value ? level.label : '';
    });
}

/* --------------------------------------------------------------------------
   3. Password Confirmation Match
   -------------------------------------------------------------------------- */
function initConfirmMatch() {
    const pwInput      = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    const errorEl      = document.getElementById('confirmError');

    if (!pwInput || !confirmInput || !errorEl) return;

    const validate = () => {
        if (!confirmInput.value) {
            hide(errorEl);
            confirmInput.classList.remove('input-error');
            return;
        }
        const mismatch = confirmInput.value !== pwInput.value;
        confirmInput.classList.toggle('input-error', mismatch);
        mismatch ? show(errorEl) : hide(errorEl);
    };

    confirmInput.addEventListener('input', validate);
    pwInput.addEventListener('input', validate);
}

/* --------------------------------------------------------------------------
   4. Form Submit → Loading Spinner
   -------------------------------------------------------------------------- */
function initFormSpinners() {
    const map = [
        { formId: 'loginForm',    btnId: 'loginBtn',    spinnerId: 'loginSpinner'    },
        { formId: 'registerForm', btnId: 'registerBtn', spinnerId: 'registerSpinner' },
        { formId: 'forgotForm',   btnId: 'forgotBtn',   spinnerId: 'forgotSpinner'   },
        { formId: 'resetForm',    btnId: 'resetBtn',    spinnerId: 'resetSpinner'    },
    ];

    map.forEach(({ formId, btnId, spinnerId }) => {
        const form    = document.getElementById(formId);
        const btn     = document.getElementById(btnId);
        const spinner = document.getElementById(spinnerId);

        if (!form || !btn || !spinner) return;

        form.addEventListener('submit', (e) => {
            // Run browser's native validation first
            if (!form.checkValidity()) return;

            // Extra guard: confirm match (register / reset forms)
            const confirmInput = document.getElementById('password_confirmation');
            const pwInput      = document.getElementById('password');
            if (confirmInput && pwInput && confirmInput.value !== pwInput.value) {
                e.preventDefault();
                return;
            }

            btn.disabled = true;
            hide(btn.querySelector('.btn-text'));
            show(spinner);
        });
    });
}

/* --------------------------------------------------------------------------
   5. Input focus ripple / micro-interaction
   -------------------------------------------------------------------------- */
function initInputAnimations() {
    document.querySelectorAll('.auth-input').forEach((input) => {
        input.addEventListener('focus', () => {
            input.closest('.auth-field')?.classList.add('auth-field--focused');
        });
        input.addEventListener('blur', () => {
            input.closest('.auth-field')?.classList.remove('auth-field--focused');
        });
    });
}

/* --------------------------------------------------------------------------
   6. Auto-dismiss flash alerts after 5 s
   -------------------------------------------------------------------------- */
function initAlertAutoDismiss() {
    document.querySelectorAll('.auth-alert').forEach((alert) => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s, max-height 0.5s, margin 0.5s';
            alert.style.opacity    = '0';
            alert.style.maxHeight  = '0';
            alert.style.marginBottom = '0';
            alert.style.overflow   = 'hidden';
        }, 5000);
    });
}

/* --------------------------------------------------------------------------
   Bootstrap on DOM ready
   -------------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    initStrengthMeter();
    initConfirmMatch();
    initFormSpinners();
    initInputAnimations();
    initAlertAutoDismiss();
});
