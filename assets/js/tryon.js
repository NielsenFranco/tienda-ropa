document.addEventListener("DOMContentLoaded", () => {
    const boton = document.getElementById("probarPrendaBtn");
    const contenedorResultado = document.getElementById("resultadoTryOn");
    const imagenResultado = document.getElementById("imagenResultado");

    if (!boton) return;

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

    boton.addEventListener("click", async function () {
        const prendaId = this.dataset.prendaId;
        const tipoPrenda = this.dataset.tipoPrenda || "Top";

        if (!prendaId) {
            alert("ID de prenda no encontrado.");
            return;
        }

        spinner.style.display = "flex";
        imagenResultado.style.display = "none";
        contenedorResultado.style.display = "block";

        try {
            const response = await fetch(TRYON_PROXY_URL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: new URLSearchParams({
                    id_prenda: prendaId,
                    categoria: tipoPrenda
                })
            });

            const resultado = await response.json();

            if (!resultado.job_id) {
                throw new Error("La IA no devolvió un job_id válido.");
            }

            const jobId = resultado.job_id;
            let status = resultado.status;
            let outputImage = null;

            // 🔄 Polling hasta que esté listo
            while (status !== "completed") {
                await new Promise(r => setTimeout(r, 2000)); // esperar 2 segundos

                const checkResponse = await fetch(`${BASE_URL}/proxy_check_job.php?job_id=${jobId}`);
                const checkResult = await checkResponse.json();
                status = checkResult.status;

                if (status === "completed" && checkResult.output?.[0]?.image_url) {
                    outputImage = checkResult.output[0].image_url;
                    break;
                } else if (status === "failed") {
                    throw new Error("La IA falló al procesar la imagen.");
                }
            }

            if (outputImage) {
                imagenResultado.src = outputImage;
                imagenResultado.style.display = "block";
            } else {
                throw new Error("No se pudo obtener la imagen procesada.");
            }

        } catch (err) {
            console.error("❌ Error en la predicción:", err);
            alert("❌ Error: " + (err?.message || "Error desconocido al contactar la IA."));
        } finally {
            spinner.style.display = "none";
        }
    });
});
