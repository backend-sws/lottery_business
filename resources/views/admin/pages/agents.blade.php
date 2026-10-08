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
    
    function openAgentManageModal(id, name = '') {
        modal.style.display = 'flex';
        modalTitle.textContent = (name ? name + ' ' : '') + '(Agent #' + id + ')';
        modalBody.innerHTML = `
            <form id="modal-form" onsubmit="submitForm(event, 'assign-agent-target', ${id})">
                <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:15px;">Set up monthly targets for this field agent.</p>
                <div class="input-group">
                    <label>Agent User ID</label>
                    <div class="input-field"><input type="number" id="target_agent_id" value="${id}" readonly required></div>
                </div>
                <div class="input-group">
                    <label>Target Type</label>
                    <select id="target_type" class="filter-select" style="width:100%;">
                        <option value="amount">Amount (₹)</option>
                        <option value="count">Collection Count</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Target Value</label>
                    <div class="input-field"><input type="number" id="target_value" required placeholder="e.g. 50000"></div>
                </div>
                <div class="input-group">
                    <label>Start Date</label>
                    <div class="input-field"><input type="date" id="target_start" required></div>
                </div>
                <div class="input-group">
                    <label>End Date</label>
                    <div class="input-field"><input type="date" id="target_end" required></div>
                </div>
                <button type="submit" class="btn-primary" style="width:100%; border-radius: 6px;">Assign Target</button>
            </form>
            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #fee2e2; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #ef4444; font-size: 0.85rem; display: block;">Danger Zone</strong>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Permanently remove this agent account</span>
                </div>
                <button type="button" class="btn-secondary text-danger" onclick="deleteAgent(${id}, '${name ? name.replace(/'/g, "\\'") : ''}')" style="padding: 7px 14px; font-size: 0.8rem; font-weight: 600; background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Delete Agent
                </button>
            </div>
        `;
        
        // Auto-fill dates
        const now = new Date();
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
        document.getElementById('target_start').value = firstDay;
        document.getElementById('target_end').value = lastDay;
    }
</script>
