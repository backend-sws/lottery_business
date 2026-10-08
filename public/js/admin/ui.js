
    function updateAdminProfileHeader() {
        try {
            const userStr = localStorage.getItem('admin_user');
            if (userStr) {
                const user = JSON.parse(userStr);
                const nameEl = document.getElementById('topbar-user-name');
                const roleEl = document.getElementById('topbar-user-role');
                const avatarEl = document.getElementById('topbar-user-avatar');
                if (nameEl && user.name) nameEl.textContent = user.name;
                if (roleEl) roleEl.textContent = (user.role || 'Super Admin').toUpperCase();
                if (avatarEl && user.name) {
                    avatarEl.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=004d40&color=fff&bold=true`;
                }
            }
        } catch(e) {}
    }

    // ----- UI TOGGLES -----
    function showApp() {
        loginScreen.style.display = 'none';
        appShell.style.display = 'flex';
        updateAdminProfileHeader();
        handleRoute(); // Load current route
    }

    function showLogin() {
        appShell.style.display = 'none';
        loginScreen.style.display = 'flex';
    }

    window.openModal = function(type, id = null) {
        modal.style.display = 'flex';
        let html = '';

        if (type === 'create-committee') {
            modalTitle.textContent = 'New Committee';
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'committees')">
                    <div class="input-group"><label>Name</label><div class="input-field"><input type="text" id="c_name" required></div></div>
                    <div class="input-group"><label>Amount (₹)</label><div class="input-field"><input type="number" id="c_amount" required></div></div>
                    <div class="input-group"><label>Total Members Limit</label><div class="input-field"><input type="number" id="c_total" required></div></div>
                    <div class="input-group"><label>Duration (Months)</label><div class="input-field"><input type="number" id="c_duration" required></div></div>
                    <div class="input-group"><label>Frequency</label>
                        <select id="c_freq" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly" selected>Monthly</option>
                        </select>
                    </div>
                    <div class="input-group"><label>Return Percentage (%)</label><div class="input-field"><input type="number" step="0.01" min="0" max="100" id="c_return" placeholder="e.g. 10.50"></div></div>
                    <div class="input-group" style="display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-top:1px solid var(--border-color); margin-top:8px;">
                        <div>
                            <label style="margin-bottom:2px; font-weight:700;"><i class="fa-solid fa-fire" style="color:#f59e0b; margin-right:4px;"></i> Mark as Trending</label>
                            <p style="font-size:0.72rem; color:var(--text-muted); margin:0;">Show this committee prominently on the mobile app</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="c_trending">
                            <span class="toggle-track"></span>
                            <span class="toggle-knob"></span>
                        </label>
                    </div>
                    <button type="submit" class="btn-primary">Create Committee</button>
                </form>
            `;
        } 
        else if (type === 'create-member') {
            modalTitle.textContent = id === 'agent' ? 'New Agent' : (id === 'member' ? 'New Member' : 'New User / Agent');
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'members')">
                    <div class="input-group"><label>Role</label>
                        <select id="m_role" class="input-field" style="width:100%; border:none; background:transparent;">
                            ${id === 'agent' 
                                ? '<option value="agent" selected>Agent</option>' 
                                : (id === 'member'
                                    ? '<option value="member" selected>Member</option>'
                                    : `
                                        <option value="member" selected>Member</option>
                                        <option value="agent">Agent</option>
                                      `
                                  )
                            }
                        </select>
                    </div>
                    <div class="input-group"><label>Full Name</label><div class="input-field"><input type="text" id="m_name" required></div></div>
                    <div class="input-group"><label>Email</label><div class="input-field"><input type="email" id="m_email" required></div></div>
                    <div class="input-group"><label>Phone</label><div class="input-field"><input type="text" id="m_phone"></div></div>
                    <div class="input-group"><label>Address</label><div class="input-field"><input type="text" id="m_address"></div></div>
                    <div class="input-group"><label>Password</label><div class="input-field"><input type="password" id="m_pass" required></div></div>
                    <div class="input-group"><label>Enroll in Committee ID (Optional)</label><div class="input-field"><input type="number" id="m_enroll_comm_id"></div></div>
                    <button type="submit" class="btn-primary">Create User</button>
                </form>
            `;
        }
        else if (type === 'enroll-member') {
            modalTitle.textContent = 'Enroll Member in Committee';
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'enroll-member', ${id})">
                    <div class="input-group"><label>Committee ID</label><div class="input-field"><input type="number" id="em_comm_id" required></div></div>
                    <button type="submit" class="btn-primary">Enroll Member</button>
                </form>
            `;
        }
        else if (type === 'collect-installment') {
            modalTitle.textContent = 'Collect Payment';
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'installments')">
                    <div class="input-group">
                        <label>Select Member</label>
                        <select id="i_user" onchange="updateCommitteeDropdown(this.value)" class="input-field" style="width:100%; border:none; background:transparent;" required>
                            <option value="">-- Choose Member --</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Select Committee</label>
                        <select id="i_comm" onchange="updateAmountField()" class="input-field" style="width:100%; border:none; background:transparent;" required>
                            <option value="">-- Choose Committee --</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Amount Collected (₹)</label>
                        <div class="input-field"><input type="number" id="i_amount" required></div>
                    </div>
                    <div class="input-group">
                        <label>Due Date</label>
                        <select id="i_due" class="input-field" style="width:100%; border:none; background:transparent;" required>
                            <option value="">-- Choose Due Date --</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Record Payment</button>
                </form>
            `;
            setTimeout(async () => {
                try {
                    const res = await fetch('/api/admin/members?paginate=200', { headers: getHeaders() });
                    const json = await res.json();
                    const members = json.data?.data || json.data || [];
                    const select = document.getElementById('i_user');
                    if (select && members.length > 0) {
                        members.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value = m.id;
                            opt.textContent = `${m.name} (#${m.id} - ${m.phone || m.email})`;
                            select.appendChild(opt);
                        });
                        window.installmentMembersList = members;
                    }
                } catch(e) {
                    console.error("Error loading members:", e);
                }
            }, 100);
        }
        else if (type === 'edit-committee') {
            modalTitle.textContent = 'Edit Committee #' + id;
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'edit-committee', ${id})">
                    <div class="input-group"><label>Name</label><div class="input-field"><input type="text" id="ec_name" required></div></div>
                    <div class="input-group"><label>Amount (₹)</label><div class="input-field"><input type="number" id="ec_amount" required></div></div>
                    <div class="input-group"><label>Total Members Limit</label><div class="input-field"><input type="number" id="ec_total" required></div></div>
                    <div class="input-group"><label>Duration (Months)</label><div class="input-field"><input type="number" id="ec_duration" required></div></div>
                    <div class="input-group"><label>Frequency</label>
                        <select id="ec_freq" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="input-group"><label>Return Percentage (%)</label><div class="input-field"><input type="number" step="0.01" min="0" max="100" id="ec_return"></div></div>
                    <div class="input-group"><label>Status</label>
                        <select id="ec_status" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="defaulted">Defaulted</option>
                        </select>
                    </div>
                    <div class="input-group" style="display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-top:1px solid var(--border-color); margin-top:8px;">
                        <div>
                            <label style="margin-bottom:2px; font-weight:700;"><i class="fa-solid fa-fire" style="color:#f59e0b; margin-right:4px;"></i> Mark as Trending</label>
                            <p style="font-size:0.72rem; color:var(--text-muted); margin:0;">Show this committee prominently on the mobile app</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="ec_trending">
                            <span class="toggle-track"></span>
                            <span class="toggle-knob"></span>
                        </label>
                    </div>
                    <button type="submit" class="btn-primary">Update Committee</button>
                </form>
            `;
        }
        else if (type === 'draw-lottery') {
            modalTitle.textContent = 'Draw Lottery Winner';
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'lotteries')">
                    <div class="input-group"><label>Committee ID</label><div class="input-field"><input type="number" id="l_comm" required></div></div>
                    <div class="input-group"><label>Winner (User) ID</label><div class="input-field"><input type="number" id="l_winner" required></div></div>
                    <div class="input-group"><label>Draw Date</label><div class="input-field"><input type="date" id="l_draw_date" required value="${new Date().toISOString().split('T')[0]}"></div></div>
                    <button type="submit" class="btn-primary">Save Draw Winner <i class="fa-solid fa-save"></i></button>
                </form>
            `;
        }

        else if (type === 'create-loan') {
            modalTitle.textContent = 'Create New Loan';
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'loans')">
                    <div class="flex-wrap-gap">
                        <div class="flex-1">
                            <h3 style="margin-bottom:10px; font-size:1rem;">Borrower Details</h3>
                            <div class="input-group">
                                <label>Borrower Type</label>
                                <select id="ln_user_type" class="input-field" style="width:100%; border:none; background:transparent;" onchange="toggleBorrowerFields()">
                                    <option value="existing" selected>Existing Member</option>
                                    <option value="new">Create New Member</option>
                                </select>
                            </div>
                            
                            <!-- Existing User Select -->
                            <div class="input-group" id="ln_existing_user_group">
                                <label>Select User</label>
                                <select id="ln_user_id" class="input-field" style="width:100%; border:none; background:transparent;" required>
                                    <option value="">Loading members...</option>
                                </select>
                            </div>

                            <!-- New User Fields (hidden by default) -->
                            <div id="ln_new_user_fields" style="display:none;">
                                <div class="input-group"><label>Full Name</label><div class="input-field"><input type="text" id="ln_name"></div></div>
                                <div class="input-group"><label>Email</label><div class="input-field"><input type="email" id="ln_email"></div></div>
                                <div class="input-group"><label>Phone</label><div class="input-field"><input type="text" id="ln_phone"></div></div>
                                <div class="input-group"><label>Password</label><div class="input-field"><input type="password" id="ln_pass"></div></div>
                                <div class="input-group"><label>Address</label><div class="input-field"><input type="text" id="ln_address"></div></div>
                                
                                <h3 style="margin-top:15px; margin-bottom:10px; font-size:1rem;">KYC Documents</h3>
                                <div class="input-group"><label>Photo</label><div class="input-field"><input type="file" id="ln_photo" accept="image/*"></div></div>
                                <div class="input-group"><label>ID Proof</label><div class="input-field"><input type="file" id="ln_id_proof" accept=".jpg,.png,.pdf"></div></div>
                                <div class="input-group"><label>Aadhar Card</label><div class="input-field"><input type="file" id="ln_aadhar" accept=".jpg,.png,.pdf"></div></div>
                                <div class="input-group"><label>PAN Card</label><div class="input-field"><input type="file" id="ln_pan" accept=".jpg,.png,.pdf"></div></div>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 style="margin-bottom:10px; font-size:1rem;">Loan Terms</h3>
                            <div class="input-group"><label>Principal Amount (₹)</label><div class="input-field"><input type="number" id="ln_amount" required></div></div>
                            <div class="input-group"><label>Interest Rate (%)</label><div class="input-field"><input type="number" step="0.01" id="ln_rate" required placeholder="e.g. 12.5"></div></div>
                            <div class="input-group"><label>Duration (Months)</label><div class="input-field"><input type="number" id="ln_duration" required></div></div>
                            <div class="input-group"><label>Payment Frequency</label>
                                <select id="ln_freq" class="input-field" style="width:100%; border:none; background:transparent;">
                                    <option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly" selected>Monthly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top:20px; width:100%;">Create Loan & Generate EMIs</button>
                </form>
            `;

            window.toggleBorrowerFields = function() {
                const userType = document.getElementById('ln_user_type').value;
                const existingGroup = document.getElementById('ln_existing_user_group');
                const newFields = document.getElementById('ln_new_user_fields');
                const existingSelect = document.getElementById('ln_user_id');
                
                const newFieldsIds = ['ln_name', 'ln_email', 'ln_phone', 'ln_pass', 'ln_address'];
                
                if (userType === 'existing') {
                    existingGroup.style.display = 'block';
                    newFields.style.display = 'none';
                    if (existingSelect) existingSelect.required = true;
                    newFieldsIds.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.required = false;
                    });
                } else {
                    existingGroup.style.display = 'none';
                    newFields.style.display = 'block';
                    if (existingSelect) existingSelect.required = false;
                    newFieldsIds.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.required = true;
                    });
                }
            };

            setTimeout(async () => {
                try {
                    const res = await fetch('/api/admin/members', { headers: getHeaders() });
                    const data = await res.json();
                    const membersList = Array.isArray(data?.data?.data)
                        ? data.data.data
                        : (Array.isArray(data?.data)
                            ? data.data
                            : (Array.isArray(data)
                                ? data
                                : []));
                    const select = document.getElementById('ln_user_id');
                    if (select) {
                        select.innerHTML = '<option value="">-- Select Member --</option>';
                        membersList.forEach(m => {
                            const isAgent = m.roles && m.roles.some(r => r.name === 'agent');
                            if (!isAgent) {
                                const opt = document.createElement('option');
                                opt.value = m.id;
                                opt.textContent = `${m.name} (#${m.id}) - ${m.email}`;
                                select.appendChild(opt);
                            }
                        });
                    }
                } catch(e) {
                    console.error("Error loading members:", e);
                }
            }, 100);
        }
        else if (type === 'create-material') {
            modalTitle.textContent = 'New Material';
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'materials')" enctype="multipart/form-data">
                    <div class="input-group"><label>Name</label><div class="input-field"><input type="text" id="mat_name" required></div></div>
                    <div class="input-group"><label>Price (₹)</label><div class="input-field"><input type="number" step="0.01" id="mat_price" required></div></div>
                    <div class="input-group"><label>Unit</label><div class="input-field"><input type="text" id="mat_unit" required placeholder="e.g. kg, m³, per brick, gm"></div></div>
                    <div class="input-group">
                        <label>Image File</label>
                        <div class="input-field">
                            <input type="file" id="mat_image" accept="image/*">
                        </div>
                    </div>
                    <div class="input-group"><label>Or Image URL</label><div class="input-field"><input type="text" id="mat_image_url" placeholder="https://example.com/image.jpg"></div></div>
                    <div class="input-group"><label>Status</label>
                        <select id="mat_status" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Create Material</button>
                </form>
            `;
        }
        else if (type === 'edit-material') {
            modalTitle.textContent = 'Edit Material #' + id;
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'edit-material', ${id})" enctype="multipart/form-data">
                    <div class="input-group"><label>Name</label><div class="input-field"><input type="text" id="emat_name" required></div></div>
                    <div class="input-group"><label>Price (₹)</label><div class="input-field"><input type="number" step="0.01" id="emat_price" required></div></div>
                    <div class="input-group"><label>Unit</label><div class="input-field"><input type="text" id="emat_unit" required></div></div>
                    <div class="input-group">
                        <label>Image File</label>
                        <div class="input-field">
                            <input type="file" id="emat_image" accept="image/*">
                        </div>
                    </div>
                    <div class="input-group"><label>Or Image URL</label><div class="input-field"><input type="text" id="emat_image_url" placeholder="https://example.com/image.jpg"></div></div>
                    <div class="input-group"><label>Status</label>
                        <select id="emat_status" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Update Material</button>
                </form>
            `;
        }
        else if (type === 'create-material-stock') {
            modalTitle.textContent = 'New Stock Transaction';
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'material-stocks')">
                    <div class="input-group"><label>Material (Optional)</label>
                        <select id="stock_mat_id" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="">None / General</option>
                            <!-- Dynamically loaded -->
                        </select>
                    </div>
                    <div class="input-group"><label>Title / Description</label><div class="input-field"><input type="text" id="stock_title" required placeholder="e.g. Cement Bulk Order #827"></div></div>
                    <div class="input-group"><label>Amount (₹)</label><div class="input-field"><input type="number" step="0.01" id="stock_amount" required></div></div>
                    <div class="input-group"><label>Type</label>
                        <select id="stock_type" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="credit" selected>Credit (Add Stock / Sale +)</option>
                            <option value="debit">Debit (Purchase / Use -)</option>
                        </select>
                    </div>
                    <div class="input-group"><label>Status</label>
                        <select id="stock_status" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="success" selected>SUCCESS</option>
                            <option value="pending">PENDING</option>
                            <option value="failed">FAILED</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Record Transaction</button>
                </form>
            `;
            setTimeout(async () => {
                try {
                    const res = await fetch('/api/admin/materials', { headers: getHeaders() });
                    const json = await res.json();
                    const select = document.getElementById('stock_mat_id');
                    if (json.data && select) {
                        json.data.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value = m.id;
                            opt.textContent = `${m.name} (${m.unit})`;
                            select.appendChild(opt);
                        });
                    }
                } catch(e) {}
            }, 100);
        }
        else if (type === 'edit-material-stock') {
            modalTitle.textContent = 'Edit Stock Transaction #' + id;
            html = `
                <form id="modal-form" onsubmit="submitForm(event, 'edit-material-stock', ${id})">
                    <div class="input-group"><label>Material (Optional)</label>
                        <select id="estock_mat_id" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="">None / General</option>
                            <!-- Dynamically loaded -->
                        </select>
                    </div>
                    <div class="input-group"><label>Title / Description</label><div class="input-field"><input type="text" id="estock_title" required></div></div>
                    <div class="input-group"><label>Amount (₹)</label><div class="input-field"><input type="number" step="0.01" id="estock_amount" required></div></div>
                    <div class="input-group"><label>Type</label>
                        <select id="estock_type" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="credit">Credit (Add Stock / Sale +)</option>
                            <option value="debit">Debit (Purchase / Use -)</option>
                        </select>
                    </div>
                    <div class="input-group"><label>Status</label>
                        <select id="estock_status" class="input-field" style="width:100%; border:none; background:transparent;">
                            <option value="success">SUCCESS</option>
                            <option value="pending">PENDING</option>
                            <option value="failed">FAILED</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Update Transaction</button>
                </form>
            `;
            setTimeout(async () => {
                try {
                    const res = await fetch('/api/admin/materials', { headers: getHeaders() });
                    const json = await res.json();
                    const select = document.getElementById('estock_mat_id');
                    if (json.data && select) {
                        json.data.forEach(m => {
                            const opt = document.createElement('option');
                            opt.value = m.id;
                            opt.textContent = `${m.name} (${m.unit})`;
                            select.appendChild(opt);
                        });
                        // Select current option if loaded in edit
                        if (window.currentStockMaterialId) {
                            select.value = window.currentStockMaterialId;
                        }
                    }
                } catch(e) {}
            }, 100);
        }

        modalBody.innerHTML = html;
    };

    window.closeModal = function() {
        modal.style.display = 'none';
        modalBody.innerHTML = '';
    };

    window.openEditCommitteeModal = async function(id) {
        try {
            const res = await fetch(`/api/admin/committees/${id}`, { headers: getHeaders() });
            const data = await res.json();
            openModal('edit-committee', id);
            document.getElementById('ec_name').value = data.name;
            document.getElementById('ec_amount').value = data.amount;
            document.getElementById('ec_total').value = data.total_members;
            document.getElementById('ec_duration').value = data.duration;
            document.getElementById('ec_freq').value = data.payment_frequency;
            document.getElementById('ec_return').value = data.return_percentage || '';
            document.getElementById('ec_status').value = data.status;
            document.getElementById('ec_trending').checked = data.trending == 1 || data.trending === true;
        } catch (err) { console.error("Error fetching committee:", err); }
    };

    window.deleteCommittee = async function(id) {
        if (!confirm("Are you sure you want to delete this committee? This action cannot be undone.")) return;
        try {
            const res = await fetch(`/api/admin/committees/${id}`, {
                method: 'DELETE',
                headers: getHeaders()
            });
            const data = await res.json();
            if (res.ok) {
                alert('Success: ' + (data.message || 'Committee Deleted'));
                handleRoute(); // Refresh current view
            } else {
                alert('Error: ' + (data.message || 'Failed to delete committee'));
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    };

    window.updateCommitteeDropdown = async function(userId) {
        const commSelect = document.getElementById('i_comm');
        const amountInput = document.getElementById('i_amount');
        const dueSelect = document.getElementById('i_due');
        
        if (!commSelect) return;
        commSelect.innerHTML = '<option value="">-- Choose Committee --</option>';
        dueSelect.innerHTML = '<option value="">-- Choose Due Date --</option>';
        amountInput.value = '';
        
        if (!userId) return;
        
        const members = window.installmentMembersList || [];
        const user = members.find(m => m.id == userId);
        if (user && user.committees && Array.isArray(user.committees)) {
            user.committees.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `${c.name} (₹${c.amount})`;
                commSelect.appendChild(opt);
            });
        }
    };
    
    window.updateAmountField = async function() {
        const userId = document.getElementById('i_user').value;
        const committeeId = document.getElementById('i_comm').value;
        const amountInput = document.getElementById('i_amount');
        const dueSelect = document.getElementById('i_due');
        
        if (!amountInput || !dueSelect) return;
        amountInput.value = '';
        dueSelect.innerHTML = '<option value="">-- Choose Due Date --</option>';
        
        if (!userId || !committeeId) return;
        
        const members = window.installmentMembersList || [];
        const user = members.find(m => m.id == userId);
        if (user && user.committees) {
            const committee = user.committees.find(c => c.id == committeeId);
            if (committee) {
                amountInput.value = committee.amount || '';
            }
        }
        
        // Fetch pending installments to populate due_date select
        try {
            const res = await fetch(`/api/admin/installments/pending?user_id=${userId}&committee_id=${committeeId}`, { headers: getHeaders() });
            const json = await res.json();
            const pendingList = json.data || [];
            
            if (pendingList.length > 0) {
                pendingList.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.due_date;
                    opt.textContent = item.due_date;
                    dueSelect.appendChild(opt);
                });
            } else {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- No pending installments --';
                dueSelect.appendChild(opt);
            }
        } catch (e) {
            console.error("Error loading pending due dates:", e);
        }
    };