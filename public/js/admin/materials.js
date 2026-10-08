(function() {
    // ----- DATA LOADERS -----
    window.loadMaterialsData = async function() {
        const tbody = document.getElementById('materials-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> Loading materials...</td></tr>';

        try {
            const res = await window.apiFetch('/api/admin/materials');
            if (!res || !res.ok) return;
            const json = await res.json();
            
            if (json.data) {
                let html = '';
                json.data.forEach(m => {
                    const img = m.image_url 
                        ? `<img src="${m.image_url}" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">`
                        : `<div style="width: 40px; height: 40px; border-radius: 8px; background: var(--border-color); display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-image text-muted"></i></div>`;
                    
                    html += `
                        <tr>
                            <td>${m.id}</td>
                            <td>${img}</td>
                            <td><strong>${m.name}</strong></td>
                            <td>₹ ${parseFloat(m.price).toLocaleString()}</td>
                            <td>${m.unit}</td>
                            <td><span class="badge ${m.status === 'active' ? 'badge-success' : 'badge-danger'}">${m.status.toUpperCase()}</span></td>
                            <td>
                                <button class="btn-secondary" onclick="openEditMaterialModal(${m.id})"><i class="fa-solid fa-edit"></i> Edit</button>
                                <button class="btn-secondary text-danger" onclick="deleteMaterial(${m.id})"><i class="fa-solid fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html || '<tr><td colspan="7" class="text-center">No materials found.</td></tr>';
            } else {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Failed to load materials: ${json.message || 'Error'}</td></tr>`;
            }
        } catch (err) {
            console.error('Materials Load Error:', err);
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Connection error.</td></tr>';
        }

        // Also load stocks
        loadMaterialStocksData();
    };

    window.loadMaterialStocksData = async function() {
        const tbody = document.getElementById('material-stocks-tbody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="9" class="text-center"><i class="fa-solid fa-spinner fa-spin"></i> Loading stocks...</td></tr>';

        try {
            const res = await window.apiFetch('/api/admin/material-stocks');
            if (!res || !res.ok) return;
            const json = await res.json();
            
            if (json.data) {
                let html = '';
                json.data.forEach(s => {
                    const userName = s.user ? `<strong>${s.user.name}</strong> (${s.user.email})` : '<span class="text-muted">None</span>';
                    const matName = s.material ? `<strong>${s.material.name}</strong> (${s.material.unit})` : '<span class="text-muted">General</span>';
                    const amountText = s.type === 'credit' ? `+ ₹ ${parseFloat(s.amount).toLocaleString()}` : `- ₹ ${parseFloat(s.amount).toLocaleString()}`;
                    const amountClass = s.type === 'credit' ? 'text-success' : 'text-danger';
                    const statusClass = s.status === 'success' ? 'badge-success' : (s.status === 'pending' ? 'badge-warning' : 'badge-danger');
                    const dateStr = s.created_at ? new Date(s.created_at).toLocaleString() : 'N/A';

                    html += `
                        <tr>
                            <td>${s.id}</td>
                            <td>${userName}</td>
                            <td>${matName}</td>
                            <td>${s.title}</td>
                            <td class="${amountClass}"><strong>${amountText}</strong></td>
                            <td><span class="badge ${s.type === 'credit' ? 'badge-info' : 'badge-warning'}">${s.type.toUpperCase()}</span></td>
                            <td><span class="badge ${statusClass}">${s.status.toUpperCase()}</span></td>
                            <td>${dateStr}</td>
                            <td>
                                <button class="btn-secondary" onclick="openEditMaterialStockModal(${s.id})"><i class="fa-solid fa-edit"></i> Edit</button>
                                <button class="btn-secondary text-danger" onclick="deleteMaterialStock(${s.id})"><i class="fa-solid fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html || '<tr><td colspan="8" class="text-center">No stock transactions found.</td></tr>';
            } else {
                tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Failed to load stocks: ${json.message || 'Error'}</td></tr>`;
            }
        } catch (err) {
            console.error('Material Stocks Load Error:', err);
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-danger">Connection error.</td></tr>';
        }
    };

    // ----- MODAL OPENERS -----
    window.openEditMaterialModal = async function(id) {
        try {
            const res = await window.apiFetch(`/api/admin/materials/${id}`);
            if (!res || !res.ok) return;
            const json = await res.json();
            if (json.data) {
                openModal('edit-material', id);
                document.getElementById('emat_name').value = json.data.name;
                document.getElementById('emat_price').value = json.data.price;
                document.getElementById('emat_unit').value = json.data.unit;
                document.getElementById('emat_status').value = json.data.status;
            } else {
                alert('Error fetching material details: ' + (json.message || 'Error'));
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    };

    window.openEditMaterialStockModal = async function(id) {
        try {
            const res = await window.apiFetch(`/api/admin/material-stocks/${id}`);
            if (!res || !res.ok) return;
            const json = await res.json();
            if (json.data) {
                window.currentStockMaterialId = json.data.material_id || '';
                window.currentStockUserId = json.data.user_id || '';
                openModal('edit-material-stock', id);
                document.getElementById('estock_title').value = json.data.title;
                document.getElementById('estock_amount').value = json.data.amount;
                document.getElementById('estock_type').value = json.data.type;
                document.getElementById('estock_status').value = json.data.status;
            } else {
                alert('Error fetching stock details: ' + (json.message || 'Error'));
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    };

    // ----- DELETERS -----
    window.deleteMaterial = async function(id) {
        if (!confirm("Are you sure you want to delete this material? This will set related stock records to General/None.")) return;
        try {
            const res = await window.apiFetch(`/api/admin/materials/${id}`, {
                method: 'DELETE'
            });
            if (!res) return;
            const json = await res.json();
            if (json.status || res.ok) {
                alert('Success: ' + (json.message || 'Material deleted'));
                loadMaterialsData();
            } else {
                alert('Error: ' + (json.message || 'Failed to delete material'));
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    };

    window.deleteMaterialStock = async function(id) {
        if (!confirm("Are you sure you want to delete this stock transaction record?")) return;
        try {
            const res = await window.apiFetch(`/api/admin/material-stocks/${id}`, {
                method: 'DELETE'
            });
            if (!res) return;
            const json = await res.json();
            if (json.status || res.ok) {
                alert('Success: ' + (json.message || 'Stock record deleted'));
                loadMaterialStocksData();
            } else {
                alert('Error: ' + (json.message || 'Failed to delete stock record'));
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    };
})();
