<!-- Settings View -->
<div id="view-settings" class="view-section" style="display:none;">
    <div class="header-action">
        <div>
            <h2>Portal Settings</h2>
            <p>Configure general variables, alert thresholds, and payment integrations.</p>
        </div>
        <button class="btn-primary" onclick="alert('Settings saved successfully!')"><i class="fa-solid fa-save"></i> Save Configurations</button>
    </div>

    <div class="kyc-flow-grid">
        <!-- Left: General Configurations -->
        <div class="flex-column gap-24">
            <div class="panel-card" style="margin-bottom:0;">
                <div class="panel-card-header" style="margin-bottom:15px;">
                    <h3 style="font-size: 1rem;"><i class="fa-solid fa-sliders" style="color: var(--primary);"></i> Core System Parameters</h3>
                </div>
                <div class="flex-column gap-12">
                    <div class="input-group" style="margin-bottom:0;">
                        <label>Business / Platform Name</label>
                        <div class="input-field"><input type="text" value="Janta Traders Enterprise Finance"></div>
                    </div>
                    <div class="input-group" style="margin-bottom:0; margin-top:12px;">
                        <label>Base Currency Code</label>
                        <select class="filter-select" style="width:100%;">
                            <option value="INR">INR (₹) - Indian Rupee</option>
                            <option value="USD">USD ($) - US Dollar</option>
                            <option value="EUR">EUR (€) - Euro</option>
                        </select>
                    </div>
                    <div class="input-group" style="margin-bottom:0; margin-top:12px;">
                        <label>Default Return Percentage (%)</label>
                        <div class="input-field"><input type="number" step="0.01" value="12.00"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Notification Gateway and Integrations -->
        <div class="flex-column gap-24">
            <div class="panel-card" style="margin-bottom:0;">
                <div class="panel-card-header" style="margin-bottom:15px;">
                    <h3 style="font-size: 1rem;"><i class="fa-solid fa-bell" style="color: var(--primary);"></i> Notification Gateway (SMS / WhatsApp)</h3>
                </div>
                <div class="flex-column gap-12">
                    <div class="input-group" style="margin-bottom:0;">
                        <label>SMS Gateway Provider</label>
                        <select class="filter-select" style="width:100%;">
                            <option value="twilio">Twilio SMS</option>
                            <option value="nexmo">Vonage / Nexmo</option>
                            <option value="msg91">MSG91</option>
                        </select>
                    </div>
                    <div class="input-group" style="margin-bottom:0; margin-top:12px;">
                        <label>Alert Escalation Threshold (Days Overdue)</label>
                        <div class="input-field"><input type="number" value="3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
