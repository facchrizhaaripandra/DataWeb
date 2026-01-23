const Utils = {
    // DOM Manipulation
    showElement: function (selector) {
        $(selector).removeClass("d-none").show();
    },

    hideElement: function (selector) {
        $(selector).addClass("d-none").hide();
    },

    toggleElement: function (selector) {
        $(selector).toggleClass("d-none");
    },

    // Form Handling
    serializeForm: function (formId) {
        const form = document.getElementById(formId);
        if (!form) return {};

        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            // Handle array inputs (e.g., data[column])
            const match = key.match(/(\w+)\[(\w+)\]/);
            if (match) {
                const mainKey = match[1];
                const subKey = match[2];

                if (!data[mainKey]) {
                    data[mainKey] = {};
                }
                data[mainKey][subKey] = value;
            } else {
                data[key] = value;
            }
        }

        return data;
    },

    clearForm: function (formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            // Clear validation classes
            $(form).find(".is-invalid").removeClass("is-invalid");
            $(form).find(".invalid-feedback").remove();
        }
    },

    // Validation
    validateEmail: function (email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    validatePhone: function (phone) {
        const re = /^[\+]?[1-9][\d]{0,15}$/;
        return re.test(phone.replace(/[\s\-\(\)]/g, ""));
    },

    // Date & Time
    formatDateTime: function (dateString, format = "id-ID") {
        const date = new Date(dateString);

        if (format === "id-ID") {
            return date.toLocaleDateString("id-ID", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });
        } else if (format === "relative") {
            return this.getRelativeTime(date);
        }

        return date.toLocaleString();
    },

    getRelativeTime: function (date) {
        const now = new Date();
        const diff = now - date;

        const minute = 60 * 1000;
        const hour = minute * 60;
        const day = hour * 24;
        const week = day * 7;
        const month = day * 30;
        const year = day * 365;

        if (diff < minute) {
            return "baru saja";
        } else if (diff < hour) {
            const minutes = Math.floor(diff / minute);
            return `${minutes} menit yang lalu`;
        } else if (diff < day) {
            const hours = Math.floor(diff / hour);
            return `${hours} jam yang lalu`;
        } else if (diff < week) {
            const days = Math.floor(diff / day);
            return `${days} hari yang lalu`;
        } else if (diff < month) {
            const weeks = Math.floor(diff / week);
            return `${weeks} minggu yang lalu`;
        } else if (diff < year) {
            const months = Math.floor(diff / month);
            return `${months} bulan yang lalu`;
        } else {
            const years = Math.floor(diff / year);
            return `${years} tahun yang lalu`;
        }
    },

    // String Manipulation
    truncateText: function (text, length = 100) {
        if (text.length <= length) return text;
        return text.substring(0, length) + "...";
    },

    capitalizeFirst: function (string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    },

    slugify: function (text) {
        return text
            .toLowerCase()
            .replace(/[^\w ]+/g, "")
            .replace(/ +/g, "-");
    },

    // Number Formatting
    formatNumber: function (num, decimals = 0) {
        return new Intl.NumberFormat("id-ID", {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(num);
    },

    formatCurrency: function (amount, currency = "IDR") {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    },

    // File Handling
    formatFileSize: function (bytes, decimals = 2) {
        if (bytes === 0) return "0 Bytes";

        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ["Bytes", "KB", "MB", "GB", "TB"];

        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return (
            parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i]
        );
    },

    getFileExtension: function (filename) {
        return filename.slice(((filename.lastIndexOf(".") - 1) >>> 0) + 2);
    },

    // Local Storage
    setLocalStorage: function (key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
            return true;
        } catch (e) {
            console.error("Error saving to localStorage:", e);
            return false;
        }
    },

    getLocalStorage: function (key, defaultValue = null) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : defaultValue;
        } catch (e) {
            console.error("Error reading from localStorage:", e);
            return defaultValue;
        }
    },

    removeLocalStorage: function (key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (e) {
            console.error("Error removing from localStorage:", e);
            return false;
        }
    },

    // URL Parameters
    getUrlParameter: function (name) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    },

    updateUrlParameter: function (key, value) {
        const url = new URL(window.location);
        url.searchParams.set(key, value);
        window.history.pushState({}, "", url);
    },

    // Copy to Clipboard
    copyToClipboard: function (text) {
        return new Promise((resolve, reject) => {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard
                    .writeText(text)
                    .then(() => resolve(true))
                    .catch((err) => reject(err));
            } else {
                // Fallback for older browsers
                const textarea = document.createElement("textarea");
                textarea.value = text;
                textarea.style.position = "fixed";
                textarea.style.opacity = "0";
                document.body.appendChild(textarea);
                textarea.select();

                try {
                    const successful = document.execCommand("copy");
                    document.body.removeChild(textarea);
                    successful
                        ? resolve(true)
                        : reject(new Error("Copy failed"));
                } catch (err) {
                    document.body.removeChild(textarea);
                    reject(err);
                }
            }
        });
    },

    // Debounce and Throttle
    debounce: function (func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    throttle: function (func, limit) {
        let inThrottle;
        return function () {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => (inThrottle = false), limit);
            }
        };
    },

    // Random Generators
    generateId: function (length = 8) {
        const chars =
            "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let result = "";
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    },

    generatePassword: function (length = 12) {
        const chars = {
            uppercase: "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
            lowercase: "abcdefghijklmnopqrstuvwxyz",
            numbers: "0123456789",
            symbols: "!@#$%^&*()_+-=[]{}|;:,.<>?",
        };

        let password = "";
        const allChars = Object.values(chars).join("");

        // Ensure at least one of each type
        password += this.getRandomChar(chars.uppercase);
        password += this.getRandomChar(chars.lowercase);
        password += this.getRandomChar(chars.numbers);
        password += this.getRandomChar(chars.symbols);

        // Fill the rest
        for (let i = password.length; i < length; i++) {
            password += this.getRandomChar(allChars);
        }

        // Shuffle the password
        return password
            .split("")
            .sort(() => Math.random() - 0.5)
            .join("");
    },

    getRandomChar: function (chars) {
        return chars.charAt(Math.floor(Math.random() * chars.length));
    },
};

// Make available globally
window.Utils = Utils;

// jQuery extensions
if (typeof jQuery !== "undefined") {
    $.fn.extend({
        serializeObject: function () {
            const data = {};
            const formArray = $(this).serializeArray();

            $.each(formArray, function () {
                if (data[this.name] !== undefined) {
                    if (!Array.isArray(data[this.name])) {
                        data[this.name] = [data[this.name]];
                    }
                    data[this.name].push(this.value);
                } else {
                    data[this.name] = this.value;
                }
            });

            return data;
        },

        resetForm: function () {
            this[0].reset();
            $(this).find(".is-invalid").removeClass("is-invalid");
            $(this).find(".invalid-feedback").remove();
            return this;
        },
    });
}
