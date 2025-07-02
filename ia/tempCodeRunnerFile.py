from fastapi import FastAPI, UploadFile, File, Form, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from typing import Optional
import uvicorn
import uuid
import asyncio

app = FastAPI()

# CORS para permitir llamadas desde localhost o desde archivos locales
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # En dev puede ser *, en producción restringir
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Simulación de trabajos almacenados en memoria
jobs = {}

@app.post("/tryon/v1")
async def tryon(
    image_garment_file: UploadFile = File(...),
    image_model_file: UploadFile = File(...),
    garment_type: str = Form(...),
    model_type: str = Form(...),
    repaint_other_garment: Optional[str] = Form("false"),
    x_api_key: Optional[str] = None
):
    # Validar API key opcionalmente
    # Puede validar si se quiere:
    # key_expected = "mi_api_key"
    # if x_api_key != key_expected:
    #    raise HTTPException(status_code=401, detail="Unauthorized")

    # Leer archivos para simular procesamiento (en producción guardaría)
    garment_content = await image_garment_file.read()
    model_content = await image_model_file.read()

    # Generar job_id unico
    job_id = str(uuid.uuid4())
    # Guardar info del trabajo en memoria con estado inicial
    jobs[job_id] = {
        "status": "pending",
        "garment_type": garment_type,
        "model_type": model_type,
        "result_image_url": None,
        # En un caso real se guardaría imagen o ruta procesada
    }

    # Lanzar tarea async simulada que cambiará estado a completed en 10s
    asyncio.create_task(process_job(job_id))

    return JSONResponse(content={"job_id": job_id, "status": "pending"})

@app.get("/requests/v1")
async def get_request(job_id: str, x_api_key: Optional[str] = None):
    # Validar job_id existe
    job = jobs.get(job_id)
    if not job:
        raise HTTPException(status_code=404, detail="Job not found")

    return JSONResponse(content={
        "job_id": job_id,
        "status": job['status'],
        "output": [{"image_url": job["result_image_url"]}] if job['result_image_url'] else [],
    })

async def process_job(job_id):
    # Simulación de procesamiento pesado: espera 10 segundos
    await asyncio.sleep(10)
    # Actualizar status y colocar url de resultado simulado
    jobs[job_id]["status"] = "completed"
    # Como URL simulada usar imagen publicly disponible - placeholder con texto
    jobs[job_id]["result_image_url"] = f"https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/ac030349-e078-4939-a5bb-3c5d1ebdcbdb.png"

if __name__ == "__main__":
    uvicorn.run("local_tryon_server:app", host="0.0.0.0", port=7860, reload=True)

