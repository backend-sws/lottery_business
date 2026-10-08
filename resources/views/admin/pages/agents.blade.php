<!-- Agent Management View -->
<div id="view-agents" class="view-section" style="display:none;">
    
    <!-- Header with uppercase tracking category (Dawadukkan Design) -->
    <div style="margin-bottom: 28px;">
        <p style="font-size: 0.7rem; font-weight: 700; letter-spacing: 0.25em; text-transform: uppercase; margin-bottom: 6px; color: var(--primary);">
            Field Team Operations
        </p>
        <div class="flex-row" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <h2 style="font-size: 1.8rem; font-weight: 700; color: #0f172a; letter-spacing: -0.02em; margin: 0;">Tenant Agents</h2>
            <div class="header-btns" style="display: flex; gap: 12px;">
                <button class="btn-primary" onclick="openModal('create-member', 'agent')"><i class="fa-solid fa-user-plus"></i> Register New Agent</button>
            </div>
        </div>
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 6px;">Monitor and manage your field collection team.</p>
    </div>

    <!-- Agent Metrics Grid (Dawadukkan Design - Value Top, Icon Right, Circle Overlays) -->
    <div class="stats-grid" style="margin-bottom: 28px;">
        <div class="stat-card relative-card">
            <div class="shape-circle-1"></div>
            <div class="shape-circle-2"></div>
            <div class="shape-circle-3"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; position: relative; z-index: 1;">
                <div class="flex-column" style="gap: 2px; flex: 1; min-width: 0;">
                    <h2 id="agent-metric-total" style="font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.1;">--</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Total Agents</span>
                    <div style="display:flex; align-items:center; gap:4px; margin-top: 4px;">
                        <span class="badge badge-neutral" style="padding: 1px 6px; font-size: 0.6rem; border-radius: 4px; font-weight: 700;">Staff</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper" style="background-color: var(--primary-light); color: var(--primary); flex-shrink: 0;">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
            </div>
        </div>

        <div class="stat-card relative-card">
            <div class="shape-circle-1"></div>
            <div class="shape-circle-2"></div>
            <div class="shape-circle-3"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; position: relative; z-index: 1;">
                <div class="flex-column" style="gap: 2px; flex: 1; min-width: 0;">
                    <h2 id="agent-metric-active" style="font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.1;">--</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Active Today</span>
                    <div style="display:flex; align-items:center; gap:4px; margin-top: 4px;">
                        <span class="badge badge-success" style="padding: 1px 6px; font-size: 0.6rem; border-radius: 4px; font-weight: 700;">Live Now</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper" style="background-color: var(--success-bg); color: var(--success); flex-shrink: 0;">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>
        </div>

        <div class="stat-card relative-card">
            <div class="shape-circle-1"></div>
            <div class="shape-circle-2"></div>
            <div class="shape-circle-3"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; position: relative; z-index: 1;">
                <div class="flex-column" style="gap: 2px; flex: 1; min-width: 0;">
                    <h2 id="agent-metric-collections" style="font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.1;">--</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Monthly Collections</span>
                    <div style="display:flex; align-items:center; gap:4px; margin-top: 4px;">
                        <i class="fa-solid fa-circle-check" style="color: var(--success); font-size: 0.7rem;"></i>
                        <span style="font-size: 0.65rem; color: var(--text-muted); font-weight: 600;">Live collections</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper" style="background-color: #eff6ff; color: #2563eb; flex-shrink: 0;">
                    <i class="fa-solid fa-money-bill-trend-up"></i>
                </div>
            </div>
        </div>

        <div class="stat-card relative-card">
            <div class="shape-circle-1"></div>
            <div class="shape-circle-2"></div>
            <div class="shape-circle-3"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; position: relative; z-index: 1;">
                <div class="flex-column" style="gap: 2px; flex: 1; min-width: 0;">
                    <h2 id="agent-metric-performance" style="font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.1;">0%</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Avg. Target Progress</span>
                    <div style="display:flex; align-items:center; gap:4px; margin-top: 4px;">
                        <div id="agent-performance-status" style="color: var(--text-muted); font-size: 0.65rem; font-weight: 700;">
                            <i class="fa-solid fa-chart-simple"></i> --
                        </div>
                    </div>
                </div>
                <div class="stat-icon-wrapper" style="background-color: var(--warning-bg); color: var(--warning); flex-shrink: 0;">
                    <i class="fa-solid fa-award"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab control to toggle between Agent Database and Pending Collections Approval -->
    <div style="display: flex; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
        <button id="btn-tab-agent-list" class="btn-primary" onclick="switchAgentSubTab('list')" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 6px;">
            <i class="fa-solid fa-user-group"></i> Field Agents List
        </button>
        <button id="btn-tab-agent-approvals" class="btn-secondary" onclick="switchAgentSubTab('approvals')" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 6px;">
            <i class="fa-solid fa-square-check"></i> Pending Approvals
        </button>
    </div>

    <!-- SUBTAB 1: Field Agents List -->
    <div id="agent-list-subtab" class="panel-card" style="display: block; margin-bottom: 0;">
        
        <!-- Filters Bar -->
        <div class="filter-bar" style="justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 16px;">
            <div style="display:flex; gap:12px;">
                <button class="btn-secondary" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 6px;" onclick="alert('Filtering by region...')"><i class="fa-solid fa-filter"></i> Filter By Region</button>
                <button class="btn-secondary" style="padding: 8px 16px; font-size: 0.8rem; border-radius: 6px;" onclick="alert('Sorting...')"><i class="fa-solid fa-sort"></i> Sort</button>
            </div>
            <span id="agents-filter-count" class="text-sm text-muted">Showing 0 of 0 agents</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Agent Name</th>
                        <th>Agent ID</th>
                        <th>Assigned Region</th>
                        <th>Today's Collection</th>
                        <th>Target Achievement</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="agents-table-tbody">
                    <!-- Populated dynamically -->
                    <tr>
                        <td colspan="7" class="text-center" style="padding:30px 20px; color:var(--text-muted);">
                            <i class="fa-solid fa-spinner fa-spin" style="font-size:1.2rem; margin-bottom:6px; display:block; opacity:0.5;"></i>
                            Loading agents...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div id="agents-pagination" style="display:flex; justify-content:space-between; align-items:center; margin-top:24px; padding-top:16px; border-top:1px solid var(--border-color);">
            <span class="pagination-info text-sm text-muted">Page 1 of 1</span>
            <div style="display:flex; gap:6px;">
                <button class="btn-secondary pagination-prev" style="padding: 6px 12px; font-size:0.8rem; border-radius: 6px;" disabled><i class="fa-solid fa-chevron-left"></i></button>
                <button class="btn-secondary pagination-next" style="padding: 6px 12px; font-size:0.8rem; border-radius: 6px;" disabled><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </div>

    </div>

    <!-- SUBTAB 2: Pending Approvals (Original Agent Collections approvals view) -->
    <div id="agent-approvals-subtab" class="panel-card" style="display: none; margin-bottom: 0;">
        <!-- Committee Pending Collections -->
        <h3 style="margin-top: 10px; margin-bottom: 15px; color: var(--primary); font-size:0.95rem; text-transform: uppercase;"><i class="fa-solid fa-users-rectangle"></i> Pending Committee Collections</h3>
        <div class="table-responsive" style="margin-bottom: 30px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Agent</th>
                        <th>Member</th>
                        <th>Committee Details</th>
                        <th>Amount (₹)</th>
                        <th>Collected At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="agent-pending-committee-collections-tbody">
                    <!-- Loaded dynamically -->
                </tbody>
            </table>
        </div>

        <!-- Loan Pending Collections -->
        <h3 style="margin-top: 20px; margin-bottom: 15px; color: var(--accent); font-size:0.95rem; text-transform: uppercase;"><i class="fa-solid fa-hand-holding-dollar"></i> Pending Loan Collections</h3>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Agent</th>
                        <th>Member</th>
                        <th>Loan Details</th>
                        <th>Amount (₹)</th>
                        <th>Collected At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="agent-pending-loan-collections-tbody">
                    <!-- Loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function switchAgentSubTab(tab) {
        const listBtn = document.getElementById('btn-tab-agent-list');
        const approvalsBtn = document.getElementById('btn-tab-agent-approvals');
        const listContent = document.getElementById('agent-list-subtab');
        const approvalsContent = document.getElementById('agent-approvals-subtab');

        if (tab === 'list') {
            listBtn.className = 'btn-primary';
            approvalsBtn.className = 'btn-secondary';
            listContent.style.display = 'block';
            approvalsContent.style.display = 'none';
        } else {
            listBtn.className = 'btn-secondary';
            approvalsBtn.className = 'btn-primary';
            listContent.style.display = 'none';
            approvalsContent.style.display = 'block';
        }
    }
    
    function switchAgentModalTab(tab) {
        const detailsBtn = document.getElementById('tab-btn-agent-details');
        const targetsBtn = document.getElementById('tab-btn-agent-targets');
        const detailsContent = document.getElementById('tab-content-agent-details');
        const targetsContent = document.getElementById('tab-content-agent-targets');

        if (!detailsBtn || !targetsBtn || !detailsContent || !targetsContent) return;

        if (tab === 'details') {
            detailsBtn.className = 'btn-primary';
            targetsBtn.className = 'btn-secondary';
            detailsContent.style.display = 'block';
            targetsContent.style.display = 'none';
        } else {
            detailsBtn.className = 'btn-secondary';
            targetsBtn.className = 'btn-primary';
            detailsContent.style.display = 'none';
            targetsContent.style.display = 'block';
        }
    }

    async function updateAgentDetails(event, agentId) {
        event.preventDefault();
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Updating...';

        const payload = {
            name: document.getElementById('agent_detail_name').value.trim(),
            email: document.getElementById('agent_detail_email').value.trim(),
            phone: document.getElementById('agent_detail_phone').value.trim(),
            address: document.getElementById('agent_detail_address').value.trim()
        };

        const pass = document.getElementById('agent_detail_password').value;
        if (pass && pass.trim().length > 0) {
            if (pass.length < 8) {
                alert("Password must be at least 8 characters long.");
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                return;
            }
            payload.password = pass;
        }

        try {
            const res = await fetch(`/api/admin/users/${agentId}`, {
                method: 'PUT',
                headers: getHeaders(),
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (res.ok && (data.status === true || data.status === 'success' || data.data)) {
                alert("Agent details updated successfully!");
                if (typeof loadAgentsList === 'function') {
                    loadAgentsList();
                }
                // Update live modal display elements
                const nameDisplay = document.getElementById('modal-agent-name-display');
                if (nameDisplay) nameDisplay.textContent = payload.name;
                const phoneDisplay = document.getElementById('modal-agent-phone-display');
                if (phoneDisplay) phoneDisplay.innerHTML = `<i class="fa-solid fa-phone" style="color:var(--primary); margin-right:4px;"></i> ${payload.phone}`;
                const emailDisplay = document.getElementById('modal-agent-email-display');
                if (emailDisplay) emailDisplay.innerHTML = `<i class="fa-solid fa-envelope" style="color:var(--primary); margin-right:4px;"></i> ${payload.email}`;
                const addressDisplay = document.getElementById('modal-agent-address-display');
                if (addressDisplay) addressDisplay.innerHTML = `<i class="fa-solid fa-location-dot" style="color:var(--primary); margin-right:4px;"></i> ${payload.address}`;
                
                modalTitle.textContent = `${payload.name} (Agent #${agentId})`;
            } else {
                alert(data.message || "Failed to update agent details. Please check the inputs.");
            }
        } catch (err) {
            console.error("Error updating agent:", err);
            alert("Error updating agent details: " + err.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }
    window.updateAgentDetails = updateAgentDetails;
    
    function openAgentManageModal(id, name = '') {
        const modalCard = modal.querySelector('.modal-card-new');
        if (modalCard) modalCard.style.maxWidth = '680px';
        modal.style.display = 'flex';
        
        // Find existing agent in cached list if available
        let cached = (window.currentAgentsList || []).find(a => a.id == id) || {};
        const agentName = name || cached.name || 'Agent';
        const agentEmail = cached.email || '';
        const agentPhone = cached.phone || '';
        const agentAddress = cached.address || '';
        const todayCollection = cached.today_collection || '₹0';
        const targetProgress = cached.target_progress || 0;
        const statusText = cached.status || 'Active';
        const isOffline = statusText.toLowerCase() === 'offline';
        const badgeClass = isOffline ? 'badge-neutral' : 'badge-success';
        const statusDot = isOffline ? '#94a3b8' : '#10b981';
        
        let formattedDate = '--';
        if (cached.created_at) {
            try {
                formattedDate = new Date(cached.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
            } catch(e) {}
        }

        modalTitle.innerHTML = `<i class="fa-solid fa-user-gear" style="color:var(--primary); margin-right:8px;"></i> Manage Agent <span style="font-size:0.85rem; color:var(--text-muted); font-weight:500;">(#AGT-${id})</span>`;
        
        modalBody.innerHTML = `
            <div class="agent-manage-wrapper" style="display:flex; flex-direction:column; gap:16px;">
                <!-- Profile Banner Card -->
                <div style="background: linear-gradient(135deg, rgba(0, 72, 56, 0.05) 0%, rgba(16, 185, 129, 0.05) 100%); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="position: relative;">
                            <img id="modal-agent-avatar" src="https://ui-avatars.com/api/?name=${encodeURIComponent(agentName)}&background=004838&color=fff&size=52&rounded=true&bold=true" alt="Avatar" style="width: 52px; height: 52px; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                            <span id="modal-agent-status-indicator" style="position: absolute; bottom: 1px; right: 1px; width: 12px; height: 12px; border-radius: 50%; border: 2px solid #fff; background-color: ${statusDot};"></span>
                        </div>
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <h3 id="modal-agent-name-display" style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a;">${agentName}</h3>
                                <span id="modal-agent-badge" class="badge ${badgeClass}" style="padding: 2px 8px; font-size: 0.65rem;">${statusText}</span>
                                <span style="font-size: 0.72rem; font-weight: 700; color: var(--primary); background: var(--primary-light); padding: 2px 8px; border-radius: 4px;">#AGT-${id}</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 14px; margin-top: 5px; font-size: 0.78rem; color: #475569; flex-wrap: wrap;">
                                <span id="modal-agent-phone-display"><i class="fa-solid fa-phone" style="color: var(--primary); margin-right: 4px;"></i> ${agentPhone || 'Loading phone...'}</span>
                                <span id="modal-agent-email-display"><i class="fa-solid fa-envelope" style="color: var(--primary); margin-right: 4px;"></i> ${agentEmail || 'Loading email...'}</span>
                                <span id="modal-agent-address-display"><i class="fa-solid fa-location-dot" style="color: var(--primary); margin-right: 4px;"></i> ${agentAddress || 'All Regions'}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.72rem; font-weight: 700; background: #ffffff; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border-color); color: var(--primary);">
                            <i class="fa-solid fa-user-tie"></i> Field Operations
                        </span>
                    </div>
                </div>

                <!-- Quick KPI Stats Grid -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px; text-align: center;">
                        <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block;">Today's Collection</span>
                        <strong id="modal-agent-today-coll" style="font-size: 1.1rem; font-weight: 700; color: #10b981;">${todayCollection}</strong>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px; text-align: center;">
                        <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block;">Target Progress</span>
                        <strong id="modal-agent-target-prog" style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">${targetProgress}%</strong>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 10px 12px; text-align: center;">
                        <span style="font-size: 0.68rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; display: block;">Registered On</span>
                        <strong id="modal-agent-created-at" style="font-size: 0.85rem; font-weight: 700; color: #0f172a;">${formattedDate}</strong>
                    </div>
                </div>

                <!-- Modal Subtabs Navigation -->
                <div style="display: flex; gap: 8px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                    <button type="button" id="tab-btn-agent-details" class="btn-primary" onclick="switchAgentModalTab('details')" style="padding: 7px 16px; font-size: 0.8rem; border-radius: 6px;">
                        <i class="fa-solid fa-id-card"></i> Agent Details & Edit
                    </button>
                    <button type="button" id="tab-btn-agent-targets" class="btn-secondary" onclick="switchAgentModalTab('targets')" style="padding: 7px 16px; font-size: 0.8rem; border-radius: 6px;">
                        <i class="fa-solid fa-bullseye"></i> Assign Monthly Target
                    </button>
                </div>

                <!-- TAB 1: Agent Details (Overview & Edit) -->
                <div id="tab-content-agent-details" style="display: block;">
                    <form id="agent-details-form" onsubmit="updateAgentDetails(event, ${id})">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;">
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">Full Name</label>
                                <div class="input-field" style="padding: 8px 12px;">
                                    <i class="fa-solid fa-user" style="color: var(--text-muted); margin-right: 8px; font-size: 0.85rem;"></i>
                                    <input type="text" id="agent_detail_name" value="${agentName}" required placeholder="Agent full name" style="width: 100%; border: none; background: transparent; font-size: 0.85rem; outline: none;">
                                </div>
                            </div>
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">Mobile / Phone Number</label>
                                <div class="input-field" style="padding: 8px 12px;">
                                    <i class="fa-solid fa-phone" style="color: var(--text-muted); margin-right: 8px; font-size: 0.85rem;"></i>
                                    <input type="text" id="agent_detail_phone" value="${agentPhone}" required placeholder="e.g. 9876543210" style="width: 100%; border: none; background: transparent; font-size: 0.85rem; outline: none;">
                                </div>
                            </div>
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">Email Address</label>
                                <div class="input-field" style="padding: 8px 12px;">
                                    <i class="fa-solid fa-envelope" style="color: var(--text-muted); margin-right: 8px; font-size: 0.85rem;"></i>
                                    <input type="email" id="agent_detail_email" value="${agentEmail}" required placeholder="agent@example.com" style="width: 100%; border: none; background: transparent; font-size: 0.85rem; outline: none;">
                                </div>
                            </div>
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">Assigned Region / Address</label>
                                <div class="input-field" style="padding: 8px 12px;">
                                    <i class="fa-solid fa-location-dot" style="color: var(--text-muted); margin-right: 8px; font-size: 0.85rem;"></i>
                                    <input type="text" id="agent_detail_address" value="${agentAddress}" required placeholder="e.g. North Sector, Main Market" style="width: 100%; border: none; background: transparent; font-size: 0.85rem; outline: none;">
                                </div>
                            </div>
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">Assigned System Role</label>
                                <div class="input-field" style="padding: 8px 12px; background: #f1f5f9; cursor: not-allowed;">
                                    <i class="fa-solid fa-shield-halved" style="color: var(--text-muted); margin-right: 8px; font-size: 0.85rem;"></i>
                                    <input type="text" value="Field Agent" readonly style="width: 100%; border: none; background: transparent; font-size: 0.85rem; color: #475569; cursor: not-allowed; outline: none;">
                                </div>
                            </div>
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">Update Password (Optional)</label>
                                <div class="input-field" style="padding: 8px 12px;">
                                    <i class="fa-solid fa-lock" style="color: var(--text-muted); margin-right: 8px; font-size: 0.85rem;"></i>
                                    <input type="password" id="agent_detail_password" placeholder="Leave empty to keep current password" style="width: 100%; border: none; background: transparent; font-size: 0.85rem; outline: none;">
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn-primary" style="padding: 8px 20px; font-size: 0.82rem; border-radius: 6px;">
                                <i class="fa-solid fa-floppy-disk"></i> Save & Update Agent Details
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: Assign Target Form -->
                <div id="tab-content-agent-targets" style="display: none;">
                    <div id="modal-agent-active-target-box" style="margin-bottom: 14px; padding: 12px; border-radius: 8px; background: #fffbeb; border: 1px solid #fef3c7; display: none;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <strong style="font-size: 0.82rem; color: #92400e; display: block;"><i class="fa-solid fa-bullseye"></i> Current Active Target</strong>
                                <span id="modal-agent-active-target-text" style="font-size: 0.75rem; color: #b45309;">Loading target info...</span>
                            </div>
                            <span class="badge badge-pending" style="font-size: 0.65rem;">Active Target</span>
                        </div>
                    </div>
                    <form id="modal-target-form" onsubmit="submitForm(event, 'assign-agent-target', ${id})">
                        <input type="hidden" id="target_agent_id" value="${id}">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px;">
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">Target Type</label>
                                <select id="target_type" class="filter-select" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 0.85rem; background: #f9fafb;">
                                    <option value="amount">Collection Amount (₹)</option>
                                    <option value="count">Collection Count (Transactions)</option>
                                </select>
                            </div>
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">Target Value</label>
                                <div class="input-field" style="padding: 8px 12px;">
                                    <i class="fa-solid fa-hashtag" style="color: var(--text-muted); margin-right: 8px; font-size: 0.85rem;"></i>
                                    <input type="number" id="target_value" required placeholder="e.g. 50000" style="width: 100%; border: none; background: transparent; font-size: 0.85rem; outline: none;">
                                </div>
                            </div>
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">Start Date</label>
                                <div class="input-field" style="padding: 8px 12px;">
                                    <input type="date" id="target_start" required style="width: 100%; border: none; background: transparent; font-size: 0.85rem; outline: none;">
                                </div>
                            </div>
                            <div class="input-group" style="margin-bottom: 0;">
                                <label style="font-size: 0.78rem; font-weight: 600; margin-bottom: 4px; display: block;">End Date</label>
                                <div class="input-field" style="padding: 8px 12px;">
                                    <input type="date" id="target_end" required style="width: 100%; border: none; background: transparent; font-size: 0.85rem; outline: none;">
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn-primary" style="padding: 8px 20px; font-size: 0.82rem; border-radius: 6px;">
                                <i class="fa-solid fa-bullseye"></i> Assign New Target
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone Footer -->
                <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #fee2e2; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <div>
                        <strong style="color: #ef4444; font-size: 0.82rem; display: block;"><i class="fa-solid fa-triangle-exclamation"></i> Danger Zone</strong>
                        <span style="font-size: 0.74rem; color: var(--text-muted);">Permanently remove this agent account and revoke field permissions</span>
                    </div>
                    <button type="button" class="btn-secondary text-danger" onclick="deleteAgent(${id}, '${(agentName || '').replace(/'/g, "\\'")}')" style="padding: 7px 14px; font-size: 0.8rem; font-weight: 600; background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-trash-can"></i> Delete Agent
                    </button>
                </div>
            </div>
        `;
        
        // Auto-fill target dates for current month
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
        const tStart = document.getElementById('target_start');
        const tEnd = document.getElementById('target_end');
        if (tStart) tStart.value = firstDay;
        if (tEnd) tEnd.value = lastDay;

        // Fetch fresh details from API
        fetch(`/api/admin/members/${id}`, { headers: getHeaders() })
            .then(res => res.json())
            .then(res => {
                const data = res.data || res;
                if (!data || !data.id) return;
                
                if (data.name) {
                    const nEl = document.getElementById('modal-agent-name-display');
                    if (nEl) nEl.textContent = data.name;
                    const inpName = document.getElementById('agent_detail_name');
                    if (inpName) inpName.value = data.name;
                }
                if (data.phone) {
                    const pEl = document.getElementById('modal-agent-phone-display');
                    if (pEl) pEl.innerHTML = `<i class="fa-solid fa-phone" style="color:var(--primary); margin-right:4px;"></i> ${data.phone}`;
                    const inpPhone = document.getElementById('agent_detail_phone');
                    if (inpPhone) inpPhone.value = data.phone;
                }
                if (data.email) {
                    const eEl = document.getElementById('modal-agent-email-display');
                    if (eEl) eEl.innerHTML = `<i class="fa-solid fa-envelope" style="color:var(--primary); margin-right:4px;"></i> ${data.email}`;
                    const inpEmail = document.getElementById('agent_detail_email');
                    if (inpEmail) inpEmail.value = data.email;
                }
                if (data.address) {
                    const aEl = document.getElementById('modal-agent-address-display');
                    if (aEl) aEl.innerHTML = `<i class="fa-solid fa-location-dot" style="color:var(--primary); margin-right:4px;"></i> ${data.address}`;
                    const inpAddr = document.getElementById('agent_detail_address');
                    if (inpAddr) inpAddr.value = data.address;
                }
                if (data.today_collection) {
                    const tcEl = document.getElementById('modal-agent-today-coll');
                    if (tcEl) tcEl.textContent = data.today_collection;
                }
                if (typeof data.target_progress !== 'undefined') {
                    const tpEl = document.getElementById('modal-agent-target-prog');
                    if (tpEl) tpEl.textContent = data.target_progress + '%';
                }
                if (data.created_at) {
                    const caEl = document.getElementById('modal-agent-created-at');
                    if (caEl) {
                        try {
                            caEl.textContent = new Date(data.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
                        } catch(e) {}
                    }
                }
            })
            .catch(err => console.error("Error loading full agent info:", err));

        // Fetch active target info
        fetch(`/api/admin/agents/targets?agent_id=${id}`, { headers: getHeaders() })
            .then(res => res.json())
            .then(res => {
                const targets = res.data?.data || res.data || [];
                const active = Array.isArray(targets) ? targets.find(t => t.status === 'active') : null;
                const box = document.getElementById('modal-agent-active-target-box');
                const text = document.getElementById('modal-agent-active-target-text');
                if (active && box && text) {
                    const valStr = active.target_type === 'amount' ? `₹${parseFloat(active.target_value).toLocaleString('en-IN')}` : `${active.target_value} collections`;
                    text.textContent = `Target: ${valStr} (${active.start_date} to ${active.end_date})`;
                    box.style.display = 'block';
                    
                    // Pre-fill existing target values
                    const tv = document.getElementById('target_value');
                    const tt = document.getElementById('target_type');
                    if (tv) tv.value = active.target_value;
                    if (tt) tt.value = active.target_type;
                }
            })
            .catch(err => console.error("Error loading agent target:", err));
    }
    window.openAgentManageModal = openAgentManageModal;
</script>
