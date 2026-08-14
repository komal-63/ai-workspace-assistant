from fastapi import FastAPI
from pydantic import BaseModel
from sentence_transformers import SentenceTransformer

app = FastAPI()

model = SentenceTransformer("all-MiniLM-L6-v2")


class EmbedRequest(BaseModel):
    text: str


@app.get("/")
def home():
    return {
        "message": "Embedding service is running"
    }


@app.post("/embed")
def embed(request: EmbedRequest):
    vector = model.encode(request.text).tolist()

    return {
        "vector": vector,
        "dimensions": len(vector)
    }