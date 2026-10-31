import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;

const fontFamily = 'Tajawal, system-ui, sans-serif';

Chart.defaults.font.family = fontFamily;
Chart.defaults.color = '#837164';

function revenueChart(canvas, payload) {
    if (!canvas) {
        return;
    }

    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(178, 92, 56, 0.24)');
    gradient.addColorStop(0.65, 'rgba(178, 92, 56, 0.06)');
    gradient.addColorStop(1, 'rgba(178, 92, 56, 0)');

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: payload.labels,
            datasets: [
                {
                    data: payload.values,
                    borderColor: '#b25c38',
                    backgroundColor: gradient,
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.42,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#fffdf9',
                    pointHoverBorderColor: '#b25c38',
                    pointHoverBorderWidth: 2.5,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    rtl: true,
                    textDirection: 'rtl',
                    backgroundColor: '#2a1c15',
                    titleFont: { family: fontFamily, weight: '700', size: 12 },
                    bodyFont: { family: fontFamily, size: 12 },
                    padding: 12,
                    cornerRadius: 12,
                    displayColors: false,
                    callbacks: {
                        label: (context) => `${Number(context.parsed.y).toFixed(3)} د.أ`,
                    },
                },
            },
            scales: {
                x: {
                    reverse: true,
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#9a8877',
                        font: { family: fontFamily, size: 11 },
                        maxRotation: 0,
                        autoSkipPadding: 14,
                        align: 'inner',
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(233, 222, 206, 0.7)' },
                    border: { display: false },
                    ticks: {
                        color: '#9a8877',
                        font: { family: fontFamily, size: 11 },
                        padding: 8,
                        callback: (value) => `${value} د.أ`,
                    },
                },
            },
        },
    });
}

function doughnutChart(canvas, payload) {
    if (!canvas) {
        return;
    }

    const unit = payload.unit ? ` ${payload.unit}` : '';

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: payload.labels,
            datasets: [
                {
                    data: payload.values,
                    backgroundColor: payload.colors,
                    borderColor: '#fffdf9',
                    borderWidth: 3,
                    hoverOffset: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    rtl: true,
                    textDirection: 'rtl',
                    backgroundColor: '#2a1c15',
                    titleFont: { family: fontFamily, weight: '700', size: 12 },
                    bodyFont: { family: fontFamily, size: 12 },
                    padding: 12,
                    cornerRadius: 12,
                    displayColors: false,
                    callbacks: {
                        label: (context) => `${context.label}: ${context.parsed}${unit}`,
                    },
                },
            },
        },
    });
}

function barChart(canvas, payload) {
    if (!canvas) {
        return;
    }

    const unit = payload.unit ? ` ${payload.unit}` : '';

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: payload.labels,
            datasets: [
                {
                    data: payload.values,
                    backgroundColor: payload.colors || '#b25c38',
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 34,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    rtl: true,
                    textDirection: 'rtl',
                    backgroundColor: '#2a1c15',
                    titleFont: { family: fontFamily, weight: '700', size: 12 },
                    bodyFont: { family: fontFamily, size: 12 },
                    padding: 12,
                    cornerRadius: 12,
                    displayColors: false,
                    callbacks: {
                        label: (context) => `${context.parsed.y}${unit}`,
                    },
                },
            },
            scales: {
                x: {
                    reverse: true,
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        color: '#9a8877',
                        font: { family: fontFamily, size: 11 },
                        maxRotation: 0,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(233, 222, 206, 0.7)' },
                    border: { display: false },
                    ticks: {
                        color: '#9a8877',
                        font: { family: fontFamily, size: 11 },
                        padding: 8,
                        precision: 0,
                    },
                },
            },
        },
    });
}

window.Dukkani = { revenueChart, doughnutChart, barChart };

function money(value) {
    const amount = Number(value || 0) / 1000;

    return amount.toLocaleString('en-US', {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3,
    }) + ' د.أ';
}

function productForm(config) {
    return {
        qty: 1,
        notes: '',
        base: Number(config.base || 0),
        options: config.options || [],
        deltas: config.deltas || {},
        selected: config.selected || {},

        get unit() {
            let total = this.base;

            for (const [optionId, valueId] of Object.entries(this.selected)) {
                if (valueId === null || valueId === undefined || valueId === '') {
                    continue;
                }

                total += Number(this.deltas[`${optionId}:${valueId}`] || 0);
            }

            return total;
        },

        get total() {
            return this.unit * Math.max(1, Number(this.qty) || 1);
        },

        get missingRequired() {
            return this.options.some((option) => option.required && !this.selected[option.id]);
        },

        money,
    };
}

function checkoutPage(config) {
    return {
        fulfillment: config.fulfillment || 'delivery',
        payment: config.payment || 'cash',
        subtotal: Number(config.subtotal || 0),
        deliveryFee: Number(config.deliveryFee || 0),
        depositPercent: Number(config.depositPercent || 0),
        deliveryEnabled: Boolean(config.deliveryEnabled),
        pickupEnabled: Boolean(config.pickupEnabled),
        date: config.date || '',
        time: config.time || '',
        blocked: config.blocked || [],
        hours: config.hours || {},
        slots: config.slots || [],
        earliestDate: config.earliestDate || '',
        earliestTime: config.earliestTime || '',
        name: config.name || '',
        phone: config.phone || '',
        address: config.address || '',
        notes: config.notes || '',
        consent: false,

        init() {
            if (this.fulfillment === 'delivery' && !this.deliveryEnabled) {
                this.fulfillment = 'pickup';
            }

            if (this.fulfillment === 'pickup' && !this.pickupEnabled) {
                this.fulfillment = 'delivery';
            }

            this.$watch('date', () => {
                if (!this.availableSlots.includes(this.time)) {
                    this.time = this.availableSlots[0] || '';
                }
            });
        },

        get day() {
            if (!this.date) {
                return null;
            }

            return new Date(`${this.date}T12:00:00`).getDay();
        },

        get dayHours() {
            return this.hours[this.day] || { closed: true, open: null, close: null };
        },

        get isBlocked() {
            return this.blocked.includes(this.date);
        },

        get closed() {
            return this.isBlocked || this.dayHours.closed;
        },

        get closedReason() {
            if (this.isBlocked) {
                return 'المتجر مغلق في هذا التاريخ (مناسبة خاصة أو صيانة). اختاري يوماً آخر.';
            }

            return 'المتجر مغلق في هذا اليوم من الأسبوع. اختاري يوماً آخر.';
        },

        get availableSlots() {
            if (this.closed) {
                return [];
            }

            const open = this.dayHours.open;
            const close = this.dayHours.close;

            return this.slots.filter((slot) => slot >= open
                && slot <= close
                && !(this.date === this.earliestDate && slot < this.earliestTime));
        },

        get hoursLabel() {
            if (!this.dayHours || this.dayHours.closed || !this.dayHours.open) {
                return 'مغلق';
            }

            return `${this.dayHours.open} — ${this.dayHours.close}`;
        },

        get fee() {
            return this.fulfillment === 'delivery' ? this.deliveryFee : 0;
        },

        get total() {
            return this.subtotal + this.fee;
        },

        get deposit() {
            return this.payment === 'cash' ? 0 : Math.round((this.total * this.depositPercent) / 100);
        },

        get balance() {
            return Math.max(0, this.total - this.deposit);
        },

        fillDemo() {
            this.name = 'نور الخالدي';
            this.phone = '0791111001';
            this.address = 'عمّان، عبدون، شارع الأمير هاشم، بناية ١٤، الطابق الثاني';
            this.notes = this.notes || 'مناسبة عائلية صغيرة، ويا ريت التغليف يكون هدية.';
            this.consent = true;
        },

        money,
    };
}

function optionEditor(initial) {
    return {
        options: Array.isArray(initial) ? initial : [],

        addOption() {
            this.options.push({
                name: '',
                type: 'single',
                is_required: false,
                values: [{ value: '', price_delta: 0 }],
            });
        },

        removeOption(index) {
            this.options.splice(index, 1);
        },

        addValue(option) {
            option.values.push({ value: '', price_delta: 0 });
        },

        removeValue(option, index) {
            if (option.values.length > 1) {
                option.values.splice(index, 1);
            }
        },
    };
}

Alpine.data('productForm', productForm);
Alpine.data('checkoutPage', checkoutPage);
Alpine.data('optionEditor', optionEditor);

Alpine.start();
