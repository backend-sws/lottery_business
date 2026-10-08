document.addEventListener('DOMContentLoaded', () => {
    // ----- NEW FIGMA VIEWS BINDINGS & LOADERS -----
    let collectionsTrendChartInstance = null;
    let collectionsMethodsChartInstance = null;

    // Load KYC page user list dropdown
    async function loadKycData() {
        try {
            const res = await fetch('/api/admin/members', { headers: getHeaders() });
            const data = await res.json();
            const select = document.getElementById('kyc_user_select');
            if (!select) return;

            select.innerHTML = '<option value="">-- Choose Member / Agent --</option>';
            const membersList = Array.isArray(data?.data?.data)
                ? data.data.data
                : (Array.isArray(data?.data)
                    ? data.data
                    : (Array.isArray(data)
                        ? data
                        : []));
            if (membersList.length > 0) {
                membersList.forEach(m => {
                    const roleText = m.roles && m.roles.some(r => r.name === 'agent') ? 'Agent' : 'Member';
                    select.innerHTML += `<option value="${m.id}">${m.name} (${roleText} - #${m.id})</option>`;
                });
            } else {
                select.innerHTML = '<option value="">No members/agents found</option>';
            }

            // Populate the KYC review list queue
            const reviewList = document.getElementById('kyc-review-list');
            if (reviewList) {
                reviewList.innerHTML = '';
                if (membersList.length > 0) {
                    membersList.forEach(m => {
                        const roleText = m.roles && m.roles.some(r => r.name === 'agent') ? 'Agent' : 'Member';
                        
                        // Status & Badges
                        let statusText = 'Pending Uploads';
                        let badgeClass = 'badge-failed';
                        const hasAadhar = !!m.aadhar_card;
                        const hasPan = !!m.pan_card;
                        const hasIdProof = !!m.id_proof;
                        
                        if (hasAadhar && hasPan && hasIdProof) {
                            statusText = 'Verified';
                            badgeClass = 'badge-success';
                        } else if (hasAadhar || hasPan || hasIdProof) {
                            statusText = 'Awaiting Review';
                            badgeClass = 'badge-pending';
                        }

                        // Doc Buttons
                        const aadharBtn = hasAadhar 
                            ? `<button class="btn-secondary" onclick="viewKycDocument(${m.id}, '${m.aadhar_card}')" style="padding: 4px 8px; font-size: 0.7rem; color:#15803d; border-color:#bbf7d0; background:#f0fdf4; margin-right:4px; cursor:pointer;"><i class="fa-solid fa-file-image"></i> Aadhar</button>`
                            : `<span style="font-size: 0.7rem; color:var(--text-muted); border:1px dashed #cbd5e1; padding:4px 8px; border-radius:4px; margin-right:4px;"><i class="fa-solid fa-circle-xmark" style="color:#ef4444; margin-right:3px;"></i> Aadhar</span>`;

                        const panBtn = hasPan 
                            ? `<button class="btn-secondary" onclick="viewKycDocument(${m.id}, '${m.pan_card}')" style="padding: 4px 8px; font-size: 0.7rem; color:#15803d; border-color:#bbf7d0; background:#f0fdf4; margin-right:4px; cursor:pointer;"><i class="fa-solid fa-file-image"></i> PAN</button>`
                            : `<span style="font-size: 0.7rem; color:var(--text-muted); border:1px dashed #cbd5e1; padding:4px 8px; border-radius:4px; margin-right:4px;"><i class="fa-solid fa-circle-xmark" style="color:#ef4444; margin-right:3px;"></i> PAN</span>`;

                        const idProofBtn = hasIdProof 
                            ? `<button class="btn-secondary" onclick="viewKycDocument(${m.id}, '${m.id_proof}')" style="padding: 4px 8px; font-size: 0.7rem; color:#15803d; border-color:#bbf7d0; background:#f0fdf4; margin-right:4px; cursor:pointer;"><i class="fa-solid fa-file-image"></i> ID Proof</button>`
                            : `<span style="font-size: 0.7rem; color:var(--text-muted); border:1px dashed #cbd5e1; padding:4px 8px; border-radius:4px; margin-right:4px;"><i class="fa-solid fa-circle-xmark" style="color:#ef4444; margin-right:3px;"></i> ID Proof</span>`;

                        // Actions
                        let actionsHtml = '';
                        if (hasAadhar || hasPan || hasIdProof) {
                            actionsHtml = `
                                <div style="display:flex; gap:6px; margin-top:4px; width:100%;">
                                    <button class="btn-primary" onclick="approveKyc(${m.id})" style="flex:1; padding:6px; font-size:0.75rem; background:var(--primary); height:auto; border-radius:6px; font-weight:700; cursor:pointer;"><i class="fa-solid fa-check"></i> Approve KYC</button>
                                    <button class="btn-secondary text-danger" onclick="rejectKyc(${m.id})" style="padding:6px 12px; font-size:0.75rem; background:#fee2e2; border-color:#fca5a5; border-radius:6px; font-weight:700; cursor:pointer;"><i class="fa-solid fa-trash"></i> Reject</button>
                                </div>
                            `;
                        }

                        reviewList.innerHTML += `
                            <div style="margin:0; padding:12px; background:#ffffff; border-radius:8px; border:1px solid var(--border-color); display:flex; flex-direction:column; gap:10px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <strong style="font-size:0.85rem; color:#0f172a; display:block;">${m.name}</strong>
                                        <span style="font-size:0.7rem; color:var(--text-muted);">ID: #MEM-${m.id} • ${roleText}</span>
                                    </div>
                                    <span class="badge ${badgeClass}" style="font-size:0.65rem; text-transform:capitalize;">${statusText}</span>
                                </div>
                                <div style="display:flex; gap:4px; align-items:center; flex-wrap:wrap;">
                                    ${aadharBtn}
                                    ${panBtn}
                                    ${idProofBtn}
                                </div>
                                ${actionsHtml}
                            </div>
                        `;
                    });
                } else {
                    reviewList.innerHTML = `<div style="text-align:center; padding:20px; color:var(--text-muted);">No members/agents registered.</div>`;
                }
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Load Collections Overview data & charts
    async function loadCollectionsOverviewData() {
        try {
            // Fetch stats from dashboard API
            const res = await fetch('/api/admin/dashboard', { headers: getHeaders() });
            const payload = await res.json();
            const stats = payload.data;

            // ===== METRIC CARD 1: Total Collected Today =====
            const todayColl = document.getElementById('coll-metric-collected');
            if (todayColl) {
                const formatted = stats.today_collection_formatted || '₹0';
                todayColl.textContent = formatted.startsWith('₹') ? formatted : '₹' + formatted;
            }
            // Yesterday comparison
            const yesterdayEl = document.getElementById('coll-yesterday-change');
            if (yesterdayEl) {
                const pct = stats.yesterday_change_percent || 0;
                const icon = yesterdayEl.querySelector('i');
                const text = yesterdayEl.querySelector('span') || yesterdayEl;
                if (pct >= 0) {
                    if (icon) { icon.className = 'fa-solid fa-arrow-trend-up'; icon.style.color = 'var(--success)'; }
                    text.textContent = '+' + pct + '% vs yesterday';
                } else {
                    if (icon) { icon.className = 'fa-solid fa-arrow-trend-down'; icon.style.color = 'var(--danger, #ef4444)'; }
                    text.textContent = pct + '% vs yesterday';
                }
            }

            // ===== METRIC CARD 2: Active Agents =====
            const actAgents = document.getElementById('coll-metric-agents');
            if (actAgents) actAgents.textContent = stats.active_agents_count || 0;

            // ===== METRIC CARD 3: Collection Success Rate =====
            const successRate = document.getElementById('coll-metric-success');
            if (successRate) {
                const rate = stats.collection_success_rate || 0;
                successRate.textContent = rate + '%';
                // Update badge
                const successBadge = document.getElementById('coll-success-badge');
                if (successBadge) {
                    if (rate >= 80) {
                        successBadge.textContent = 'High';
                        successBadge.className = 'badge badge-success';
                    } else if (rate >= 50) {
                        successBadge.textContent = 'Medium';
                        successBadge.className = 'badge badge-pending';
                    } else {
                        successBadge.textContent = 'Low';
                        successBadge.className = 'badge badge-failed';
                    }
                    successBadge.style.cssText = 'padding: 1px 6px; font-size: 0.6rem; border-radius: 4px; font-weight: 700;';
                }
            }
            
            // ===== METRIC CARD 4: Monthly Target Progress =====
            const targetProg = document.getElementById('coll-metric-progress');
            if (targetProg) {
                const progValue = stats.monthly_target_progress || 0;
                targetProg.textContent = progValue + '%';
                // Find progress bar within the card
                const card = targetProg.closest('.stat-card');
                if (card) {
                    const progBar = card.querySelector('.progress-bar-fill');
                    if (progBar) progBar.style.width = progValue + '%';
                }
            }
            
            // ===== RECENT COLLECTIONS TABLE =====
            const collRes = await fetch('/api/admin/agents/collections', { headers: getHeaders() });
            const rawColData = await collRes.json();
            const colData = Array.isArray(rawColData.data?.data) 
                ? rawColData.data.data 
                : (Array.isArray(rawColData.data) ? rawColData.data : (Array.isArray(rawColData) ? rawColData : []));
            const totalCount = rawColData.data?.total || colData.length;
            
            const collTbody = document.getElementById('collections-table-tbody');
            if (collTbody) {
                collTbody.innerHTML = '';
                const countElem = document.getElementById('collections-table-count');
                
                if (colData.length > 0) {
                    if (countElem) countElem.textContent = `Showing ${colData.length} of ${totalCount} collections total`;
                    
                    colData.forEach(c => {
                        const agentName = c.agent ? c.agent.name : 'Unknown Agent';
                        const memberName = c.member ? c.member.name : 'Unknown Member';
                        const method = c.details || c.collection_type || 'N/A';
                        const amount = Number(c.amount_collected || 0).toLocaleString('en-IN');
                        const dateStr = c.collected_at || c.created_at;
                        const time = dateStr ? new Date(dateStr).toLocaleTimeString('en-IN', {hour: '2-digit', minute:'2-digit', hour12: true}) : '--';
                        const dateFormatted = dateStr ? new Date(dateStr).toLocaleDateString('en-IN', {day: '2-digit', month: 'short'}) : '';
                        
                        // Status badge
                        let statusBadge = '';
                        if (c.status === 'approved') {
                            statusBadge = `<span class="badge badge-success" style="padding:3px 8px; font-size:0.7rem; text-transform:capitalize;"><i class="fa-solid fa-circle-check" style="font-size:0.65rem; margin-right:3px;"></i>Approved</span>`;
                        } else if (c.status === 'pending') {
                            statusBadge = `<span class="badge badge-pending" style="padding:3px 8px; font-size:0.7rem; text-transform:capitalize;"><i class="fa-solid fa-clock" style="font-size:0.65rem; margin-right:3px;"></i>Pending</span>`;
                        } else if (c.status === 'rejected') {
                            statusBadge = `<span class="badge badge-failed" style="padding:3px 8px; font-size:0.7rem; text-transform:capitalize;"><i class="fa-solid fa-circle-xmark" style="font-size:0.65rem; margin-right:3px;"></i>Rejected</span>`;
                        } else {
                            statusBadge = `<span class="badge badge-neutral" style="padding:3px 8px; font-size:0.7rem; text-transform:capitalize;">${c.status || 'N/A'}</span>`;
                        }

                        // Action buttons with Delete
                        let actionHtml = '';
                        if (c.status === 'pending') {
                            actionHtml = `
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <button class="btn-primary" style="padding:4px 9px; font-size:0.75rem; background-color: var(--accent); border-radius:5px; cursor:pointer;" onclick="approveCollectionFromOverview(${c.id})">Approve</button>
                                    <button class="btn-secondary" style="padding:4px 9px; font-size:0.75rem; background:#fee2e2; border:1px solid #fecaca; color:#dc2626; border-radius:5px; cursor:pointer;" onclick="rejectCollectionFromOverview(${c.id})">Reject</button>
                                    <button class="btn-secondary text-danger" style="padding:4px 8px; font-size:0.75rem; background:#fef2f2; border:1px solid #fca5a5; color:#ef4444; border-radius:5px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" onclick="deleteCollectionFromOverview(${c.id})" title="Delete Collection">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </button>
                                </div>
                            `;
                        } else {
                            actionHtml = `
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <button class="btn-secondary text-danger" style="padding:4px 9px; font-size:0.75rem; background:#fef2f2; border:1px solid #fca5a5; color:#ef4444; border-radius:5px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" onclick="deleteCollectionFromOverview(${c.id})" title="Delete Collection">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </button>
                                </div>
                            `;
                        }

                        // Method badge color
                        let methodBadge = 'badge-neutral';
                        const methodLower = method.toLowerCase();
                        if (methodLower.includes('upi') || methodLower.includes('digital') || methodLower.includes('wallet')) {
                            methodBadge = 'badge-success';
                        } else if (methodLower.includes('cash')) {
                            methodBadge = 'badge-pending';
                        }

                        collTbody.innerHTML += `
                            <tr>
                                <td>
                                    <div class="user-avatar-group">
                                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(agentName)}&background=004d40&color=fff&size=32&rounded=true&bold=true" alt="Avatar" style="width:32px;height:32px;border-radius:50%;">
                                        <span class="user-detail-name">${agentName}</span>
                                    </div>
                                </td>
                                <td>${memberName}</td>
                                <td class="font-semibold">₹${amount}</td>
                                <td><span class="badge ${methodBadge}" style="text-transform:capitalize;">${method}</span></td>
                                <td><span style="font-size:0.8rem;">${dateFormatted}</span> <span style="font-size:0.7rem;color:var(--text-muted);">${time}</span></td>
                                <td>${statusBadge}</td>
                                <td>${actionHtml}</td>
                            </tr>
                        `;
                    });
                } else {
                    if (countElem) countElem.textContent = 'No collections found';
                    collTbody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align:center; padding:40px 20px; color:var(--text-muted);">
                                <i class="fa-solid fa-inbox" style="font-size:2rem; margin-bottom:8px; display:block; opacity:0.4;"></i>
                                No collection records found yet. Collections will appear here once agents start collecting.
                            </td>
                        </tr>`;
                }
            }

            // ===== PAGINATION =====
            const paginationContainer = document.getElementById('collections-pagination');
            if (paginationContainer && rawColData.data) {
                const currentPage = rawColData.data.current_page || 1;
                const lastPage = rawColData.data.last_page || 1;
                const prevBtn = paginationContainer.querySelector('.pagination-prev');
                const nextBtn = paginationContainer.querySelector('.pagination-next');
                const pageInfo = paginationContainer.querySelector('.pagination-info');
                if (pageInfo) pageInfo.textContent = `Page ${currentPage} of ${lastPage}`;
                if (prevBtn) prevBtn.disabled = currentPage <= 1;
                if (nextBtn) nextBtn.disabled = currentPage >= lastPage;
            }

            // ===== RENDER CHARTS =====
            renderCollectionsCharts(stats.weekly_trends, stats.collection_methods);
        } catch (e) {
            console.error('Collections Overview Error:', e);
        }
    }

    // Render collections overview charts
    function renderCollectionsCharts(weeklyTrends = null, methods = null) {
        const ctxTrend = document.getElementById('collectionsTrendChart');
        if (ctxTrend) {
            let labels = [];
            let dataVals = [];
            
            if (weeklyTrends && Array.isArray(weeklyTrends) && weeklyTrends.length > 0) {
                labels = weeklyTrends.map(t => t.date ? (t.day + '\n' + t.date) : t.day);
                dataVals = weeklyTrends.map(t => parseFloat(t.total) || 0);
            }

            const isDarkTheme = document.body.classList.contains('dark-theme');
            const gridColor = isDarkTheme ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.08)';
            const textColor = isDarkTheme ? '#94a3b8' : '#64748b';

            const canvasCtx = ctxTrend.getContext('2d');
            const gradient = canvasCtx.createLinearGradient(0, 0, 0, 250);
            if (isDarkTheme) {
                gradient.addColorStop(0, 'rgba(255, 122, 0, 0.25)'); // Orange gradient
                gradient.addColorStop(1, 'rgba(255, 122, 0, 0.0)');
            } else {
                gradient.addColorStop(0, 'rgba(255, 122, 0, 0.15)'); // Orange gradient
                gradient.addColorStop(1, 'rgba(255, 122, 0, 0.0)');
            }
            
            const strokeColor = '#FF7A00';

            if (collectionsTrendChartInstance) collectionsTrendChartInstance.destroy();
            collectionsTrendChartInstance = new Chart(canvasCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Collections Today',
                        data: dataVals,
                        borderColor: strokeColor,
                        backgroundColor: gradient,
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointBackgroundColor: strokeColor,
                        pointHoverBackgroundColor: strokeColor,
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [5,5], color: gridColor },
                            ticks: { color: textColor }
                        },
                        x: { 
                            grid: { display: false },
                            ticks: { color: textColor }
                        }
                    }
                }
            });
        }

        const ctxMethod = document.getElementById('collectionsMethodsChart');
        if (ctxMethod) {
            let digitalPct = 50, cashPct = 50;
            if (methods) {
                digitalPct = methods.digital || 0;
                cashPct = methods.cash || 0;
            }

            // Update center label dynamically
            const centerLabel = document.getElementById('coll-methods-center-pct');
            if (centerLabel) centerLabel.textContent = digitalPct + '%';
            
            // Update legend values dynamically
            const legendDigital = document.getElementById('coll-methods-digital-pct');
            if (legendDigital) legendDigital.textContent = digitalPct + '%';
            const legendCash = document.getElementById('coll-methods-cash-pct');
            if (legendCash) legendCash.textContent = cashPct + '%';

            if (collectionsMethodsChartInstance) collectionsMethodsChartInstance.destroy();
            collectionsMethodsChartInstance = new Chart(ctxMethod.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['UPI & Wallet', 'Cash'],
                    datasets: [{
                        data: [digitalPct, cashPct],
                        backgroundColor: ['#004d40', '#0ea5e9'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: { legend: { display: false } }
                }
            });
        }
    }

    // Load settings page mock
    function loadSettingsData() {
        console.log("Settings view loaded");
    }

    // Load agents database list
    async function loadAgentsList() {
        try {
            const res = await fetch('/api/admin/members?role=agent', { headers: getHeaders() });
            const data = await res.json();
            const tbody = document.getElementById('agents-table-tbody');
            if (!tbody) return;

            tbody.innerHTML = '';
            const membersList = Array.isArray(data?.data?.data)
                ? data.data.data
                : (Array.isArray(data?.data)
                    ? data.data
                    : (Array.isArray(data)
                        ? data
                        : []));
            const agents = membersList;

            // Calculate metrics dynamically
            let totalCollections = 0;
            let activeAgentsCount = 0;
            let totalTargetProgress = 0;
            
            agents.forEach(a => {
                const sumString = a.today_collection ? a.today_collection.replace(/[₹,]/g, '') : '0';
                const todaySum = parseFloat(sumString) || 0;
                if (todaySum > 0) {
                    activeAgentsCount++;
                }
                totalCollections += todaySum;
                totalTargetProgress += parseFloat(a.target_progress) || 0;
            });

            if (document.getElementById('agent-metric-total')) {
                document.getElementById('agent-metric-total').textContent = agents.length;
            }
            if (document.getElementById('agent-metric-active')) {
                document.getElementById('agent-metric-active').textContent = activeAgentsCount;
            }
            if (document.getElementById('agent-metric-collections')) {
                document.getElementById('agent-metric-collections').textContent = '₹' + totalCollections.toLocaleString('en-IN');
            }

            const avgProgress = agents.length > 0 ? Math.round(totalTargetProgress / agents.length) : 0;
            const perfElem = document.getElementById('agent-metric-performance');
            if (perfElem) {
                perfElem.textContent = avgProgress + '%';
            }
            const perfStatusElem = document.getElementById('agent-performance-status');
            if (perfStatusElem) {
                if (avgProgress >= 80) {
                    perfStatusElem.innerHTML = '<i class="fa-solid fa-star"></i> Outstanding';
                    perfStatusElem.style.color = 'var(--warning)';
                } else if (avgProgress >= 50) {
                    perfStatusElem.innerHTML = '<i class="fa-solid fa-star"></i> Good';
                    perfStatusElem.style.color = 'var(--primary)';
                } else {
                    perfStatusElem.innerHTML = '<i class="fa-solid fa-star"></i> Average';
                    perfStatusElem.style.color = 'var(--text-muted)';
                }
            }

            const countElem = document.getElementById('agents-filter-count');
            if (countElem) {
                countElem.textContent = `Showing ${agents.length} of ${agents.length} agents`;
            }

            if (agents.length > 0) {
                agents.forEach(a => {
                    const todayCollection = a.today_collection || '₹0';
                    const targetProgress = a.target_progress || 0;
                    const statusText = a.status || 'Active';
                    let badgeClass = 'badge-success';
                    if (statusText.toLowerCase() === 'offline') badgeClass = 'badge-neutral';
                    if (statusText.toLowerCase() === 'on leave') badgeClass = 'badge-failed';

                    tbody.innerHTML += `
                        <tr>
                            <td>
                                <div class="user-avatar-group">
                                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(a.name)}&background=004d40&color=fff&size=32&rounded=true&bold=true" alt="Avatar" style="width:32px;height:32px;border-radius:50%;">
                                    <div class="flex-column">
                                        <span class="user-detail-name">${a.name}</span>
                                        <span class="user-detail-sub">${a.email || '--'}</span>
                                    </div>
                                </div>
                            </td>
                            <td>#AGT-${a.id}</td>
                            <td>${a.address || 'All Regions'}</td>
                            <td class="font-semibold">${todayCollection}</td>
                            <td>
                                <div class="progress-container">
                                    <div class="progress-bar-bg">
                                        <div class="progress-bar-fill" style="width: ${targetProgress}%;"></div>
                                    </div>
                                    <span class="text-sm font-semibold">${targetProgress}%</span>
                                </div>
                            </td>
                            <td><span class="badge ${badgeClass}">${statusText}</span></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <button class="btn-secondary" style="padding: 4px 8px; font-size: 0.8rem; font-weight:600; color:var(--primary); background:#f8fafc; border:1px solid var(--border-color); border-radius:6px; cursor:pointer; display:inline-flex; align-items:center; gap:4px;" onclick="openAgentManageModal(${a.id}, '${a.name.replace(/'/g, "\\'")}')">
                                        <i class="fa-solid fa-gear"></i> Manage
                                    </button>
                                    <button class="btn-secondary text-danger" onclick="deleteAgent(${a.id}, '${a.name.replace(/'/g, "\\'")}')" style="padding: 4px 8px; font-size: 0.8rem; font-weight: 600; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Delete Agent">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No agents registered in system.</td></tr>';
            }

            // Pagination setup
            const paginationContainer = document.getElementById('agents-pagination');
            if (paginationContainer && data.data) {
                const currentPage = data.data.current_page || 1;
                const lastPage = data.data.last_page || 1;
                const prevBtn = paginationContainer.querySelector('.pagination-prev');
                const nextBtn = paginationContainer.querySelector('.pagination-next');
                const pageInfo = paginationContainer.querySelector('.pagination-info');
                if (pageInfo) pageInfo.textContent = `Page ${currentPage} of ${lastPage}`;
                if (prevBtn) prevBtn.disabled = currentPage <= 1;
                if (nextBtn) nextBtn.disabled = currentPage >= lastPage;
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Approve & Reject from overview
    window.approveCollectionFromOverview = async function(id) {
        if (!confirm('Are you sure you want to approve this collection?')) return;
        try {
            const res = await fetch(`/api/admin/agents/collections/${id}/approve`, {
                method: 'POST',
                headers: getHeaders()
            });
            const data = await res.json();
            if (data.status) {
                alert('Collection approved successfully!');
                loadCollectionsOverviewData();
            } else {
                alert(data.message || 'Approval failed');
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred');
        }
    };

    window.rejectCollectionFromOverview = async function(id) {
        if (!confirm('Are you sure you want to reject this collection?')) return;
        try {
            const res = await fetch(`/api/admin/agents/collections/${id}/reject`, {
                method: 'POST',
                headers: getHeaders()
            });
            const data = await res.json();
            if (data.status) {
                alert('Collection rejected successfully!');
                loadCollectionsOverviewData();
            } else {
                alert(data.message || 'Rejection failed');
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred');
        }
    };

    window.deleteCollectionFromOverview = async function(id) {
        if (!confirm('Are you sure you want to delete this collection record? This action cannot be undone.')) return;
        try {
            const res = await fetch(`/api/admin/agents/collections/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });
            const data = await res.json();
            if (data.status) {
                alert(data.message || 'Collection deleted successfully!');
                if (typeof loadCollectionsOverviewData === 'function') {
                    loadCollectionsOverviewData();
                }
                if (typeof loadAgentsView === 'function') {
                    loadAgentsView();
                }
            } else {
                alert(data.message || 'Failed to delete collection');
            }
        } catch (e) {
            console.error(e);
            alert('An error occurred: ' + e.message);
        }
    };

    // Override loadPayoutsData to support both layouts
    const originalLoadPayoutsData = window.loadPayoutsData || function(){};
    window.loadPayoutsData = async function() {
        try {
            await originalLoadPayoutsData();
            // Duplicate to payments-payouts-tbody if exists
            const origBody = document.getElementById('payouts-tbody');
            const destBody = document.getElementById('payments-payouts-tbody');
            if (origBody && destBody) {
                destBody.innerHTML = origBody.innerHTML;
            }
        } catch (e) {
            console.error(e);
        }
    };

    // KYC Approve and Reject actions
    window.approveKyc = async function(id) {
        try {
            const res = await fetch(`/api/admin/kyc/${id}/approve`, {
                method: 'POST',
                headers: getHeaders()
            });
            const data = await res.json();
            alert(data.message || 'KYC Approved successfully.');
            loadKycData();
        } catch (e) {
            console.error(e);
            alert('Failed: ' + e.message);
        }
    };

    window.rejectKyc = async function(id) {
        if (!confirm('Are you sure you want to reject and clear these KYC documents?')) return;
        try {
            const res = await fetch(`/api/admin/kyc/${id}/reject`, {
                method: 'POST',
                headers: getHeaders()
            });
            const data = await res.json();
            alert(data.message || 'KYC Documents rejected and cleared.');
            loadKycData();
        } catch (e) {
            console.error(e);
            alert('Failed: ' + e.message);
        }
    };

    window.loadKycData = loadKycData;
    window.loadCollectionsOverviewData = loadCollectionsOverviewData;
    window.loadAgentsList = loadAgentsList;
});
