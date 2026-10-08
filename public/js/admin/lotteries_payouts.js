
    async function loadLotterySettingsData() {
        try {
            const res = await window.apiFetch('/api/admin/lotteries/setting');
            if (!res || !res.ok) return;
            const payload = await res.json();
            if (payload.status && payload.data) {
                const data = payload.data;
                const titleInput = document.getElementById('grand_draw_title');
                const dateInput = document.getElementById('grand_draw_date');
                const descInput = document.getElementById('grand_draw_description');
                
                if (titleInput) titleInput.value = data.grand_draw_title || '';
                if (dateInput && data.grand_draw_date) {
                    const dateStr = data.grand_draw_date.replace(' ', 'T').substring(0, 16);
                    dateInput.value = dateStr;
                }
                if (descInput) descInput.value = data.grand_draw_description || '';
            }
        } catch (err) { console.error('Lottery Settings Load Error:', err); }
    }

    window.saveLotterySettings = async function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const data = {
            grand_draw_title: formData.get('grand_draw_title'),
            grand_draw_date: formData.get('grand_draw_date'),
            grand_draw_description: formData.get('grand_draw_description')
        };
        
        try {
            const res = await window.apiFetch('/api/admin/lotteries/setting', {
                method: 'POST',
                body: JSON.stringify(data)
            });
            if (!res) return;
            const payload = await res.json();
            if (payload.status) {
                alert('Lottery settings saved successfully!');
                loadLotterySettingsData();
            } else {
                alert('Error saving settings: ' + (payload.message || 'Unknown error'));
            }
        } catch (err) {
            console.error(err);
            alert('Failed to save settings.');
        }
    };

    async function loadLotteriesData() {
        loadLotterySettingsData();
        const winnerDateInput = document.getElementById('winner_draw_date');
        if (winnerDateInput && !winnerDateInput.value) {
            winnerDateInput.value = new Date().toISOString().split('T')[0];
        }
        try {
            const res = await window.apiFetch('/api/admin/lotteries');
            if (!res || !res.ok) return;
            const payload = await res.json();
            const data = payload.data || [];
            const tbody = document.getElementById('lotteries-tbody');
            if (!tbody) return;
            
            if(Array.isArray(data) && data.length > 0) {
                let lotHtml = '';
                data.forEach(l => {
                    const commName = l.committee ? l.committee.name : 'N/A';
                    const commId = l.committee_id || '';
                    const winnerName = l.winner ? l.winner.name : 'Unknown';
                    const winnerId = l.winner_id || '';
                    const winnerPhoto = l.winner && l.winner.photo ? l.winner.photo : '';
                    
                    const avatarHtml = winnerPhoto 
                        ? `<img src="${winnerPhoto}" style="width:28px; height:28px; border-radius:50%; object-fit:cover; margin-right:8px; vertical-align:middle;">`
                        : `<div style="width:28px; height:28px; border-radius:50%; background:#e2e8f0; color:#64748b; font-weight:600; display:inline-flex; align-items:center; justify-content:center; margin-right:8px; vertical-align:middle; font-size:11px;">${winnerName.substring(0, 1).toUpperCase()}</div>`;
                    
                    const prizePool = l.prize_amount ? `₹${parseFloat(l.prize_amount).toLocaleString('en-IN')}` : '₹0';
                    
                    lotHtml += `
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:12px 16px; font-size:13px; color:#64748b;">#${l.id}</td>
                            <td style="padding:12px 16px; font-size:13px; font-weight:600; color:#1e293b;">
                                ${avatarHtml}
                                <span>${winnerName} <small style="color:#64748b; font-weight:normal;">(ID: ${winnerId})</small></span>
                            </td>
                            <td style="padding:12px 16px; font-size:13px; color:#334155;"><strong>${commName}</strong> <small style="color:#64748b;">(ID: ${commId})</small></td>
                            <td style="padding:12px 16px; font-size:13px; font-weight:700; color:#10b981;">${prizePool}</td>
                            <td style="padding:12px 16px; font-size:13px; color:#64748b;">${l.draw_date}</td>
                            <td style="padding:12px 16px; text-align:right;">
                                <button class="btn-secondary" onclick="editLotteryWinner(${l.id}, ${commId}, ${winnerId}, '${l.draw_date}')" style="padding:4px 8px; font-size:0.75rem; border:1px solid #cbd5e1; background:white; cursor:pointer; border-radius:4px; margin-right:5px; transition:all 0.2s;"><i class="fa-solid fa-pen" style="color:#3b82f6;"></i> Edit</button>
                                <button class="btn-secondary" onclick="deleteLotteryWinner(${l.id})" style="padding:4px 8px; font-size:0.75rem; border:1px solid #fecaca; background:white; cursor:pointer; border-radius:4px; transition:all 0.2s;"><i class="fa-solid fa-trash" style="color:#ef4444;"></i> Delete</button>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = lotHtml;
            } else {
                tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 30px; color: #888; background: white;">No lottery winners yet.</td></tr>`;
            }
        } catch (err) { console.error('Lotteries Load Error:', err); }
    }

    async function loadPaymentSettingsData() {
        try {
            const res = await window.apiFetch('/api/admin/payment-setting');
            if (!res || !res.ok) return;
            const payload = await res.json();
            if (payload.status && payload.data) {
                const data = payload.data;
                const phoneInput = document.getElementById('pay-setting-phone');
                if (phoneInput) {
                    phoneInput.value = data.admin_phone || '';
                }
                const qrPreview = document.getElementById('pay-setting-qr-preview');
                if (qrPreview) {
                    if (data.qr_code) {
                        qrPreview.innerHTML = `<img src="${data.qr_code}" style="max-width:100%; max-height:100%; border-radius:8px;">`;
                    } else {
                        qrPreview.innerHTML = `<span style="color: #94a3b8; font-size: 0.85rem;">No QR Code Set</span>`;
                    }
                }
            }
        } catch (err) { console.error('Payment Settings Load Error:', err); }
    }

    // Bind form submit for payment settings
    document.addEventListener('submit', async function(e) {
        if (e.target && e.target.id === 'payment-settings-form') {
            e.preventDefault();
            const formData = new FormData(e.target);
            try {
                const res = await window.apiFetch('/api/admin/payment-setting', {
                    method: 'POST',
                    body: formData
                });
                if (!res) return;
                const payload = await res.json();
                if (payload.status) {
                    alert('Payment settings saved successfully!');
                    loadPaymentSettingsData();
                } else {
                    alert('Error saving payment settings: ' + (payload.message || 'Unknown error'));
                }
            } catch (err) {
                console.error(err);
                alert('Failed to save settings.');
            }
        }
    });

    async function loadPayoutsData() {
        try {
            loadPaymentSettingsData();
            const res = await window.apiFetch('/api/admin/payouts');
            if (!res || !res.ok) return;
            const data = await res.json();
            const tbody = document.getElementById('payouts-tbody');
            if (!tbody) return;
            
            if(Array.isArray(data) && data.length > 0) {
                let payHtml = '';
                data.forEach(p => {
                    const userName = p.user ? p.user.name : p.user_id;
                    const commName = p.committee ? p.committee.name : p.committee_id;
                    const actionBtn = p.status === 'pending' ? `<button class="btn-primary" onclick="payPayout(${p.id})"><i class="fa-solid fa-building-columns"></i> Pay to Bank</button>` : `<span class="badge" class="bg-success"><i class="fa-solid fa-check"></i> Paid on ${p.paid_date}</span>`;
                    const statusColor = p.status === 'pending' ? '#f59e0b' : '#10b981';

                    payHtml += `
                        <tr>
                            <td>#${p.id}</td>
                            <td><strong>${userName}</strong></td>
                            <td>${commName}</td>
                            <td>₹${p.total_deposits}</td>
                            <td>₹${p.return_amount}</td>
                            <td><strong>₹${p.total_payout}</strong></td>
                            <td><span style="color:${statusColor}"><i class="fa-solid fa-circle text-sm"></i> ${p.status}</span></td>
                            <td>${actionBtn}</td>
                        </tr>
                    `;
                });
                tbody.innerHTML = payHtml;
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No payouts found.</td></tr>';
            }
        } catch (err) { console.error('Payouts Load Error:', err); }
    }

    window.payPayout = async function(id) {
        if (!confirm("Are you sure you want to mark this as Paid and transfer to the user's bank?")) return;
        try {
            const res = await window.apiFetch(`/api/admin/payouts/${id}/pay`, {
                method: 'POST'
            });
            if (!res) return;
            const data = await res.json();
            if (res.ok) {
                alert('Success: ' + data.message);
                loadPayoutsData();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) { console.error(err); alert('Failed to process payout.'); }
    };

    window.addLotteryWinner = async function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const data = {
            committee_id: formData.get('committee_id'),
            winner_id: formData.get('winner_id'),
            draw_date: formData.get('draw_date')
        };

        const isEditing = !!window.editingLotteryId;
        const url = isEditing 
            ? `/api/admin/lotteries/${window.editingLotteryId}` 
            : '/api/admin/lotteries/manual-draw';
        const method = isEditing ? 'PUT' : 'POST';

        try {
            const res = await window.apiFetch(url, {
                method: method,
                body: JSON.stringify(data)
            });
            if (!res) return;
            const payload = await res.json();
            if (payload.status) {
                alert(isEditing ? 'Lottery winner updated successfully!' : 'Lottery winner added successfully!');
                cancelLotteryEdit();
                loadLotteriesData();
            } else {
                alert('Error processing request: ' + (payload.message || 'Unknown error'));
            }
        } catch (err) {
            console.error(err);
            alert('Failed to save winner details.');
        }
    };

    window.editLotteryWinner = function(id, committeeId, winnerId, drawDate) {
        window.editingLotteryId = id;
        const titleEl = document.querySelector('#add-lottery-winner-form')?.previousElementSibling;
        if (titleEl) titleEl.textContent = `Edit Lottery Winner #${id}`;
        
        const commInput = document.getElementById('winner_committee_id');
        if (commInput) commInput.value = committeeId;
        const userInput = document.getElementById('winner_user_id');
        if (userInput) userInput.value = winnerId;
        const drawInput = document.getElementById('winner_draw_date');
        if (drawInput) drawInput.value = drawDate;
        
        const btn = document.querySelector('#add-lottery-winner-form button[type="submit"]');
        if (btn) {
            btn.innerHTML = 'Update Winner <i class="fa-solid fa-save"></i>';
            btn.style.background = '#0ea5e9';
        }
        
        let cancelBtn = document.getElementById('cancel-edit-btn');
        if (!cancelBtn && btn) {
            cancelBtn = document.createElement('button');
            cancelBtn.id = 'cancel-edit-btn';
            cancelBtn.type = 'button';
            cancelBtn.className = 'btn-secondary';
            cancelBtn.style.marginRight = '8px';
            cancelBtn.style.background = '#64748b';
            cancelBtn.style.color = 'white';
            cancelBtn.style.border = 'none';
            cancelBtn.style.padding = '10px 20px';
            cancelBtn.style.borderRadius = '6px';
            cancelBtn.style.fontWeight = '600';
            cancelBtn.style.cursor = 'pointer';
            cancelBtn.textContent = 'Cancel';
            cancelBtn.onclick = function() {
                cancelLotteryEdit();
            };
            btn.parentNode.insertBefore(cancelBtn, btn);
        }
    };

    window.cancelLotteryEdit = function() {
        window.editingLotteryId = null;
        const titleEl = document.querySelector('#add-lottery-winner-form')?.previousElementSibling;
        if (titleEl) titleEl.textContent = 'Add New Lottery Winner';
        
        const form = document.getElementById('add-lottery-winner-form');
        if (form) form.reset();
        
        const dateInput = document.getElementById('winner_draw_date');
        if (dateInput) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
        
        const btn = document.querySelector('#add-lottery-winner-form button[type="submit"]');
        if (btn) {
            btn.innerHTML = 'Add Winner';
            btn.style.background = '#8b5cf6';
        }
        
        const cancelBtn = document.getElementById('cancel-edit-btn');
        if (cancelBtn) cancelBtn.remove();
    };

    window.deleteLotteryWinner = async function(id) {
        if (!confirm('Are you sure you want to delete this lottery winner?')) return;
        try {
            const res = await window.apiFetch(`/api/admin/lotteries/${id}`, {
                method: 'DELETE'
            });
            if (!res) return;
            const payload = await res.json();
            if (payload.status) {
                alert('Lottery winner deleted successfully!');
                if (window.editingLotteryId === id) {
                    cancelLotteryEdit();
                }
                loadLotteriesData();
            } else {
                alert('Error deleting winner: ' + (payload.message || 'Unknown error'));
            }
        } catch (err) {
            console.error(err);
            alert('Failed to delete winner.');
        }
    };