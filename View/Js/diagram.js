// diagram.js - Versión corregida
document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM cargado, inicializando gráficos...");

    // Verificar si chartData está definido desde PHP
    if (typeof chartData === "undefined") {
        console.error("Error: No se encontraron datos para las gráficas");
        // Declarar chartData como un objeto vacío para evitar errores
        window.chartData = {
            status: [],
            jobTitle: [],
            userRoles: [],
        };
    }

    console.log("Datos recibidos:", chartData);

    // Configuración de colores para las gráficas
    const colors = {
        status: [
            "rgba(13, 110, 253, 0.8)", // Azul
            "rgba(25, 135, 84, 0.8)", // Verde
            "rgba(220, 53, 69, 0.8)", // Rojo
            "rgba(255, 193, 7, 0.8)", // Amarillo
        ],
        jobTitle: [
            "rgba(13, 110, 253, 0.8)", // Azul
            "rgba(25, 135, 84, 0.8)", // Verde
            "rgba(220, 53, 69, 0.8)", // Rojo
            "rgba(255, 193, 7, 0.8)", // Amarillo
            "rgba(111, 66, 193, 0.8)", // Púrpura
        ],
        userRoles: [
            "rgba(220, 53, 69, 0.8)", // Rojo para admin
            "rgba(255, 193, 7, 0.8)", // Amarillo para manager
            "rgba(13, 110, 253, 0.8)", // Azul para user
        ],
    };

    // Procesar datos de estado de equipos
    function processStatusData(data) {
        const statusCounts = {};

        if (!Array.isArray(data)) {
            console.error("Error: Los datos de estado no son un array");
            return { labels: [], data: [] };
        }

        data.forEach((item) => {
            const status = item.user_status || "Desconocido";
            statusCounts[status] = (statusCounts[status] || 0) + 1;
        });

        return {
            labels: Object.keys(statusCounts),
            data: Object.values(statusCounts),
        };
    }

    // Crear gráfico de estado de equipos
    function createStatusChart() {
        console.log("Creando gráfico de estado...");
       const canvas = document.getElementById("statusChart");
        canvas.width = 300; // Ajustar el ancho
        canvas.height = 200; // Ajustar la altura

        if (!canvas) {
            console.error("Error: No se encontró el elemento canvas para el gráfico de estado");
            return null;
        }

        const ctx = canvas.getContext("2d");
        const processedData = processStatusData(chartData.status || []);

        // Verificar si hay datos para mostrar
        if (processedData.labels.length === 0) {
            console.warn("No hay datos para mostrar en el gráfico de estado");
            // Mostrar mensaje en el canvas
            ctx.font = "16px Arial";
            ctx.fillStyle = "#666";
            ctx.textAlign = "center";
            ctx.fillText("No hay datos disponibles", canvas.width / 2, canvas.height / 2);
            return null;
        }

        try {
            return new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: processedData.labels,
                    datasets: [
                        {
                            data: processedData.data,
                            backgroundColor: colors.status.slice(0, processedData.labels.length),
                            borderWidth: 3,
                            borderColor: "rgba(255, 255, 255, 0.9)",
                            hoverBorderColor: "rgba(255, 255, 255, 1)",
                            hoverBackgroundColor: colors.status
                                .map((color) => color.replace("0.8", "1"))
                                .slice(0, processedData.labels.length),
                            hoverOffset: 10,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "70%",
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                font: {
                                    size: 14,
                                    family: '"Segoe UI", Arial, sans-serif',
                                },
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: "circle",
                            },
                        },
                        title: {
                            display: true,
                            text: "Equipment status",
                            font: {
                                size: 20,
                                weight: "bold",
                                family: '"Segoe UI", Arial, sans-serif',
                            },
                            padding: {
                                top: 30,
                                bottom: 30,
                            },
                            color:"rgba(58, 60, 177, 0.9)",
                        },
                        tooltip: {
                            backgroundColor: "rgba(255, 255, 255, 0.9)",
                            titleColor: "#333",
                            bodyColor: "#333",
                            bodyFont: {
                                size: 14,
                                family: '"Segoe UI", Arial, sans-serif',
                            },
                            padding: 15,
                            borderColor: "rgba(0, 0, 0, 0.1)",
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => {
                                    const label = context.label || "";
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                },
                            },
                        },
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1500,
                        easing: "easeOutCirc",
                    },
                },
            });
        } catch (error) {
            console.error("Error al crear el gráfico de estado:", error);
            return null;
        }
    }

    // Crear gráfico de cargos
    function createJobTitleChart() {
        console.log("Creando gráfico de cargos...");
          const canvas = document.getElementById("jobTitleChart");
        canvas.width = 300; // Ajustar el ancho
        canvas.height = 200; // Ajustar la altura

        if (!canvas) {
            console.error("Error: No se encontró el elemento canvas para el gráfico de cargos");
            return null;
        }

        const ctx = canvas.getContext("2d");

        // Verificar si hay datos de cargos
        if (!chartData.jobTitle || !Array.isArray(chartData.jobTitle) || chartData.jobTitle.length === 0) {
            console.warn("No hay datos para mostrar en el gráfico de cargos");
            // Mostrar mensaje en el canvas
            ctx.font = "16px Arial";
            ctx.fillStyle = "#666";
            ctx.textAlign = "center";
            ctx.fillText("No hay datos disponibles", canvas.width / 2, canvas.height / 2);
            return null;
        }

        try {
            return new Chart(ctx, {
                type: "bar",
                data: {
                    labels: chartData.jobTitle.map((item) => item.job_title),
                    datasets: [
                        {
                            label: "Número de Empleados",
                            data: chartData.jobTitle.map((item) => Number.parseInt(item.total_employees)),
                            backgroundColor: colors.jobTitle.slice(0, chartData.jobTitle.length),
                            borderWidth: 3,
                            borderRadius: 12,
                            barThickness: 30,
                            borderColor: "rgba(255, 255, 255, 0.9)",
                            hoverBorderColor: "rgba(255, 255, 255, 1)",
                            hoverBackgroundColor: colors.jobTitle
                                .map((color) => color.replace("0.8", "1"))
                                .slice(0, chartData.jobTitle.length),
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: "y",
                    plugins: {
                        legend: {
                            display: false,
                        },
                        title: {
                            display: true,
                            text: "Distribution by job title",
                            font: {
                                size: 20,
                                weight: "bold",
                                family: '"Segoe UI", Arial, sans-serif',
                            },
                            padding: {
                                top: 30,
                                bottom: 30,
                            },
                            color: "rgba(58, 60, 177, 0.9)",
                        },
                        tooltip: {
                            backgroundColor: "rgba(255, 255, 255, 0.9)",
                            titleColor: "#333",
                            bodyColor: "#333",
                            bodyFont: {
                                size: 14,
                                family: '"Segoe UI", Arial, sans-serif',
                            },
                            padding: 15,
                            borderColor: "rgba(0, 0, 0, 0.1)",
                            borderWidth: 1,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                            ticks: {
                                font: {
                                    size: 12,
                                    family: '"Segoe UI", Arial, sans-serif',
                                },
                                color: "rgba(32, 19, 212, 0.9)",
                            },
                        },
                        y: {
                            grid: {
                                display: false,
                                drawBorder: false,
                            },
                            ticks: {
                                font: {
                                    size: 14,
                                    weight: "bold",
                                    family: '"Segoe UI", Arial, sans-serif',
                                },
                                color: "rgba(32, 19, 212, 0.9)",
                            },
                        },
                    },
                    animation: {
                        duration: 1500,
                        easing: "easeOutCirc",
                    },
                },
            });
        } catch (error) {
            console.error("Error al crear el gráfico de cargos:", error);
            return null;
        }
    }

    // Crear gráfico de usuarios por rol
    function createUserRolesChart() {
        console.log("Creando gráfico de roles de usuario...");
         const canvas = document.getElementById("userRolesChart");
        canvas.width = 300; // Ajustar el ancho
        canvas.height = 200; // Ajustar la altura

        if (!canvas) {
            console.error("Error: No se encontró el elemento canvas para el gráfico de roles de usuario");
            return null;
        }

        const ctx = canvas.getContext("2d");

        // Verificar si hay datos de roles de usuario
        if (!chartData.userRoles || !Array.isArray(chartData.userRoles) || chartData.userRoles.length === 0) {
            console.warn("No hay datos para mostrar en el gráfico de roles de usuario");
            // Mostrar mensaje en el canvas
            ctx.font = "16px Arial";
            ctx.fillStyle = "#666";
            ctx.textAlign = "center";
            ctx.fillText("No hay datos disponibles", canvas.width / 2, canvas.height / 2);
            return null;
        }

        try {
            return new Chart(ctx, {
                type: "pie",
                data: {
                    labels: chartData.userRoles.map((item) => item.role),
                    datasets: [
                        {
                            data: chartData.userRoles.map((item) => Number.parseInt(item.total)),
                            backgroundColor: colors.userRoles.slice(0, chartData.userRoles.length),
                            borderWidth: 3,
                            borderColor: "rgba(255, 255, 255, 0.9)",
                            hoverBorderColor: "rgba(255, 255, 255, 1)",
                            hoverBackgroundColor: colors.userRoles
                                .map((color) => color.replace("0.8", "1"))
                                .slice(0, chartData.userRoles.length),
                            hoverOffset: 8,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom",
                            labels: {
                                font: {
                                    size: 14,
                                    family: '"Segoe UI", Arial, sans-serif',
                                },
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: "circle",
                            },
                        },
                        title: {
                            display: true,
                            text: "Registered Users By Role",
                            font: {
                                size: 20,
                                weight: "bold",
                                family: '"Segoe UI", Arial, sans-serif',
                            },
                            padding: {
                                top: 30,
                                bottom: 30,
                            },
                            color: "rgba(58, 60, 177, 0.9)",
                        },
                        tooltip: {
                            backgroundColor: "rgba(255, 255, 255, 0.9)",
                            titleColor: "#333",
                            bodyColor: "#333",
                            bodyFont: {
                                size: 14,
                                family: '"Segoe UI", Arial, sans-serif',
                            },
                            padding: 15,
                            borderColor: "rgba(0, 0, 0, 0.1)",
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => {
                                    const label = context.label || "";
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                },
                            },
                        },
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1500,
                        easing: "easeOutCirc",
                    },
                },
            });
        } catch (error) {
            console.error("Error al crear el gráfico de roles de usuario:", error);
            return null;
        }
    }

    try {
        // Inicializar gráficos
        console.log("Inicializando gráficos...");
        let statusChart = null;
        let jobTitleChart = null;
        let userRolesChart = null;

        // Intentar crear los gráficos
        try {
            statusChart = createStatusChart();
        } catch (error) {
            console.error("Error al crear el gráfico de estado:", error);
        }

        try {
            jobTitleChart = createJobTitleChart();
        } catch (error) {
            console.error("Error al crear el gráfico de cargos:", error);
        }

        try {
            userRolesChart = createUserRolesChart();
        } catch (error) {
            console.error("Error al crear el gráfico de roles de usuario:", error);
        }

        console.log("Gráficos creados:", {
            statusChart: !!statusChart,
            jobTitleChart: !!jobTitleChart,
            userRolesChart: !!userRolesChart,
        });

        // Activar el primer gráfico por defecto
        const chartSwitches = document.querySelectorAll(".chart-switch");
        if (chartSwitches.length > 0) {
            chartSwitches[0].classList.add("active");
            // Mostrar el primer gráfico
            document.getElementById("statusChart").classList.remove("d-none");
        } else {
            console.warn("No se encontraron botones para cambiar entre gráficos");
        }

        // Manejar cambios entre gráficos
        chartSwitches.forEach((button) => {
            button.addEventListener("click", function () {
                const chartId = this.getAttribute("data-chart");
                console.log("Cambiando a gráfico:", chartId);

                // Ocultar todos los gráficos
                document.querySelectorAll(".chart-canvas").forEach((chart) => {
                    chart.classList.add("d-none");
                });

                // Mostrar el gráfico seleccionado
                const selectedChart = document.getElementById(chartId);
                if (selectedChart) {
                    selectedChart.classList.remove("d-none");
                } else {
                    console.error("Error: No se encontró el gráfico con ID:", chartId);
                }

                // Actualizar clase activa en botones
                chartSwitches.forEach((btn) => {
                    btn.classList.remove("active");
                });
                this.classList.add("active");
            });
        });

        // Función para adaptar los gráficos cuando cambia el tamaño de la ventana
        let resizeTimeout;
        window.addEventListener("resize", () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                const chartContainer = document.querySelector(".chart-container");
                if (chartContainer) {
                    const containerWidth = chartContainer.offsetWidth;
                    const containerHeight = window.innerHeight * 0.6; // 60% de la altura de la ventana

                    // Actualizar tamaño de los gráficos si existen
                    if (statusChart && typeof statusChart.resize === "function") {
                        statusChart.resize(containerWidth, containerHeight);
                    }
                    if (jobTitleChart && typeof jobTitleChart.resize === "function") {
                        jobTitleChart.resize(containerWidth, containerHeight);
                    }
                    if (userRolesChart && typeof userRolesChart.resize === "function") {
                        userRolesChart.resize(containerWidth, containerHeight);
                    }

                    // Actualizar opciones responsivas
                    const fontSize = containerWidth < 768 ? 12 : 14;
                    const legendPosition = containerWidth < 768 ? "bottom" : "right";
                    [statusChart, jobTitleChart, userRolesChart].forEach((chart) => {
                        if (chart && chart.options && chart.options.plugins && chart.options.plugins.legend) {
                            chart.options.plugins.legend.position = legendPosition;
                            chart.options.plugins.legend.labels.font.size = fontSize;

                            if (chart.options.plugins.title) {
                                chart.options.plugins.title.font.size = fontSize + 6;
                            }

                            if (typeof chart.update === "function") {
                                chart.update("none");
                            }
                        }
                    });
                }
            }, 250); // Debounce de 250ms
        });
    } catch (error) {
        console.error("Error al inicializar los gráficos:", error);
    }
});