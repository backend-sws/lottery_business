<!-- Executive Dashboard View -->
<div id="view-dashboard" class="view-section">
    
    <!-- ── Hero Banner (Dawadukkan Design) ─────────────────────────────────── -->
    <div class="hero-banner">
        <!-- Background decorations -->
        <div class="hero-banner-bg">
            <div class="hero-banner-circle-1"></div>
            <div class="hero-banner-circle-2"></div>
            <div class="hero-banner-circle-3"></div>
        </div>

        <div style="position: relative; z-index: 1;">
            <p style="font-size: 0.7rem; font-weight: 700; tracking: 0.15em; text-transform: uppercase; margin-bottom: 4px; color: var(--primary);">
                Management Portal
            </p>
            <h1 style="font-size: 1.6rem; font-weight: 700; color: #111827; letter-spacing: -0.01em;">
                Welcome back, Super Admin 👋
            </h1>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;" id="hero-banner-date-sub">
                <!-- Autoloaded date -->
                Administrator
            </p>
        </div>

        <div class="hero-stats-group">
            <div class="hero-stat-box">
                <div class="hero-stat-box-num" id="hero-modules-count">--</div>
                <div class="hero-stat-box-lbl">Modules</div>
            </div>
            <div class="hero-stat-box">
                <div class="hero-stat-box-num" id="hero-controls-count">--</div>
                <div class="hero-stat-box-lbl">Controls</div>
            </div>
            <div class="hero-stat-box" style="background-color: var(--success-bg); border-color: rgba(16,185,129,0.2);">
                <div class="hero-stat-box-num" id="hero-today-collections" style="color: var(--success); display:flex; align-items:center; justify-content:center; gap:3px;">
                    <i class="fa-solid fa-bolt" style="font-size:0.85rem;"></i> --
                </div>
                <div class="hero-stat-box-lbl" style="color: #065f46;">Collections</div>
            </div>
        </div>
    </div>

    <!-- Stats Grid (Dawadukkan Design - Value Top, Icon Right, Circle Overlays) -->
    <div class="stats-grid" style="margin-bottom: 24px;">
        
        <!-- Card 1: Total Disbursement -->
        <div class="stat-card relative-card">
            <div class="shape-circle-1"></div>
            <div class="shape-circle-2"></div>
            <div class="shape-circle-3"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; position: relative; z-index: 1;">
                <div class="flex-column" style="gap: 2px; flex: 1; min-width: 0;">
                    <h2 id="dash-disbursements" style="font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.1;">--</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Total Disbursement</span>
                    <div style="display:flex; align-items:center; gap:4px; margin-top: 4px;">
                        <i class="fa-solid fa-circle-check" style="color: var(--success); font-size: 0.7rem;"></i>
                        <span id="dash-disbursement-sub" style="font-size: 0.65rem; color: var(--text-muted); font-weight: 600;">Live data</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper" style="background-color: var(--primary-light); color: var(--primary); flex-shrink: 0;">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
        </div>

        <!-- Card 2: Active Members -->
        <div class="stat-card relative-card">
            <div class="shape-circle-1"></div>
            <div class="shape-circle-2"></div>
            <div class="shape-circle-3"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; position: relative; z-index: 1;">
                <div class="flex-column" style="gap: 2px; flex: 1; min-width: 0;">
                    <h2 id="dash-active-members" style="font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.1;">--</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Active Members</span>
                    <div style="display:flex; align-items:center; gap:4px; margin-top: 4px;">
                        <span class="badge" style="background-color: #f3e8ff; color: #7c3aed; padding: 1px 6px; font-size: 0.6rem; border-radius: 4px; font-weight: 700;">Stable</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper" style="background-color: #f3e8ff; color: #7c3aed; flex-shrink: 0;">
                    <i class="fa-solid fa-user-group"></i>
                </div>
            </div>
        </div>

        <!-- Card 3: Total Collections -->
        <div class="stat-card relative-card">
            <div class="shape-circle-1"></div>
            <div class="shape-circle-2"></div>
            <div class="shape-circle-3"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; position: relative; z-index: 1;">
                <div class="flex-column" style="gap: 2px; flex: 1; min-width: 0;">
                    <h2 id="dash-total-collections" style="font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.1;">--</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">Total Collections</span>
                    <div style="display:flex; align-items:center; gap:4px; margin-top: 4px;">
                        <span class="badge" style="background-color: #eff6ff; color: #2563eb; padding: 1px 6px; font-size: 0.6rem; border-radius: 4px; font-weight: 700;">Monthly</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper" style="background-color: #eff6ff; color: #2563eb; flex-shrink: 0;">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <!-- Card 4: KYC Compliance -->
        <div class="stat-card relative-card">
            <div class="shape-circle-1"></div>
            <div class="shape-circle-2"></div>
            <div class="shape-circle-3"></div>
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; position: relative; z-index: 1;">
                <div class="flex-column" style="gap: 2px; flex: 1; min-width: 0;">
                    <h2 id="dash-kyc-compliance" style="font-size: 1.5rem; font-weight: 700; color: #111827; line-height: 1.1;">--</h2>
                    <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600;">KYC Compliance</span>
                    <div style="display:flex; align-items:center; gap:4px; margin-top: 4px;">
                        <span class="badge badge-success" style="padding: 1px 6px; font-size: 0.6rem; border-radius: 4px; font-weight: 700;">Good</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper" style="background-color: #ecfdf5; color: #059669; flex-shrink: 0;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid layout (2-1 column layout) -->
    <div class="layout-grid-2-1">
        
        <!-- Left Side: Trends, Quick Access, and Transactions -->
        <div class="flex-column" style="gap: 20px;">
            
            <!-- Monthly Collection Trends Bar Chart -->
            <div class="panel-card" style="margin-bottom:0;">
                <div class="panel-card-header">
                    <div class="flex-column" style="gap:4px;">
                        <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #374151;">Monthly Collection Trends</h3>
                        <span style="font-size:0.75rem; color:var(--text-muted);">Total sales revenue across the workspace for the last 6 months</span>
                    </div>
                    <select class="filter-select" style="min-width: 130px; padding: 6px 12px; font-size: 0.8rem; border-radius: 6px; border: 1px solid #d1d5db;">
                        <option>Last 6 Months</option>
                        <option>Last 12 Months</option>
                    </select>
                </div>
                <div class="chart-wrapper">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <!-- Dawadukkan Quick Access Section -->
            <div class="panel-card" style="margin-bottom: 0;">
                <div class="panel-card-header">
                    <div class="flex-column" style="gap: 4px;">
                        <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #374151;">Quick Access</h3>
                        <span style="font-size:0.75rem; color:var(--text-muted);">Jump directly to key application modules</span>
                    </div>
                    <i class="fa-solid fa-square-poll-horizontal" style="color: var(--text-muted); opacity: 0.5;"></i>
                </div>
                
                <div class="quick-access-grid">
                    <a href="#members" class="quick-access-tile">
                        <div class="tile-icon-box" style="background-color: var(--primary-light); color: var(--primary);">
                            <i class="fa-solid fa-user-group"></i>
                        </div>
                        <div class="tile-details">
                            <div class="tile-title">Members</div>
                            <div class="tile-desc">Register & manage members</div>
                        </div>
                        <i class="fa-solid fa-arrow-right tile-arrow"></i>
                    </a>

                    <a href="#agents" class="quick-access-tile">
                        <div class="tile-icon-box" style="background-color: #f3e8ff; color: #7c3aed;">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div class="tile-details">
                            <div class="tile-title">Field Agents</div>
                            <div class="tile-desc">Target limits & performance</div>
                        </div>
                        <i class="fa-solid fa-arrow-right tile-arrow"></i>
                    </a>

                    <a href="#kyc" class="quick-access-tile">
                        <div class="tile-icon-box" style="background-color: #fff5f5; color: #ef4444;">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div class="tile-details">
                            <div class="tile-title">KYC Center</div>
                            <div class="tile-desc">Identity & photo submissions</div>
                        </div>
                        <i class="fa-solid fa-arrow-right tile-arrow"></i>
                    </a>

                    <a href="#collections" class="quick-access-tile">
                        <div class="tile-icon-box" style="background-color: #eff6ff; color: #2563eb;">
                            <i class="fa-solid fa-money-bill-trend-up"></i>
                        </div>
                        <div class="tile-details">
                            <div class="tile-title">Collections</div>
                            <div class="tile-desc">Live agent feeds & ratios</div>
                        </div>
                        <i class="fa-solid fa-arrow-right tile-arrow"></i>
                    </a>
                </div>
            </div>

            <!-- Recent Transactions Table -->
            <div class="panel-card" style="margin-bottom:0;">
                <div class="panel-card-header">
                    <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #374151;">Recent Transactions</h3>
                    <a href="#collections" style="font-size: 0.8rem; font-weight: 700; color: var(--primary); text-decoration: none;">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>Reference ID</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="dashboard-transactions-tbody">
                            <tr>
                                <td colspan="5" class="text-center" style="padding: 24px; color: var(--text-muted);">
                                    <i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px;"></i> Loading recent transactions...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Side: Distribution, Recent Activity, Priority Tasks, Platform Status -->
        <div class="flex-column" style="gap: 20px;">
            
            <!-- Member Distribution Doughnut Chart -->
            <div class="panel-card" style="margin-bottom:0;">
                <div class="panel-card-header">
                    <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #374151;">Member Distribution</h3>
                </div>
                <div class="chart-wrapper" style="height: 180px;">
                    <canvas id="doughnutChart"></canvas>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <div style="font-size: 1.35rem; font-weight: 700; color: #0f172a; line-height: 1;" id="dist-total-val">--</div>
                        <div style="font-size: 0.6rem; font-weight: 600; color: #64748b; margin-top: 4px; letter-spacing: 0.05em;">TOTAL</div>
                    </div>
                </div>
                <div class="flex-column gap-12" style="margin-top: 28px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.8rem;">
                        <span style="display:flex; align-items:center; gap:8px; font-weight: 600; color: #374151;"><i class="fa-solid fa-circle" style="color: #004d40; font-size:0.7rem;"></i> Active Members</span>
                        <span class="font-semibold" id="dist-pct-active" style="color: #111827;">--%</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.8rem;">
                        <span style="display:flex; align-items:center; gap:8px; font-weight: 600; color: #374151;"><i class="fa-solid fa-circle" style="color: #10b981; font-size:0.7rem;"></i> Pending / KYC</span>
                        <span class="font-semibold" id="dist-pct-pending" style="color: #111827;">--%</span>
                    </div>
                </div>
            </div>

            <!-- Dawadukkan Style Recent Activity Section -->
            <div class="panel-card" style="margin-bottom:0;">
                <div class="panel-card-header">
                    <div class="flex-column" style="gap: 4px;">
                        <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #374151;">Recent Activity</h3>
                        <span style="font-size: 0.75rem; color: var(--text-muted);">Latest workspace events</span>
                    </div>
                    <i class="fa-regular fa-clock" style="color: var(--text-muted); opacity: 0.5;"></i>
                </div>
                
                <div class="activity-list" id="dashboard-activity-list">
                    <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:0.85rem;">
                        <i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px;"></i> Loading recent activity...
                    </div>
                </div>
            </div>

            <!-- Priority Tasks Panel -->
            <div class="panel-card" style="margin-bottom:0;">
                <div class="panel-card-header">
                    <h3 style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #374151;">Priority Tasks</h3>
                    <span id="priority-tasks-total" style="background-color: #ef4444; color: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">0</span>
                </div>
                <div class="flex-column" style="gap: 10px;">
                    <div class="task-item warning">
                        <div class="task-item-header">
                            <span class="task-title" style="color: #2563eb;">KYC Verifications</span>
                        </div>
                        <span class="task-desc" id="priority-kyc-desc">Loading pending verifications...</span>
                    </div>

                    <div class="task-item danger">
                        <div class="task-item-header">
                            <span class="task-title" style="color: #c53030;">Overdue Accounts</span>
                        </div>
                        <span class="task-desc" id="priority-overdue-desc">Loading overdue accounts...</span>
                    </div>
                </div>
            </div>

            <!-- Platform Status Card -->
            <div class="status-panel-dark">
                <h4>Platform Status</h4>
                <h2 style="font-size: 1.4rem;">Optimal Efficiency</h2>
                <div class="status-indicator">
                    <div class="pulse-dot"></div>
                    <span style="font-weight: 500; font-size:0.8rem; opacity: 0.9;">All nodes active</span>
                </div>
                <button class="btn-secondary" style="border: none; background-color: rgba(255,255,255,0.2); color: #ffffff; width: 100%; border-radius: 6px; padding: 10px 0; font-size: 0.8rem;" onclick="alert('Showing system logs...')">View System Logs</button>
            </div>

        </div>

    </div>
</div>
