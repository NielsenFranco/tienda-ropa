/**
 * Profile Manager - Gestión del perfil de usuario
 * Funcionalidades para la página de perfil de cliente
 */
const profileManager = {
    /**
     * Alternar la visibilidad de la sección de subida de foto
     */
    togglePhotoUpload: function() {
        const photoSection = document.getElementById('photo-upload-section');
        if (photoSection.style.display === 'none' || photoSection.style.display === '') {
            photoSection.style.display = 'block';
            this.animateSectionAppear(photoSection);
        } else {
            this.animateSectionDisappear(photoSection);
        }
    },

    /**
     * Animación para mostrar la sección
     */
    animateSectionAppear: function(section) {
        section.style.opacity = '0';
        section.style.transform = 'translateY(-10px)';
        section.style.display = 'block';
        
        setTimeout(() => {
            section.style.transition = 'all 0.3s ease';
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, 10);
    },

    /**
     * Animación para ocultar la sección
     */
    animateSectionDisappear: function(section) {
        section.style.transition = 'all 0.3s ease';
        section.style.opacity = '0';
        section.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            section.style.display = 'none';
        }, 300);
    },

    /**
     * Previsualizar imagen antes de subir
     */
    previewImage: function(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            const profilePicture = document.querySelector('.profile-picture img');
            const defaultAvatar = document.querySelector('.default-avatar');
            
            reader.onload = function(e) {
                if (profilePicture) {
                    profilePicture.src = e.target.result;
                } else if (defaultAvatar) {
                    // Reemplazar el avatar por defecto con la imagen
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = 'Nueva foto de perfil';
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';
                    
                    defaultAvatar.parentNode.replaceChild(img, defaultAvatar);
                }
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    },

    /**
     * Validar formulario antes de enviar
     */
    validateForm: function(form) {
        const username = form.querySelector('#username').value.trim();
        const email = form.querySelector('#email').value.trim();
        const fileInput = form.querySelector('#foto_perfil');
        
        // Validar campos requeridos
        if (!username || !email) {
            this.showMessage('Todos los campos son obligatorios.', 'error');
            return false;
        }
        
        // Validar formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            this.showMessage('Por favor, ingresa un email válido.', 'error');
            return false;
        }
        
        // Validar archivo si se seleccionó uno
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!allowedTypes.includes(file.type)) {
                this.showMessage('Solo se permiten archivos JPG, PNG o GIF.', 'error');
                return false;
            }
            
            if (file.size > maxSize) {
                this.showMessage('El archivo es demasiado grande. Máximo 5MB.', 'error');
                return false;
            }
        }
        
        return true;
    },

    /**
     * Mostrar mensaje temporal
     */
    showMessage: function(message, type) {
        // Crear elemento de mensaje
        const messageDiv = document.createElement('div');
        messageDiv.className = `mensaje ${type} temporary-message`;
        messageDiv.textContent = message;
        messageDiv.style.marginTop = '1rem';
        
        // Insertar después del formulario
        const form = document.querySelector('.profile-form');
        form.parentNode.insertBefore(messageDiv, form.nextSibling);
        
        // Remover después de 5 segundos
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 5000);
    },

    /**
     * Inicializar event listeners
     */
    init: function() {
        // Event listener para previsualizar imagen
        const fileInput = document.getElementById('foto_perfil');
        if (fileInput) {
            fileInput.addEventListener('change', () => {
                this.previewImage(fileInput);
            });
        }
        
        // Event listener para validar formulario
        const form = document.querySelector('.profile-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        }
        
        // Event listener para el icono de edición (alternativa al onclick)
        const editIcon = document.querySelector('.edit-icon');
        if (editIcon) {
            editIcon.addEventListener('click', () => {
                this.togglePhotoUpload();
            });
        }
        
        console.log('Profile Manager inicializado correctamente');
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    profileManager.init();
});