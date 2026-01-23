// Core Application JavaScript
document.addEventListener("DOMContentLoaded", function () {
    // Initialize tooltips
    initTooltips();

    // Initialize popovers
    initPopovers();

    // Initialize form validation
    initFormValidation();

    // Handle AJAX CSRF token
    setupAjaxCSRF();

    // Initialize loading states
    initLoadingStates();

    // Initialize modals
    initModals();

    // Initialize notifications
    initNotifications();
});

// Tooltip Initialization
function initTooltips() {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]'),
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: "hover focus",
        });
    });
}

// Popover Initialization
function initPopovers() {
    const popoverTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="popover"]'),
    );
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Form Validation
function initFormValidation() {
    // Example form validation
    const forms = document.querySelectorAll(".needs-validation");

    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener(
            "submit",
            function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add("was-validated");
            },
            false,
        );
    });
}

// AJAX CSRF Token Setup
function setupAjaxCSRF() {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
}

// Loading States
function initLoadingStates() {
    // Handle loading buttons
    document.addEventListener("click", function (e) {
        const button = e.target.closest(".btn-loading");
        if (button) {
            const originalText = button.innerHTML;
            const loadingText = button.dataset.loadingText || "Memproses...";

            button.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                ${loadingText}
            `;
            button.disabled = true;

            // Reset after 30 seconds max
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 30000);
        }
    });
}

// Modal Initialization
function initModals() {
    // Auto-focus on modal shown
    document.addEventListener("shown.bs.modal", function (event) {
        const modal = event.target;
        const input = modal.querySelector(
            'input:not([type="hidden"]), select, textarea',
        );
        if (input) {
            input.focus();
        }
    });

    // Clear modal content on hidden
    document.addEventListener("hidden.bs.modal", function (event) {
        const modal = event.target;
        const forms = modal.querySelectorAll("form");
        forms.forEach((form) => {
            form.reset();
            form.classList.remove("was-validated");
        });
    });
}

// Notification System
function initNotifications() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll(".alert:not(.alert-permanent)");
    alerts.forEach((alert) => {
        setTimeout(() => {
            if (alert.parentNode) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });

    // Close button functionality
    document.addEventListener("click", function (e) {
        if (e.target.closest(".alert .btn-close")) {
            const alert = e.target.closest(".alert");
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}

// Utility Functions
function showLoading(selector = "body") {
    const element = document.querySelector(selector);
    if (element) {
        element.classList.add("loading");
    }
}

function hideLoading(selector = "body") {
    const element = document.querySelector(selector);
    if (element) {
        element.classList.remove("loading");
    }
}

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return "0 Bytes";

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ["Bytes", "KB", "MB", "GB", "TB"];

    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
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
}

// Export for use in other files
window.App = {
    showLoading,
    hideLoading,
    formatBytes,
    formatDate,
    debounce,
    throttle,
};

const SidebarModule = (function () {
    let isCollapsed = false;
    let isMobile = false;
    let isOpen = false;

    // Public methods
    return {
        init: function () {
            this.checkMobile();
            this.loadState();
            this.initEventListeners();
            this.setupTooltips();
            this.setupSubmenus();

            // Initialize based on device
            if (isMobile) {
                this.setupMobile();
            } else {
                this.setupDesktop();
            }
        },

        checkMobile: function () {
            isMobile = window.innerWidth <= 768;
            return isMobile;
        },

        loadState: function () {
            if (!isMobile) {
                const savedState = Utils.getLocalStorage(
                    "sidebarState",
                    "expanded",
                );
                isCollapsed = savedState === "collapsed";
            }
        },

        saveState: function () {
            if (!isMobile) {
                const state = isCollapsed ? "collapsed" : "expanded";
                Utils.setLocalStorage("sidebarState", state);
            }
        },

        initEventListeners: function () {
            // Hamburger button click
            document.addEventListener("click", (e) => {
                if (
                    e.target.closest(".navbar-toggler") ||
                    e.target.closest("#mobileSidebarToggle")
                ) {
                    e.preventDefault();
                    this.toggle();
                }
            });

            // Close sidebar when clicking overlay
            document.addEventListener("click", (e) => {
                if (isOpen && e.target.closest(".sidebar-overlay")) {
                    this.close();
                }
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener("click", (e) => {
                if (isMobile && isOpen) {
                    const sidebar = document.getElementById("sidebar");
                    const navbarToggler =
                        document.querySelector(".navbar-toggler");

                    if (
                        !sidebar.contains(e.target) &&
                        !navbarToggler.contains(e.target) &&
                        !e.target.closest(".sidebar-overlay")
                    ) {
                        this.close();
                    }
                }
            });

            // Keyboard shortcuts
            document.addEventListener("keydown", (e) => {
                // Escape to close sidebar
                if (e.key === "Escape" && isOpen) {
                    this.close();
                }

                // Ctrl+B or Cmd+B to toggle sidebar (desktop only)
                if ((e.ctrlKey || e.metaKey) && e.key === "b" && !isMobile) {
                    e.preventDefault();
                    this.toggleDesktop();
                }
            });

            // Handle window resize
            let resizeTimer;
            window.addEventListener("resize", () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    const wasMobile = isMobile;
                    this.checkMobile();

                    if (wasMobile !== isMobile) {
                        if (isMobile) {
                            this.setupMobile();
                        } else {
                            this.setupDesktop();
                        }
                    }
                }, 250);
            });

            // Collapse button in sidebar footer (desktop only)
            document
                .getElementById("sidebarCollapse")
                ?.addEventListener("click", (e) => {
                    e.preventDefault();
                    this.toggleDesktop();
                });
        },

        setupMobile: function () {
            // Close sidebar initially on mobile
            this.close();

            // Show hamburger button in navbar
            const navbarToggler = document.querySelector(".navbar-toggler");
            if (navbarToggler) {
                navbarToggler.style.display = "block";
            }

            // Hide collapse button in sidebar
            const collapseBtn = document.getElementById("sidebarCollapse");
            if (collapseBtn) {
                collapseBtn.style.display = "none";
            }

            // Remove collapsed class if exists
            document.getElementById("sidebar")?.classList.remove("collapsed");

            // Reset transform for mobile
            document
                .querySelector(".main-content")
                ?.style.removeProperty("transform");
            document
                .querySelector(".navbar")
                ?.style.removeProperty("transform");
        },

        setupDesktop: function () {
            // Hide hamburger button
            const navbarToggler = document.querySelector(".navbar-toggler");
            if (navbarToggler) {
                navbarToggler.style.display = "none";
            }

            // Show collapse button
            const collapseBtn = document.getElementById("sidebarCollapse");
            if (collapseBtn) {
                collapseBtn.style.display = "block";
            }

            // Remove overlay if exists
            document.querySelector(".sidebar-overlay")?.remove();

            // Reset body classes
            document.body.classList.remove("sidebar-open");

            // Apply saved state
            const sidebar = document.getElementById("sidebar");
            if (sidebar) {
                sidebar.classList.remove("show");
                sidebar.style.transform = "none";

                if (isCollapsed) {
                    sidebar.classList.add("collapsed");
                } else {
                    sidebar.classList.remove("collapsed");
                }
            }

            // Reset main content and navbar
            const mainContent = document.querySelector(".main-content");
            const navbar = document.querySelector(".navbar");

            if (mainContent) {
                mainContent.style.transform = "none";
                mainContent.style.marginLeft = isCollapsed
                    ? "70px"
                    : "var(--sidebar-width)";
            }

            if (navbar) {
                navbar.style.transform = "none";
                navbar.style.marginLeft = isCollapsed
                    ? "70px"
                    : "var(--sidebar-width)";
            }

            this.setupTooltips();
        },

        setupTooltips: function () {
            if (isCollapsed && !isMobile) {
                const navLinks =
                    document.querySelectorAll(".sidebar .nav-link");
                navLinks.forEach((link) => {
                    const span = link.querySelector("span");
                    if (span) {
                        link.setAttribute("data-bs-toggle", "tooltip");
                        link.setAttribute("data-bs-placement", "right");
                        link.setAttribute("title", span.textContent);

                        // Initialize Bootstrap tooltip
                        new bootstrap.Tooltip(link, {
                            trigger: "hover",
                            placement: "right",
                        });
                    }
                });
            } else {
                // Remove tooltips
                const navLinks =
                    document.querySelectorAll(".sidebar .nav-link");
                navLinks.forEach((link) => {
                    link.removeAttribute("data-bs-toggle");
                    link.removeAttribute("data-bs-placement");
                    link.removeAttribute("title");

                    // Destroy existing tooltip
                    const tooltip = bootstrap.Tooltip.getInstance(link);
                    if (tooltip) {
                        tooltip.dispose();
                    }
                });
            }
        },

        setupSubmenus: function () {
            const submenuToggles = document.querySelectorAll(".submenu-toggle");
            submenuToggles.forEach((toggle) => {
                toggle.addEventListener("click", function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const parent = this.closest(".has-submenu");
                    const submenu = parent.querySelector(".submenu");

                    parent.classList.toggle("open");
                    submenu.classList.toggle("open");
                });
            });
        },

        toggle: function () {
            if (isMobile) {
                if (isOpen) {
                    this.close();
                } else {
                    this.open();
                }
            } else {
                this.toggleDesktop();
            }
        },

        open: function () {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.querySelector(".main-content");
            const navbar = document.querySelector(".navbar");
            const navbarToggler = document.querySelector(".navbar-toggler");

            // Create overlay if not exists
            let overlay = document.querySelector(".sidebar-overlay");
            if (!overlay) {
                overlay = document.createElement("div");
                overlay.className = "sidebar-overlay";
                document.body.appendChild(overlay);
            }

            // Show elements
            sidebar.classList.add("show");
            overlay.classList.add("show");

            // Slide content
            if (mainContent)
                mainContent.style.transform = `translateX(${sidebar.offsetWidth}px)`;
            if (navbar)
                navbar.style.transform = `translateX(${sidebar.offsetWidth}px)`;

            // Update navbar toggler state
            if (navbarToggler) {
                navbarToggler.setAttribute("aria-expanded", "true");
            }

            // Prevent body scroll
            document.body.classList.add("sidebar-open");

            isOpen = true;

            // Dispatch event
            document.dispatchEvent(new CustomEvent("sidebar:opened"));
        },

        close: function () {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.querySelector(".main-content");
            const navbar = document.querySelector(".navbar");
            const overlay = document.querySelector(".sidebar-overlay");
            const navbarToggler = document.querySelector(".navbar-toggler");

            // Hide elements
            sidebar.classList.remove("show");
            if (overlay) overlay.classList.remove("show");

            // Reset slide
            if (mainContent) mainContent.style.transform = "translateX(0)";
            if (navbar) navbar.style.transform = "translateX(0)";

            // Update navbar toggler state
            if (navbarToggler) {
                navbarToggler.setAttribute("aria-expanded", "false");
            }

            // Allow body scroll
            document.body.classList.remove("sidebar-open");

            // Remove overlay after transition
            setTimeout(() => {
                if (overlay && !overlay.classList.contains("show")) {
                    overlay.remove();
                }
            }, 300);

            isOpen = false;

            // Dispatch event
            document.dispatchEvent(new CustomEvent("sidebar:closed"));
        },

        toggleDesktop: function () {
            const sidebar = document.getElementById("sidebar");
            const mainContent = document.querySelector(".main-content");
            const navbar = document.querySelector(".navbar");

            if (isCollapsed) {
                // Expand
                sidebar.classList.remove("collapsed");
                if (mainContent)
                    mainContent.style.marginLeft = "var(--sidebar-width)";
                if (navbar) navbar.style.marginLeft = "var(--sidebar-width)";
                isCollapsed = false;
            } else {
                // Collapse
                sidebar.classList.add("collapsed");
                if (mainContent) mainContent.style.marginLeft = "70px";
                if (navbar) navbar.style.marginLeft = "70px";
                isCollapsed = true;
            }

            this.setupTooltips();
            this.saveState();

            // Dispatch event
            document.dispatchEvent(
                new CustomEvent("sidebar:toggled", {
                    detail: { collapsed: isCollapsed },
                }),
            );
        },

        // Public getters
        getIsOpen: function () {
            return isOpen;
        },

        getIsCollapsed: function () {
            return isCollapsed;
        },

        getIsMobile: function () {
            return isMobile;
        },

        // Active link highlighting
        highlightActiveLink: function () {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll(".sidebar .nav-link");

            navLinks.forEach((link) => {
                link.classList.remove("active");

                const href = link.getAttribute("href");
                if (href && currentPath.startsWith(href)) {
                    link.classList.add("active");

                    // Also highlight parent if it's a submenu
                    const parentItem = link.closest(".has-submenu");
                    if (parentItem) {
                        parentItem.classList.add("open");
                        const submenu = parentItem.querySelector(".submenu");
                        if (submenu) {
                            submenu.classList.add("open");
                        }
                    }
                }
            });
        },

        // Update badge counts
        updateBadgeCounts: function () {
            // Update dataset count
            $.ajax({
                url: "/api/datasets/count",
                method: "GET",
                success: function (response) {
                    const badge = document.querySelector(
                        '.sidebar [href="{{ route("datasets.index") }}"] .badge',
                    );
                    if (badge && response.count !== undefined) {
                        badge.textContent = response.count;
                    }
                },
            });
        },
    };
})();

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
    SidebarModule.init();
    SidebarModule.highlightActiveLink();

    // Update badge counts periodically
    setInterval(() => {
        SidebarModule.updateBadgeCounts();
    }, 300000);
});

// Make available globally
window.SidebarModule = SidebarModule;
