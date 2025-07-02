import gradio as gr
import os
import time
import requests
from PIL import Image
from flask import Flask, request, jsonify
import threading
import base64

# CSS de la interfaz (puede eliminarse si solo usás API)
tryon_css = """
#col-garment, #col-person, #col-result, #col-examples {
    margin: 0 auto;
    max-width: 420px;
}
#garm_img, #person_img, #result_img {
    aspect-ratio: 3 / 4;
    width: 100%;
    max-height: 560px;
    object-fit: contain;
}
"""

# Procesamiento básico de imágenes
def preprocess_img(img_path, max_size=1024):
    if img_path is None:
        return None
    img = Image.open(img_path)
    if max(img.size) > max_size:
        img.thumbnail((max_size, max_size))
        img.save(img_path)
    return img_path

# Lógica principal de la IA
def run_turbo(person_img, garm_img, category="Top"):
    if person_img is None or garm_img is None:
        return None, "No input image"

    tryon_url = "https://huhu-ai.hf.space/tryon/v1"
    payload = {
        'garment_type': category,
        'model_type': "SD_V1",
        'repaint_other_garment': 'false'
    }
    files = {
        'image_garment_file': open(garm_img, 'rb'),
        'image_model_file': open(person_img, 'rb'),
    }

    response = requests.post(tryon_url, data=payload, files=files)
    if response.status_code != 200:
        return None, "Error en IA externa"

    job_data = response.json()
    job_id = job_data.get("job_id")
    status = job_data.get("status")

    if not job_id:
        return None, "No job_id recibido"

    # Esperar resultado
    for _ in range(40):
        r = requests.get(f"https://huhu-ai.hf.space/requests/v1?job_id={job_id}")
        if r.ok:
            data = r.json()
            if data["status"] == "completed":
                image_url = data['output'][0]['image_url']
                return image_url, "OK"
            elif data["status"] == "failed":
                return None, "Fallo en procesamiento"
        time.sleep(1.5)

    return None, "Timeout"

# Crear app de Flask
flask_app = Flask(__name__)

@flask_app.route('/predict', methods=['POST'])
def predict_api():
    model_img = request.files.get('image_model_file')
    garment_img = request.files.get('image_garment_file')
    garment_type = request.form.get('garment_type', 'Top')

    if not model_img or not garment_img:
        return jsonify({'error': 'Faltan imágenes'}), 400

    model_path = "tmp_model.jpg"
    garment_path = "tmp_garment.jpg"
    model_img.save(model_path)
    garment_img.save(garment_path)

    output_url, estado = run_turbo(model_path, garment_path, garment_type)

    if output_url:
        img_response = requests.get(output_url)
        if img_response.status_code == 200:
            b64img = base64.b64encode(img_response.content).decode('utf-8')
            return jsonify({"data": [f"data:image/jpeg;base64,{b64img}"]})
        else:
            return jsonify({"error": "No se pudo cargar imagen resultante"}), 500
    else:
        return jsonify({"error": "Error al contactar la IA externa", "detalle": estado}), 500

# Iniciar ambos servidores
def iniciar_gradio():
    with gr.Blocks(css=tryon_css) as demo:
        gr.Markdown("### Huhu Try-on Local Demo")
        person_img = gr.Image(label="Imagen del usuario", type="filepath")
        garment_img = gr.Image(label="Imagen de la prenda", type="filepath")
        category = gr.Dropdown(choices=["Top", "Bottom", "Fullbody"], value="Top", label="Tipo de prenda")
        btn = gr.Button("Probar IA")
        resultado = gr.Image(label="Resultado")

        btn.click(fn=run_turbo, inputs=[person_img, garment_img, category], outputs=[resultado, gr.Textbox(visible=False)])

    demo.queue().launch(share=False)

def iniciar_flask():
    flask_app.run(port=5000)

# Lanzar ambos en paralelo
threading.Thread(target=iniciar_gradio).start()
iniciar_flask()
