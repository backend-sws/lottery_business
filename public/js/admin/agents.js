    // Load Agents View
    window.loadAgentsView = async function() {
        try {
            // Load Pending Collections
            const colRes = await window.apiFetch('/api/admin/agents/collections?status=pending');
            if (!colRes || !colRes.ok) return;
            const colJson = await colRes.json();
            
            const colData = Array.isArray(colJson) 
                ? colJson 
                : (colJson.data && Array.isArray(colJson.data.data) 
                    ? colJson.data.data 
                    : (colJson.data && Array.isArray(colJson.data) 
                        ? colJson.data 
                        : []));

            const commBody = document.getElementById('agent-pending-committee-collections-tbody');
            const loanBody = document.getElementById('agent-pending-loan-collections-tbody');
            if (commBody) commBody.innerHTML = '';
            if (loanBody) loanBody.innerHTML = '';
            
            let hasComm = false, hasLoan = false;
            let commIndex = 1, loanIndex = 1;
            let commHtml = '', loanHtml = '';

            if (colData.length > 0) {
                colData.forEach(c => {
                    const agentName = c.agent ? c.agent.name : 'N/A';
                    const memberName = c.member ? c.member.name : 'N/A';
                    if (c.collection_type === 'committee') {
                        hasComm = true;
                        const details = c.installment && c.installment.committee ? c.installment.committee.name : 'Unknown Committee';
                        commHtml += `
                            <tr>
                                <td>#${commIndex++}</td>
                                <td>${agentName}</td>
                                <td>${memberName}</td>
                                <td>${details}</td>
                                <td class="text-success">₹${parseFloat(c.amount_collected).toFixed(2)}</td>
                                <td>${new Date(c.collected_at).toLocaleDateString()}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <button class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem;" onclick="approveCollection(${c.id})">Approve</button>
                                        <button class="btn-secondary text-danger" style="padding: 5px 8px; font-size: 0.8rem; background: #fef2f2; border: 1px solid #fca5a5; color: #ef4444; border-radius: 5px; cursor: pointer;" onclick="deleteCollectionFromOverview(${c.id})" title="Delete Collection"><i class="fa-solid fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    } else if (c.collection_type === 'loan') {
                        hasLoan = true;
                        const details = c.loan_installment && c.loan_installment.loan ? `Loan #${c.loan_installment.loan.id}` : 'Unknown Loan';
                        loanHtml += `
                            <tr>
                                <td>#${loanIndex++}</td>
                                <td>${agentName}</td>
                                <td>${memberName}</td>
                                <td>${details}</td>
                                <td class="text-success">₹${parseFloat(c.amount_collected).toFixed(2)}</td>
                                <td>${new Date(c.collected_at).toLocaleDateString()}</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:6px;">
                                        <button class="btn-primary" style="padding: 5px 10px; font-size: 0.8rem;" onclick="approveCollection(${c.id})">Approve</button>
                                        <button class="btn-secondary text-danger" style="padding: 5px 8px; font-size: 0.8rem; background: #fef2f2; border: 1px solid #fca5a5; color: #ef4444; border-radius: 5px; cursor: pointer;" onclick="deleteCollectionFromOverview(${c.id})" title="Delete Collection"><i class="fa-solid fa-trash-can"></i></button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    }
                });
            }

            if (commBody) {
                commBody.innerHTML = hasComm ? commHtml : '<tr><td colspan="7" class="text-center">No pending committee collections.</td></tr>';
            }
            if (loanBody) {
                loanBody.innerHTML = hasLoan ? loanHtml : '<tr><td colspan="7" class="text-center">No pending loan collections.</td></tr>';
            }

        } catch(err) { 
            console.error(err); 
            const commBody = document.getElementById('agent-pending-committee-collections-tbody');
            if (commBody) {
                commBody.innerHTML = `<tr><td colspan="7" class="text-danger text-center">Error: ${err.message || 'Unknown Error'}</td></tr>`;
            }
        }
    }

    // Approve Collection Action
    window.approveCollection = async function(id) {
        if(!confirm("Are you sure you want to approve this collection and settle the user's due amount?")) return;
        try {
            const res = await window.apiFetch('/api/admin/agents/collections/' + id + '/approve', {
                method: 'POST'
            });
            if (!res) return;
            const data = await res.json();
            if(data.status === true || data.status === 'success') {
                alert(data.message);
                loadAgentsView();
            } else {
                alert(data.message || "Failed to approve. Please try again or re-login.");
            }
        } catch(err) { console.error(err); alert("An error occurred: " + err.message); }
    };

    // Delete Agent Functionality
    window.deleteAgent = async function(id, name) {
        const agentLabel = name ? `agent "${name}"` : `Agent #${id}`;
        if (!confirm(`Are you sure you want to delete ${agentLabel}?`)) return;

        try {
            let res = await window.apiFetch(`/api/admin/members/${id}`, {
                method: 'DELETE'
            });
            if (!res) return;
            let data = await res.json();

            // Handle active records / collections
            if (res.status === 422 && data?.data?.has_financials) {
                const forceConfirm = confirm(
                    `This ${agentLabel} has associated collection or target records.\n\nDo you want to FORCE DELETE this agent and clean up their assigned records?`
                );
                if (forceConfirm) {
                    res = await window.apiFetch(`/api/admin/members/${id}?force=true`, {
                        method: 'DELETE'
                    });
                    if (!res) return;
                    data = await res.json();
                } else {
                    return;
                }
            }

            if (res.ok) {
                alert(data.message || 'Agent deleted successfully');
                if (typeof closeModal === 'function') {
                    closeModal();
                }
                if (typeof loadAgentsList === 'function') {
                    loadAgentsList();
                }
                if (typeof loadAgentsView === 'function') {
                    loadAgentsView();
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to delete agent'));
            }
        } catch (err) {
            console.error("Error deleting agent:", err);
            alert("An error occurred while deleting agent.");
        }
    };