// Global JavaScript for AI-ACADEMY LMS
// Handles common interactions, async loads, and utilities

document.addEventListener('DOMContentLoaded', () => {
    // Initialize tooltips and popovers
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', (e) => {
            e.preventDefault();
            const menu = dropdown.nextElementSibling;
            if (menu) {
                menu.classList.toggle('show');
            }
        });
    });

    // Close dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.matches('.dropdown-toggle')) {
            const dropdowns = document.querySelectorAll('.dropdown-menu');
            dropdowns.forEach(menu => menu.classList.remove('show'));
        }
    });

    // Async load for widgets (e.g., notifications)
    loadNotifications();

    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            if (!validateForm(form)) {
                e.preventDefault();
            }
        });
    });

    // Progress bars animation
    animateProgressBars();
});

// Load notifications asynchronously
function loadNotifications() {
    fetch('api/notifications.php')
        .then(response => response.json())
        .then(data => {
            const notificationBadge = document.querySelector('.fa-bell').nextSibling;
            if (notificationBadge) {
                notificationBadge.textContent = data.count;
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

// Form validation utility
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    return isValid;
}

// Animate progress bars
function animateProgressBars() {
    const progressBars = document.querySelectorAll('.progress');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.width = width;
        }, 500);
    });
}

// Utility for showing alerts
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    document.querySelector('.main-content').prepend(alertDiv);
    setTimeout(() => alertDiv.remove(), 5000);
}

// AJAX utility
function ajaxRequest(url, method = 'GET', data = null) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
        body: data ? JSON.stringify(data) : null,
    })
    .then(response => response.json());
}

// Engagement tracking (for algorithm)
function trackEngagement(action, points) {
    ajaxRequest('api/engagement.php', 'POST', { action, points })
        .then(data => {
            if (data.success) {
                console.log('Engagement tracked:', action);
            }
        })
        .catch(error => console.error('Error tracking engagement:', error));
}