
    async function loadLoansData() {
        try {
            const res = await window.apiFetch('/api/admin/loans');
            if (!res || !res.ok) return;
            const payload = await res.json();
            const data = payload.data || [];
            const tbody = document.getElementById('loans-tbody');
            if (!tbody) return;
            
            if(Array.isArray(data) && data.length > 0) {
                let loansHtml = '';
                data.forEach(l => {
                    const userName = l.user ? l.user.name : 'Unknown';
                    loansHtml += `
                        <tr id="loan-row-${l.id}">
                            <td>#${l.id}</td>
                            <td><strong>${userName}</strong></td>
                            <td class="text-success">₹${l.amount}</td>
                            <td>${l.interest_rate_percent}%</td>
                            <td>${l.duration_months} M</td>
                            <td style="text-transform:capitalize;">${l.payment_frequency}</td>
                            <td><span style="color:${l.status === 'active' ? '#3b82f6' : '#10b981'}"><i class="fa-solid fa-circle text-sm"></i> ${l.status}</span></td>
                            <td>
                                <div style="display: flex; gap: 8px; align-items: center; white-space: nowrap;">
                                    <button class="btn-primary" onclick="toggleLoanDetails(${l.id})" style="padding:4px 8px; font-size:0.8rem; margin:0;"><i class="fa-solid fa-chevron-down"></i> Installments</button>
                                    <button class="btn-secondary text-danger" onclick="deleteLoan(${l.id})" style="padding:4px 8px; font-size:0.8rem; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); margin:0;"><i class="fa-solid fa-trash-can"></i> Delete</button>
                                </div>
                            </td>
                        </tr>
                        <tr id="loan-details-${l.id}" style="display:none; background: rgba(0,0,0,0.02);">
                            <td colspan="8" style="padding: 0;">
                                <div id="loan-details-content-${l.id}" style="padding: 20px; border-top: 1px solid rgba(0,0,0,0.05);">
                                    <div style="text-align:center; color:#888;"><i class="fa-solid fa-spinner fa-spin"></i> Loading installments...</div>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = loansHtml;
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No loans found.</td></tr>';
            }
        } catch (err) { console.error('Load Loans Error:', err); }
    }

    window.toggleLoanDetails = async function(id) {
        const detailRow = document.getElementById(`loan-details-${id}`);
        if (!detailRow) return;
        if (detailRow.style.display === 'table-row') {
            detailRow.style.display = 'none';
            return;
        }
        
        detailRow.style.display = 'table-row';
        const contentDiv = document.getElementById(`loan-details-content-${id}`);

        try {
            const res = await window.apiFetch(`/api/admin/loans/${id}`);
            if (!res || !res.ok) {
                if (contentDiv) contentDiv.innerHTML = `<div class="text-danger text-center">Failed to load installments.</div>`;
                return;
            }
            const payload = await res.json();
            const loan = payload.data;
            const installments = loan?.installments || [];

            let total = installments.length;
            let paid = installments.filter(i => i.status === 'paid').length;
            let pending = total - paid;

            let html = `
                <div class="flex-wrap-gap">
                    <div class="stat-card-success">
                        <h4 style="margin:0; font-size: 0.85rem; text-transform: uppercase;">Total Installments</h4>
                        <div style="font-size: 1.5rem; font-weight: bold;">${total}</div>
                    </div>
                    <div class="stat-card-info">
                        <h4 style="margin:0; font-size: 0.85rem; text-transform: uppercase;">Paid</h4>
                        <div style="font-size: 1.5rem; font-weight: bold;">${paid}</div>
                    </div>
                    <div class="stat-card-danger">
                        <h4 style="margin:0; font-size: 0.85rem; text-transform: uppercase;">Due / Pending</h4>
                        <div style="font-size: 1.5rem; font-weight: bold;">${pending}</div>
                    </div>
                </div>
                
                <table class="data-table" style="font-size: 0.85rem; box-shadow: none; border: 1px solid rgba(0,0,0,0.05);">
                    <thead style="background: rgba(0,0,0,0.02);">
                        <tr>
                            <th>#</th>
                            <th>Due Date</th>
                            <th>Principal (₹)</th>
                            <th>Interest (₹)</th>
                            <th>Total (₹)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            installments.forEach((inst, index) => {
                let statusBadge = inst.status === 'paid' 
                    ? '<span class="text-success"><i class="fa-solid fa-check"></i> Paid</span>' 
                    : '<span class="text-warning"><i class="fa-solid fa-clock"></i> Pending</span>';
                
                let actionBtn = inst.status === 'pending'
                    ? `<button class="btn-primary" onclick="collectLoanInstallment(${inst.id}, ${id})" style="padding: 2px 8px; font-size: 0.75rem;">Collect</button>`
                    : `<span style="color: #94a3b8; font-size: 0.75rem;">Paid on ${inst.paid_date}</span>`;

                let deleteBtn = `<button class="btn-secondary text-danger" onclick="deleteLoanInstallmentFromLoan(${inst.id}, ${id})" style="padding: 2px 6px; font-size: 0.75rem; border: 1px solid #fecaca; background: #fff; color: #ef4444; border-radius: 4px; cursor: pointer;" title="Delete Installment"><i class="fa-solid fa-trash-can"></i></button>`;

                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${inst.due_date}</td>
                        <td>₹${inst.principal_component}</td>
                        <td>₹${inst.interest_component}</td>
                        <td><strong>₹${inst.total_amount}</strong></td>
                        <td>${statusBadge}</td>
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                ${actionBtn}
                                ${deleteBtn}
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;
            if (contentDiv) contentDiv.innerHTML = html;

        } catch (err) {
            if (contentDiv) contentDiv.innerHTML = `<div class="text-danger text-center">Failed to load installments.</div>`;
            console.error(err);
        }
    };

    window.deleteLoanInstallmentFromLoan = async function(installmentId, loanId) {
        if (!confirm(`Are you sure you want to delete installment #${installmentId}? This action cannot be undone.`)) return;

        try {
            const res = await window.apiFetch(`/api/admin/loan-installments/${installmentId}`, {
                method: 'DELETE'
            });
            if (!res) return;
            const data = await res.json();
            if (res.ok && (data.status === true || data.status === 'success' || data.success === true)) {
                alert(data.message || 'Loan installment deleted successfully');
                const detailRow = document.getElementById(`loan-details-${loanId}`);
                if (detailRow) {
                    detailRow.style.display = 'none';
                    toggleLoanDetails(loanId);
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to delete loan installment'));
            }
        } catch (err) {
            console.error("Error deleting loan installment:", err);
            alert("An error occurred while deleting loan installment.");
        }
    };

    window.collectLoanInstallment = async function(installmentId, loanId) {
        if (!confirm("Confirm payment collection for this installment?")) return;
        try {
            const res = await window.apiFetch(`/api/admin/loans/${installmentId}/collect`, {
                method: 'POST'
            });
            if (!res) return;
            const data = await res.json();
            if (res.ok) {
                alert('Success: ' + data.message);
                // Refresh the slide down section
                const detailElem = document.getElementById(`loan-details-${loanId}`);
                if (detailElem) detailElem.style.display = 'none';
                loadLoansData();
                if (typeof loadBalanceSheetData === 'function') loadBalanceSheetData();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            console.error(err);
            alert('Failed to collect payment');
        }
    };

    window.deleteLoan = async function(id) {
        if (!confirm("Are you sure you want to delete this loan? This will delete all its installments as well. This action cannot be undone.")) return;
        try {
            const res = await window.apiFetch(`/api/admin/loans/${id}`, {
                method: 'DELETE'
            });
            if (!res) return;
            const data = await res.json();
            if (res.ok) {
                alert('Success: ' + (data.message || 'Loan deleted successfully'));
                loadLoansData();
                if (typeof loadBalanceSheetData === 'function') loadBalanceSheetData();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete loan'));
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    };