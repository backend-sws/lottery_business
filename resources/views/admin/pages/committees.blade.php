                <!-- Committees View -->
                <div id="view-committees" class="view-section" style="display:none;">
                    <div class="header-action">
                        <h2>Committees Management</h2>
                        <button class="btn-primary" onclick="openModal('create-committee')"><i class="fa-solid fa-plus"></i> New Committee</button>
                    </div>
                    <div class="glass-panel p-4" style="padding: 20px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Duration</th>
                                    <th>Period (Timeline)</th>
                                    <th>Frequency</th>
                                    <th>Return</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="committees-tbody">
                                <!-- Data injected via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Collection Committees View (Grid of Committees) -->
                <div id="view-collection-committees" class="view-section" style="display:none;">
                    <div class="header-action">
                        <h2>Select Committee for Collection</h2>
                    </div>
                    <div class="glass-panel p-4" style="padding: 20px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="collection-committees-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Committee Details View (Members + Live Stats) -->
                <div id="view-committee-details" class="view-section" style="display:none;">
                    <div class="header-action">
                        <h2 id="cd-title">Committee Details</h2>
                        <button class="btn-secondary" onclick="window.history.back()"><i class="fa-solid fa-arrow-left"></i> Back</button>
                    </div>
                    <div class="glass-panel p-4" style="padding: 20px;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Member ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Paid (₹)</th>
                                    <th>Due (₹)</th>
                                    <th>Total (₹)</th>
                                    <th>Installments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="committee-details-tbody">
                            </tbody>
                        </table>
                    </div>
                </div>
