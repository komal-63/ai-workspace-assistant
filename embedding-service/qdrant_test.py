from sentence_transformers import SentenceTransformer
from qdrant_client import QdrantClient
from qdrant_client.models import PointStruct

model = SentenceTransformer("all-MiniLM-L6-v2")

client = QdrantClient(url="http://localhost:6333")

chunks = [
    {
        "id": 1,
        "document_id": 1,
        "content": "Laravel Sanctum is used for API authentication."
    },
    {
        "id": 2,
        "document_id": 1,
        "content": "Laravel Passport provides OAuth 2.0 authentication for applications."
    },
    {
        "id": 3,
        "document_id": 1,
        "content": "Laravel queues allow long-running tasks to run asynchronously in the background."
    },
    {
        "id": 4,
        "document_id": 1,
        "content": "Laravel caching improves application performance by storing frequently used data."
    },
    {
        "id": 5,
        "document_id": 1,
        "content": "Laravel middleware filters HTTP requests before they reach the controller."
    },
]

points = []

for chunk in chunks:
    vector = model.encode(chunk["content"]).tolist()

    points.append(
        PointStruct(
            id=chunk["id"],
            vector=vector,
            payload={
                "document_id": chunk["document_id"],
                "content": chunk["content"],
            },
        )
    )

client.upsert(
    collection_name="document_chunks",
    points=points,
)

print("5 chunks inserted successfully!")

# Search
question = "What does my uploaded document say about API authentication?"

question_vector = model.encode(question).tolist()

results = client.query_points(
    collection_name="document_chunks",
    query=question_vector,
    limit=3,
).points

print("\nSearch Results:\n")

for result in results:
    print("Score:", result.score)
    print("Content:", result.payload["content"])
    print("--------------------")