
        // Variables globales para los gráficos
        let monthlyRevenueChart;
        let topProductsChart;

        // Datos simulados (en un proyecto real, estos vendrían de la base de datos via AJAX)
        const dashboardData = {
            stats: {
                totalRevenue: 2850000,
                totalOrders: 156,
                totalProducts: 892,
                activeEmployees: 8
            },
            monthlyRevenue: [
                { month: 'Ene', revenue: 1200000 },
                { month: 'Feb', revenue: 1450000 },
                { month: 'Mar', revenue: 1680000 },
                { month: 'Abr', revenue: 1520000 },
                { month: 'May', revenue: 1850000 },
                { month: 'Jun', revenue: 2100000 },
                { month: 'Jul', revenue: 2350000 },
                { month: 'Ago', revenue: 2200000 },
                { month: 'Sep', revenue: 2450000 },
                { month: 'Oct', revenue: 2650000 },
                { month: 'Nov', revenue: 2800000 },
                { month: 'Dic', revenue: 2850000 }
            ],
            topProducts: [
                { name: 'Café Americano', sales: 245, revenue: 490000 },
                { name: 'Cappuccino', sales: 189, revenue: 567000 },
                { name: 'Latte', sales: 156, revenue: 624000 },
                { name: 'Expreso', sales: 134, revenue: 268000 },
                { name: 'Mocha', sales: 98, revenue: 392000 },
                { name: 'Frappé', sales: 87, revenue: 435000 },
                { name: 'Chocolate Caliente', sales: 76, revenue: 228000 },
                { name: 'Té Verde', sales: 54, revenue: 162000 }
            ],
            employeeRevenue: [
                { name: 'Ana García', role: 'Cajero', sales: 45, revenue: 890000 },
                { name: 'Carlos López', role: 'Mesero', sales: 38, revenue: 750000 },
                { name: 'María Rodríguez', role: 'Cajero', sales: 42, revenue: 820000 },
                { name: 'Juan Pérez', role: 'Mesero', sales: 31, revenue: 590000 },
                { name: 'Laura Martín', role: 'Mesero', sales: 28, revenue: 540000 },
                { name: 'Diego Silva', role: 'Cocinero', sales: 0, revenue: 0 },
                { name: 'Carmen Vega', role: 'Mesero', sales: 25, revenue: 480000 },
                { name: 'Roberto Cruz', role: 'Cocinero', sales: 0, revenue: 0 }
            ],
            waiterTables: [
                { name: 'Carlos López', tables: 24, orders: 38, average: 1.58 },
                { name: 'María Rodríguez', tables: 28, orders: 42, average: 1.50 },
                { name: 'Juan Pérez', tables: 19, orders: 31, average: 1.63 },
                { name: 'Laura Martín', tables: 17, orders: 28, average: 1.65 },
                { name: 'Carmen Vega', tables: 15, orders: 25, average: 1.67 }
            ]
        };

        // Función para formatear números como moneda
        function formatCurrency(amount) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(amount);
        }

        // Función para inicializar las fechas
        function initializeDates() {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            
            document.getElementById('startDate').value = firstDay.toISOString().split('T')[0];
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
        }

        // Función para actualizar estadísticas
        function updateStats() {
            document.getElementById('totalRevenue').textContent = formatCurrency(dashboardData.stats.totalRevenue);
            document.getElementById('totalOrders').textContent = dashboardData.stats.totalOrders.toLocaleString();
            document.getElementById('totalProducts').textContent = dashboardData.stats.totalProducts.toLocaleString();
            document.getElementById('activeEmployees').textContent = dashboardData.stats.activeEmployees;
        }

        // RF-12: Crear gráfico de recaudo mensual
        function createMonthlyRevenueChart() {
            const ctx = document.getElementById('monthlyRevenueChart').getContext('2d');
            
            if (monthlyRevenueChart) {
                monthlyRevenueChart.destroy();
            }

            monthlyRevenueChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: dashboardData.monthlyRevenue.map(item => item.month),
                    datasets: [{
                        label: 'Recaudo Mensual',
                        data: dashboardData.monthlyRevenue.map(item => item.revenue),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    elements: {
                        point: {
                            hoverRadius: 8
                        }
                    }
                }
            });
        }

        // RF-13: Crear gráfico de productos más vendidos
        function createTopProductsChart() {
            const ctx = document.getElementById('topProductsChart').getContext('2d');
            
            if (topProductsChart) {
                topProductsChart.destroy();
            }

            const colors = [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
            ];

            topProductsChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: dashboardData.topProducts.map(item => item.name),
                    datasets: [{
                        data: dashboardData.topProducts.map(item => item.sales),
                        backgroundColor: colors,
                        borderWidth: 0,
                        hoverBorderWidth: 3,
                        hoverBorderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // RF-14: Actualizar tabla de ingresos por empleado
        function updateEmployeeRevenueTable() {
            const tbody = document.getElementById('employeeRevenueTable');
            tbody.innerHTML = '';

            dashboardData.employeeRevenue.forEach(employee => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td style="font-weight: 500;">${employee.name}</td>
                    <td><span style="background: #e2e8f0; padding: 4px 8px; border-radius: 12px; font-size: 0.8em;">${employee.role}</span></td>
                    <td style="text-align: center;">${employee.sales}</td>
                    <td style="color: #48bb78; font-weight: 600;">${formatCurrency(employee.revenue)}</td>
                `;
            });
        }

        // RF-15: Actualizar tabla de mesas atendidas por mesero
        function updateWaiterTablesTable() {
            const tbody = document.getElementById('waiterTablesTable');
            tbody.innerHTML = '';

            dashboardData.waiterTables.forEach(waiter => {
                const row = tbody.insertRow();
                row.innerHTML = `
                    <td style="font-weight: 500;">${waiter.name}</td>
                    <td style="text-align: center; color: #4299e1; font-weight: 600;">${waiter.tables}</td>
                    <td style="text-align: center;">${waiter.orders}</td>
                    <td style="text-align: center; color: #ed8936; font-weight: 600;">${waiter.average.toFixed(2)}</td>
                `;
            });
        }

        // Función principal para actualizar el dashboard
        function updateDashboard() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;

            if (!startDate || !endDate) {
                alert('Por favor selecciona un rango de fechas válido');
                return;
            }

            // Mostrar indicador de carga
            document.querySelectorAll('.loading').forEach(el => {
                el.textContent = 'Actualizando datos...';
            });

            // Simular llamada AJAX (en un proyecto real, aquí irían las llamadas a PHP)
            setTimeout(() => {
                updateStats();
                createMonthlyRevenueChart();
                createTopProductsChart();
                updateEmployeeRevenueTable();
                updateWaiterTablesTable();
            }, 1000);
        }

        // Inicializar dashboard al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            initializeDates();
            updateDashboard();
        });

        // Actualizar automáticamente cada 5 minutos
        setInterval(updateDashboard, 300000);