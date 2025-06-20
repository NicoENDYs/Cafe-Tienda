
        // Variables globales para los gráficos
        let monthlyRevenueChart;
        let topProductsChart;

        // Datos simulados (en un proyecto real, estos vendrían de la base de datos via AJAX)
        
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


        // JavaScript para el menú responsive
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const navLinks = document.querySelectorAll('.nav-link');

    // Abrir menú
    menuToggle.addEventListener('click', function() {
        sidebar.classList.add('open');
        sidebarOverlay.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevenir scroll del body
    });

    // Cerrar menú con botón X
    sidebarClose.addEventListener('click', function() {
        closeSidebar();
    });

    // Cerrar menú con overlay
    sidebarOverlay.addEventListener('click', function() {
        closeSidebar();
    });

    // Cerrar menú al hacer clic en un enlace (solo en móvil)
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });

    // Función para cerrar el sidebar
    function closeSidebar() {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('active');
        document.body.style.overflow = ''; // Restaurar scroll del body
    }

    // Cerrar menú con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) {
            closeSidebar();
        }
    });

    // Manejar redimensionamiento de ventana
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeSidebar();
        }
    });
});