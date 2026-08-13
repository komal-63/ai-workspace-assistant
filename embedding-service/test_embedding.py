from sentence_transformers import SentenceTransformer

model = SentenceTransformer("sentence-transformers/all-MiniLM-L6-v2")

text = "Laravel middleware filters incoming HTTP requests."

embedding = model.encode(text)

print("Vector dimensions:", len(embedding))
print("First 10 values:", embedding[:10])