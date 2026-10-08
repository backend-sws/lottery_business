
    async function loadInstallmentsData() {
        try {
            // 1. Committee Installments
            const resComm = await fetch('/api/admin/installments', { headers: getHeaders() });
            const dataComm = await resComm.json();
            const commTbody = document.getElementById('installments-tbody');
            commTbody.innerHTML = '';
            
            const listComm = Array.isArray(dataComm?.data?.data)
                ? dataComm.data.data
                : (Array.isArray(dataComm?.data)
                    ? dataComm.data
                    : (Array.isArray(dataComm)
                        ? dataComm
                        : []));
            
            if(listComm.length > 0) {
                listComm.forEach(i => {
                    const userName = i.user ? i.user.name : i.user_id;
                    const commName = i.committee ? i.committee.name : i.committee_id;
                    let badgeColor = i.status === 'paid' ? '#10b981' : '#f59e0b';
                    commTbody.innerHTML += `
                        <tr>
                            <td>#${i.id}</td>
                            <td>${userName}</td>
                            <td>${commName}</td>
                            <td>₹${i.amount}</td>
                            <td>${i.paid_date ? i.paid_date : '<span style="color:#ef4444; font-size:0.8rem;">Not Paid Yet</span>'}</td>
                            <td><span style="color:${badgeColor}"><i class="fa-solid fa-circle text-sm"></i> ${i.status}</span></td>
                            <td>
                                <button class="btn-secondary text-danger" onclick="deleteInstallment(${i.id})" style="padding: 4px 8px; font-size: 0.8rem; font-weight: 600; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Delete Installment">
                                    <i class="fa-solid fa-trash-can"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                commTbody.innerHTML = '<tr><td colspan="7" class="text-center">No committee installments found.</td></tr>';
            }

            // 2. Loan Installments
            const resLoan = await fetch('/api/admin/loan-installments', { headers: getHeaders() });
            const payloadLoan = await resLoan.json();
            const dataLoan = payloadLoan.data || [];
            const loanTbody = document.getElementById('loan-installments-tbody');
            if(loanTbody) {
                loanTbody.innerHTML = '';
                if(Array.isArray(dataLoan) && dataLoan.length > 0) {
                    dataLoan.forEach(i => {
                        const userName = (i.loan && i.loan.user) ? i.loan.user.name : 'Unknown User';
                        const loanIdDisplay = i.loan ? `Loan #${i.loan.id}` : 'Unknown Loan';
                        let badgeColor = i.status === 'paid' ? '#10b981' : '#f59e0b';
                        loanTbody.innerHTML += `
                            <tr>
                                <td>#${i.id}</td>
                                <td>${userName}</td>
                                <td>${loanIdDisplay}</td>
                                <td>₹${i.total_amount}</td>
                                <td>${i.paid_date ? i.paid_date : '<span style="color:#ef4444; font-size:0.8rem;">Not Paid Yet</span>'}</td>
                                <td><span style="color:${badgeColor}"><i class="fa-solid fa-circle text-sm"></i> ${i.status}</span></td>
                                <td>
                                    <button class="btn-secondary text-danger" onclick="deleteLoanInstallment(${i.id})" style="padding: 4px 8px; font-size: 0.8rem; font-weight: 600; color: #ef4444; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Delete Loan Installment">
                                        <i class="fa-solid fa-trash-can"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    loanTbody.innerHTML = '<tr><td colspan="7" class="text-center">No loan installments found.</td></tr>';
                }
            }
        } catch (err) { console.error(err); }
    }

    // Delete Committee Installment
    window.deleteInstallment = async function(id) {
        if (!confirm(`Are you sure you want to delete installment #${id}? This action cannot be undone.`)) return;

        try {
            const res = await fetch(`/api/admin/installments/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });
            const data = await res.json();
            if (res.ok && data.status) {
                alert(data.message || 'Installment deleted successfully');
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
    window.deleteLoanInstallment = async function(id) {
        if (!confirm(`Are you sure you want to delete loan installment #${id}? This action cannot be undone.`)) return;

        try {
            const res = await fetch(`/api/admin/loan-installments/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });
            const data = await res.json();
            if (res.ok && (data.status === true || data.status === 'success' || data.success === true)) {
                alert(data.message || 'Loan installment deleted successfully');
                loadInstallmentsData();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete loan installment'));
            }
        } catch (err) {
            console.error("Error deleting loan installment:", err);
            alert("An error occurred while deleting loan installment.");
        }
    };