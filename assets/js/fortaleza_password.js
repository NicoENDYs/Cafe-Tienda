        // Medidor de fortaleza de contraseña
        function updatePasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('passwordStrengthBar');
            let strength = 0;
            
            // Longitud mínima
            if (password.length >= 8) strength += 20;
            if (password.length >= 12) strength += 20;
            
            // Contiene números
            if (/\d/.test(password)) strength += 20;
            
            // Contiene letras mayúsculas y minúsculas
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 20;
            
            // Contiene caracteres especiales
            if (/[^a-zA-Z0-9]/.test(password)) strength += 20;
            
            // Actualizar barra de progreso
            strengthBar.style.width = strength + '%';
            
            // Cambiar color según fortaleza
            if (strength < 40) {
                strengthBar.style.backgroundColor = '#ff0000'; // Rojo (débil)
            } else if (strength < 70) {
                strengthBar.style.backgroundColor = '#ffcc00'; // Amarillo (media)
            } else {
                strengthBar.style.backgroundColor = '#00cc00'; // Verde (fuerte)
            }
        }
        