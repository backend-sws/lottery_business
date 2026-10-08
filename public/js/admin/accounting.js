
    async function loadPnLData() {
        try {
            const res = await window.apiFetch('/api/admin/accounting/pnl');
            if (!res || !res.ok) return;
            const payload = await res.json();
            const data = payload.data;
            if(!data) return;

            document.getElementById('pnl-total-revenue').textContent = '₹' + data.total_revenue;
            document.getElementById('pnl-total-expense').textContent = '₹' + data.total_expense;
            document.getElementById('pnl-net-profit').textContent = '₹' + data.net_profit;

            const revTbody = document.getElementById('pnl-revenue-tbody');
            if (revTbody) {
                let revHtml = '';
                (data.revenue || []).forEach(acc => {
                    revHtml += `<tr><td>${acc.name}</td><td class="text-right">₹${acc.balance}</td></tr>`;
                });
                revTbody.innerHTML = revHtml;
            }

            const expTbody = document.getElementById('pnl-expense-tbody');
            if (expTbody) {
                let expHtml = '';
                (data.expenses || []).forEach(acc => {
                    expHtml += `<tr><td>${acc.name}</td><td class="text-right">₹${acc.balance}</td></tr>`;
                });
                expTbody.innerHTML = expHtml;
            }
        } catch(err) { console.error(err); }
    }

    async function loadBalanceSheetData() {
        try {
            const res = await window.apiFetch('/api/admin/accounting/balance-sheet');
            if (!res || !res.ok) return;
            const payload = await res.json();
            const data = payload.data;
            if(!data) return;

            document.getElementById('bs-total-assets').textContent = '₹' + data.total_assets;
            document.getElementById('bs-total-liabilities').textContent = '₹' + data.total_equity_and_liabilities;

            const assetsTbody = document.getElementById('bs-assets-tbody');
            if (assetsTbody) {
                let assetsHtml = '';
                (data.assets || []).forEach(acc => {
                    assetsHtml += `<tr><td>${acc.name}</td><td class="text-right">₹${acc.balance}</td></tr>`;
                });
                assetsTbody.innerHTML = assetsHtml;
            }

            const liabTbody = document.getElementById('bs-liabilities-tbody');
            if (liabTbody) {
                let liabHtml = '';
                (data.liabilities || []).forEach(acc => {
                    liabHtml += `<tr><td>${acc.name}</td><td class="text-right">₹${acc.balance}</td></tr>`;
                });
                // Add Net Profit to Equity/Liabilities side
                liabHtml += `<tr><td><strong>Net Profit (Equity)</strong></td><td class="text-right"><strong>₹${data.net_profit}</strong></td></tr>`;
                liabTbody.innerHTML = liabHtml;
            }
            
        } catch(err) { console.error(err); }
    }

    // Load Member Ledger
    document.getElementById('btn-load-member-ledger')?.addEventListener('click', async () => {
        const id = document.getElementById('ledger-member-id').value;
        if (!id) return alert('Enter Member ID');
        try {
            const res = await window.apiFetch(`/api/admin/accounting/member-ledger/${id}`);
            if (!res) return;
            const payload = await res.json();
            if (!res.ok) { alert(payload.message || 'Error loading ledger'); return; }
            
            document.getElementById('ledger-member-name').textContent = 'Ledger for Member: ' + (payload.data?.member || id);
            const tbody = document.getElementById('member-ledger-tbody');
            if (tbody) {
                let rowsHtml = '';
                (payload.data?.entries || []).forEach(entry => {
                    rowsHtml += `
                        <tr>
                            <td>${entry.transaction_date}</td>
                            <td>${entry.description}</td>
                            <td>${entry.account?.name || '--'}</td>
                            <td class="text-success">${entry.debit > 0 ? '₹'+entry.debit : '-'}</td>
                            <td class="text-danger">${entry.credit > 0 ? '₹'+entry.credit : '-'}</td>
                        </tr>
                    `;
                });
                tbody.innerHTML = rowsHtml;
            }
        } catch(e) { console.error(e); alert('Error fetching ledger'); }
    });

    // Load Committee Ledger
    document.getElementById('btn-load-committee-ledger')?.addEventListener('click', async () => {
        const id = document.getElementById('ledger-committee-id').value;
        if (!id) return alert('Enter Committee ID');
        try {
            const res = await window.apiFetch(`/api/admin/accounting/committee-ledger/${id}`);
            if (!res) return;
            const payload = await res.json();
            if (!res.ok) { alert(payload.message || 'Error loading ledger'); return; }
            
            document.getElementById('ledger-committee-name').textContent = 'Ledger for Committee: ' + (payload.data?.committee || id);
            const tbody = document.getElementById('committee-ledger-tbody');
            if (tbody) {
                let rowsHtml = '';
                (payload.data?.entries || []).forEach(entry => {
                    rowsHtml += `
                        <tr>
                            <td>${entry.transaction_date}</td>
                            <td>${entry.description}</td>
                            <td>${entry.account?.name || '--'}</td>
                            <td class="text-success">${entry.debit > 0 ? '₹'+entry.debit : '-'}</td>
                            <td class="text-danger">${entry.credit > 0 ? '₹'+entry.credit : '-'}</td>
                        </tr>
                    `;
                });
                tbody.innerHTML = rowsHtml;
            }
        } catch(e) { console.error(e); alert('Error fetching ledger'); }
    });