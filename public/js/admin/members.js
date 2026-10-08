(function() {
    // Hold filters and state in local scope
    let currentPage = 1;
    let searchVal = '';
    let communityVal = '';
    let statusVal = '';

    // Load Members Data
    window.loadMembersData = async function(page = 1) {
        currentPage = page;
        const tbody = document.getElementById('members-table-tbody');
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 20px;">
                    <i class="fa-solid fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading community members...
                </td>
            </tr>
        `;

        try {
            // Get filter values
            const searchInput = document.getElementById('member-search-input');
            const commFilter = document.getElementById('member-community-filter');
            const statusFilter = document.getElementById('member-status-filter');

            searchVal = searchInput ? searchInput.value.trim() : '';
            communityVal = commFilter ? commFilter.value : '';
            statusVal = statusFilter ? statusFilter.value : '';

            // Build query params
            const queryParams = new URLSearchParams({
                page: currentPage,
                search: searchVal,
                community: communityVal,
                status: statusVal,
                role: 'member'
            });

            const res = await fetch(`/api/admin/members?${queryParams.toString()}`, { headers: getHeaders() });
            if (res.status === 401) {
                const logoutBtn = document.getElementById('logout-btn');
                if (logoutBtn) logoutBtn.click();
                return;
            }

            const data = await res.json();
            const paginatedData = data.data;
            const membersList = Array.isArray(paginatedData?.data) ? paginatedData.data : [];

            tbody.innerHTML = '';

            if (membersList.length > 0) {
                membersList.forEach(m => {
                    // Compute status
                    const status = getMemberStatus(m);
                    let badgeClass = 'badge-neutral';
                    if (status === 'active') badgeClass = 'badge-success';
                    else if (status === 'pending') badgeClass = 'badge-pending';
                    else if (status === 'inactive') badgeClass = 'badge-failed';

                    // Formatting investment
                    const totalInv = m.total_investment ? parseFloat(m.total_investment) : 0;
                    const formattedInvestment = `₹${totalInv.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;

                    // Committee Names
                    const committeeNames = m.committees && m.committees.length > 0
                        ? m.committees.map(c => c.name).join(', ')
                        : '<span style="color:var(--text-muted);">None</span>';

                    // Avatar HTML
                    let avatarHtml = `<div style="display:flex; width: 32px; height: 32px; align-items:center; justify-content:center; border-radius: 6px; background-color: var(--primary-light); color: var(--primary); flex-shrink: 0; font-weight: 700;">
                        ${m.name.charAt(0).toUpperCase()}
                    </div>`;

                    if (m.photo) {
                        const filenamePath = m.photo.startsWith('kyc/') ? m.photo.substring(4) : m.photo;
                        avatarHtml = `<img src="/api/profile-photo/${filenamePath}" style="width: 32px; height: 32px; border-radius: 6px; object-fit: cover; flex-shrink: 0;" onerror="this.outerHTML='<div style=\\\'display:flex; width: 32px; height: 32px; align-items:center; justify-content:center; border-radius: 6px; background-color: var(--primary-light); color: var(--primary); flex-shrink: 0; font-weight: 700;\\\'>${m.name.charAt(0).toUpperCase()}</div>'"/>`;
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>
                                <div class="user-avatar-group">
                                    ${avatarHtml}
                                    <div class="flex-column" style="gap: 1px;">
                                        <span class="user-detail-name">${m.name}</span>
                                        <span class="user-detail-sub">${m.email}</span>
                                    </div>
                                </div>
                            </td>
                            <td>#MEM-${m.id}</td>
                            <td>${committeeNames}</td>
                            <td class="font-semibold">${formattedInvestment}</td>
                            <td><span class="badge ${badgeClass}" style="text-transform: capitalize;">${status}</span></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <button class="btn-secondary" onclick="viewMemberDetails(${m.id})" style="padding: 4px 8px; font-size: 0.8rem; font-weight: 600; color: var(--primary); background: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-eye"></i> View
                                    </button>
                                    <button class="btn-secondary text-danger" onclick="deleteMember(${m.id}, '${m.name.replace(/'/g, "\\'")}')" style="padding: 4px 8px; font-size: 0.8rem; font-weight: 600; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Delete Member">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });

                // Update pagination controls
                updatePagination(paginatedData);
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 20px; color: var(--text-muted);">No members match the filter criteria.</td></tr>';
                document.getElementById('members-pagination-text').textContent = 'Showing 0 to 0 of 0 members';
                document.getElementById('members-pagination-controls').innerHTML = '';
            }

            // Also reload metric counters
            loadMemberMetrics();

        } catch (err) {
            console.error("Error loading members:", err);
            tbody.innerHTML = `<tr><td colspan="6" class="text-danger text-center" style="padding: 20px;">Error loading members: ${err.message}</td></tr>`;
        }
    };

    // Calculate Member Status consistent with server-side logic
    function getMemberStatus(m) {
        const hasActiveCommittees = m.committees && m.committees.some(c => c.status === 'active');
        const hasActiveLoans = m.loans && m.loans.some(l => l.status === 'active');

        if (hasActiveCommittees || hasActiveLoans) {
            return 'active';
        }

        if (!m.aadhar_card || !m.pan_card || !m.id_proof) {
            return 'pending';
        }

        return 'inactive';
    }

    // Dynamic Member Metrics Loader
    async function loadMemberMetrics() {
        try {
            // Get total count
            const totalRes = await fetch('/api/admin/members?role=member', { headers: getHeaders() });
            const totalJson = await totalRes.json();
            const totalCount = totalJson.data.total || 0;
            const totalMetric = document.getElementById('members-metric-total');
            if (totalMetric) totalMetric.textContent = totalCount.toLocaleString();

            // Get active count
            const activeRes = await fetch('/api/admin/members?status=active&role=member', { headers: getHeaders() });
            const activeJson = await activeRes.json();
            const activeCount = activeJson.data.total || 0;
            const activeMetric = document.getElementById('members-metric-active');
            if (activeMetric) activeMetric.textContent = activeCount.toLocaleString();

            // Get pending count
            const pendingRes = await fetch('/api/admin/members?status=pending&role=member', { headers: getHeaders() });
            const pendingJson = await pendingRes.json();
            const pendingCount = pendingJson.data.total || 0;
            const pendingMetric = document.getElementById('members-metric-pending');
            if (pendingMetric) pendingMetric.textContent = pendingCount.toLocaleString();

            // Get total assets (Collections)
            const dashRes = await fetch('/api/admin/dashboard', { headers: getHeaders() });
            const dashJson = await dashRes.json();
            const assets = dashJson.data.total_collections_formatted || '0';
            const assetsMetric = document.getElementById('members-metric-assets');
            if (assetsMetric) assetsMetric.textContent = '₹' + assets;

            // Dynamically populate community filter with real committees
            const commFilter = document.getElementById('member-community-filter');
            if (commFilter && commFilter.options.length <= 1) {
                const commRes = await fetch('/api/admin/committees', { headers: getHeaders() });
                const commData = await commRes.json();
                const committees = Array.isArray(commData?.data?.data)
                    ? commData.data.data
                    : (Array.isArray(commData?.data) ? commData.data : []);
                
                committees.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.name;
                    opt.textContent = c.name;
                    commFilter.appendChild(opt);
                });
            }

        } catch (e) {
            console.error("Error loading metrics in members:", e);
        }
    }

    // Update Pagination UI
    function updatePagination(paginatedData) {
        const textEl = document.getElementById('members-pagination-text');
        const controlsEl = document.getElementById('members-pagination-controls');
        if (!textEl || !controlsEl) return;

        const from = paginatedData.from || 0;
        const to = paginatedData.to || 0;
        const total = paginatedData.total || 0;
        textEl.textContent = `Showing ${from} to ${to} of ${total} members`;

        controlsEl.innerHTML = '';
        const currentPage = paginatedData.current_page;
        const lastPage = paginatedData.last_page;

        if (lastPage <= 1) return;

        // Prev Button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'btn-secondary';
        prevBtn.style.padding = '6px 12px';
        prevBtn.style.fontSize = '0.8rem';
        prevBtn.style.borderRadius = '6px';
        prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => loadMembersData(currentPage - 1);
        controlsEl.appendChild(prevBtn);

        // Page Numbers
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(lastPage, currentPage + 2);

        if (startPage > 1) {
            const firstBtn = document.createElement('button');
            firstBtn.className = 'btn-secondary';
            firstBtn.style.padding = '6px 12px';
            firstBtn.style.fontSize = '0.8rem';
            firstBtn.style.borderRadius = '6px';
            firstBtn.textContent = '1';
            firstBtn.onclick = () => loadMembersData(1);
            controlsEl.appendChild(firstBtn);

            if (startPage > 2) {
                const dots = document.createElement('span');
                dots.style.alignSelf = 'center';
                dots.style.color = 'var(--text-muted)';
                dots.style.padding = '0 4px';
                dots.textContent = '...';
                controlsEl.appendChild(dots);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = i === currentPage ? 'btn-primary' : 'btn-secondary';
            pageBtn.style.padding = '6px 12px';
            pageBtn.style.fontSize = '0.8rem';
            pageBtn.style.borderRadius = '6px';
            if (i === currentPage) {
                pageBtn.style.backgroundColor = 'var(--primary)';
            }
            pageBtn.textContent = i;
            pageBtn.onclick = () => loadMembersData(i);
            controlsEl.appendChild(pageBtn);
        }

        if (endPage < lastPage) {
            if (endPage < lastPage - 1) {
                const dots = document.createElement('span');
                dots.style.alignSelf = 'center';
                dots.style.color = 'var(--text-muted)';
                dots.style.padding = '0 4px';
                dots.textContent = '...';
                controlsEl.appendChild(dots);
            }

            const lastBtn = document.createElement('button');
            lastBtn.className = 'btn-secondary';
            lastBtn.style.padding = '6px 12px';
            lastBtn.style.fontSize = '0.8rem';
            lastBtn.style.borderRadius = '6px';
            lastBtn.textContent = lastPage;
            lastBtn.onclick = () => loadMembersData(lastPage);
            controlsEl.appendChild(lastBtn);
        }

        // Next Button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'btn-secondary';
        nextBtn.style.padding = '6px 12px';
        nextBtn.style.fontSize = '0.8rem';
        nextBtn.style.borderRadius = '6px';
        nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
        nextBtn.disabled = currentPage === lastPage;
        nextBtn.onclick = () => loadMembersData(currentPage + 1);
        controlsEl.appendChild(nextBtn);
    }

    // View Member Details Modal
    window.viewMemberDetails = async function(id) {
        document.getElementById('modal-title').textContent = 'Loading Member Details...';
        document.getElementById('modal-body').innerHTML = `
            <div style="text-align:center; padding: 40px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 15px;">
                <i class="fa-solid fa-spinner fa-spin fa-2x" style="color:var(--primary);"></i>
                <span style="font-size:0.9rem; color:var(--text-muted);">Fetching complete profile & accounts registry...</span>
            </div>
        `;
        document.getElementById('global-modal').style.display = 'flex';

        try {
            const res = await fetch(`/api/admin/members/${id}`, { headers: getHeaders() });
            const payload = await res.json();
            const m = payload.data;

            document.getElementById('modal-title').textContent = `Member Profile: ${m.name} (#MEM-${m.id})`;

            let modalAvatarHtml = `<div style="width:60px; height:60px; border-radius:50%; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.6rem; font-weight:700; border:2px solid var(--primary); flex-shrink:0;">
                ${m.name.charAt(0).toUpperCase()}
            </div>`;

            if (m.photo) {
                const filenamePath = m.photo.startsWith('kyc/') ? m.photo.substring(4) : m.photo;
                modalAvatarHtml = `<img src="/api/profile-photo/${filenamePath}" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); flex-shrink: 0;" onerror="this.outerHTML='<div style=\\\'width:60px; height:60px; border-radius:50%; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.6rem; font-weight:700; border:2px solid var(--primary); flex-shrink:0;\\\'>${m.name.charAt(0).toUpperCase()}</div>'"/>`;
            }

            // Generate modal body
            document.getElementById('modal-body').innerHTML = `
                <div class="member-details-container" style="display:flex; flex-direction:column; gap:20px; color:#1e293b; max-height: 75vh; overflow-y: auto; padding-right: 5px;">
                    <!-- Profile & Actions Header -->
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #e2e8f0; padding-bottom:15px; flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; align-items:center; gap:15px;">
                            ${modalAvatarHtml}
                            <div>
                                <h4 style="font-size:1.15rem; font-weight:700; margin:0; color:#0f172a;">${m.name}</h4>
                                <p style="font-size:0.8rem; color:var(--text-muted); margin:4px 0 0 0;">Role: <span class="badge" style="background:#e0f2fe; color:#0369a1; text-transform:uppercase; font-size:0.65rem; font-weight:700; padding:2px 6px;">${m.roles && m.roles[0] ? m.roles[0].name : 'member'}</span></p>
                            </div>
                        </div>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <button class="btn-primary" onclick="impersonateMember(${m.id})" style="padding: 8px 14px; font-size:0.8rem; border-radius:6px;"><i class="fa-solid fa-user-ninja"></i> Login as User</button>
                            <button class="btn-secondary" onclick="openChangePasswordModal(${m.id}, '${m.name.replace(/'/g, "\\'")}')" style="padding: 8px 14px; font-size:0.8rem; border-radius:6px;"><i class="fa-solid fa-key"></i> Reset Password</button>
                            <button class="btn-secondary text-danger" onclick="deleteMember(${m.id}, '${m.name.replace(/'/g, "\\'")}')" style="padding: 8px 14px; font-size:0.8rem; border-radius:6px; background:#fef2f2; border:1px solid #fecaca; color:#ef4444;"><i class="fa-solid fa-trash-can"></i> Delete Member</button>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div style="display:grid; grid-template-columns: 1.2fr 1fr; gap:20px;">
                        <!-- Left: Info & Bank Details -->
                        <div style="display:flex; flex-direction:column; gap:20px;">
                            <!-- Info -->
                            <div class="panel-card" style="margin:0; padding:15px; border-radius:8px; border:1px solid var(--border-color); background:#fff;">
                                <h5 style="margin:0 0 12px 0; font-size:0.85rem; font-weight:700; color:var(--primary); text-transform: uppercase; border-bottom:1px solid #f1f5f9; padding-bottom:6px; letter-spacing:0.03em;"><i class="fa-solid fa-address-book" style="margin-right:6px;"></i> Contact Details</h5>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:0.8rem;">
                                    <div><span style="color:var(--text-muted); font-size:0.75rem;">Email Address</span><strong style="display:block; margin-top:2px; word-break:break-all; color:#334155;">${m.email}</strong></div>
                                    <div><span style="color:var(--text-muted); font-size:0.75rem;">Phone Number</span><strong style="display:block; margin-top:2px; color:#334155;">${m.phone || 'N/A'}</strong></div>
                                    <div style="grid-column: span 2;"><span style="color:var(--text-muted); font-size:0.75rem;">Residential Address</span><strong style="display:block; margin-top:2px; color:#334155;">${m.address || 'N/A'}</strong></div>
                                </div>
                            </div>

                            <!-- Bank details -->
                            <div class="panel-card" style="margin:0; padding:15px; border-radius:8px; border:1px solid var(--border-color); background:#fff;">
                                <h5 style="margin:0 0 12px 0; font-size:0.85rem; font-weight:700; color:var(--primary); text-transform: uppercase; border-bottom:1px solid #f1f5f9; padding-bottom:6px; letter-spacing:0.03em;"><i class="fa-solid fa-building-columns" style="margin-right:6px;"></i> Bank Account Info</h5>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:0.8rem;">
                                    <div><span style="color:var(--text-muted); font-size:0.75rem;">Bank Name</span><strong style="display:block; margin-top:2px; color:#334155;">${m.bank_name || 'N/A'}</strong></div>
                                    <div><span style="color:var(--text-muted); font-size:0.75rem;">Account Number</span><strong style="display:block; margin-top:2px; color:#334155;">${m.bank_account_number || 'N/A'}</strong></div>
                                    <div><span style="color:var(--text-muted); font-size:0.75rem;">IFSC Code</span><strong style="display:block; margin-top:2px; color:#334155;">${m.bank_ifsc || 'N/A'}</strong></div>
                                    <div><span style="color:var(--text-muted); font-size:0.75rem;">Account Type</span><strong style="display:block; margin-top:2px; color:#334155;">${m.bank_account_type || 'N/A'}</strong></div>
                                </div>
                            </div>
                        </div>

                        <!-- Right: KYC Registry Documents -->
                        <div class="panel-card" style="margin:0; padding:15px; border-radius:8px; border:1px solid var(--border-color); background:#fff; display:flex; flex-direction:column; gap:12px;">
                            <h5 style="margin:0; font-size:0.85rem; font-weight:700; color:var(--primary); text-transform: uppercase; border-bottom:1px solid #f1f5f9; padding-bottom:6px; letter-spacing:0.03em;"><i class="fa-solid fa-id-card" style="margin-right:6px;"></i> KYC Documents</h5>
                            <div style="display:flex; flex-direction:column; gap:10px; font-size:0.8rem;">
                                ${renderDocItem('Profile Photo', m.photo, m.id)}
                                ${renderDocItem('Aadhar Card (UIDAI)', m.aadhar_card, m.id)}
                                ${renderDocItem('PAN Card (IT Dept)', m.pan_card, m.id)}
                                ${renderDocItem('ID Proof Scan', m.id_proof, m.id)}
                            </div>
                        </div>
                    </div>

                    <!-- Enrolled Committees -->
                    <div class="panel-card" style="margin:0; padding:15px; border-radius:8px; border:1px solid var(--border-color); background:#fff;">
                        <h5 style="margin:0 0 12px 0; font-size:0.85rem; font-weight:700; color:var(--primary); text-transform: uppercase; border-bottom:1px solid #f1f5f9; padding-bottom:6px; letter-spacing:0.03em;"><i class="fa-solid fa-users-rectangle" style="margin-right:6px;"></i> Enrolled Committees (${m.committees ? m.committees.length : 0})</h5>
                        <div class="table-responsive">
                            <table class="data-table" style="font-size:0.75rem; margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th>Committee Name</th>
                                        <th>Amount</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${m.committees && m.committees.length > 0 ? m.committees.map(c => `
                                        <tr>
                                            <td><strong>${c.name}</strong></td>
                                            <td>₹${parseFloat(c.amount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                                            <td style="text-transform: capitalize;">${c.payment_frequency}</td>
                                            <td>${c.duration} Months</td>
                                            <td><span class="badge badge-${c.status === 'active' ? 'success' : 'neutral'}">${c.status}</span></td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="5" style="text-align:center; padding:12px; color:var(--text-muted);">Not enrolled in any committee.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Loans -->
                    <div class="panel-card" style="margin:0; padding:15px; border-radius:8px; border:1px solid var(--border-color); background:#fff;">
                        <h5 style="margin:0 0 12px 0; font-size:0.85rem; font-weight:700; color:var(--primary); text-transform: uppercase; border-bottom:1px solid #f1f5f9; padding-bottom:6px; letter-spacing:0.03em;"><i class="fa-solid fa-hand-holding-dollar" style="margin-right:6px;"></i> Loan Accounts (${m.loans ? m.loans.length : 0})</h5>
                        <div class="table-responsive">
                            <table class="data-table" style="font-size:0.75rem; margin-bottom:0;">
                                <thead>
                                    <tr>
                                        <th>Loan ID</th>
                                        <th>Principal Amount</th>
                                        <th>Interest Rate</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${m.loans && m.loans.length > 0 ? m.loans.map(l => `
                                        <tr>
                                            <td>#${l.id}</td>
                                            <td class="font-semibold">₹${parseFloat(l.amount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}</td>
                                            <td>${l.interest_rate}%</td>
                                            <td>${l.duration} Months</td>
                                            <td><span class="badge badge-${l.status === 'active' ? 'success' : (l.status === 'paid' ? 'primary' : 'failed')}">${l.status}</span></td>
                                        </tr>
                                    `).join('') : '<tr><td colspan="5" style="text-align:center; padding:12px; color:var(--text-muted);">No loan records found.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        } catch (err) {
            console.error(err);
            document.getElementById('modal-body').innerHTML = `
                <div style="padding:30px; text-align:center; color:var(--danger);">
                    <i class="fa-solid fa-triangle-exclamation fa-2x" style="margin-bottom:10px;"></i>
                    <p>Failed to load member profile details.</p>
                    <p style="font-size:0.8rem; color:var(--text-muted); margin-top:5px;">${err.message || 'Unknown Error'}</p>
                </div>
            `;
        }
    };

    // Render single document list item
    function renderDocItem(label, filename, userId) {
        if (!filename) {
            return `
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; background:#f8fafc; border-radius:6px; border:1px dashed #cbd5e1;">
                    <div>
                        <span style="font-weight:600; color:#64748b;">${label}</span>
                        <p style="font-size:0.75rem; color:#ef4444; margin:2px 0 0 0;"><i class="fa-solid fa-circle-xmark"></i> Not Uploaded</p>
                    </div>
                </div>
            `;
        }

        return `
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; background:#f0fdf4; border-radius:6px; border:1px solid #bbf7d0;">
                <div style="flex:1; min-width:0; padding-right:8px;">
                    <span style="font-weight:600; color:#166534;">${label}</span>
                    <p style="font-size:0.7rem; color:#15803d; margin:2px 0 0 0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${filename}"><i class="fa-solid fa-circle-check"></i> ${filename}</p>
                </div>
                <button class="btn-secondary" onclick="viewKycDocument(${userId}, '${filename}')" style="padding:4px 8px; font-size:0.7rem; border-color:#bbf7d0; background:#fff; color:#15803d; cursor:pointer; flex-shrink:0;"><i class="fa-solid fa-download"></i> View / Get</button>
            </div>
        `;
    }

    // Secure KYC doc fetcher
    window.viewKycDocument = async function(userId, filename) {
        try {
            const res = await fetch(`/api/documents/kyc/${userId}/${filename}`, {
                headers: getHeaders()
            });
            if (!res.ok) {
                alert('Failed to load document: Unauthorized or not found.');
                return;
            }
            const blob = await res.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } catch(err) {
            console.error("Error downloading document:", err);
            alert("An error occurred while downloading the document.");
        }
    };

    // Load active community options dynamically into filter
    async function loadCommunityFilterOptions() {
        try {
            const res = await fetch('/api/admin/committees', { headers: getHeaders() });
            const data = await res.json();
            const select = document.getElementById('member-community-filter');
            if (!select) return;

            const committees = Array.isArray(data?.data?.data)
                ? data.data.data
                : (Array.isArray(data?.data)
                    ? data.data
                    : (Array.isArray(data)
                        ? data
                        : []));

            select.innerHTML = '<option value="">All Communities</option>';
            if (committees.length > 0) {
                committees.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.name;
                    opt.textContent = c.name;
                    select.appendChild(opt);
                });
            }
        } catch(e) {
            console.error("Error loading community filter options:", e);
        }
    }

    // Impersonate Member
    window.impersonateMember = async function(id) {
        try {
            const res = await fetch(`/api/admin/members/${id}/impersonate`, { headers: getHeaders() });
            const data = await res.json();
            if (res.ok) {
                localStorage.setItem('member_token', data.token);
                window.location.href = data.redirect_url;
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            console.error(err);
            alert('Failed to impersonate member');
        }
    };

    // Change Password Modal & Action
    window.openChangePasswordModal = function(id, name) {
        // Prepare global modal card or change-password layout
        const modalEl = document.getElementById('global-modal');
        const modalTitleEl = document.getElementById('modal-title');
        const modalBodyEl = document.getElementById('modal-body');
        
        modalTitleEl.textContent = 'Change Member Password';
        modalBodyEl.innerHTML = `
            <form id="change-pwd-form" onsubmit="submitChangePassword(event, ${id})">
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Resetting password for: <strong>${name}</strong></p>
                <div class="input-group" style="margin-bottom:12px;">
                    <label>New Password</label>
                    <div class="input-field"><input type="password" id="new-password" required minlength="8" placeholder="At least 8 characters"></div>
                </div>
                <div class="input-group" style="margin-bottom:15px;">
                    <label>Confirm Password</label>
                    <div class="input-field"><input type="password" id="confirm-new-password" required minlength="8" placeholder="Repeat new password"></div>
                </div>
                <button type="submit" class="btn-primary" style="width:100%;">Update Password</button>
            </form>
        `;
        modalEl.style.display = 'flex';
    };

    window.submitChangePassword = async function(e, id) {
        e.preventDefault();
        const pwd = document.getElementById('new-password').value;
        const confirmPwd = document.getElementById('confirm-new-password').value;

        if (pwd !== confirmPwd) {
            alert("Passwords do not match!");
            return;
        }

        try {
            const res = await fetch(`/api/admin/members/${id}/change-password`, {
                method: 'POST',
                headers: getHeaders(),
                body: JSON.stringify({ password: pwd, password_confirmation: confirmPwd })
            });

            if (res.ok) {
                alert("Password changed successfully.");
                closeModal();
            } else {
                const errData = await res.json();
                alert(errData.message || "Failed to change password.");
            }
        } catch (err) {
            console.error(err);
            alert("An error occurred.");
        }
    };

    // Delete Member
    window.deleteMember = async function(id, name) {
        if (!confirm(`Are you sure you want to delete member "${name}"?`)) return;

        try {
            let res = await fetch(`/api/admin/members/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });
            let data = await res.json();

            // Handle member with active financial records
            if (res.status === 422 && data?.data?.has_financials) {
                const forceConfirm = confirm(
                    `This member "${name}" has linked financial records (installments, loans, or payouts).\n\nDo you want to FORCE DELETE this member and permanently clean up their associated records?`
                );
                if (forceConfirm) {
                    res = await fetch(`/api/admin/members/${id}?force=true`, {
                        method: 'DELETE',
                        headers: getHeaders()
                    });
                    data = await res.json();
                } else {
                    return;
                }
            }

            if (res.ok) {
                alert(data.message || 'Member deleted successfully');
                if (typeof closeModal === 'function') {
                    closeModal();
                }
                loadMembersData(currentPage);
            } else {
                alert('Error: ' + (data.message || 'Failed to delete member'));
            }
        } catch (err) {
            console.error("Error deleting member:", err);
            alert("An error occurred while deleting member.");
        }
    };

    // Attach Event Listeners on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('member-search-input');
        const commFilter = document.getElementById('member-community-filter');
        const statusFilter = document.getElementById('member-status-filter');

        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('keyup', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    loadMembersData(1);
                }, 300);
            });
        }

        if (commFilter) {
            commFilter.addEventListener('change', () => {
                loadMembersData(1);
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', () => {
                loadMembersData(1);
            });
        }

        // Initialize community dropdown options
        loadCommunityFilterOptions();
    });

})();