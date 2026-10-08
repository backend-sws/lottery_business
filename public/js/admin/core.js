
    
    // Core State & Dynamic Token Retrieval
    window.getAuthToken = () => localStorage.getItem('admin_token') || '';
    let authToken = window.getAuthToken();

    // Elements
    const loginScreen = document.getElementById('login-screen');
    const appShell = document.getElementById('app-shell');
    const loginForm = document.getElementById('login-form');
    const logoutBtn = document.getElementById('logout-btn');
    const errorMsg = document.getElementById('login-error');
    const navLinks = document.querySelectorAll('.nav-link');
    const viewSections = document.querySelectorAll('.view-section');

    const modal = document.getElementById('global-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalBody = document.getElementById('modal-body');

    // Chart instances
    let lineChartInstance = null;
    let doughnutChartInstance = null;

    // Dynamic Fetch Headers
    window.getHeaders = (isFormData = false) => {
        const token = window.getAuthToken();
        const headers = {
            'Accept': 'application/json'
        };
        if (!isFormData) {
            headers['Content-Type'] = 'application/json';
        }
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        return headers;
    };
    const getHeaders = window.getHeaders;

    // Centralized Session & 401 Expiration Handler (debounced)
    let isHandling401 = false;
    window.handleSessionExpired = function(msg = 'Session expired. Please log in again.') {
        if (isHandling401) return;
        isHandling401 = true;

        console.warn('Authentication session expired. Cleanly redirecting to login view.');
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        authToken = null;

        if (window.routeAbortController) {
            try { window.routeAbortController.abort(); } catch(e) {}
            window.routeAbortController = null;
        }

        if (typeof showLogin === 'function') {
            showLogin();
        }
        const errEl = document.getElementById('login-error');
        if (errEl) {
            errEl.textContent = msg;
            errEl.style.display = 'block';
        }

        setTimeout(() => { isHandling401 = false; }, 2000);
    };

    // Resilient apiFetch wrapper with automatic token injection and route abort support
    window.apiFetch = async function(url, options = {}) {
        const token = window.getAuthToken();
        const isFormData = options.body instanceof FormData;

        const mergedHeaders = {
            ...window.getHeaders(isFormData),
            ...(options.headers || {})
        };

        const config = {
            ...options,
            headers: mergedHeaders
        };

        // Attach route signal if none provided
        if (!config.signal && window.routeAbortController) {
            config.signal = window.routeAbortController.signal;
        }

        try {
            const res = await fetch(url, config);
            if (res.status === 401) {
                if (token) {
                    window.handleSessionExpired();
                }
                return res;
            }
            return res;
        } catch (err) {
            if (err.name === 'AbortError') {
                return null; // Route changed cleanly, ignore
            }
            console.error('apiFetch request error for ' + url + ':', err);
            throw err;
        }
    };

    // ----- DARK MODE CONFIG -----
    const toggleBtn = document.getElementById('dark-mode-toggle');
    if (toggleBtn) {
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-theme');
            toggleBtn.innerHTML = '<i class="fa-regular fa-sun"></i>';
        }
        
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-theme');
            const isDark = document.body.classList.contains('dark-theme');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            toggleBtn.innerHTML = isDark ? '<i class="fa-regular fa-sun"></i>' : '<i class="fa-regular fa-moon"></i>';
            
            // Re-render charts to update grid/axis colors
            if (typeof renderCharts === 'function' && document.getElementById('lineChart')) {
                loadDashboardData();
            }
            if (typeof loadCollectionsOverviewData === 'function' && document.getElementById('collectionsTrendChart')) {
                loadCollectionsOverviewData();
            }
        });
    }

    // ----- SIDEBAR MINIMIZE CONFIG -----
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        if (localStorage.getItem('sidebar-minimized') === 'true') {
            sidebar.classList.add('minimized');
        }
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('minimized');
            localStorage.setItem('sidebar-minimized', sidebar.classList.contains('minimized') ? 'true' : 'false');
        });

        // Generate tooltips for minimized navigation
        const links = sidebar.querySelectorAll('.nav-menu a, .sidebar-footer a');
        links.forEach(link => {
            const text = link.querySelector('span');
            if (text) {
                link.setAttribute('title', text.textContent.trim());
            }
        });
    }


    // ----- ROUTING (Simple Hash Router with Abort Support) -----
    window.routeAbortController = null;

    function handleRoute() {
        // Cancel pending requests from previous view to prevent network congestion & UI freezes
        if (window.routeAbortController) {
            try { window.routeAbortController.abort(); } catch (e) {}
        }
        window.routeAbortController = new AbortController();

        const hash = window.location.hash || '#dashboard';
        let targetView = hash.replace('#', '');
        let paramId = null;

        if (targetView.startsWith('committee-details-')) {
            paramId = targetView.replace('committee-details-', '');
            targetView = 'committee-details';
        } else if (targetView === 'committees-active') {
            paramId = 'active';
            targetView = 'committees';
        }

        // Update nav active states
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href === hash || (targetView === 'committee-details' && href === '#collection-committees') || (targetView === 'committees' && href === '#committees') || (targetView === 'payments' && href === '#payouts')) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });

        // Show/hide sections & trigger data loaders safely
        viewSections.forEach(section => {
            if (section.id === `view-${targetView}`) {
                section.style.display = 'block';
                // Trigger data load based on view safely
                try {
                    if (targetView === 'dashboard' && typeof loadDashboardData === 'function') loadDashboardData();
                    if (targetView === 'committees' && typeof loadCommitteesData === 'function') loadCommitteesData(paramId);
                    if (targetView === 'members' && typeof loadMembersData === 'function') loadMembersData();
                    if (targetView === 'installments' && typeof loadInstallmentsData === 'function') loadInstallmentsData();
                    if (targetView === 'lotteries' && typeof loadLotteriesData === 'function') loadLotteriesData();
                    if (targetView === 'loans') {
                        if (typeof loadLoansData === 'function') loadLoansData();
                        if (typeof loadBalanceSheetData === 'function') loadBalanceSheetData();
                    }
                    if ((targetView === 'payouts' || targetView === 'payments') && typeof loadPayoutsData === 'function') loadPayoutsData();
                    if (targetView === 'collection-committees' && typeof loadCollectionCommittees === 'function') loadCollectionCommittees();
                    if (targetView === 'committee-details' && typeof loadCommitteeDetails === 'function') loadCommitteeDetails(paramId);
                    if (targetView === 'paid-members' && typeof loadPaidMembersData === 'function') loadPaidMembersData();
                    if (targetView === 'due-members' && typeof loadDueMembersData === 'function') loadDueMembersData();
                    if (targetView === 'pnl' && typeof loadPnLData === 'function') loadPnLData();
                    if (targetView === 'balance-sheet' && typeof loadBalanceSheetData === 'function') loadBalanceSheetData();
                    if (targetView === 'agents') {
                        if (typeof loadAgentsView === 'function') loadAgentsView();
                        if (typeof loadAgentsList === 'function') loadAgentsList();
                    }
                    if (targetView === 'kyc' && typeof loadKycData === 'function') loadKycData();
                    if (targetView === 'collections' && typeof loadCollectionsOverviewData === 'function') loadCollectionsOverviewData();
                    if (targetView === 'settings' && typeof loadSettingsData === 'function') loadSettingsData();
                    if (targetView === 'materials' && typeof loadMaterialsData === 'function') loadMaterialsData();
                    if (targetView === 'terms' && typeof loadTermsData === 'function') loadTermsData();
                    if (targetView === 'member-ledger') {
                        const mb = document.getElementById('member-ledger-tbody'); if (mb) mb.innerHTML = '';
                        const mn = document.getElementById('ledger-member-name'); if (mn) mn.textContent = '';
                    }
                    if (targetView === 'committee-ledger') {
                        const cb = document.getElementById('committee-ledger-tbody'); if (cb) cb.innerHTML = '';
                        const cn = document.getElementById('ledger-committee-name'); if (cn) cn.textContent = '';
                    }
                } catch (loadErr) {
                    console.error('Error triggering loader for view ' + targetView + ':', loadErr);
                }
            } else {
                section.style.display = 'none';
            }
        });
    }
    window.addEventListener('hashchange', handleRoute);