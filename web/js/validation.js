/**
 * Form Validation Module
 * Provides client-side form validation with accessibility support
 */

class Validator {
  constructor() {
    this.rules = {
      required: (value) => value.trim() !== '',
      email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
      minLength: (value, length) => value.length >= length,
      maxLength: (value, length) => value.length <= length,
      phone: (value) => /^[\d\s+\-()]+$/.test(value) && value.replace(/\D/g, '').length >= 9,
      password: (value) => value.length >= 8,
      match: (value, matchValue) => value === matchValue,
      alphanumeric: (value) => /^[a-zA-Z0-9]+$/.test(value),
      numeric: (value) => /^\d+$/.test(value)
    };

    this.messages = {
      required: 'This field is required',
      email: 'Please enter a valid email address',
      minLength: 'Minimum length is {length} characters',
      maxLength: 'Maximum length is {length} characters',
      phone: 'Please enter a valid phone number',
      password: 'Password must be at least 8 characters',
      match: 'Fields do not match',
      alphanumeric: 'Only letters and numbers are allowed',
      numeric: 'Only numbers are allowed'
    };
  }

  /**
   * Validate a single field
   */
  validateField(input, rules) {
    const value = input.value;
    const errors = [];

    for (const rule of rules) {
      const [ruleName, ...params] = rule.split(':');
      
      if (this.rules[ruleName]) {
        const isValid = this.rules[ruleName](value, ...params);
        
        if (!isValid) {
          let message = this.messages[ruleName];
          // Replace placeholders in message
          params.forEach((param, index) => {
            message = message.replace(`{${Object.keys(params)[index] || 'length'}}`, param);
          });
          errors.push(message);
        }
      }
    }

    return errors;
  }

  /**
   * Display error message for a field
   */
  showError(input, message) {
    // Remove existing error
    this.clearError(input);

    // Add error class to input
    input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
    input.classList.remove('border-gray-300', 'focus:border-blue-500', 'focus:ring-blue-500');
    input.setAttribute('aria-invalid', 'true');

    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'text-red-600 text-sm mt-1';
    errorDiv.setAttribute('role', 'alert');
    errorDiv.setAttribute('data-error-for', input.id || input.name);
    errorDiv.textContent = message;

    // Insert error message after input
    input.parentNode.insertBefore(errorDiv, input.nextSibling);

    // Update aria-describedby
    const errorId = `error-${input.id || input.name}`;
    errorDiv.id = errorId;
    input.setAttribute('aria-describedby', errorId);
  }

  /**
   * Clear error message for a field
   */
  clearError(input) {
    // Remove error classes
    input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
    input.classList.add('border-gray-300', 'focus:border-blue-500', 'focus:ring-blue-500');
    input.removeAttribute('aria-invalid');

    // Remove error message
    const errorDiv = input.parentNode.querySelector(`[data-error-for="${input.id || input.name}"]`);
    if (errorDiv) {
      input.removeAttribute('aria-describedby');
      errorDiv.remove();
    }
  }

  /**
   * Show success state for a field
   */
  showSuccess(input) {
    this.clearError(input);
    input.classList.add('border-green-500');
    input.classList.remove('border-gray-300');
  }

  /**
   * Validate form
   */
  validateForm(form) {
    let isValid = true;
    const fields = form.querySelectorAll('[data-validate]');

    fields.forEach(input => {
      const rules = input.getAttribute('data-validate').split('|');
      const errors = this.validateField(input, rules);

      if (errors.length > 0) {
        this.showError(input, errors[0]);
        isValid = false;
      } else {
        this.showSuccess(input);
      }
    });

    return isValid;
  }

  /**
   * Setup real-time validation for a form
   */
  setupForm(form) {
    const fields = form.querySelectorAll('[data-validate]');

    fields.forEach(input => {
      // Validate on blur
      input.addEventListener('blur', () => {
        const rules = input.getAttribute('data-validate').split('|');
        const errors = this.validateField(input, rules);

        if (errors.length > 0) {
          this.showError(input, errors[0]);
        } else {
          this.showSuccess(input);
        }
      });

      // Clear error on input
      input.addEventListener('input', () => {
        if (input.classList.contains('border-red-500')) {
          this.clearError(input);
        }
      });

      // Handle password confirmation matching
      if (input.getAttribute('data-match')) {
        const matchFieldId = input.getAttribute('data-match');
        const matchField = document.getElementById(matchFieldId);
        
        if (matchField) {
          const validateMatch = () => {
            if (input.value !== matchField.value && input.value !== '') {
              this.showError(input, 'Passwords do not match');
            } else if (input.value === matchField.value && input.value !== '') {
              this.showSuccess(input);
            }
          };

          input.addEventListener('input', validateMatch);
          matchField.addEventListener('input', validateMatch);
        }
      }
    });

    // Validate on submit
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      
      if (this.validateForm(form)) {
        // Form is valid, trigger custom event
        form.dispatchEvent(new CustomEvent('validSubmit', {
          detail: { formData: new FormData(form) }
        }));
      } else {
        // Focus first invalid field
        const firstError = form.querySelector('[aria-invalid="true"]');
        if (firstError) {
          firstError.focus();
        }
      }
    });
  }

  /**
   * Setup all forms with data-validate-form attribute
   */
  setupAllForms() {
    document.querySelectorAll('[data-validate-form]').forEach(form => {
      this.setupForm(form);
    });
  }

  /**
   * Custom validation rule
   */
  addRule(name, validator, message) {
    this.rules[name] = validator;
    this.messages[name] = message;
  }

  /**
   * Get form data as object
   */
  getFormData(form) {
    const formData = new FormData(form);
    const data = {};
    
    for (const [key, value] of formData.entries()) {
      data[key] = value;
    }
    
    return data;
  }

  /**
   * Reset form validation state
   */
  resetForm(form) {
    const fields = form.querySelectorAll('[data-validate]');
    
    fields.forEach(input => {
      this.clearError(input);
      input.classList.remove('border-green-500');
      input.classList.add('border-gray-300');
    });
    
    form.reset();
  }
}

// Create global instance
const validator = new Validator();

// Auto-setup forms when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => validator.setupAllForms());
} else {
  validator.setupAllForms();
}

// Export for use in other modules
/* eslint-disable no-undef */
if (typeof module !== 'undefined' && module.exports) {
  module.exports = validator;
}
/* eslint-enable no-undef */
