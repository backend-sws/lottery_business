// ----- INITIALIZATION -----
document.addEventListener('DOMContentLoaded', () => {
    const token = (typeof window.getAuthToken === 'function' ? window.getAuthToken() : localStorage.getItem('admin_token'));
    if (token) {
        if (typeof authToken !== 'undefined') authToken = token;
        showApp();
    } else {
        showLogin();
    }
});
