import os
import json
import logging
from typing import List, Dict, Any

import torch
from flask import Flask, request, jsonify
from transformers import AutoTokenizer, AutoModelForSequenceClassification

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Resolve model path relative to this script file
MODEL_PATH = os.path.join(os.path.dirname(os.path.abspath(__file__)), "model")

ID2LABEL = {
    0: "negative",
    1: "neutral",
    2: "positive",
}

# ----------------------------------------------------------------
# Load model ONCE at startup — stays in memory until server stops
# ----------------------------------------------------------------
logging.info(f"Loading tokenizer from {MODEL_PATH}...")
tokenizer = AutoTokenizer.from_pretrained(MODEL_PATH)

logging.info(f"Loading model from {MODEL_PATH}...")
model = AutoModelForSequenceClassification.from_pretrained(MODEL_PATH)
model.eval()

logging.info("Model ready. Starting Flask server...")

app = Flask(__name__)


def predict_sentiment(text: str) -> Dict[str, Any]:
    inputs = tokenizer(
        text,
        return_tensors="pt",
        truncation=True,
        padding=True,
        max_length=512,
    )
    with torch.no_grad():
        outputs = model(**inputs)

    probabilities = torch.softmax(outputs.logits, dim=-1).squeeze()
    predicted_class = torch.argmax(probabilities).item()

    return {
        "sentiment_label": ID2LABEL[predicted_class],
        "sentiment_score": round(probabilities[predicted_class].item(), 4),
    }


@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"}), 200


@app.route("/analyze", methods=["POST"])
def analyze():
    data = request.get_json(silent=True)

    if not data or not isinstance(data, list):
        return jsonify({"error": "Expected a JSON array of objects with 'id' and 'text'."}), 400

    results = []
    for i, item in enumerate(data):
        response_id = item.get("id", i)
        text = (item.get("text") or "").strip()

        if not text:
            results.append({"id": response_id, "sentiment_label": "neutral", "sentiment_score": 1.0})
            continue

        try:
            prediction = predict_sentiment(text)
            results.append({"id": response_id, **prediction})
        except Exception as e:
            logging.error(f"Inference failed for id={response_id}: {e}")
            results.append({"id": response_id, "sentiment_label": "parse_error", "sentiment_score": 0.0})

    return jsonify(results), 200


if __name__ == "__main__":
    # Runs on http://127.0.0.1:5000
    app.run(host="127.0.0.1", port=5000, debug=False)