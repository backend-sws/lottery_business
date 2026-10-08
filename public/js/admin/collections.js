
    async function loadCollectionCommittees() {
        try {
            const res = await window.apiFetch('/api/admin/committees');
            if (!res || !res.ok) return;
            const data = await res.json();
            const tbody = document.getElementById('collection-committees-tbody');
            if (!tbody) return;
            const committees = Array.isArray(data?.data?.data)
                ? data.data.data
                : (Array.isArray(data?.data)
                    ? data.data
                    : (Array.isArray(data)
                        ? data
                        : []));
            if (committees.length > 0) {
                let commHtml = '';
                committees.forEach(c => {
                    commHtml += `
                        <tr>
                            <td>#${c.id}</td>
                            <td><strong>${c.name}</strong></td>
                            <td>₹${c.amount}</td>
                            <td><span class="text-success"><i class="fa-solid fa-circle text-sm"></i> ${c.status}</span></td>
                            <td><button class="btn-primary" onclick="window.location.hash='#committee-details-${c.id}'" style="padding:4px 8px; font-size:0.8rem;">View Collection</button></td>
                        </tr>
                    `;
                });
                tbody.innerHTML = commHtml;
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center">No committees found.</td></tr>';
            }
        } catch (err) { console.error('Load Collection Committees Error:', err); }
    }

    async function loadCommitteeDetails(id) {
        if(!id) return;
        try {
            const res = await window.apiFetch(`/api/admin/committees/${id}/collection-stats`);
            if (!res || !res.ok) return;
            const data = await res.json();
            
            const titleElem = document.getElementById('cd-title');
            if (titleElem && data.committee) {
                titleElem.textContent = `${data.committee.name} Collection`;
            }
            
            const tbody = document.getElementById('committee-details-tbody');
            if (!tbody) return;
            
            const commAmount = data.committee?.amount || 0;
            
            if(data.members && Array.isArray(data.members) && data.members.length > 0) {
                let memHtml = '';
                data.members.forEach(m => {
                    memHtml += `
                        <tr>
                            <td>#${m.id}</td>
                            <td><strong>${m.name}</strong></td>
                            <td>${m.phone || m.email}</td>
                            <td class="text-success">₹${m.total_deposited}</td>
                            <td class="text-danger">₹${m.total_due}</td>
                            <td>₹${m.total_expected}</td>
                            <td>${m.installments_remaining} left (of ${m.total_installments})</td>
                            <td>
                                ${m.installments_remaining > 0 ? `
                                    <button class="btn-primary" onclick="openCollectPaymentModal(${m.id}, ${id}, ${commAmount})" style="padding:4px 8px; font-size:0.75rem; border-radius:4px; cursor:pointer;"><i class="fa-solid fa-indian-rupee-sign"></i> Pay Installment</button>
                                ` : '<span class="badge badge-success">Completed</span>'}
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = memHtml;
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No member records found for this committee.</td></tr>';
            }
        } catch (err) { console.error('Load Committee Details Error:', err); }
    }

    // Modal & Collect Payment dynamic callbacks
    window.openCollectPaymentModal = function(userId, committeeId, amount) {
        document.getElementById('modal-title').textContent = 'Collect Committee Installment';
        document.getElementById('modal-body').innerHTML = `
            <form id="collect-installment-form" onsubmit="submitCollectInstallment(event)">
                <input type="hidden" id="ci_user_id" value="${userId}">
                <input type="hidden" id="ci_committee_id" value="${committeeId}">
                
                <div class="input-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:#374151;">Installment Amount (₹)</label>
                    <input type="number" id="ci_amount" value="${amount}" class="filter-select" style="width:100%; border:1px solid #d1d5db; height:40px; border-radius:6px; padding:0 12px; box-sizing:border-box;" required>
                </div>
                
                <div class="input-group" style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:#374151;">Payment Date</label>
                    <input type="date" id="ci_paid_date" value="${new Date().toISOString().split('T')[0]}" class="filter-select" style="width:100%; border:1px solid #d1d5db; height:40px; border-radius:6px; padding:0 12px; box-sizing:border-box;" required>
                </div>
                
                <button type="submit" class="btn-primary" style="width:100%; height:42px; border-radius:8px; font-weight:700; cursor:pointer;"><i class="fa-solid fa-circle-check"></i> Submit Payment</button>
            </form>
        `;
        document.getElementById('global-modal').style.display = 'flex';
    };

    window.submitCollectInstallment = async function(event) {
        event.preventDefault();
        
        const userId = document.getElementById('ci_user_id').value;
        const committeeId = document.getElementById('ci_committee_id').value;
        const amount = document.getElementById('ci_amount').value;
        const paidDate = document.getElementById('ci_paid_date').value;
        
        try {
            const res = await window.apiFetch('/api/admin/installments/collect', {
                method: 'POST',
                body: JSON.stringify({
                    user_id: userId,
                    committee_id: committeeId,
                    amount: amount,
                    paid_date: paidDate,
                    status: 'paid'
                })
            });
            if (!res) return;
            const data = await res.json();
            if (data.status === true || data.status === 'success') {
                alert(data.message || 'Payment collected successfully!');
                closeModal();
                loadCommitteeDetails(committeeId);
            } else {
                alert(data.message || 'Failed to collect payment.');
            }
        } catch (e) {
            console.error(e);
            alert('Error: ' + e.message);
        }
    };