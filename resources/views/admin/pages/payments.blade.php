<!-- Payments / Payouts View -->
<div id="view-payments" class="view-section" style="display:none;">
    <div class="header-action">
        <div>
            <h2>Payments & Disbursements</h2>
            <p>Approve draw payout settlements, view history of bank clearances and field collection vouchers.</p>
        </div>
    </div>

    <!-- Quick Notice to Settings -->
    <div class="panel-card" style="margin-bottom: 24px; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: rgba(0,72,56,0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <div>
                <strong style="font-size: 0.95rem; color: var(--text-main); display: block;">Mobile App Payment Gateway & QR Code</strong>
                <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted);">Manage UPI QR code uploads and WhatsApp helpline numbers in the dedicated Portal Settings view.</p>
            </div>
        </div>
        <a href="#settings" class="btn-secondary" style="text-decoration: none; padding: 10px 18px; font-size: 0.85rem; font-weight: 600; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-gear"></i> Configure in Settings
        </a>
    </div>

    <!-- Payouts Table -->
    <div class="panel-card">
        <div class="panel-card-header">
            <h3>Pending Settlements (Payouts)</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Member</th>
                        <th>Committee</th>
                        <th>Total Deposits</th>
                        <th>Return Amount</th>
                        <th>Total Payout</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="payments-payouts-tbody">
                    <!-- Populated dynamically by admin.js -->
                </tbody>
            </table>
        </div>
    </div>
</div>
