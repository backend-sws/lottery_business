
    // ----- CHARTS (Chart.js) -----
    function renderCharts(trends = null, distribution = null) {
        const ctxLine = document.getElementById('lineChart');
        if (!ctxLine) return;
        
        let labels = [];
        let dataVals = [];
        
        if (trends && Array.isArray(trends) && trends.length > 0) {
            labels = trends.map(t => t.month);
            dataVals = trends.map(t => t.total);
        } else {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const curDate = new Date();
            for (let i = 5; i >= 0; i--) {
                const d = new Date(curDate.getFullYear(), curDate.getMonth() - i, 1);
                labels.push(monthNames[d.getMonth()]);
                dataVals.push(0);
            }
        }

        const isDarkTheme = document.body.classList.contains('dark-theme');
        const gridColor = isDarkTheme ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.08)';
        const textColor = isDarkTheme ? '#94a3b8' : '#64748b';
        
        const canvasCtx = ctxLine.getContext('2d');
        const gradient = canvasCtx.createLinearGradient(0, 0, 0, 250);
        if (isDarkTheme) {
            gradient.addColorStop(0, 'rgba(255, 122, 0, 0.25)'); // Orange gradient
            gradient.addColorStop(1, 'rgba(255, 122, 0, 0.0)');
        } else {
            gradient.addColorStop(0, 'rgba(255, 122, 0, 0.15)'); // Orange gradient
            gradient.addColorStop(1, 'rgba(255, 122, 0, 0.0)');
        }
        
        const strokeColor = '#FF7A00';

        if (lineChartInstance) lineChartInstance.destroy();
        lineChartInstance = new Chart(canvasCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    { 
                        label: 'Collections (in Lakhs ₹)', 
                        data: dataVals, 
                        borderColor: strokeColor,
                        backgroundColor: gradient,
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        pointBackgroundColor: strokeColor,
                        pointHoverBackgroundColor: strokeColor,
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 2
                    }
                ]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } }, 
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [5, 5], color: gridColor },
                        ticks: { color: textColor }
                    }, 
                    x: { 
                        grid: { display: false },
                        ticks: { color: textColor }
                    } 
                } 
            }
        });

        const ctxDoughnut = document.getElementById('doughnutChart');
        if (!ctxDoughnut) return;

        let doughnutData = [0, 0, 0];
        let doughnutLabels = ['Active Members', 'Pending / KYC', 'Inactive'];
        if (distribution) {
            const activeVal = Number(distribution.active || 0);
            const pendingVal = Number(distribution.pending || 0);
            const inactiveVal = Number(distribution.inactive || 0);

            if (activeVal === 0 && pendingVal === 0 && inactiveVal === 0) {
                doughnutData = [1];
                doughnutLabels = ['No Data Yet'];
            } else {
                doughnutData = [activeVal, pendingVal, inactiveVal];
            }
        }

        if (doughnutChartInstance) doughnutChartInstance.destroy();
        doughnutChartInstance = new Chart(ctxDoughnut.getContext('2d'), {
            type: 'doughnut',
            data: { 
                labels: doughnutLabels, 
                datasets: [{ 
                    data: doughnutData, 
                    backgroundColor: doughnutLabels.length === 1 ? ['#cbd5e1'] : ['#004d40', '#10b981', '#cbd5e1'], 
                    borderWidth: 0, 
                    hoverOffset: 4 
                }] 
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                cutout: '75%', 
                plugins: { legend: { display: false } } 
            }
        });
    }