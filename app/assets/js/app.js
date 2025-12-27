/**
 * CHM Sistema - JavaScript Principal
 * @author ch-mestriner (https://ch-mestriner.com.br)
 * @date 23/12/2025
 * @version 1.0.0
 */

// Máscaras de input
document.addEventListener('DOMContentLoaded', function() {
    // Máscara de telefone
    document.querySelectorAll('[data-mask="phone"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            } else {
                value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            }
            e.target.value = value;
        });
    });

    // Máscara de CPF
    document.querySelectorAll('[data-mask="cpf"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            e.target.value = value;
        });
    });

    // Máscara de CNPJ
    document.querySelectorAll('[data-mask="cnpj"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
            e.target.value = value;
        });
    });

    // Máscara de CEP
    document.querySelectorAll('[data-mask="cep"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
            e.target.value = value;
        });
    });

    // Máscara de placa
    document.querySelectorAll('[data-mask="plate"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            if (value.length > 3) {
                value = value.substring(0, 3) + '-' + value.substring(3, 7);
            }
            e.target.value = value;
        });
    });

    // Máscara de moeda
    document.querySelectorAll('[data-mask="money"]').forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (parseInt(value) / 100).toFixed(2);
            value = value.replace('.', ',');
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            e.target.value = 'R$ ' + value;
        });
    });

    // Busca CEP automática ao digitar 8 dígitos
    document.querySelectorAll('[data-cep-search]').forEach(input => {
        input.addEventListener('input', async function(e) {
            const cep = e.target.value.replace(/\D/g, '');
            if (cep.length !== 8) return;

            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                
                if (!data.erro) {
                    const form = e.target.closest('form');
                    if (form.querySelector('[name="address"]')) form.querySelector('[name="address"]').value = data.logradouro || '';
                    if (form.querySelector('[name="neighborhood"]')) form.querySelector('[name="neighborhood"]').value = data.bairro || '';
                    if (form.querySelector('[name="city"]')) form.querySelector('[name="city"]').value = data.localidade || '';
                    if (form.querySelector('[name="state"]')) form.querySelector('[name="state"]').value = data.uf || '';
                }
            } catch (error) {
                console.error('Erro ao buscar CEP:', error);
            }
        });
    });

    // Confirmação de exclusão
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function(e) {
            const message = el.dataset.confirm || 'Tem certeza que deseja excluir?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Form AJAX
    document.querySelectorAll('form[data-ajax]').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn?.innerHTML;
            
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Aguarde...';
            }

            try {
                const formData = new FormData(form);
                formData.append('_token', CSRF_TOKEN);

                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    if (result.data?.redirect) {
                        setTimeout(() => window.location.href = result.data.redirect, 1000);
                    } else if (form.dataset.reload) {
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    showToast(result.message, 'error');
                    if (result.errors) {
                        Object.keys(result.errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = result.errors[field][0];
                                input.parentNode.appendChild(feedback);
                            }
                        });
                    }
                }
            } catch (error) {
                showToast('Erro ao processar requisição.', 'error');
                console.error(error);
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        });
    });

    // Limpa validação ao digitar
    document.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const feedback = this.parentNode.querySelector('.invalid-feedback');
            if (feedback) feedback.remove();
        });
    });

    // Data picker - formato BR
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', function() {
            // Mantém o formato nativo do HTML5
        });
    });

    // Auto-cálculo de valores
    const valueInput = document.querySelector('[name="value"]');
    const extrasInput = document.querySelector('[name="extras"]');
    const discountInput = document.querySelector('[name="discount"]');
    const totalDisplay = document.querySelector('#total-display');

    function calculateTotal() {
        if (!valueInput || !totalDisplay) return;
        
        const value = parseFloat(valueInput.value.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
        const extras = parseFloat(extrasInput?.value.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
        const discount = parseFloat(discountInput?.value.replace(/[^\d,]/g, '').replace(',', '.')) || 0;
        
        const total = value + extras - discount;
        totalDisplay.textContent = formatMoney(total);
    }

    [valueInput, extrasInput, discountInput].forEach(input => {
        if (input) input.addEventListener('input', calculateTotal);
    });
});

// Formata moeda
function formatMoney(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

// Formata data BR
function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('pt-BR');
}

// Toast notification
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Loading overlay
function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'spinner-overlay';
    overlay.id = 'loading-overlay';
    overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div>';
    document.body.appendChild(overlay);
}

function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) overlay.remove();
}

// API Request helper
async function apiRequest(endpoint, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(APP_URL + endpoint, options);
    return response.json();
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions for global use
window.showToast = showToast;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.apiRequest = apiRequest;
window.formatMoney = formatMoney;
window.formatDate = formatDate;
