// Loan Requests & Applications Admin Management Module
(function() {
    window.allLoanRequests = [];
    window.currentLoanRequestFilter = 'pending';

    async function loadLoanRequestsData() {
        const tbody = document.getElementById('loan-requests-tbody');
        if (tbody) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#94a3b8;"><i class="fa-solid fa-spinner fa-spin"></i> Loading applications...</td></tr>`;
        }

        try {
            const res = await window.apiFetch('/api/admin/loan-requests');
            if (!res || !res.ok) {
                if (tbody) tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#ef4444;"><i class="fa-solid fa-circle-exclamation"></i> Failed to load loan requests.</td></tr>`;
                return;
            }

            const payload = await res.json();
            const requests = (payload.data && Array.isArray(payload.data)) ? payload.data : [];
            window.allLoanRequests = requests;

            // Compute statistics
            let pending = 0, approved = 0, rejected = 0, totalReqAmount = 0;
            requests.forEach(r => {
                if (r.status === 'pending') pending++;
                else if (r.status === 'approved') approved++;
                else if (r.status === 'rejected') rejected++;
                totalReqAmount += parseFloat(r.amount || 0);
            });

            // Update stats cards
            const pendingEl = document.getElementById('stat-pending-requests');
            const approvedEl = document.getElementById('stat-approved-requests');
            const rejectedEl = document.getElementById('stat-rejected-requests');
            const volumeEl = document.getElementById('stat-requested-amount');
            const badgeEl = document.getElementById('sidebar-pending-loan-badge');

            if (pendingEl) pendingEl.textContent = pending;
            if (approvedEl) approvedEl.textContent = approved;
            if (rejectedEl) rejectedEl.textContent = rejected;
            if (volumeEl) volumeEl.textContent = '₹' + totalReqAmount.toLocaleString('en-IN');

            if (badgeEl) {
                if (pending > 0) {
                    badgeEl.style.display = 'inline-block';
                    badgeEl.textContent = pending;
                } else {
                    badgeEl.style.display = 'none';
                }
            }

            renderLoanRequestsTable();
        } catch (err) {
            console.error('Error fetching loan requests:', err);
            if (tbody) tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#ef4444;">Network connection error.</td></tr>`;
        }
    }
    window.loadLoanRequestsData = loadLoanRequestsData;

    window.setLoanRequestFilter = function(status, btn) {
        window.currentLoanRequestFilter = status;
        document.querySelectorAll('.loan-req-filter').forEach(b => {
            b.style.background = '#f8fafc';
            b.style.color = '#475569';
            b.style.borderColor = '#e2e8f0';
        });
        if (btn) {
            btn.style.background = '#004838';
            btn.style.color = '#ffffff';
            btn.style.borderColor = '#004838';
        }
        renderLoanRequestsTable();
    };

    window.filterLoanRequestsTable = function() {
        renderLoanRequestsTable();
    };

    function renderLoanRequestsTable() {
        const tbody = document.getElementById('loan-requests-tbody');
        if (!tbody) return;

        const searchVal = (document.getElementById('loan-request-search')?.value || '').toLowerCase().trim();
        const filterStatus = window.currentLoanRequestFilter;

        let filtered = window.allLoanRequests.filter(r => {
            if (filterStatus !== 'all' && r.status !== filterStatus) {
                return false;
            }
            if (searchVal) {
                const uName = (r.user?.name || '').toLowerCase();
                const uPhone = (r.user?.phone || '').toLowerCase();
                const purpose = (r.purpose || '').toLowerCase();
                const idStr = String(r.id);
                return uName.includes(searchVal) || uPhone.includes(searchVal) || purpose.includes(searchVal) || idStr.includes(searchVal);
            }
            return true;
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" style="text-align:center; padding:50px 20px; color:#64748b;">
                        <i class="fa-solid fa-folder-open" style="font-size:2.5rem; color:#cbd5e1; margin-bottom:12px; display:block;"></i>
                        <strong>No loan applications found.</strong>
                        <p style="margin:4px 0 0 0; font-size:0.8rem; color:#94a3b8;">There are no applications matching the selected criteria.</p>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        filtered.forEach(r => {
            const user = r.user || {};
            const userName = user.name || 'Member #' + r.user_id;
            const userPhone = user.phone || 'N/A';
            const userEmail = user.email || '';
            const amount = parseFloat(r.amount || 0).toLocaleString('en-IN');
            const freq = (r.payment_frequency || 'monthly').charAt(0).toUpperCase() + (r.payment_frequency || 'monthly').slice(1);
            const duration = (r.duration_months || 0) + ' Mo';
            const purpose = r.purpose ? r.purpose : '<span style="color:#94a3b8; font-style:italic;">General Financial Assistance</span>';
            const created = r.created_at ? new Date(r.created_at).toLocaleDateString('en-IN', { day:'2-digit', month:'short', year:'numeric' }) : 'N/A';

            let statusBadge = '';
            let actionButtons = '';

            if (r.status === 'pending') {
                statusBadge = `<span style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; padding:5px 12px; border-radius:20px; font-weight:700; font-size:0.75rem; display:inline-flex; align-items:center; gap:5px;"><i class="fa-solid fa-clock"></i> Pending Review</span>`;
                actionButtons = `
                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <button onclick="openApproveLoanModal(${r.id})" style="background:#004838; color:white; border:none; padding:7px 14px; border-radius:8px; font-weight:700; font-size:0.8rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:all 0.2s;">
                            <i class="fa-solid fa-check"></i> Approve
                        </button>
                        <button onclick="rejectLoanRequest(${r.id})" style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:7px 12px; border-radius:8px; font-weight:600; font-size:0.8rem; cursor:pointer; display:inline-flex; align-items:center; gap:5px; transition:all 0.2s;">
                            <i class="fa-solid fa-xmark"></i> Reject
                        </button>
                    </div>
                `;
            } else if (r.status === 'approved') {
                statusBadge = `<span style="background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; padding:5px 12px; border-radius:20px; font-weight:700; font-size:0.75rem; display:inline-flex; align-items:center; gap:5px;"><i class="fa-solid fa-circle-check"></i> Approved</span>`;
                actionButtons = `
                    <div style="text-align:right;">
                        <a href="#loans" style="color:#004838; text-decoration:none; font-weight:700; font-size:0.8rem; display:inline-flex; align-items:center; gap:4px;">
                            <span>View Active Loan</span> <i class="fa-solid fa-arrow-right" style="font-size:0.75rem;"></i>
                        </a>
                    </div>
                `;
            } else {
                statusBadge = `<span style="background:#fee2e2; color:#991b1b; border:1px solid #fecaca; padding:5px 12px; border-radius:20px; font-weight:700; font-size:0.75rem; display:inline-flex; align-items:center; gap:5px;"><i class="fa-solid fa-circle-xmark"></i> Rejected</span>`;
                actionButtons = `
                    <div style="text-align:right; color:#94a3b8; font-size:0.8rem;">
                        ${r.admin_remarks ? '<span title="' + r.admin_remarks + '"><i class="fa-regular fa-comment-dots"></i> Reason noted</span>' : 'Declined'}
                    </div>
                `;
            }

            html += `
                <tr style="border-bottom:1px solid #f1f5f9; transition:background 0.2s;">
                    <td style="padding:14px 18px; font-weight:700; color:#64748b; font-size:0.85rem;">#${r.id}</td>
                    <td style="padding:14px 18px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:36px; height:36px; border-radius:50%; background:#e0f2fe; color:#0369a1; font-weight:700; display:flex; align-items:center; justify-content:center; font-size:0.95rem;">
                                ${userName.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <strong style="font-size:0.9rem; color:#1e293b; display:block;">${userName}</strong>
                                <span style="font-size:0.75rem; color:#64748b;"><i class="fa-solid fa-phone" style="font-size:0.7rem;"></i> ${userPhone}</span>
                            </div>
                        </div>
                    </td>
                    <td style="padding:14px 18px; font-weight:700; color:#004838; font-size:0.95rem;">₹${amount}</td>
                    <td style="padding:14px 18px; font-size:0.85rem; color:#334155;">
                        <strong>${duration}</strong> <span style="color:#64748b;">(${freq})</span>
                    </td>
                    <td style="padding:14px 18px; font-size:0.85rem; color:#475569; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        ${purpose}
                    </td>
                    <td style="padding:14px 18px; font-size:0.8rem; color:#64748b;">${created}</td>
                    <td style="padding:14px 18px;">${statusBadge}</td>
                    <td style="padding:14px 18px; text-align:right;">${actionButtons}</td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    window.openApproveLoanModal = function(id) {
        const req = window.allLoanRequests.find(r => r.id === id);
        if (!req) return;

        document.getElementById('approve-loan-request-id').value = req.id;
        document.getElementById('approve-member-name').textContent = req.user?.name || ('Member #' + req.user_id);
        document.getElementById('approve-requested-amount').textContent = '₹' + parseFloat(req.amount || 0).toLocaleString('en-IN');
        document.getElementById('approve-loan-amount').value = req.amount;
        document.getElementById('approve-duration-months').value = req.duration_months;
        document.getElementById('approve-payment-frequency').value = req.payment_frequency || 'monthly';
        document.getElementById('approve-interest-rate').value = '12.0';
        document.getElementById('approve-admin-remarks').value = '';

        const modal = document.getElementById('modal-approve-loan-request');
        if (modal) modal.style.display = 'flex';
    };

    window.closeApproveLoanModal = function() {
        const modal = document.getElementById('modal-approve-loan-request');
        if (modal) modal.style.display = 'none';
    };

    window.submitApproveLoanRequest = async function(e) {
        e.preventDefault();
        const id = document.getElementById('approve-loan-request-id').value;
        const amount = document.getElementById('approve-loan-amount').value;
        const duration = document.getElementById('approve-duration-months').value;
        const freq = document.getElementById('approve-payment-frequency').value;
        const rate = document.getElementById('approve-interest-rate').value;
        const remarks = document.getElementById('approve-admin-remarks').value;

        const btn = document.getElementById('btn-confirm-approve-loan');
        const origText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Approving Loan...';
        }

        try {
            const res = await window.apiFetch(`/api/admin/loan-requests/${id}/approve`, {
                method: 'POST',
                body: JSON.stringify({
                    amount: parseFloat(amount),
                    duration_months: parseInt(duration),
                    payment_frequency: freq,
                    interest_rate_percent: parseFloat(rate),
                    admin_remarks: remarks
                })
            });

            if (!res) return;
            const payload = await res.json();
            if (payload.status || payload.success) {
                alert('Loan Approved Successfully! Active loan schedule generated.');
                closeApproveLoanModal();
                loadLoanRequestsData();
                if (typeof loadLoansData === 'function') loadLoansData();
            } else {
                alert('Approval Error: ' + (payload.message || 'Could not approve loan'));
            }
        } catch (err) {
            console.error('Approval failed:', err);
            alert('Failed to connect to server.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        }
    };

    window.rejectLoanRequest = async function(id) {
        const reason = prompt('Please enter rejection remark/reason for the applicant (optional):', 'Application does not meet eligibility criteria.');
        if (reason === null) return; // user cancelled

        try {
            const res = await window.apiFetch(`/api/admin/loan-requests/${id}/reject`, {
                method: 'POST',
                body: JSON.stringify({ admin_remarks: reason })
            });
            if (!res) return;
            const payload = await res.json();
            if (payload.status || payload.success) {
                alert('Loan request has been rejected.');
                loadLoanRequestsData();
            } else {
                alert('Error: ' + (payload.message || 'Could not reject request'));
            }
        } catch (err) {
            console.error('Reject error:', err);
            alert('Request failed.');
        }
    };

    // Auto-load if opened directly with hash
    function checkHashAndLoad() {
        if (window.location.hash === '#loan-requests') {
            loadLoanRequestsData();
        }
    }
    window.addEventListener('hashchange', checkHashAndLoad);
    document.addEventListener('DOMContentLoaded', checkHashAndLoad);
    setTimeout(checkHashAndLoad, 300);
})();
