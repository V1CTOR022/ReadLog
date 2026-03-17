// Validação do formulário e data mínima
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formTarefa');
    const dataInput = document.getElementById('data_vencimento');
    
    // Define a data mínima como hoje
    const today = new Date().toISOString().split('T')[0];
    dataInput.min = today;
    
    // Validação do formulário
    form.addEventListener('submit', function(e) {
        const descricao = document.getElementById('descricao').value.trim();
        const data = dataInput.value;
        
        if (descricao === '') {
            e.preventDefault();
            showNotification('Por favor, digite o nome da tarefa!', 'error');
            return;
        }
        
        if (data === '') {
            e.preventDefault();
            showNotification('Por favor, selecione uma data de vencimento!', 'error');
            return;
        }
        
        // Adiciona classe de loading ao botão
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.classList.add('loading');
        submitBtn.disabled = true;
    });
    
    // Adiciona animações aos cards de tarefa
    animateTaskCards();
    
    // Inicializa tooltips
    initTooltips();
});

// Função para mostrar notificações
function showNotification(message, type = 'info') {
    // Remove notificação existente
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Cria nova notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'error' ? '#f56565' : '#48bb78'};
        color: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideIn 0.3s ease;
        font-weight: 500;
    `;
    
    document.body.appendChild(notification);
    
    // Remove notificação após 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Animação para cards de tarefa
function animateTaskCards() {
    const cards = document.querySelectorAll('.task-card');
    
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        
        // Adiciona efeito hover suave
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
}

// Inicializa tooltips
function initTooltips() {
    const buttons = document.querySelectorAll('.btn[title]');
    
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function(e) {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.title;
            tooltip.style.cssText = `
                position: absolute;
                background: #2d3748;
                color: white;
                padding: 5px 10px;
                border-radius: 5px;
                font-size: 0.85rem;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                white-space: nowrap;
                z-index: 1000;
                pointer-events: none;
                margin-bottom: 5px;
            `;
            
            this.style.position = 'relative';
            this.appendChild(tooltip);
        });
        
        button.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}

// Confirmação antes de excluir
document.querySelectorAll('form[action="delete_tarefa.php"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        if (!confirm('Tem certeza que deseja excluir esta tarefa?')) {
            e.preventDefault();
        }
    });
});

// Adiciona classe de loading ao clicar em botões de ação
document.querySelectorAll('.action-form button').forEach(button => {
    button.addEventListener('click', function() {
        this.classList.add('loading');
        this.disabled = true;
    });
});

// Atualiza contadores em tempo real (opcional - se quiser fazer requisições AJAX)
function updateTaskCounts() {
    // Esta função pode ser implementada com AJAX
    // para atualizar os contadores sem recarregar a página
    console.log('Contadores atualizados');
}

// Animação de entrada para o header
window.addEventListener('load', function() {
    const header = document.querySelector('.header');
    header.style.opacity = '0';
    header.style.transform = 'translateY(-20px)';
    
    setTimeout(() => {
        header.style.transition = 'all 0.5s ease';
        header.style.opacity = '1';
        header.style.transform = 'translateY(0)';
    }, 100);
});

// Adiciona estilos de animação ao documento
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);