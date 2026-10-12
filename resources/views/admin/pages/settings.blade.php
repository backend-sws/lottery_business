<!-- Settings View -->
<div id="view-settings" class="view-section" style="display:none;">
    <div class="header-action" style="margin-bottom: 24px;">
        <div>
            <h2>Portal & Payment Settings</h2>
            <p>Manage payment gateway QR codes, WhatsApp support channels, and core platform configurations.</p>
        </div>
    </div>

    <!-- Payment Gateway & Dynamic QR Code Section -->
    <div class="panel-card" style="margin-bottom: 24px; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div class="panel-card-header" style="background: linear-gradient(135deg, rgba(0,72,56,0.05), rgba(16,185,129,0.05)); padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-main);">Mobile App Payment Gateway & QR Code</h3>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">Manage the dynamic QR code and WhatsApp number shown in the mobile app's "Scan & Repay" screen.</p>
                </div>
            </div>
            <span id="qr-status-badge" style="padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: #fef3c7; color: #b45309; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i> Checking Status...
            </span>
        </div>

        <div style="padding: 24px;">
            <form id="payment-settings-form" enctype="multipart/form-data">
                <div style="display: grid; grid-template-columns: 1fr 340px; gap: 32px; align-items: start;">
                    <!-- Left Column: Form Controls -->
                    <div class="flex-column gap-20">
                        <!-- Admin WhatsApp Number -->
                        <div class="input-group" style="margin-bottom: 0;">
                            <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-brands fa-whatsapp" style="color: #25d366; font-size: 1.1rem;"></i> Admin WhatsApp & Helpline Number
                            </label>
                            <div class="input-field" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; display: flex; align-items: center; gap: 12px; transition: border-color 0.2s;">
                                <span style="font-weight: 600; color: #64748b; font-size: 0.95rem;">+91</span>
                                <input type="text" name="admin_phone" id="pay-setting-phone" placeholder="9661001833" style="border: none; background: transparent; outline: none; width: 100%; font-size: 0.95rem; font-weight: 500; color: #1e293b;">
                            </div>
                            <small style="color: #64748b; font-size: 0.8rem; margin-top: 6px; display: block;">
                                When members tap <strong>"Share Payment Screenshot"</strong> or <strong>"Call Helpline"</strong>, they will be redirected to this number.
                            </small>
                        </div>

                        <!-- QR Code File Input -->
                        <div class="input-group" style="margin-bottom: 0;">
                            <label style="font-weight: 600; font-size: 0.9rem; color: var(--text-main); margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-cloud-arrow-up" style="color: var(--primary);"></i> Upload New UPI / Bank QR Code Image
                            </label>
                            <div id="drop-zone" style="border: 2px dashed #cbd5e1; border-radius: 14px; padding: 24px 20px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.2s;" onclick="document.getElementById('pay-setting-qrcode').click();">
                                <i class="fa-solid fa-image" style="font-size: 2.2rem; color: #94a3b8; margin-bottom: 10px;"></i>
                                <div style="font-size: 0.95rem; font-weight: 600; color: #334155; margin-bottom: 4px;">Click to select or drag & drop QR code image</div>
                                <div style="font-size: 0.8rem; color: #64748b;">Supports PNG, JPG, JPEG, WEBP (Max 10MB)</div>
                                <input type="file" name="qr_code" id="pay-setting-qrcode" accept="image/*" style="display: none;">
                            </div>
                            <div id="selected-file-info" style="display: none; margin-top: 10px; padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 10px; font-size: 0.85rem; color: #065f46; align-items: center; justify-content: space-between;">
                                <span><i class="fa-solid fa-file-circle-check"></i> <strong id="selected-file-name">filename.png</strong></span>
                                <button type="button" onclick="clearSelectedQrFile(event)" style="background: none; border: none; color: #dc2626; cursor: pointer; font-size: 0.85rem;"><i class="fa-solid fa-xmark"></i> Cancel</button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: flex; gap: 14px; align-items: center; margin-top: 8px;">
                            <button type="submit" id="save-pay-settings-btn" class="btn-primary" style="padding: 12px 28px; font-size: 0.95rem; font-weight: 600; display: inline-flex; align-items: center; gap: 10px; border-radius: 12px;">
                                <i class="fa-solid fa-save"></i> <span>Save & Publish QR Code</span>
                            </button>
                        </div>

                        <div id="pay-settings-alert" style="display: none; padding: 12px 16px; border-radius: 10px; font-size: 0.9rem; margin-top: 8px;"></div>
                    </div>

                    <!-- Right Column: Mobile App Live Preview Card -->
                    <div>
                        <div style="text-align: center; margin-bottom: 12px;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fa-solid fa-mobile-screen"></i> Member App Live Preview
                            </span>
                        </div>

                        <!-- Simulated App Mockup -->
                        <div style="background: #ffffff; border-radius: 24px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 2px solid #e2e8f0; text-align: center; max-width: 340px; margin: 0 auto;">
                            <h4 style="margin: 0 0 16px 0; font-size: 1.05rem; font-weight: 700; color: #1e293b;">Scan & Repay</h4>

                            <div id="pay-setting-qr-preview" style="border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 14px; width: 220px; height: 220px; margin: 0 auto; display: flex; align-items: center; justify-content: center; overflow: hidden; background: #f8fafc; position: relative;">
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; gap: 8px;">
                                    <i class="fa-solid fa-qrcode" style="font-size: 3.5rem; color: #cbd5e1;"></i>
                                    <strong style="color: #64748b; font-size: 0.85rem;">No QR Code Available</strong>
                                    <span style="font-size: 0.7rem; color: #94a3b8;">Admin has not set QR Code yet</span>
                                </div>
                            </div>

                            <div style="margin-top: 14px; display: flex; align-items: center; justify-content: center; gap: 6px; color: #10b981; font-weight: 700; font-size: 0.75rem;">
                                <i class="fa-solid fa-shield-halved"></i> Secure Instant UPI Verification
                            </div>

                            <div style="margin-top: 16px; background: #25d366; color: white; padding: 12px; border-radius: 14px; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 10px rgba(37,211,102,0.25);">
                                <i class="fa-brands fa-whatsapp"></i> Share Payment Screenshot
                            </div>

                            <div style="margin-top: 10px; font-size: 0.75rem; color: #64748b;">
                                Redirects to: <span id="preview-whatsapp-text" style="font-weight: 600; color: #1e293b;">+91 9661001833</span>
                            </div>

                            <div style="margin-top: 6px; font-size: 0.75rem; color: #004838; font-weight: 600;">
                                <i class="fa-solid fa-phone"></i> Helpline: <span id="preview-helpline-text">9661001833</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Core Parameters & Notification Gateway (General) -->
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

<script>
(function() {
    async function fetchAndRenderPaymentSettings() {
        const previewBox = document.getElementById('pay-setting-qr-preview');
        const statusBadge = document.getElementById('qr-status-badge');
        const phoneInput = document.getElementById('pay-setting-phone');
        const previewPhone = document.getElementById('preview-whatsapp-text');
        const previewHelp = document.getElementById('preview-helpline-text');

        if (statusBadge) {
            statusBadge.style.background = '#fef3c7';
            statusBadge.style.color = '#b45309';
            statusBadge.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking Status...';
        }

        try {
            const res = await window.apiFetch('/api/admin/payment-setting');
            if (!res || !res.ok) return;
            const payload = await res.json();
            if ((payload.status || payload.success) && payload.data) {
                const data = payload.data;
                const rawPhone = (data.admin_phone || '').replace(/^\+91\s*/, '').trim();
                if (phoneInput && !phoneInput.value) {
                    phoneInput.value = rawPhone;
                }
                const formattedPhone = rawPhone ? `+91 ${rawPhone}` : '+91 9661001833';
                if (previewPhone) previewPhone.textContent = formattedPhone;
                if (previewHelp) previewHelp.textContent = rawPhone || '9661001833';

                if (previewBox) {
                    if (data.qr_code) {
                        previewBox.innerHTML = `
                            <img src="${data.qr_code}" alt="Payment QR Code" style="max-width:100%; max-height:100%; border-radius:12px; object-fit:contain; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                        `;
                        if (statusBadge) {
                            statusBadge.style.background = '#d1fae5';
                            statusBadge.style.color = '#065f46';
                            statusBadge.innerHTML = '<i class="fa-solid fa-circle-check"></i> Active QR Code';
                        }
                    } else {
                        previewBox.innerHTML = `
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; gap: 8px;">
                                <i class="fa-solid fa-qrcode" style="font-size: 3.5rem; color: #cbd5e1;"></i>
                                <strong style="color: #64748b; font-size: 0.85rem;">No QR Code Available</strong>
                                <span style="font-size: 0.7rem; color: #94a3b8;">Admin has not set QR Code yet</span>
                            </div>
                        `;
                        if (statusBadge) {
                            statusBadge.style.background = '#fef3c7';
                            statusBadge.style.color = '#b45309';
                            statusBadge.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> No QR Code Set';
                        }
                    }
                }
            }
        } catch (err) {
            console.error('Error fetching payment settings:', err);
            if (statusBadge) {
                statusBadge.style.background = '#fee2e2';
                statusBadge.style.color = '#991b1b';
                statusBadge.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Load Error';
            }
        }
    }

    window.loadPaymentSettingsData = fetchAndRenderPaymentSettings;

    function checkAndLoadSettings() {
        if (window.location.hash === '#settings' || window.location.hash === '#payments') {
            fetchAndRenderPaymentSettings();
        }
    }
    window.addEventListener('hashchange', checkAndLoadSettings);
    document.addEventListener('DOMContentLoaded', checkAndLoadSettings);
    setTimeout(checkAndLoadSettings, 400);

    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'pay-setting-phone') {
            const raw = e.target.value.replace(/^\+91\s*/, '').trim();
            const previewPhone = document.getElementById('preview-whatsapp-text');
            const previewHelp = document.getElementById('preview-helpline-text');
            if (previewPhone) previewPhone.textContent = raw ? `+91 ${raw}` : '+91 9661001833';
            if (previewHelp) previewHelp.textContent = raw || '9661001833';
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'pay-setting-qrcode') {
            const file = e.target.files && e.target.files[0];
            if (file) {
                const nameEl = document.getElementById('selected-file-name');
                const infoEl = document.getElementById('selected-file-info');
                if (nameEl) nameEl.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
                if (infoEl) infoEl.style.display = 'flex';

                const reader = new FileReader();
                reader.onload = function(evt) {
                    const previewBox = document.getElementById('pay-setting-qr-preview');
                    const statusBadge = document.getElementById('qr-status-badge');
                    if (previewBox) {
                        previewBox.innerHTML = `
                            <img src="${evt.target.result}" alt="Preview" style="max-width:100%; max-height:100%; border-radius:12px; object-fit:contain; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
                        `;
                    }
                    if (statusBadge) {
                        statusBadge.style.background = '#e0f2fe';
                        statusBadge.style.color = '#0369a1';
                        statusBadge.innerHTML = '<i class="fa-solid fa-eye"></i> Ready to Save';
                    }
                };
                reader.readAsDataURL(file);
            }
        }
    });

    window.clearSelectedQrFile = function(e) {
        if (e) e.stopPropagation();
        const fileInput = document.getElementById('pay-setting-qrcode');
        if (fileInput) fileInput.value = '';
        const infoEl = document.getElementById('selected-file-info');
        if (infoEl) infoEl.style.display = 'none';
        fetchAndRenderPaymentSettings();
    };

    const dropZone = document.getElementById('drop-zone');
    if (dropZone) {
        ['dragenter', 'dragover'].forEach(name => {
            dropZone.addEventListener(name, (e) => {
                e.preventDefault(); e.stopPropagation();
                dropZone.style.borderColor = 'var(--primary)';
                dropZone.style.background = '#ecfdf5';
            });
        });
        ['dragleave', 'drop'].forEach(name => {
            dropZone.addEventListener(name, (e) => {
                e.preventDefault(); e.stopPropagation();
                dropZone.style.borderColor = '#cbd5e1';
                dropZone.style.background = '#f8fafc';
            });
        });
        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            if (dt && dt.files && dt.files.length > 0) {
                const fileInput = document.getElementById('pay-setting-qrcode');
                if (fileInput) {
                    fileInput.files = dt.files;
                    fileInput.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });
    }

    document.addEventListener('submit', async function(e) {
        if (e.target && e.target.id === 'payment-settings-form') {
            e.preventDefault();
            const btn = document.getElementById('save-pay-settings-btn');
            const alertBox = document.getElementById('pay-settings-alert');
            const originalBtnHtml = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Saving & Publishing...';
            }
            if (alertBox) alertBox.style.display = 'none';

            const formData = new FormData(e.target);
            try {
                const res = await window.apiFetch('/api/admin/payment-setting', {
                    method: 'POST',
                    body: formData
                });
                if (!res) return;
                const payload = await res.json();
                if (payload.status || payload.success) {
                    if (alertBox) {
                        alertBox.style.display = 'block';
                        alertBox.style.background = '#d1fae5';
                        alertBox.style.color = '#065f46';
                        alertBox.style.border = '1px solid #a7f3d0';
                        alertBox.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + (payload.message || 'Payment QR Code & WhatsApp settings saved successfully!');
                    }
                    const infoEl = document.getElementById('selected-file-info');
                    if (infoEl) infoEl.style.display = 'none';
                    const fileInput = document.getElementById('pay-setting-qrcode');
                    if (fileInput) fileInput.value = '';

                    await fetchAndRenderPaymentSettings();
                } else {
                    if (alertBox) {
                        alertBox.style.display = 'block';
                        alertBox.style.background = '#fee2e2';
                        alertBox.style.color = '#991b1b';
                        alertBox.style.border = '1px solid #fecaca';
                        alertBox.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + (payload.message || 'Error updating payment settings.');
                    }
                }
            } catch (err) {
                console.error('Save error:', err);
                if (alertBox) {
                    alertBox.style.display = 'block';
                    alertBox.style.background = '#fee2e2';
                    alertBox.style.color = '#991b1b';
                    alertBox.style.border = '1px solid #fecaca';
                    alertBox.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Request failed. Please check connection.';
                }
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalBtnHtml;
                }
            }
        }
    });
})();
</script>
