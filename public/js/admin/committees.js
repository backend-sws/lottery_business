
    async function loadCommitteesData(status = null) {
        try {
            let url = '/api/admin/committees';
            if (status === 'active') url += '?status=active';
            const res = await window.apiFetch(url);
            if (!res || !res.ok) return;
            const data = await res.json();
            const tbody = document.getElementById('committees-tbody');
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
                            <td>${c.duration} Months</td>
                            <td><span class="badge badge-primary">${c.payment_frequency}</span></td>
                            <td>${c.return_percentage}%</td>
                            <td><span class="text-success"><i class="fa-solid fa-circle text-sm"></i> ${c.status}</span></td>
                            <td>
                                <button class="btn-secondary" onclick="window.location.hash='#committee-details-${c.id}'" class="badge badge-outline-info"><i class="fa-solid fa-users"></i> Members</button>
                                <button class="btn-secondary" onclick="openEditCommitteeModal(${c.id})" style="padding:4px 8px; font-size:0.8rem; margin-right: 5px;"><i class="fa-solid fa-pen"></i> Edit</button>
                                <button class="btn-secondary text-danger" onclick="deleteCommittee(${c.id})" class="badge badge-outline-danger"><i class="fa-solid fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = commHtml;
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center">No committees found.</td></tr>';
            }
        } catch (err) { console.error('Load Committees Error:', err); }
    }

    window.switchMemberTab = function(tab) {
        const btnAgents = document.getElementById('tab-btn-agents');
        const btnComm = document.getElementById('tab-btn-committee-members');
        const btnLoan = document.getElementById('tab-btn-loan-members');
        const tableAgents = document.getElementById('table-agents');
        const tableComm = document.getElementById('table-committee-members');
        const tableLoan = document.getElementById('table-loan-members');

        btnAgents.className = 'btn-secondary';
        btnComm.className = 'btn-secondary';
        btnLoan.className = 'btn-secondary';
        
        tableAgents.style.display = 'none';
        tableComm.style.display = 'none';
        tableLoan.style.display = 'none';

        if (tab === 'agents') {
            btnAgents.className = 'btn-primary';
            tableAgents.style.display = 'block';
        } else if (tab === 'committee') {
            btnComm.className = 'btn-primary';
            tableComm.style.display = 'block';
        } else if (tab === 'loan') {
            btnLoan.className = 'btn-primary';
            tableLoan.style.display = 'block';
        }
    };