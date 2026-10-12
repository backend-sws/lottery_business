<!-- Loan Requests View -->
<div id="view-loan-requests" class="view-section" style="display:none;">
    <div class="header-action" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2>Loan Requests & Applications</h2>
            <p>Review incoming member loan applications, set interest rates, and disburse active loans.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="#loans" class="btn-secondary" style="text-decoration: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-list-check"></i> View Active Loans
            </a>
            <button class="btn-primary" onclick="loadLoanRequestsData()" style="padding: 10px 18px; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-arrows-rotate"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Stats Overview Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="panel-card" style="padding: 18px; border-radius: 14px; background: white; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #fef3c7; color: #b45309; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <span style="font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Pending Review</span>
                <h3 id="stat-pending-requests" style="margin: 4px 0 0 0; font-size: 1.5rem; font-weight: 700; color: #1e293b;">0</h3>
            </div>
        </div>
        <div class="panel-card" style="padding: 18px; border-radius: 14px; background: white; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span style="font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Approved Loans</span>
                <h3 id="stat-approved-requests" style="margin: 4px 0 0 0; font-size: 1.5rem; font-weight: 700; color: #1e293b;">0</h3>
            </div>
        </div>
        <div class="panel-card" style="padding: 18px; border-radius: 14px; background: white; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #fee2e2; color: #b91c1c; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <div>
                <span style="font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Rejected</span>
                <h3 id="stat-rejected-requests" style="margin: 4px 0 0 0; font-size: 1.5rem; font-weight: 700; color: #1e293b;">0</h3>
            </div>
        </div>
        <div class="panel-card" style="padding: 18px; border-radius: 14px; background: white; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div>
                <span style="font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Requested Volume</span>
                <h3 id="stat-requested-amount" style="margin: 4px 0 0 0; font-size: 1.3rem; font-weight: 700; color: #1e293b;">₹0</h3>
            </div>
        </div>
    </div>

    <!-- Table Container with Filters -->
    <div class="panel-card" style="background: white; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <!-- Filter Tabs -->
            <div style="display: flex; gap: 8px;">
                <button type="button" class="loan-req-filter active" data-status="pending" onclick="setLoanRequestFilter('pending', this)" style="padding: 7px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #cbd5e1; background: #004838; color: white; cursor: pointer; transition: all 0.2s;">
                    Pending Review
                </button>
                <button type="button" class="loan-req-filter" data-status="all" onclick="setLoanRequestFilter('all', this)" style="padding: 7px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #e2e8f0; background: #f8fafc; color: #475569; cursor: pointer; transition: all 0.2s;">
                    All Requests
                </button>
                <button type="button" class="loan-req-filter" data-status="approved" onclick="setLoanRequestFilter('approved', this)" style="padding: 7px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #e2e8f0; background: #f8fafc; color: #475569; cursor: pointer; transition: all 0.2s;">
                    Approved
                </button>
                <button type="button" class="loan-req-filter" data-status="rejected" onclick="setLoanRequestFilter('rejected', this)" style="padding: 7px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #e2e8f0; background: #f8fafc; color: #475569; cursor: pointer; transition: all 0.2s;">
                    Rejected
                </button>
            </div>

            <!-- Search Field -->
            <div style="display: flex; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 6px 14px; gap: 10px; min-width: 260px;">
                <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8; font-size: 0.85rem;"></i>
                <input type="text" id="loan-request-search" placeholder="Search applicant, phone or purpose..." oninput="filterLoanRequestsTable()" style="border: none; background: transparent; outline: none; width: 100%; font-size: 0.85rem;">
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; color: #475569; font-size: 0.85rem; text-align: left;">
                        <th style="padding: 14px 18px;">ID</th>
                        <th style="padding: 14px 18px;">Applicant Member</th>
                        <th style="padding: 14px 18px;">Requested Amount</th>
                        <th style="padding: 14px 18px;">Term & Frequency</th>
                        <th style="padding: 14px 18px;">Purpose / Reason</th>
                        <th style="padding: 14px 18px;">Applied On</th>
                        <th style="padding: 14px 18px;">Status</th>
                        <th style="padding: 14px 18px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody id="loan-requests-tbody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                            <i class="fa-solid fa-spinner fa-spin" style="font-size: 1.6rem; margin-bottom: 10px; display: block;"></i>
                            Loading loan applications...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- APPROVE LOAN REQUEST MODAL -->
<div id="modal-approve-loan-request" class="modal-overlay" style="display:none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 2100; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 20px; width: 100%; max-width: 520px; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); margin: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #1e293b;">Approve Loan Request</h3>
                    <span style="font-size: 0.8rem; color: #64748b;">Set interest rate and disburse active loan schedule.</span>
                </div>
            </div>
            <button onclick="closeApproveLoanModal()" style="background: none; border: none; font-size: 1.2rem; color: #94a3b8; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form id="form-approve-loan-request" onsubmit="submitApproveLoanRequest(event)">
            <input type="hidden" id="approve-loan-request-id" name="id">

            <!-- Member Summary Card -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Applicant</span>
                    <h4 id="approve-member-name" style="margin: 2px 0 0 0; font-size: 0.95rem; font-weight: 700; color: #004838;">Member Name</h4>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Requested</span>
                    <h4 id="approve-requested-amount" style="margin: 2px 0 0 0; font-size: 1.05rem; font-weight: 700; color: #10b981;">₹0</h4>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="input-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Sanctioned Principal (₹)</label>
                    <div class="input-field" style="background: #fff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px 14px;">
                        <input type="number" id="approve-loan-amount" required step="1" min="500" style="width: 100%; border: none; outline: none; font-size: 0.95rem; font-weight: 600;">
                    </div>
                </div>
                <div class="input-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Annual Interest Rate (%)</label>
                    <div class="input-field" style="background: #fff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px 14px;">
                        <input type="number" id="approve-interest-rate" required step="0.1" min="0" value="12.0" style="width: 100%; border: none; outline: none; font-size: 0.95rem; font-weight: 600;">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="input-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Duration (Months)</label>
                    <div class="input-field" style="background: #fff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px 14px;">
                        <input type="number" id="approve-duration-months" required min="1" max="120" style="width: 100%; border: none; outline: none; font-size: 0.95rem; font-weight: 600;">
                    </div>
                </div>
                <div class="input-group" style="margin-bottom: 0;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">EMI Frequency</label>
                    <select id="approve-payment-frequency" style="width: 100%; background: #fff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px 14px; outline: none; font-size: 0.9rem; font-weight: 600; color: #334155;">
                        <option value="monthly">Monthly</option>
                        <option value="weekly">Weekly</option>
                        <option value="daily">Daily</option>
                    </select>
                </div>
            </div>

            <div class="input-group" style="margin-bottom: 18px;">
                <label style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Admin Approval Remarks / Reference</label>
                <textarea id="approve-admin-remarks" rows="2" placeholder="e.g. Sanctioned under standard scheme..." style="width: 100%; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px; font-size: 0.85rem; font-family: inherit; outline: none; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" onclick="closeApproveLoanModal()" class="btn-secondary" style="padding: 10px 20px; border-radius: 10px; font-weight: 600;">Cancel</button>
                <button type="submit" id="btn-confirm-approve-loan" class="btn-primary" style="padding: 10px 24px; border-radius: 10px; font-weight: 700; background: #004838; color: white;">
                    <i class="fa-solid fa-check-double"></i> Confirm & Approve Loan
                </button>
            </div>
        </form>
    </div>
</div>
