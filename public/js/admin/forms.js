
    // ----- FORM SUBMISSIONS -----
    window.submitForm = async function(e, entity, id = null) {
        e.preventDefault();
        let endpoint = '';
        let bodyData = {};

        try {
            if (entity === 'committees') {
                endpoint = '/api/admin/committees';
                bodyData = {
                    name: document.getElementById('c_name').value,
                    amount: document.getElementById('c_amount').value,
                    total_members: document.getElementById('c_total').value,
                    duration: document.getElementById('c_duration').value,
                    payment_frequency: document.getElementById('c_freq').value,
                    start_date: new Date().toISOString().split('T')[0],
                    end_date: (function() {
                        let d = new Date();
                        let dur = parseInt(document.getElementById('c_duration').value);
                        d.setMonth(d.getMonth() + dur);
                        return d.toISOString().split('T')[0];
                    })(),
                    return_percentage: document.getElementById('c_return').value || 0,
                    status: 'active',
                    trending: document.getElementById('c_trending').checked ? 1 : 0
                };
            } else if (entity === 'edit-committee') {
                endpoint = `/api/admin/committees/${id}`;
                bodyData = {
                    name: document.getElementById('ec_name').value,
                    amount: document.getElementById('ec_amount').value,
                    total_members: document.getElementById('ec_total').value,
                    duration: document.getElementById('ec_duration').value,
                    payment_frequency: document.getElementById('ec_freq').value,
                    start_date: new Date().toISOString().split('T')[0], // Retain current logic or pass original
                    end_date: (function() {
                        let d = new Date();
                        let dur = parseInt(document.getElementById('ec_duration').value);
                        d.setMonth(d.getMonth() + dur);
                        return d.toISOString().split('T')[0];
                    })(),
                    return_percentage: document.getElementById('ec_return').value || 0,
                    status: document.getElementById('ec_status').value,
                    trending: document.getElementById('ec_trending').checked ? 1 : 0
                };
            } else if (entity === 'members') {
                endpoint = '/api/admin/users';
                bodyData = {
                    role: document.getElementById('m_role').value,
                    name: document.getElementById('m_name').value,
                    email: document.getElementById('m_email').value,
                    phone: document.getElementById('m_phone').value,
                    address: document.getElementById('m_address').value,
                    password: document.getElementById('m_pass').value,
                };
            } else if (entity === 'installments') {
                endpoint = '/api/admin/installments/collect';
                bodyData = {
                    user_id: document.getElementById('i_user').value,
                    committee_id: document.getElementById('i_comm').value,
                    amount: document.getElementById('i_amount').value,
                    due_date: document.getElementById('i_due').value,
                    paid_date: new Date().toISOString().split('T')[0],
                    status: 'paid'
                };
            } else if (entity === 'lotteries') {
                endpoint = '/api/admin/lotteries/manual-draw';
                bodyData = {
                    committee_id: document.getElementById('l_comm').value,
                    winner_id: document.getElementById('l_winner').value,
                    draw_date: document.getElementById('l_draw_date').value
                };
            } else if (entity === 'enroll-member') {
                endpoint = `/api/admin/members/${id}/enroll`;
                bodyData = {
                    committee_id: document.getElementById('em_comm_id').value
                };
            } else if (entity === 'assign-agent-target') {
                endpoint = '/api/admin/agents/targets';
                bodyData = {
                    agent_id: document.getElementById('target_agent_id').value,
                    target_type: document.getElementById('target_type').value,
                    target_value: document.getElementById('target_value').value,
                    start_date: document.getElementById('target_start').value,
                    end_date: document.getElementById('target_end').value,
                };
            } else if (entity === 'loans') {
                endpoint = '/api/admin/loans';
                bodyData = new FormData();
                const userType = document.getElementById('ln_user_type').value;
                if (userType === 'existing') {
                    const userId = document.getElementById('ln_user_id').value;
                    if (!userId) {
                        alert('Please select a member first');
                        return;
                    }
                    bodyData.append('user_id', userId);
                } else {
                    bodyData.append('name', document.getElementById('ln_name').value);
                    bodyData.append('email', document.getElementById('ln_email').value);
                    bodyData.append('phone', document.getElementById('ln_phone').value);
                    bodyData.append('password', document.getElementById('ln_pass').value);
                    bodyData.append('address', document.getElementById('ln_address').value);
                    if(document.getElementById('ln_photo').files[0]) bodyData.append('photo', document.getElementById('ln_photo').files[0]);
                    if(document.getElementById('ln_id_proof').files[0]) bodyData.append('id_proof', document.getElementById('ln_id_proof').files[0]);
                    if(document.getElementById('ln_aadhar').files[0]) bodyData.append('aadhar_card', document.getElementById('ln_aadhar').files[0]);
                    if(document.getElementById('ln_pan').files[0]) bodyData.append('pan_card', document.getElementById('ln_pan').files[0]);
                }
                bodyData.append('amount', document.getElementById('ln_amount').value);
                bodyData.append('interest_rate_percent', document.getElementById('ln_rate').value);
                bodyData.append('duration_months', document.getElementById('ln_duration').value);
                bodyData.append('payment_frequency', document.getElementById('ln_freq').value);
            } else if (entity === 'materials') {
                endpoint = '/api/admin/materials';
                bodyData = new FormData();
                bodyData.append('name', document.getElementById('mat_name').value);
                bodyData.append('price', document.getElementById('mat_price').value);
                bodyData.append('unit', document.getElementById('mat_unit').value);
                bodyData.append('status', document.getElementById('mat_status').value);
                if (document.getElementById('mat_image') && document.getElementById('mat_image').files[0]) {
                    bodyData.append('image', document.getElementById('mat_image').files[0]);
                }
                if (document.getElementById('mat_image_url') && document.getElementById('mat_image_url').value) {
                    bodyData.append('image_url', document.getElementById('mat_image_url').value);
                }
            } else if (entity === 'edit-material') {
                endpoint = `/api/admin/materials/${id}`;
                bodyData = new FormData();
                bodyData.append('name', document.getElementById('emat_name').value);
                bodyData.append('price', document.getElementById('emat_price').value);
                bodyData.append('unit', document.getElementById('emat_unit').value);
                bodyData.append('status', document.getElementById('emat_status').value);
                if (document.getElementById('emat_image') && document.getElementById('emat_image').files[0]) {
                    bodyData.append('image', document.getElementById('emat_image').files[0]);
                }
                if (document.getElementById('emat_image_url') && document.getElementById('emat_image_url').value) {
                    bodyData.append('image_url', document.getElementById('emat_image_url').value);
                }
            } else if (entity === 'material-stocks') {
                endpoint = '/api/admin/material-stocks';
                bodyData = {
                    material_id: document.getElementById('stock_mat_id').value || null,
                    user_id: document.getElementById('stock_user_id').value,
                    title: document.getElementById('stock_title').value,
                    amount: document.getElementById('stock_amount').value,
                    type: document.getElementById('stock_type').value,
                    status: document.getElementById('stock_status').value
                };
            } else if (entity === 'edit-material-stock') {
                endpoint = `/api/admin/material-stocks/${id}`;
                bodyData = {
                    material_id: document.getElementById('estock_mat_id').value || null,
                    user_id: document.getElementById('estock_user_id').value,
                    title: document.getElementById('estock_title').value,
                    amount: document.getElementById('estock_amount').value,
                    type: document.getElementById('estock_type').value,
                    status: document.getElementById('estock_status').value
                };
            }

            let options = {
                method: 'POST'
            };
            if (bodyData instanceof FormData) {
                options.body = bodyData;
            } else {
                options.body = JSON.stringify(bodyData);
            }

            // Some entities define their own method
            if (entity === 'edit-committee' || entity === 'edit-material' || entity === 'edit-material-stock') options.method = 'PUT';

            const res = await window.apiFetch(endpoint, options);
            if (!res) return;

            const data = await res.json();
            if (res.ok) {
                // If creating member and a committee ID was specified, automatically enroll them
                if (entity === 'members' && document.getElementById('m_enroll_comm_id') && document.getElementById('m_enroll_comm_id').value) {
                    try {
                        const enrollRes = await window.apiFetch(`/api/admin/members/${data.data.id}/enroll`, {
                            method: 'POST',
                            body: JSON.stringify({ committee_id: document.getElementById('m_enroll_comm_id').value })
                        });
                        if (enrollRes && enrollRes.ok) {
                            alert('Success: Member created and enrolled in committee');
                        } else if (enrollRes) {
                            const errData = await enrollRes.json();
                            alert('Member created, but enrollment failed: ' + (errData.message || 'Error'));
                        }
                    } catch(e) {
                        alert('Member created, but failed to enroll.');
                    }
                } else {
                    alert('Success: ' + (data.message || 'Action completed'));
                }
                
                closeModal();
                handleRoute(); // Refresh current view
            } else {
                alert('Error: ' + (data.message || 'Validation failed'));
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    };

    window.sendDueWarnings = function() {
        window.openNotificationModal('warning');
    };

    window.sendPaymentReminders = function() {
        window.openNotificationModal('reminder');
    };

    window.openNotificationModal = function(type) {
        const title = type === 'warning' ? 'Send Due Warnings' : 'Send Payment Reminders';
        const defaultTitleText = type === 'warning' ? 'Overdue Payment Notice' : 'Upcoming Payment Reminder';
        const defaultMsgText = type === 'warning' 
            ? 'Your installment payment is overdue. Please log in to your dashboard and complete payment immediately to avoid default.'
            : 'This is a friendly reminder that your upcoming installment is due soon. Please check your Janta Community dashboard and ensure payment is made.';

        document.getElementById('modal-title').textContent = title;
        document.getElementById('modal-body').innerHTML = `
            <form id="custom-notification-form" onsubmit="submitCustomNotification(event)">
                <div class="input-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:#374151;">Select Recipient</label>
                    <select id="cn_user_id" class="input-field" style="width:100%; border:none; background:transparent;" required>
                        ${type === 'warning' 
                            ? '<option value="all_due" selected>⚠️ All Overdue Members (Bulk)</option>'
                            : '<option value="all_pending" selected>⏰ All Pending Members (Bulk)</option>'}
                    </select>
                </div>
                <div class="input-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:#374151;">Notification Title</label>
                    <div class="input-field"><input type="text" id="cn_title" value="${defaultTitleText}" required style="width:100%; border:none; background:transparent; outline:none;"></div>
                </div>
                <div class="input-group" style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:6px; font-size:0.85rem; font-weight:600; color:#374151;">Message Content</label>
                    <textarea id="cn_message" rows="4" class="input-field" style="width:100%; height:80px; border:none; background:transparent; padding:10px; resize:none;" required>${defaultMsgText}</textarea>
                </div>
                <button type="submit" class="btn-primary" style="width:100%; height:42px; border-radius:8px; font-weight:700; cursor:pointer;"><i class="fa-solid fa-paper-plane"></i> Send Notification</button>
            </form>
        `;
        document.getElementById('global-modal').style.display = 'flex';

        // Load specific members in case they want to select an individual
        setTimeout(async () => {
            try {
                const res = await window.apiFetch('/api/admin/members?paginate=200');
                if (!res || !res.ok) return;
                const json = await res.json();
                const members = json.data?.data || json.data || [];
                const select = document.getElementById('cn_user_id');
                if (select && members.length > 0) {
                    members.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m.id;
                        opt.textContent = `${m.name} (#${m.id} - ${m.phone || m.email})`;
                        select.appendChild(opt);
                    });
                }
            } catch(e) {
                console.error("Error loading members:", e);
            }
        }, 100);
    };

    window.submitCustomNotification = async function(event) {
        event.preventDefault();
        const userId = document.getElementById('cn_user_id').value;
        const title = document.getElementById('cn_title').value;
        const message = document.getElementById('cn_message').value;

        try {
            const res = await window.apiFetch('/api/admin/notifications/send-custom', {
                method: 'POST',
                body: JSON.stringify({
                    user_id: userId,
                    title: title,
                    message: message
                })
            });
            if (!res) return;
            const data = await res.json();
            if (data.status === true || data.status === 'success') {
                alert(data.message || 'Notification sent successfully!');
                closeModal();
            } else {
                alert(data.message || 'Failed to send notification.');
            }
        } catch (e) {
            console.error(e);
            alert('Error: ' + e.message);
        }
    };

    // ----- AUTHENTICATION -----
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;

        try {
            const res = await fetch('/api/admin/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                const newToken = data.data.token;
                localStorage.setItem('admin_token', newToken);
                if (typeof authToken !== 'undefined') authToken = newToken;
                if (data.data.user) {
                    localStorage.setItem('admin_user', JSON.stringify(data.data.user));
                }
                errorMsg.style.display = 'none';
                showApp();
                // Load community filter options now that session is active
                if (typeof loadCommunityFilterOptions === 'function') {
                    loadCommunityFilterOptions();
                }
            } else {
                errorMsg.textContent = data.message || "Invalid credentials";
                errorMsg.style.display = 'block';
            }
        } catch (err) {
            errorMsg.textContent = "Server connection error.";
            errorMsg.style.display = 'block';
        }
    });

    logoutBtn.addEventListener('click', async () => {
        try {
            const currentToken = window.getAuthToken ? window.getAuthToken() : localStorage.getItem('admin_token');
            if (currentToken) {
                await fetch('/api/admin/logout', {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${currentToken}`, 'Accept': 'application/json' }
                });
            }
        } catch(e) {}
        if (window.routeAbortController) {
            try { window.routeAbortController.abort(); } catch(e) {}
            window.routeAbortController = null;
        }
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
        if (typeof authToken !== 'undefined') authToken = null;
        window.location.hash = '';
        showLogin();
    });