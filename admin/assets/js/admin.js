// Admin JavaScript Functions

// Auto-cálculo da área
function calcularArea() {
    const largura = parseFloat(document.querySelector('input[name="largura"]').value) || 0;
    const comprimento = parseFloat(document.querySelector('input[name="comprimento"]').value) || 0;
    const area = largura * comprimento;
    
    if (area > 0) {
        document.querySelector('input[name="area"]').value = area.toFixed(2);
    }
}

// Validação de formulário
function validarFormulario() {
    const form = document.querySelector('form');
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    });
    
    return isValid;
}

// Formatação de moeda
function formatarMoeda(input) {
    let value = input.value.replace(/\D/g, '');
    value = (value / 100).toFixed(2);
    input.value = value;
}

// Preview de imagem
function previewImagem(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('preview-imagem');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                
                // Animação de fade in
                preview.style.opacity = '0';
                setTimeout(() => {
                    preview.style.transition = 'opacity 0.3s ease';
                    preview.style.opacity = '1';
                }, 100);
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Função para mostrar loading
function showLoading(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="loading"></span> Processando...';
    button.disabled = true;
    
    // Restaurar após 3 segundos (caso não haja redirecionamento)
    setTimeout(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    }, 3000);
}

// Validação em tempo real
function setupRealTimeValidation() {
    const inputs = document.querySelectorAll('input[required], textarea[required], select[required]');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid') && this.value.trim()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    });
}

// Confirmações elegantes
function confirmarAcao(mensagem, callback) {
    if (confirm(mensagem)) {
        callback();
    }
}

// Auto-save draft (opcional)
function autoSaveDraft() {
    const form = document.querySelector('form');
    if (!form) return;
    
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    localStorage.setItem('admin_draft', JSON.stringify(data));
}

// Restaurar draft (opcional)
function restaurarDraft() {
    const draft = localStorage.getItem('admin_draft');
    if (!draft) return;
    
    try {
        const data = JSON.parse(draft);
        
        Object.keys(data).forEach(key => {
            const input = document.querySelector(`[name="${key}"]`);
            if (input && input.type !== 'file') {
                input.value = data[key];
            }
        });
    } catch (e) {
        console.log('Erro ao restaurar rascunho:', e);
    }
}

// Limpar draft
function limparDraft() {
    localStorage.removeItem('admin_draft');
}

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    // Setup validação em tempo real
    setupRealTimeValidation();
    
    // Auto-save a cada 30 segundos
    setInterval(autoSaveDraft, 30000);
    
    // Restaurar draft se existir
    restaurarDraft();
    
    // Limpar draft quando form for submetido com sucesso
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            setTimeout(limparDraft, 1000);
        });
    });
    
    // Auto-dismiss alerts após 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
