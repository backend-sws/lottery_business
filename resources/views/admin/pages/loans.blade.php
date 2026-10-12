                <!-- Loans View -->
                <div id="view-loans" class="view-section" style="display:none;">
                    <div class="header-action" style="margin-bottom: 20px;">
                        <div>
                            <h2>Loans Management</h2>
                            <p style="margin: 0; font-size: 0.85rem; color: #64748b;">Monitor active disbursed loans, EMI installments, and settlement schedules.</p>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <a href="#loan-requests" class="btn-secondary" style="text-decoration: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-file-invoice-dollar"></i> View Loan Requests
                            </a>
                            <button class="btn-primary" onclick="openModal('create-loan')"><i class="fa-solid fa-plus"></i> Create Direct Loan</button>
                        </div>
                    </div>
                    <div class="glass-panel p-4" style="padding: 20px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User Name</th>
                                    <th>Principal (₹)</th>
                                    <th>Interest (%)</th>
                                    <th>Duration</th>
                                    <th>Freq</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="loans-tbody"></tbody>
                        </table>
                    </div>
                </div>
