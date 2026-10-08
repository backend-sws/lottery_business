                <!-- Installments View -->
                <div id="view-installments" class="view-section" style="display:none;">
                    <div class="header-action" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                        <h2>Installments Collection</h2>
                        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                            <button class="btn-secondary" style="background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); margin: 0;" onclick="sendPaymentReminders()"><i class="fa-solid fa-clock"></i> Send Payment Reminders</button>
                            <button class="btn-secondary" style="background: rgba(244, 63, 94, 0.15); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.3); margin: 0;" onclick="sendDueWarnings()"><i class="fa-solid fa-triangle-exclamation"></i> Send Due Warnings</button>
                            <button class="btn-primary" style="margin: 0;" onclick="openModal('collect-installment')"><i class="fa-solid fa-hand-holding-dollar"></i> Collect Payment</button>
                        </div>
                    </div>
                    <div class="tab-controls" style="display: flex; gap: 10px; margin-bottom: 20px;">
                        <button id="tab-btn-committee-installments" class="btn-primary" onclick="switchInstallmentTab('committee')" style="flex: 1; padding: 12px; font-size: 1.1rem; background: #a855f7; border: none; transition: 0.3s; opacity: 1;">
                            <i class="fa-solid fa-users-viewfinder"></i> Committee Installments
                        </button>
                        <button id="tab-btn-loan-installments" class="btn-secondary" onclick="switchInstallmentTab('loan')" style="flex: 1; padding: 12px; font-size: 1.1rem; background: rgba(14, 165, 233, 0.1); color: #0ea5e9; border: 1px solid #0ea5e9; transition: 0.3s; opacity: 0.7;">
                            <i class="fa-solid fa-money-check-dollar"></i> Loan Installments
                        </button>
                    </div>

                    <!-- Committee Installments Table -->
                    <div id="tab-content-committee-installments" class="glass-panel" style="padding: 20px; overflow-x: auto; margin-bottom: 30px; display: block;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member Name</th>
                                    <th>Committee</th>
                                    <th>Amount</th>
                                    <th>Paid Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="installments-tbody"></tbody>
                        </table>
                    </div>

                    <!-- Loan Installments Table -->
                    <div id="tab-content-loan-installments" class="glass-panel" style="padding: 20px; overflow-x: auto; margin-bottom: 30px; display: none;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member Name</th>
                                    <th>Loan ID</th>
                                    <th>Amount</th>
                                    <th>Paid Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="loan-installments-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <script>
                    function switchInstallmentTab(tab) {
                        const commContent = document.getElementById('tab-content-committee-installments');
                        const loanContent = document.getElementById('tab-content-loan-installments');
                        const commBtn = document.getElementById('tab-btn-committee-installments');
                        const loanBtn = document.getElementById('tab-btn-loan-installments');

                        if(tab === 'committee') {
                            commContent.style.display = 'block';
                            loanContent.style.display = 'none';
                            
                            commBtn.className = 'btn-primary';
                            commBtn.style.background = '#a855f7';
                            commBtn.style.color = 'white';
                            commBtn.style.border = 'none';
                            commBtn.style.opacity = '1';

                            loanBtn.className = 'btn-secondary';
                            loanBtn.style.background = 'rgba(14, 165, 233, 0.1)';
                            loanBtn.style.color = '#0ea5e9';
                            loanBtn.style.border = '1px solid #0ea5e9';
                            loanBtn.style.opacity = '0.7';
                        } else {
                            commContent.style.display = 'none';
                            loanContent.style.display = 'block';
                            
                            loanBtn.className = 'btn-primary';
                            loanBtn.style.background = '#0ea5e9';
                            loanBtn.style.color = 'white';
                            loanBtn.style.border = 'none';
                            loanBtn.style.opacity = '1';

                            commBtn.className = 'btn-secondary';
                            commBtn.style.background = 'rgba(168, 85, 247, 0.1)';
                            commBtn.style.color = '#a855f7';
                            commBtn.style.border = '1px solid #a855f7';
                            commBtn.style.opacity = '0.7';
                        }
                    }
                </script>

                <!-- Lotteries View -->
                <section id="view-lotteries" class="content-section view-section" style="display:none;">
                    <!-- Grid for Settings and Add Winner -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 24px;">
                        
                        <!-- Lottery Settings Panel -->
                        <div class="card" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 0;">
                            <h3 style="margin-top:0; margin-bottom:16px; font-weight: 600; color: #1e293b;">Grand Draw Countdown Settings</h3>
                            <form id="lottery-settings-form" onsubmit="saveLotterySettings(event)">
                                <div style="margin-bottom: 12px;">
                                    <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">Grand Draw Title</label>
                                    <input type="text" id="grand_draw_title" name="grand_draw_title" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;" placeholder="e.g. The Wealth Multiplier">
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">Grand Draw Date & Time</label>
                                    <input type="datetime-local" id="grand_draw_date" name="grand_draw_date" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;">
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">Grand Draw Description</label>
                                    <textarea id="grand_draw_description" name="grand_draw_description" required rows="3" style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px; resize:none;" placeholder="e.g. Join the elite circle of participants..."></textarea>
                                </div>
                                <div style="text-align: right;">
                                    <button type="submit" class="btn-primary" style="background:#10b981; border:none; padding:10px 20px; color:white; border-radius:6px; font-weight:600; cursor:pointer;">
                                        Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Add Lottery Winner Panel -->
                        <div class="card" style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 0;">
                            <h3 style="margin-top:0; margin-bottom:16px; font-weight: 600; color: #1e293b;">Add New Lottery Winner</h3>
                            <form id="add-lottery-winner-form" onsubmit="addLotteryWinner(event)">
                                <div style="margin-bottom: 12px;">
                                    <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">Committee ID</label>
                                    <input type="number" id="winner_committee_id" name="committee_id" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;" placeholder="Enter Committee ID">
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">Winner (User) ID</label>
                                    <input type="number" id="winner_user_id" name="winner_id" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;" placeholder="Enter Winner User ID">
                                </div>
                                <div style="margin-bottom: 12px;">
                                    <label style="display:block; font-size:12px; font-weight:600; color:#64748b; margin-bottom:6px;">Draw Date</label>
                                    <input type="date" id="winner_draw_date" name="draw_date" required style="width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;">
                                </div>
                                <div style="text-align: right;">
                                    <button type="submit" class="btn-primary" style="background:#8b5cf6; border:none; padding:10px 20px; color:white; border-radius:6px; font-weight:600; cursor:pointer;">
                                        Add Winner
                                    </button>
                                </div>
                            </form>
                        </div>

                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                        <h2>Lottery Winners</h2>
                    </div>
                    <div class="card" style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; padding: 0;">
                        <table class="data-table" style="width: 100%; border-collapse: collapse; margin: 0;">
                            <thead>
                                <tr>
                                    <th style="padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">ID</th>
                                    <th style="padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Winner User</th>
                                    <th style="padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Committee</th>
                                    <th style="padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Prize Pool</th>
                                    <th style="padding: 14px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Draw Date</th>
                                    <th style="padding: 14px 16px; text-align: right; font-size: 11px; font-weight: 600; color: #64748b; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="lotteries-tbody">
                                <!-- Rows will be injected here by lotteries_payouts.js -->
                            </tbody>
                        </table>
                    </div>
                </section>
