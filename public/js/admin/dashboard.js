
    // ----- DATA FETCHING -----
    async function loadDashboardData() {
        try {
            const res = await fetch('/api/admin/dashboard', { headers: getHeaders() });
            if(res.status === 401) { logoutBtn.click(); return; }
            const payload = await res.json();
            if(!payload.data) return;
            const stats = payload.data;
            
            const heroDate = document.getElementById('hero-banner-date-sub');
            if (heroDate) {
                const opt = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                heroDate.textContent = 'Administrator • ' + new Date().toLocaleDateString('en-IN', opt);
            }

            // Hero banner dynamic metrics
            const heroModules = document.getElementById('hero-modules-count');
            if (heroModules) heroModules.textContent = stats.modules_count || 8;
            const heroControls = document.getElementById('hero-controls-count');
            if (heroControls) heroControls.textContent = stats.controls_count || 0;
            const heroCollections = document.getElementById('hero-today-collections');
            if (heroCollections) heroCollections.textContent = '₹' + (stats.today_collection_formatted || '0');

            // Legacy elements
            const oldCollection = document.getElementById('stat-collection');
            if (oldCollection) oldCollection.textContent = '₹' + Number(stats.today_collection || 0).toLocaleString('en-IN');
            const oldMembers = document.getElementById('stat-members');
            if (oldMembers) oldMembers.textContent = stats.total_members || 0;
            const oldPaid = document.getElementById('stat-paid-members');
            if (oldPaid) oldPaid.textContent = stats.paid_members_count || 0;
            const oldDue = document.getElementById('stat-due-amount');
            if (oldDue) oldDue.textContent = '₹' + Number(stats.total_due_amount || 0).toLocaleString('en-IN');

            // Figma executive metric cards
            const dashDisbursements = document.getElementById('dash-disbursements');
            if (dashDisbursements) dashDisbursements.textContent = '₹' + (stats.total_disbursements_formatted || '0');
            const dashActiveMembers = document.getElementById('dash-active-members');
            if (dashActiveMembers) dashActiveMembers.textContent = stats.active_members_count ?? (stats.total_members || 0);
            const dashTotalCollections = document.getElementById('dash-total-collections');
            if (dashTotalCollections) dashTotalCollections.textContent = '₹' + (stats.total_collections_formatted || '0');
            const dashKycCompliance = document.getElementById('dash-kyc-compliance');
            if (dashKycCompliance) dashKycCompliance.textContent = (stats.kyc_compliance_rate || 0) + '%';

            // Populate recent transactions on dashboard
            const dashTxTbody = document.getElementById('dashboard-transactions-tbody');
            if (dashTxTbody) {
                dashTxTbody.innerHTML = '';
                if (stats.recent_transactions && stats.recent_transactions.length > 0) {
                    stats.recent_transactions.forEach(tx => {
                        let badgeClass = 'badge-success';
                        if (tx.status.toLowerCase() === 'pending') badgeClass = 'badge-pending';
                        if (tx.status.toLowerCase() === 'failed') badgeClass = 'badge-failed';
                        
                        dashTxTbody.innerHTML += `
                            <tr>
                                <td>
                                    <div class="user-avatar-group">
                                        <div style="width:30px; height:30px; border-radius:50%; background:var(--primary-light); color:var(--primary); font-weight:700; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">
                                            ${(tx.name || 'U').charAt(0).toUpperCase()}
                                        </div>
                                        <span class="user-detail-name">${tx.name}</span>
                                    </div>
                                </td>
                                <td>${tx.reference_id}</td>
                                <td>${tx.type}</td>
                                <td class="font-semibold">₹${tx.amount}</td>
                                <td><span class="badge ${badgeClass}">${tx.status}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    dashTxTbody.innerHTML = '<tr><td colspan="5" class="text-center" style="padding: 24px; color: var(--text-muted);">No recent transactions recorded yet.</td></tr>';
                }
            }

            // Populate member distribution text
            const distTotalVal = document.getElementById('dist-total-val');
            if (distTotalVal && stats.member_distribution) {
                distTotalVal.textContent = stats.member_distribution.total_count ?? stats.total_members ?? 0;
            }
            const distActivePct = document.getElementById('dist-pct-active');
            if (distActivePct && stats.member_distribution) {
                distActivePct.textContent = (stats.member_distribution.active || 0) + '%';
            }
            const distPendingPct = document.getElementById('dist-pct-pending');
            if (distPendingPct && stats.member_distribution) {
                distPendingPct.textContent = (stats.member_distribution.pending || 0) + '%';
            }

            // Populate recent activity list
            const activityList = document.getElementById('dashboard-activity-list');
            if (activityList) {
                activityList.innerHTML = '';
                if (stats.recent_activity && stats.recent_activity.length > 0) {
                    stats.recent_activity.forEach(act => {
                        activityList.innerHTML += `
                            <div class="activity-item">
                                <div class="activity-icon-box" style="background-color: ${act.bg}; color: ${act.color};">
                                    <i class="${act.icon}"></i>
                                </div>
                                <div class="activity-details">
                                    <div class="activity-title">${act.title}</div>
                                </div>
                                <div class="activity-time">${act.time}</div>
                            </div>
                        `;
                    });
                } else {
                    activityList.innerHTML = '<div style="padding:15px; text-align:center; color:var(--text-muted); font-size:0.85rem;">No recent activities yet.</div>';
                }
            }

            // Populate priority tasks
            const priorityTasksTotal = document.getElementById('priority-tasks-total');
            if (priorityTasksTotal && stats.priority_tasks) {
                priorityTasksTotal.textContent = stats.priority_tasks.total || 0;
            }
            const priorityKycDesc = document.getElementById('priority-kyc-desc');
            if (priorityKycDesc && stats.priority_tasks) {
                priorityKycDesc.textContent = `${stats.priority_tasks.pending_kyc || 0} applications pending document verification.`;
            }
            const priorityOverdueDesc = document.getElementById('priority-overdue-desc');
            if (priorityOverdueDesc && stats.priority_tasks) {
                priorityOverdueDesc.textContent = `${stats.priority_tasks.overdue_accounts || 0} accounts currently overdue.`;
            }

            renderCharts(stats.monthly_trends, stats.member_distribution);
        } catch (err) { console.error(err); }
    }

    async function loadPaidMembersData() {
        try {
            const res = await fetch('/api/admin/dashboard/paid-members', { headers: getHeaders() });
            if(res.status === 401) { logoutBtn.click(); return; }
            const payload = await res.json();
            const data = payload.data || [];
            const tbody = document.getElementById('paid-members-tbody');
            if (!tbody) return;
            tbody.innerHTML = '';
            if(Array.isArray(data) && data.length > 0) {
                data.forEach(m => {
                    tbody.innerHTML += `
                        <tr>
                            <td>#${m.id}</td>
                            <td><strong>${m.name}</strong></td>
                            <td>${m.email}</td>
                            <td class="text-success">₹${Number(m.total_paid || 0).toLocaleString('en-IN')}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center" style="padding: 20px; color: var(--text-muted);">No fully paid members found.</td></tr>';
            }
        } catch(err) { console.error(err); }
    }

    async function loadDueMembersData() {
        try {
            const res = await fetch('/api/admin/dashboard/due-members', { headers: getHeaders() });
            if(res.status === 401) { logoutBtn.click(); return; }
            const payload = await res.json();
            const data = payload.data || [];
            const tbody = document.getElementById('due-members-tbody');
            if (!tbody) return;
            tbody.innerHTML = '';
            if(Array.isArray(data) && data.length > 0) {
                data.forEach(m => {
                    tbody.innerHTML += `
                        <tr>
                            <td>#${m.id}</td>
                            <td><strong>${m.name}</strong></td>
                            <td>${m.email}</td>
                            <td class="text-success">₹${Number(m.total_paid || 0).toLocaleString('en-IN')}</td>
                            <td class="text-danger">₹${Number(m.overdue_amount || 0).toLocaleString('en-IN')}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center" style="padding: 20px; color: var(--text-muted);">No members with overdue payments.</td></tr>';
            }
        } catch(err) { console.error(err); }
    }