// State management for Installments section
    window.expandedCommGroups = window.expandedCommGroups || new Set();
    window.expandedLoanGroups = window.expandedLoanGroups || new Set();
    window.cachedCommGroups = [];
    window.cachedLoanGroups = [];
    window.currentInstallmentTab = 'committee';

    async function loadInstallmentsData() {
        try {
            // 1. Committee Installments
            const resComm = await window.apiFetch('/api/admin/installments?per_page=500');
            if (!resComm || !resComm.ok) return;
            const dataComm = await resComm.json();
            
            const listComm = Array.isArray(dataComm?.data?.data)
                ? dataComm.data.data
                : (Array.isArray(dataComm?.data)
                    ? dataComm.data
                    : (Array.isArray(dataComm)
                        ? dataComm
                        : []));
            
            // Group Committee Installments by (user_id + committee_id)
            const commMap = new Map();
            listComm.forEach(i => {
                const uId = i.user_id || (i.user ? i.user.id : 'unknown');
                const cId = i.committee_id || (i.committee ? i.committee.id : 'unknown');
                const key = `${uId}_${cId}`;
                
                if (!commMap.has(key)) {
                    commMap.set(key, {
                        key: key,
                        userId: uId,
                        committeeId: cId,
                        userName: i.user ? i.user.name : `Member #${uId}`,
                        userPhone: i.user?.phone || '',
                        committeeName: i.committee ? i.committee.name : `Committee #${cId}`,
                        installmentAmount: parseFloat(i.amount) || 0,
                        installments: [],
                        totalCount: 0,
                        paidCount: 0,
                        pendingCount: 0,
                        totalAmount: 0,
                        paidAmount: 0,
                        pendingAmount: 0
                    });
                }
                
                const group = commMap.get(key);
                group.installments.push(i);
                group.totalCount++;
                const amt = parseFloat(i.amount) || 0;
                group.totalAmount += amt;
                if (i.status === 'paid') {
                    group.paidCount++;
                    group.paidAmount += amt;
                } else {
                    group.pendingCount++;
                    group.pendingAmount += amt;
                }
            });

            window.cachedCommGroups = Array.from(commMap.values());
            window.cachedCommGroups.forEach(g => {
                g.installments.sort((a, b) => {
                    if (a.due_date && b.due_date) {
                        return new Date(a.due_date) - new Date(b.due_date);
                    }
                    return a.id - b.id;
                });
            });

            // 2. Loan Installments
            const resLoan = await window.apiFetch('/api/admin/loan-installments');
            if (resLoan && resLoan.ok) {
                const payloadLoan = await resLoan.json();
                const dataLoan = payloadLoan.data || [];
                
                const loanMap = new Map();
                if (Array.isArray(dataLoan)) {
                    dataLoan.forEach(i => {
                        const loanId = i.loan_id || (i.loan ? i.loan.id : 'unknown');
                        if (!loanMap.has(loanId)) {
                            const uName = (i.loan && i.loan.user) ? i.loan.user.name : 'Unknown User';
                            const uPhone = (i.loan && i.loan.user) ? i.loan.user.phone : '';
                            loanMap.set(loanId, {
                                loanId: loanId,
                                userName: uName,
                                userPhone: uPhone,
                                loanAmount: i.loan ? parseFloat(i.loan.amount) || 0 : 0,
                                installments: [],
                                totalCount: 0,
                                paidCount: 0,
                                pendingCount: 0,
                                totalAmount: 0,
                                paidAmount: 0,
                                pendingAmount: 0
                            });
                        }
                        const group = loanMap.get(loanId);
                        group.installments.push(i);
                        group.totalCount++;
                        const amt = parseFloat(i.total_amount) || 0;
                        group.totalAmount += amt;
                        if (i.status === 'paid') {
                            group.paidCount++;
                            group.paidAmount += amt;
                        } else {
                            group.pendingCount++;
                            group.pendingAmount += amt;
                        }
                    });
                }
                
                window.cachedLoanGroups = Array.from(loanMap.values());
                window.cachedLoanGroups.forEach(g => {
                    g.installments.sort((a, b) => {
                        if (a.due_date && b.due_date) {
                            return new Date(a.due_date) - new Date(b.due_date);
                        }
                        return a.id - b.id;
                    });
                });
            }

            renderInstallmentTables();
        } catch (err) {
            console.error('Installments Load Error:', err);
        }
    }

    window.renderInstallmentTables = function() {
        const searchInput = document.getElementById('installments-search-input');
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const statusFilter = document.getElementById('installments-status-filter')?.value || 'all';

        // 1. Render Committee Groups
        const commTbody = document.getElementById('installments-tbody');
        if (commTbody) {
            let filteredComm = window.cachedCommGroups || [];
            if (query) {
                filteredComm = filteredComm.filter(g => 
                    g.userName.toLowerCase().includes(query) ||
                    g.committeeName.toLowerCase().includes(query) ||
                    (g.userPhone && g.userPhone.toLowerCase().includes(query))
                );
            }
            if (statusFilter === 'pending') {
                filteredComm = filteredComm.filter(g => g.pendingCount > 0);
            } else if (statusFilter === 'completed') {
                filteredComm = filteredComm.filter(g => g.pendingCount === 0 && g.totalCount > 0);
            }

            if (filteredComm.length > 0) {
                let html = '';
                filteredComm.forEach((g, index) => {
                    const isExpanded = window.expandedCommGroups.has(g.key);
                    
                    let statusBadge = '';
                    if (g.totalCount === 0) {
                        statusBadge = `<span style="color:#94a3b8; font-size:0.8rem;">No installments</span>`;
                    } else if (g.pendingCount === 0) {
                        statusBadge = `<span class="badge badge-success" style="font-size:0.75rem;"><i class="fa-solid fa-circle-check"></i> Completed (${g.paidCount}/${g.totalCount})</span>`;
                    } else if (g.paidCount > 0) {
                        statusBadge = `<span style="display:inline-flex; align-items:center; gap:5px; color:#2563eb; background:#eff6ff; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:600;"><i class="fa-solid fa-clock-rotate-left"></i> ${g.paidCount}/${g.totalCount} Paid</span>`;
                    } else {
                        statusBadge = `<span class="badge badge-pending" style="font-size:0.75rem;"><i class="fa-solid fa-circle-pause"></i> Pending (${g.pendingCount} Due)</span>`;
                    }

                    html += `
                        <tr id="comm-group-row-${g.key}" style="transition: background 0.2s;">
                            <td><strong style="color:#64748b;">#${index + 1}</strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #f3e8ff; color: #a855f7; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; flex-shrink: 0;">
                                        ${g.userName.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <strong style="color: #1e293b; font-size: 0.95rem;">${g.userName}</strong>
                                        ${g.userPhone ? `<div style="font-size: 0.75rem; color: #64748b;"><i class="fa-solid fa-phone" style="font-size:9px;"></i> ${g.userPhone}</div>` : ''}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #475569; background: #f8fafc; padding: 4px 10px; border-radius: 6px; border: 1px solid #e2e8f0; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 5px;">
                                    <i class="fa-solid fa-users" style="color: #a855f7;"></i> ${g.committeeName}
                                </span>
                            </td>
                            <td>
                                <span class="text-success" style="font-weight: 600; font-size: 0.95rem;">₹${g.installmentAmount.toLocaleString('en-IN', {minimumFractionDigits:2})}</span>
                            </td>
                            <td>
                                <span style="background: #f1f5f9; color: #334155; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-layer-group" style="font-size:10px;"></i> ${g.totalCount} Total
                                </span>
                            </td>
                            <td>${statusBadge}</td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px; align-items: center;">
                                    <button class="btn-primary" onclick="toggleCommitteeGroup('${g.key}')" style="padding: 6px 12px; font-size: 0.8rem; margin: 0; background: #a855f7; border: none; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid ${isExpanded ? 'fa-chevron-up' : 'fa-chevron-down'}" id="comm-chevron-${g.key}"></i> Installments (${g.totalCount})
                                    </button>
                                    ${g.pendingCount > 0 ? `
                                        <button class="btn-secondary" onclick="openCollectPaymentModal(${g.userId}, ${g.committeeId}, ${g.installmentAmount})" style="padding: 6px 10px; font-size: 0.8rem; margin: 0; color: #10b981; border: 1px solid #10b981; background: rgba(16,185,129,0.08); border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Collect Next Installment">
                                            <i class="fa-solid fa-hand-holding-dollar"></i> Collect
                                        </button>
                                    ` : ''}
                                </div>
                            </td>
                        </tr>
                        <tr id="comm-group-details-${g.key}" style="display: ${isExpanded ? 'table-row' : 'none'}; background: #faf5ff;">
                            <td colspan="7" style="padding: 0;">
                                <div style="padding: 20px; border-top: 1px solid #f3e8ff; border-bottom: 2px solid #e9d5ff;">
                                    <!-- Stat Cards matching Loan section -->
                                    <div class="flex-wrap-gap" style="margin-bottom: 16px;">
                                        <div class="stat-card-info" style="background: rgba(168, 85, 247, 0.1); color: #9333ea; border: 1px solid rgba(168, 85, 247, 0.2);">
                                            <h4 style="margin:0; font-size: 0.8rem; text-transform: uppercase;">Total Installments</h4>
                                            <div style="font-size: 1.5rem; font-weight: bold; margin: 2px 0;">${g.totalCount}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.85;">Total: ₹${g.totalAmount.toLocaleString('en-IN', {minimumFractionDigits:2})}</div>
                                        </div>
                                        <div class="stat-card-success" style="border: 1px solid rgba(16, 185, 129, 0.2);">
                                            <h4 style="margin:0; font-size: 0.8rem; text-transform: uppercase;">Paid Installments</h4>
                                            <div style="font-size: 1.5rem; font-weight: bold; margin: 2px 0;">${g.paidCount}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.85;">Collected: ₹${g.paidAmount.toLocaleString('en-IN', {minimumFractionDigits:2})}</div>
                                        </div>
                                        <div class="stat-card-danger" style="border: 1px solid rgba(244, 63, 94, 0.2);">
                                            <h4 style="margin:0; font-size: 0.8rem; text-transform: uppercase;">Due / Pending</h4>
                                            <div style="font-size: 1.5rem; font-weight: bold; margin: 2px 0;">${g.pendingCount}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.85;">Remaining: ₹${g.pendingAmount.toLocaleString('en-IN', {minimumFractionDigits:2})}</div>
                                        </div>
                                    </div>

                                    <!-- Inner Table -->
                                    <div style="background: white; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                        <table class="data-table" style="font-size: 0.85rem; margin: 0; box-shadow: none;">
                                            <thead style="background: #f8fafc;">
                                                <tr>
                                                    <th style="width: 40px; padding: 10px 14px;">#</th>
                                                    <th style="padding: 10px 14px;">Installment ID</th>
                                                    <th style="padding: 10px 14px;">Due Date</th>
                                                    <th style="padding: 10px 14px;">Amount</th>
                                                    <th style="padding: 10px 14px;">Paid Date</th>
                                                    <th style="padding: 10px 14px;">Status</th>
                                                    <th style="padding: 10px 14px; text-align: right;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${g.installments.map((inst, iIdx) => {
                                                    const isPaid = inst.status === 'paid';
                                                    return `
                                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                                            <td style="padding: 10px 14px; color: #64748b;">${iIdx + 1}</td>
                                                            <td style="padding: 10px 14px; font-weight: 600;">#${inst.id}</td>
                                                            <td style="padding: 10px 14px;">${inst.due_date || 'N/A'}</td>
                                                            <td style="padding: 10px 14px;"><strong class="text-success">₹${parseFloat(inst.amount).toLocaleString('en-IN', {minimumFractionDigits:2})}</strong></td>
                                                            <td style="padding: 10px 14px;">
                                                                ${inst.paid_date ? `<span class="text-success"><i class="fa-solid fa-calendar-check"></i> ${inst.paid_date}</span>` : '<span style="color:#ef4444; font-size:0.8rem;">Not Paid Yet</span>'}
                                                            </td>
                                                            <td style="padding: 10px 14px;">
                                                                ${isPaid ? '<span class="text-success" style="font-weight:600;"><i class="fa-solid fa-circle-check"></i> Paid</span>' : '<span class="text-warning" style="font-weight:600;"><i class="fa-solid fa-clock"></i> Pending</span>'}
                                                            </td>
                                                            <td style="padding: 10px 14px; text-align: right;">
                                                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                                                    ${!isPaid ? `
                                                                        <button class="btn-primary" onclick="openCollectPaymentModal(${g.userId}, ${g.committeeId}, ${inst.amount})" style="padding: 3px 10px; font-size: 0.75rem; background: #10b981; border: none; border-radius: 4px; cursor: pointer;">
                                                                            <i class="fa-solid fa-hand-holding-dollar"></i> Collect
                                                                        </button>
                                                                    ` : `
                                                                        <span style="color: #94a3b8; font-size: 0.75rem;"><i class="fa-solid fa-check"></i> Settled</span>
                                                                    `}
                                                                    <button class="btn-secondary text-danger" onclick="deleteInstallment(${inst.id}, '${g.key}')" style="padding: 3px 8px; font-size: 0.75rem; border: 1px solid #fecaca; background: #fff; color: #ef4444; border-radius: 4px; cursor: pointer;" title="Delete Installment">
                                                                        <i class="fa-solid fa-trash-can"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    `;
                                                }).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                commTbody.innerHTML = html;
            } else {
                commTbody.innerHTML = '<tr><td colspan="7" class="text-center" style="padding:30px; color:#64748b;"><i class="fa-solid fa-inbox" style="font-size:2rem; margin-bottom:8px; display:block; opacity:0.4;"></i>No committee installments matching your search.</td></tr>';
            }
        }

        // 2. Render Loan Groups
        const loanTbody = document.getElementById('loan-installments-tbody');
        if (loanTbody) {
            let filteredLoan = window.cachedLoanGroups || [];
            if (query) {
                filteredLoan = filteredLoan.filter(g =>
                    g.userName.toLowerCase().includes(query) ||
                    String(g.loanId).toLowerCase().includes(query) ||
                    (g.userPhone && g.userPhone.toLowerCase().includes(query))
                );
            }
            if (statusFilter === 'pending') {
                filteredLoan = filteredLoan.filter(g => g.pendingCount > 0);
            } else if (statusFilter === 'completed') {
                filteredLoan = filteredLoan.filter(g => g.pendingCount === 0 && g.totalCount > 0);
            }

            if (filteredLoan.length > 0) {
                let html = '';
                filteredLoan.forEach(g => {
                    const isExpanded = window.expandedLoanGroups.has(g.loanId);

                    let statusBadge = '';
                    if (g.totalCount === 0) {
                        statusBadge = `<span style="color:#94a3b8; font-size:0.8rem;">No EMIs</span>`;
                    } else if (g.pendingCount === 0) {
                        statusBadge = `<span class="badge badge-success" style="font-size:0.75rem;"><i class="fa-solid fa-circle-check"></i> Paid (${g.paidCount}/${g.totalCount})</span>`;
                    } else if (g.paidCount > 0) {
                        statusBadge = `<span style="display:inline-flex; align-items:center; gap:5px; color:#0284c7; background:#f0f9ff; padding:3px 10px; border-radius:12px; font-size:0.75rem; font-weight:600;"><i class="fa-solid fa-clock-rotate-left"></i> ${g.paidCount}/${g.totalCount} Paid</span>`;
                    } else {
                        statusBadge = `<span class="badge badge-pending" style="font-size:0.75rem;"><i class="fa-solid fa-circle-pause"></i> Pending (${g.pendingCount} Due)</span>`;
                    }

                    html += `
                        <tr id="loan-group-row-${g.loanId}">
                            <td><strong>Loan #${g.loanId}</strong></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; flex-shrink: 0;">
                                        ${g.userName.charAt(0).toUpperCase()}
                                    </div>
                                    <div>
                                        <strong style="color: #1e293b; font-size: 0.95rem;">${g.userName}</strong>
                                        ${g.userPhone ? `<div style="font-size: 0.75rem; color: #64748b;"><i class="fa-solid fa-phone" style="font-size:9px;"></i> ${g.userPhone}</div>` : ''}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-success" style="font-weight: 600; font-size: 0.95rem;">₹${g.loanAmount.toLocaleString('en-IN', {minimumFractionDigits:2})}</span>
                            </td>
                            <td>
                                <span style="background: #f1f5f9; color: #334155; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fa-solid fa-layer-group" style="font-size:10px;"></i> ${g.totalCount} EMIs
                                </span>
                            </td>
                            <td>${statusBadge}</td>
                            <td style="text-align: right;">
                                <button class="btn-primary" onclick="toggleLoanGroup(${g.loanId})" style="padding: 6px 12px; font-size: 0.8rem; margin: 0; background: #0ea5e9; border: none; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid ${isExpanded ? 'fa-chevron-up' : 'fa-chevron-down'}" id="loan-chevron-${g.loanId}"></i> EMIs (${g.totalCount})
                                </button>
                            </td>
                        </tr>
                        <tr id="loan-group-details-${g.loanId}" style="display: ${isExpanded ? 'table-row' : 'none'}; background: #f0f9ff;">
                            <td colspan="6" style="padding: 0;">
                                <div style="padding: 20px; border-top: 1px solid #e0f2fe; border-bottom: 2px solid #bae6fd;">
                                    <!-- Stat Cards matching Loan section -->
                                    <div class="flex-wrap-gap" style="margin-bottom: 16px;">
                                        <div class="stat-card-info" style="background: rgba(14, 165, 233, 0.1); color: #0284c7; border: 1px solid rgba(14, 165, 233, 0.2);">
                                            <h4 style="margin:0; font-size: 0.8rem; text-transform: uppercase;">Total EMIs</h4>
                                            <div style="font-size: 1.5rem; font-weight: bold; margin: 2px 0;">${g.totalCount}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.85;">Total: ₹${g.totalAmount.toLocaleString('en-IN', {minimumFractionDigits:2})}</div>
                                        </div>
                                        <div class="stat-card-success" style="border: 1px solid rgba(16, 185, 129, 0.2);">
                                            <h4 style="margin:0; font-size: 0.8rem; text-transform: uppercase;">Paid EMIs</h4>
                                            <div style="font-size: 1.5rem; font-weight: bold; margin: 2px 0;">${g.paidCount}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.85;">Collected: ₹${g.paidAmount.toLocaleString('en-IN', {minimumFractionDigits:2})}</div>
                                        </div>
                                        <div class="stat-card-danger" style="border: 1px solid rgba(244, 63, 94, 0.2);">
                                            <h4 style="margin:0; font-size: 0.8rem; text-transform: uppercase;">Due / Pending</h4>
                                            <div style="font-size: 1.5rem; font-weight: bold; margin: 2px 0;">${g.pendingCount}</div>
                                            <div style="font-size: 0.75rem; opacity: 0.85;">Remaining: ₹${g.pendingAmount.toLocaleString('en-IN', {minimumFractionDigits:2})}</div>
                                        </div>
                                    </div>

                                    <!-- Inner Table -->
                                    <div style="background: white; border-radius: 8px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                                        <table class="data-table" style="font-size: 0.85rem; margin: 0; box-shadow: none;">
                                            <thead style="background: #f8fafc;">
                                                <tr>
                                                    <th style="width: 40px; padding: 10px 14px;">#</th>
                                                    <th style="padding: 10px 14px;">EMI ID</th>
                                                    <th style="padding: 10px 14px;">Due Date</th>
                                                    <th style="padding: 10px 14px;">Principal (₹)</th>
                                                    <th style="padding: 10px 14px;">Interest (₹)</th>
                                                    <th style="padding: 10px 14px;">Total EMI (₹)</th>
                                                    <th style="padding: 10px 14px;">Paid Date</th>
                                                    <th style="padding: 10px 14px;">Status</th>
                                                    <th style="padding: 10px 14px; text-align: right;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                ${g.installments.map((inst, iIdx) => {
                                                    const isPaid = inst.status === 'paid';
                                                    return `
                                                        <tr style="border-bottom: 1px solid #f1f5f9;">
                                                            <td style="padding: 10px 14px; color: #64748b;">${iIdx + 1}</td>
                                                            <td style="padding: 10px 14px; font-weight: 600;">#${inst.id}</td>
                                                            <td style="padding: 10px 14px;">${inst.due_date || 'N/A'}</td>
                                                            <td style="padding: 10px 14px;">₹${parseFloat(inst.principal_component || 0).toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                                            <td style="padding: 10px 14px;">₹${parseFloat(inst.interest_component || 0).toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                                            <td style="padding: 10px 14px;"><strong class="text-success">₹${parseFloat(inst.total_amount).toLocaleString('en-IN', {minimumFractionDigits:2})}</strong></td>
                                                            <td style="padding: 10px 14px;">
                                                                ${inst.paid_date ? `<span class="text-success"><i class="fa-solid fa-calendar-check"></i> ${inst.paid_date}</span>` : '<span style="color:#ef4444; font-size:0.8rem;">Not Paid Yet</span>'}
                                                            </td>
                                                            <td style="padding: 10px 14px;">
                                                                ${isPaid ? '<span class="text-success" style="font-weight:600;"><i class="fa-solid fa-circle-check"></i> Paid</span>' : '<span class="text-warning" style="font-weight:600;"><i class="fa-solid fa-clock"></i> Pending</span>'}
                                                            </td>
                                                            <td style="padding: 10px 14px; text-align: right;">
                                                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                                                    ${!isPaid ? `
                                                                        <button class="btn-primary" onclick="collectLoanInstallmentInTab(${inst.id}, ${g.loanId})" style="padding: 3px 10px; font-size: 0.75rem; background: #0ea5e9; border: none; border-radius: 4px; cursor: pointer;">
                                                                            <i class="fa-solid fa-hand-holding-dollar"></i> Collect
                                                                        </button>
                                                                    ` : `
                                                                        <span style="color: #94a3b8; font-size: 0.75rem;"><i class="fa-solid fa-check"></i> Paid</span>
                                                                    `}
                                                                    <button class="btn-secondary text-danger" onclick="deleteLoanInstallment(${inst.id}, ${g.loanId})" style="padding: 3px 8px; font-size: 0.75rem; border: 1px solid #fecaca; background: #fff; color: #ef4444; border-radius: 4px; cursor: pointer;" title="Delete EMI">
                                                                        <i class="fa-solid fa-trash-can"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    `;
                                                }).join('')}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                loanTbody.innerHTML = html;
            } else {
                loanTbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding:30px; color:#64748b;"><i class="fa-solid fa-inbox" style="font-size:2rem; margin-bottom:8px; display:block; opacity:0.4;"></i>No loan installments matching your search.</td></tr>';
            }
        }
    };

    window.toggleCommitteeGroup = function(key) {
        const detailRow = document.getElementById(`comm-group-details-${key}`);
        const chevron = document.getElementById(`comm-chevron-${key}`);
        if (!detailRow) return;

        if (detailRow.style.display === 'table-row') {
            detailRow.style.display = 'none';
            window.expandedCommGroups.delete(key);
            if (chevron) {
                chevron.classList.remove('fa-chevron-up');
                chevron.classList.add('fa-chevron-down');
            }
        } else {
            detailRow.style.display = 'table-row';
            window.expandedCommGroups.add(key);
            if (chevron) {
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-up');
            }
        }
    };

    window.toggleLoanGroup = function(loanId) {
        const detailRow = document.getElementById(`loan-group-details-${loanId}`);
        const chevron = document.getElementById(`loan-chevron-${loanId}`);
        if (!detailRow) return;

        if (detailRow.style.display === 'table-row') {
            detailRow.style.display = 'none';
            window.expandedLoanGroups.delete(loanId);
            if (chevron) {
                chevron.classList.remove('fa-chevron-up');
                chevron.classList.add('fa-chevron-down');
            }
        } else {
            detailRow.style.display = 'table-row';
            window.expandedLoanGroups.add(loanId);
            if (chevron) {
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-up');
            }
        }
    };

    window.filterInstallmentsTable = function() {
        renderInstallmentTables();
    };

    window.collectLoanInstallmentInTab = async function(installmentId, loanId) {
        if (!confirm("Confirm payment collection for this loan installment?")) return;
        try {
            const res = await window.apiFetch(`/api/admin/loans/${installmentId}/collect`, {
                method: 'POST'
            });
            if (!res) return;
            const data = await res.json();
            if (res.ok) {
                alert('Success: ' + (data.message || 'Payment collected successfully'));
                window.expandedLoanGroups.add(loanId);
                loadInstallmentsData();
                if (typeof loadBalanceSheetData === 'function') loadBalanceSheetData();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            console.error(err);
            alert('Failed to collect payment');
        }
    };

    // Delete Committee Installment
    window.deleteInstallment = async function(id, groupKey) {
        if (!confirm(`Are you sure you want to delete installment #${id}? This action cannot be undone.`)) return;

        try {
            const res = await window.apiFetch(`/api/admin/installments/${id}`, {
                method: 'DELETE'
            });
            if (!res) return;
            const data = await res.json();
            if (res.ok && data.status) {
                alert(data.message || 'Installment deleted successfully');
                if (groupKey) window.expandedCommGroups.add(groupKey);
                loadInstallmentsData();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete installment'));
            }
        } catch (err) {
            console.error("Error deleting installment:", err);
            alert("An error occurred while deleting installment.");
        }
    };

    // Delete Loan Installment
    window.deleteLoanInstallment = async function(id, loanId) {
        if (!confirm(`Are you sure you want to delete loan installment #${id}? This action cannot be undone.`)) return;

        try {
            const res = await window.apiFetch(`/api/admin/loan-installments/${id}`, {
                method: 'DELETE'
            });
            if (!res) return;
            const data = await res.json();
            if (res.ok && (data.status === true || data.status === 'success' || data.success === true)) {
                alert(data.message || 'Loan installment deleted successfully');
                if (loanId) window.expandedLoanGroups.add(loanId);
                loadInstallmentsData();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete loan installment'));
            }
        } catch (err) {
            console.error("Error deleting loan installment:", err);
            alert("An error occurred while deleting loan installment.");
        }
    };