const API_BASE_URL = "http://localhost/php-LMS/backend";

function getUser() {
    const userStr = localStorage.getItem("user");
    return userStr ? JSON.parse(userStr) : null;
}

function setUser(user) {
    localStorage.setItem("user", JSON.stringify(user));
}

function removeUser() {
    localStorage.removeItem("user");
}

async function apiCall(endpoint, method = "GET", data = null) {
    const url = `${API_BASE_URL}${endpoint}`;

    const options = {
        method,
        headers: {
            "Content-Type": "application/json",
        },
        credentials: "include",
    };

    if (data && method !== "GET") {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || "API request failed");
        }

        return result.data || result;
    } catch (error) {
        console.error("API Error:", error);
        throw error;
    }
}

async function login(email, password) {
    try {
        const response = await apiCall("/api/auth/login", "POST", {
            email,
            password,
        });

        if (response.user) {
            setUser(response.user);
            return response;
        } else {
            throw new Error("Invalid response from server");
        }
    } catch (error) {
        throw new Error(error.message || "Login failed");
    }
}

async function register(userData) {
    try {
        const response = await apiCall("/api/auth/register", "POST", userData);

        if (response.user) {
            setUser(response.user);
            return response;
        } else {
            throw new Error("Invalid response from server");
        }
    } catch (error) {
        throw new Error(error.message || "Registration failed");
    }
}

async function checkAuth() {
    try {
        const response = await apiCall("/api/auth/me");
        if (response.user) {
            setUser(response.user);
            return response.user;
        }
        throw new Error("Authentication failed");
    } catch (error) {
        removeUser();
        throw new Error("Authentication failed");
    }
}

function logout() {
    removeUser();
    apiCall("/api/auth/logout", "POST").then(() => {
        window.location.href = "index.html";
    }).catch(() => {
        window.location.href = "index.html";
    });
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function showAlert(type, message) {
    const alertContainer = document.getElementById("alertContainer");
    if (!alertContainer) return;

    const alertClass = `alert-${type}`;
    const iconClass =
        type === "success"
            ? "fa-check-circle"
            : type === "error"
            ? "fa-exclamation-circle"
            : type === "warning"
            ? "fa-exclamation-triangle"
            : "fa-info-circle";

    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    alertContainer.innerHTML = alertHtml;

    setTimeout(() => {
        const alert = alertContainer.querySelector(".alert");
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validatePassword(password) {
    return password.length >= 6;
}

function validateRequired(value) {
    return value && value.trim().length > 0;
}

function isValidDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}

function isFutureDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return date >= today;
}

function isEndDateAfterStartDate(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);
    return end > start;
}

window.apiCall = apiCall;
window.login = login;
window.register = register;
window.checkAuth = checkAuth;
window.logout = logout;
window.formatDate = formatDate;
window.formatDateTime = formatDateTime;
window.showAlert = showAlert;
window.validateEmail = validateEmail;
window.validatePassword = validatePassword;
window.validateRequired = validateRequired;
window.isValidDate = isValidDate;
window.isFutureDate = isFutureDate;
window.isEndDateAfterStartDate = isEndDateAfterStartDate;
