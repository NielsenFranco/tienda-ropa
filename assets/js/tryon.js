document.addEventListener("DOMContentLoaded", () => {
    const boton = document.getElementById("probarPrendaBtn");
    const contenedorResultado = document.getElementById("resultadoTryOn");
    const imagenResultado = document.getElementById("imagenResultado");

    console.log("✅ tryon.js cargado correctamente");

    if (!boton) {
        console.log("❌ Botón no encontrado");
        return;
    }

    // RUTA BASE ABSOLUTA
    const BASE_PATH = 'http://localhost/tienda-ropa/';
    console.log("📍 Ruta base:", BASE_PATH);

    // Crear spinner
    const spinner = document.createElement("div");
    spinner.innerHTML = `
        <div class="spinner" style="margin-top: 20px; display: flex; justify-content: center;">
            <div class="loader" style="
                width: 48px;
                height: 48px;
                border: 5px solid #ccc;
                border-top: 5px solid #A47764;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            "></div>
        </div>
    `;
    contenedorResultado.appendChild(spinner);
    spinner.style.display = "none";

    const style = document.createElement("style");
    style.innerHTML = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Función para mostrar progreso
    function mostrarProgreso(mensaje, contenedor, tiempoEstimado = "30-60 segundos") {
    contenedor.innerHTML = `
        <div style="text-align: center; padding: 30px;">
            <div class="loader" style="
                width: 60px;
                height: 60px;
                border: 6px solid #f3f3f3;
                border-top: 6px solid #A47764;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin: 0 auto 20px;
            "></div>
            <h3 style="color: #333; margin-bottom: 10px;">Procesando Virtual Try-On</h3>
            <p style="color: #666; font-size: 16px; margin: 0 0 5px 0;">${mensaje}</p>
            <p style="color: #999; font-size: 14px; margin: 0;">Tiempo estimado: ${tiempoEstimado}</p>
            <p style="color: #888; font-size: 13px; margin: 15px 0 0 0;">⏳ Por favor, no cierre esta página</p>
        </div>
    `;
    }

    // 🔄 FUNCIÓN PARA OBTENER DATOS DESDE LA BASE DE DATOS
    async function obtenerDatosTryon(prendaId, usuarioId) {
        try {
            console.log("📥 Obteniendo datos desde BD - Prenda:", prendaId, "Usuario:", usuarioId);
            
            const apiUrl = `${BASE_PATH}api/obtener-datos-tryon.php?prenda_id=${prendaId}&usuario_id=${usuarioId}`;
            console.log("🌐 URL de API:", apiUrl);
            
            const response = await fetch(apiUrl);
            console.log("✅ Respuesta HTTP recibida, status:", response.status);
            
            const responseText = await response.text();
            console.log("📄 Respuesta completa:", responseText);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status} - ${responseText}`);
            }
            
            const data = JSON.parse(responseText);
            console.log("📊 Datos parseados:", data);
            
            if (!data.success) {
                throw new Error(data.error || "Error al obtener datos de la base de datos");
            }
            
            return data;
            
        } catch (error) {
            console.error("❌ Error obteniendo datos:", error);
            throw error;
        }
    }

    // 🔄 FUNCIÓN PARA HUHU.AI API
    async function procesarConHUHU(personImageUrl, garmentImageUrl, garmentCategory) {
        console.log("🌐 Enviando a HUHU.ai API...");
        console.log("👤 Person Image:", personImageUrl);
        console.log("👕 Garment Image:", garmentImageUrl);
        console.log("📦 Category:", garmentCategory);
        
        const proxyUrl = `${BASE_PATH}proxy_tryon_hf.php`;
        console.log("🌐 URL de Proxy:", proxyUrl);
        
        try {
            const response = await fetch(proxyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    personImage: personImageUrl,
                    garmentImage: garmentImageUrl,
                    garmentCategory: garmentCategory
                })
            });

            console.log("✅ Respuesta HTTP de HUHU.ai, status:", response.status);
            
            const responseText = await response.text();
            console.log("📄 Respuesta CRUDA:", responseText);
            
            if (!response.ok) {
                let errorMessage = `HTTP error! status: ${response.status}`;
                try {
                    const errorData = JSON.parse(responseText);
                    errorMessage = errorData.error || errorData.message || errorMessage;
                } catch (e) {
                    errorMessage = responseText || errorMessage;
                }
                throw new Error(errorMessage);
            }
            
            const result = JSON.parse(responseText);
            console.log("📄 Respuesta JSON:", result);
            
            return result;
            
        } catch (error) {
            console.error("❌ Error en procesarConHUHU:", error);
            throw error;
        }
    }

    boton.addEventListener("click", async function () {
        const prendaId = this.dataset.prendaId;
        const usuarioId = this.dataset.usuarioId;

        console.log("🔄 Click en botón - Prenda ID:", prendaId, "Usuario ID:", usuarioId);

        if (!prendaId || !usuarioId) {
            alert("Datos incompletos: ID de prenda o usuario no encontrado.");
            return;
        }

        spinner.style.display = "flex";
        imagenResultado.style.display = "none";
        contenedorResultado.style.display = "block";

        try {
            console.log("📥 Paso 1: Obteniendo datos desde la base de datos...");
            
            const datos = await obtenerDatosTryon(prendaId, usuarioId);
            console.log("✅ Datos obtenidos:", datos);

            console.log("🌐 Paso 2: Enviando a HUHU.ai API...");
            
            // Mostrar mensaje de progreso
            mostrarProgreso("HUHU.ai está procesando tu imagen...", contenedorResultado, "30-90 segundos");
            
            const resultado = await procesarConHUHU(
                datos.person_image_url, 
                datos.garment_image_url,
                datos.garment_category
            );

            if (resultado.success && resultado.image) {
                console.log("🎉 Procesamiento exitoso - Mostrando imagen resultado");
                
                const resultadoAdaptado = {
                    success: true,
                    result_image: resultado.image,
                    message: `Virtual Try-On completado con HUHU.ai (Categoría: ${datos.garment_category})`
                };
                
                mostrarResultadoExitoso(resultadoAdaptado, prendaId, contenedorResultado, imagenResultado);
            } else {
                throw new Error(resultado.error || "Error desconocido en HUHU.ai");
            }

        } catch (err) {
            console.error("❌ Error en la predicción:", err);
            
            contenedorResultado.innerHTML = "";
            const mensajeError = document.createElement("div");
            mensajeError.innerHTML = `
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;">
                    <h3 style="margin: 0 0 10px 0;">❌ Error en el Virtual Try-On</h3>
                    <p style="margin: 0; font-weight: bold;">${err.message}</p>
                    <p style="margin: 5px 0; font-size: 14px;">Verifica tu API key de HUHU.ai y que las imágenes sean válidas.</p>
                    ${err.message.includes('Tiempo de espera') ? 
                        '<p style="margin: 5px 0; font-size: 14px;">El servidor está ocupado. Intenta nuevamente en unos momentos.</p>' : 
                        ''}
                </div>
                <button class="btn" onclick="location.reload()" style="margin-top: 10px;">🔄 Reintentar</button>
            `;
            contenedorResultado.appendChild(mensajeError);
        } finally {
            spinner.style.display = "none";
        }
    });
});

// Función para mostrar resultado exitoso
function mostrarResultadoExitoso(resultado, prendaId, contenedorResultado, imagenResultado) {
    // Limpiar contenedor
    contenedorResultado.innerHTML = "";
    
    // Crear y configurar imagen
    imagenResultado.src = "data:image/jpeg;base64," + resultado.result_image;
    imagenResultado.style.display = "block";
    imagenResultado.alt = "Resultado Virtual Try-On";
    imagenResultado.style.maxWidth = "100%";
    imagenResultado.style.maxHeight = "500px";
    imagenResultado.style.borderRadius = "8px";
    imagenResultado.style.boxShadow = "0 4px 8px rgba(0,0,0,0.1)";
    imagenResultado.style.margin = "0 auto";
    imagenResultado.style.display = "block";
    
    // Mensaje de éxito
    const mensajeExito = document.createElement("p");
    mensajeExito.textContent = "✅ " + (resultado.message || "Virtual Try-On completado exitosamente");
    mensajeExito.style.color = "green";
    mensajeExito.style.marginTop = "15px";
    mensajeExito.style.fontWeight = "bold";
    mensajeExito.style.textAlign = "center";
    
    // Contenedor de botones
    const contenedorBotones = document.createElement("div");
    contenedorBotones.style.marginTop = "15px";
    contenedorBotones.style.textAlign = "center";
    
    // Botón descargar
    const botonDescargar = document.createElement("button");
    botonDescargar.textContent = "📥 Descargar Resultado";
    botonDescargar.className = "btn";
    botonDescargar.style.margin = "0 10px 10px 0";
    botonDescargar.onclick = function() {
        const link = document.createElement("a");
        link.href = "data:image/jpeg;base64," + resultado.result_image;
        link.download = `virtual-tryon-${prendaId}-${Date.now()}.jpg`;
        link.click();
    };
    
    // Botón probar otra prenda
    const botonOtra = document.createElement("button");
    botonOtra.textContent = "🔄 Probar Otra Prenda";
    botonOtra.className = "btn";
    botonOtra.style.margin = "0 0 10px 10px";
    botonOtra.onclick = function() {
        contenedorResultado.style.display = "none";
        contenedorResultado.innerHTML = "";
        // Resetear la imagen
        imagenResultado.src = "";
        imagenResultado.style.display = "none";
    };
    
    // Ensamblar todo
    contenedorBotones.appendChild(botonDescargar);
    contenedorBotones.appendChild(botonOtra);
    
    contenedorResultado.appendChild(imagenResultado);
    contenedorResultado.appendChild(mensajeExito);
    contenedorResultado.appendChild(contenedorBotones);
    
    // Mostrar contenedor
    contenedorResultado.style.display = "block";
}