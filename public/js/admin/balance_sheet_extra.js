
    async function loadBalanceSheetData() {
        try {
            const res = await window.apiFetch('/api/admin/accounting/balance-sheet');
            if (!res || !res.ok) return;
            const payload = await res.json();
            const data = payload?.data;
            if (!data) return;
            
            // Committees
            const commTotalAssets = document.getElementById('bs-comm-assets');
            if (commTotalAssets) commTotalAssets.textContent = '₹' + parseFloat(data.committee?.total_assets || 0).toFixed(2);
            const commTotalLiab = document.getElementById('bs-comm-liabilities');
            if (commTotalLiab) commTotalLiab.textContent = '₹' + parseFloat(data.committee?.total_liabilities || 0).toFixed(2);
            
            const commAssetsBody = document.getElementById('bs-comm-assets-tbody');
            if (commAssetsBody) {
                let assetsHtml = '';
                (data.committee?.assets || []).forEach(a => {
                    assetsHtml += `<tr><td>${a.name}</td><td class="text-right">₹${parseFloat(a.balance || 0).toFixed(2)}</td></tr>`;
                });
                commAssetsBody.innerHTML = assetsHtml;
            }
            
            const commLiabBody = document.getElementById('bs-comm-liabilities-tbody');
            if (commLiabBody) {
                let liabHtml = '';
                (data.committee?.liabilities || []).forEach(l => {
                    liabHtml += `<tr><td>${l.name}</td><td class="text-right">₹${parseFloat(l.balance || 0).toFixed(2)}</td></tr>`;
                });
                (data.committee?.equity || []).forEach(e => {
                    liabHtml += `<tr><td>${e.name}</td><td style="text-align:right; color:#8b5cf6;">₹${parseFloat(e.balance || 0).toFixed(2)}</td></tr>`;
                });
                commLiabBody.innerHTML = liabHtml;
            }

            // Loans
            const loanTotalAssets = document.getElementById('bs-loan-assets');
            if (loanTotalAssets) loanTotalAssets.textContent = '₹' + parseFloat(data.loan?.total_assets || 0).toFixed(2);
            const loanTotalLiab = document.getElementById('bs-loan-liabilities');
            if (loanTotalLiab) loanTotalLiab.textContent = '₹' + parseFloat(data.loan?.total_liabilities || 0).toFixed(2);
            
            const loanAssetsBody = document.getElementById('bs-loan-assets-tbody');
            if (loanAssetsBody) {
                let loanAssetsHtml = '';
                (data.loan?.assets || []).forEach(a => {
                    loanAssetsHtml += `<tr><td>${a.name}</td><td class="text-right">₹${parseFloat(a.balance || 0).toFixed(2)}</td></tr>`;
                });
                loanAssetsBody.innerHTML = loanAssetsHtml;
            }
            
            const loanLiabBody = document.getElementById('bs-loan-liabilities-tbody');
            if (loanLiabBody) {
                let loanLiabHtml = '';
                (data.loan?.liabilities || []).forEach(l => {
                    loanLiabHtml += `<tr><td>${l.name}</td><td class="text-right">₹${parseFloat(l.balance || 0).toFixed(2)}</td></tr>`;
                });
                (data.loan?.equity || []).forEach(e => {
                    loanLiabHtml += `<tr><td>${e.name}</td><td style="text-align:right; color:#8b5cf6;">₹${parseFloat(e.balance || 0).toFixed(2)}</td></tr>`;
                });
                loanLiabBody.innerHTML = loanLiabHtml;
            }

            // Loan Breakdown
            const breakdownBody = document.getElementById('bs-loan-breakdown-tbody');
            if (breakdownBody) {
                if (data.loan?.breakdown && data.loan.breakdown.length > 0) {
                    let bdHtml = '';
                    data.loan.breakdown.forEach(b => {
                        const statusColor = b.status === 'paid' ? '#10b981' : '#f59e0b';
                        bdHtml += `
                            <tr>
                                <td><strong>${b.user_name}</strong><br><small class="text-muted">ID: #${b.id} | ${b.interest_rate}</small></td>
                                <td>₹${parseFloat(b.principal || 0).toFixed(2)}</td>
                                <td class="text-success">₹${parseFloat(b.total_expected_interest || 0).toFixed(2)}</td>
                                <td style="color:#8b5cf6;">₹${parseFloat(b.recovered_interest || 0).toFixed(2)}</td>
                                <td>₹${parseFloat(b.total_recovered || 0).toFixed(2)} / <span class="text-muted">₹${parseFloat(b.total_expected_return || 0).toFixed(2)}</span></td>
                                <td><span style="background: ${statusColor}22; color: ${statusColor}; padding: 3px 8px; border-radius: 12px; font-size: 0.8rem; text-transform: capitalize;">${b.status}</span></td>
                            </tr>
                        `;
                    });
                    breakdownBody.innerHTML = bdHtml;
                } else {
                    breakdownBody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#888;">No loans given yet.</td></tr>`;
                }
            }

        } catch (err) { console.error('Balance Sheet Load Error:', err); }
    }

    // =====================================
    // AGENT MANAGEMENT
    // =====================================
